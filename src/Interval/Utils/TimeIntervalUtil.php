<?php

namespace Booking\Stdlib\Interval\Utils;


use Booking\Stdlib\Interval\Contract\Interval;
use Booking\Stdlib\Interval\Entity\Interval as IntervalEntity;
use Carbon\Carbon;
use Illuminate\Support\Collection;

/**
 * ToDo Добавть день, когда нужно делать эти точки!
 *
 * Class TimeIntervalUtil
 * @package Booking\Stdlib\Interval\Utils
 */
class TimeIntervalUtil
{

    /**
     *  Получить интервалы с начала дня до первого интервала и от последнего интервала до конца дня
     *
     * (начало дня) (результирующий интервл) (переданые интервалы) (результирующий интервл) (конец дня)
     *      |                 |                   а    |    б                   |                 |
     *      |+++++++++++++++++++++++++++++++++++______   _______++++++++++++++++++++++++++++++++++|
     *
     * @param Collection $sortedIntervals
     * @return Collection
     */
    public static function getDaySpacing(Collection $intervals): Collection
    {
        $sortedIntervals = static::sortIntervals($intervals);
        /**
         * @var Interval $firstInterval
         */
        $firstInterval = $sortedIntervals->first();
        /**
         * @var Interval $lastInterval
         */
        $lastInterval = $sortedIntervals->last();

        $firstDayStart = Carbon::createFromTimestamp(
            $firstInterval->buildStartTimestamp()
        )->hour(0)->minute(0)->getTimestamp();

        $lastDayEnd = Carbon::createFromTimestamp(
            $lastInterval->buildEndTimestamp()
        )->hour(23)->minute(59)->getTimestamp();

        $betweenStartDayTimestamp = new IntervalEntity(
            $firstDayStart,
            $firstInterval->buildStartTimestamp()
        );

        $betweenEndDayTimestamp = new IntervalEntity(
            $lastInterval->buildEndTimestamp(),
            $lastDayEnd
        );


        return collect([$betweenStartDayTimestamp, $betweenEndDayTimestamp]);
    }


    /**
     *  Искать промежутки между интервалами
     *
     *          Входные данные
     *
     *  промежуток А     промежуток Б
     *       |                |
     * --------------   -------------
     *              |   |
     *            Результат
     *              |   |
     *              -----
     *      промежуток между А и Б
     *
     * @param Collection $intervals
     * @param bool $addDaySpacing
     * @return Collection
     */
    public static function inverse(Collection $intervals, bool $addDaySpacing = false): Collection
    {
        $sortedCollection = static::sortIntervals($intervals);
        $resultCollection = new Collection();

        $daySpacingIntervals = null;

        if ($addDaySpacing && $sortedCollection->isNotEmpty()) {
            $daySpacingIntervals = static::getDaySpacing($sortedCollection);
        }

        for ($i = 0; $i < $sortedCollection->count() - 1; $i++) {

            $firstIntervalTimestamp = $sortedCollection->get($i)->buildEndTimestamp();
            $secondIntervalTimestamp = $sortedCollection->get($i + 1)->buildStartTimestamp();
            if ($firstIntervalTimestamp < $secondIntervalTimestamp) {
                $betweenInterval = new IntervalEntity($firstIntervalTimestamp, $secondIntervalTimestamp);
                $resultCollection->push($betweenInterval);
            }
        }

        if ($addDaySpacing) {
            $resultCollection = $resultCollection->merge($daySpacingIntervals);
        }

        return static::sortIntervals($resultCollection);
    }

    /**
     * Отсортировать интервалы
     *
     * @param Collection $intervals
     * @return Collection
     */
    public static function sortIntervals(Collection $intervals): Collection
    {
        $intervalsArray = $intervals->all();
        usort($intervalsArray, function (Interval $intervalA, Interval $intervalB) {
            $timestampA = $intervalA->buildStartTimestamp();
            $timestampB = $intervalB->buildStartTimestamp();
            if ($timestampA == $timestampB) {
                return 0;
            }
            return ($timestampA < $timestampB) ? -1 : 1;
        });

        return collect($intervalsArray);
    }


    /**
     * Установить день по которому будут браться точки
     *
     * @param Collection $timeIntervals
     * @param Interval $dayIntervalObj
     * @return Collection
     */
    public static function setDayToIntervalInCollection(Collection $timeIntervals, Interval $dayIntervalObj)
    {
        return $timeIntervals->map(function (Interval $intervalEntity) use ($dayIntervalObj) {
            $intervalEntity->setDayTimestamp($dayIntervalObj->buildStartTimestamp());
        });
    }


    /**
     * Получение старта и конца дня
     *
     * @param int $timestamp
     * @param int $offset
     * @param int $startHours
     * @param int $endHours
     * @return Interval
     */
    public static function getDayIntervalObject(int $timestamp, int $offset = 0, int $startHours = 0, int $endHours = 23): Interval
    {
        $startTime = Carbon::createFromTimestamp($timestamp)
            ->addDay($offset)
            ->hour($startHours)
            ->minute(0)
            ->getTimestamp();
        $endTime = Carbon::createFromTimestamp($timestamp)
            ->addDay($offset)
            ->hour($endHours)
            ->minute(59)
            ->getTimestamp();

        return new IntervalEntity($startTime, $endTime);
    }

    /**
     * Проверка на то что интервал содержит в себе дугой интервал
     *
     * @param Interval $intervalA
     * @param Interval $intervalB
     * @return bool
     */
    public static function isContains(Interval $intervalA, Interval $intervalB): bool
    {
        $startPoint = $intervalA->buildStartPoint();
        $endPoint = $intervalA->buildEndPoint();


        if (
            $intervalA->buildStartTimestamp() == $intervalB->buildStartTimestamp() &&
            $intervalA->buildEndTimestamp() == $intervalB->buildEndTimestamp()
        ) {
            return true;
        }

        if (
            ($intervalB->buildStartPoint()->between($startPoint, $endPoint, false)) ||
            ($intervalB->buildEndPoint()->between($startPoint, $endPoint, false))
        ) {
            return true;
        }
        return false;
    }

    /**
     * Проверка на пересечение интервалов
     *
     * @param Interval $intervalA
     * @param Interval $intervalB
     * @return bool
     */
    public static function isIntersect(Interval $intervalA, Interval $intervalB): bool
    {
        if (
            TimeIntervalUtil::isContains($intervalA, $intervalB) ||
            TimeIntervalUtil::isContains($intervalB, $intervalA)
        ) {
            return true;
        }

        return false;
    }


    /**
     *    Склейка интервалов внутри коллекции
     *
     *         Входные параметры
     *
     *       Интервал А
     *   |--------------   Интервал Б
     *   |           -------------------|
     *   |                              |
     *   |          Результат           |
     *   |                              |
     *   |       Склееный интервал      |
     *   |------------------------------|
     * @param Collection $intervals
     * @return Collection
     */
    public static function glue(Collection $intervals): Collection
    {
        $sortedIntervals = static::sortIntervals($intervals);

        for ($i = 0; $i < $sortedIntervals->count() - 1; $i++) {

            /** @var Interval $firstInterval */
            $firstInterval = $sortedIntervals->get($i);
            /** @var Interval $secondInterval */
            $secondInterval = $sortedIntervals->get($i + 1);
            if (static::isIntersect($firstInterval, $secondInterval)) {
                $gluedInterval = new IntervalEntity($firstInterval->buildStartTimestamp(), $secondInterval->buildEndTimestamp());
                $sortedIntervals->put($i, $gluedInterval);
                $sortedIntervals->forget($i + 1);
                /** Смещение элементов коллекции */
                $sortedIntervals = new Collection($sortedIntervals->values());
                $i--;
            }
        }

        return $sortedIntervals;

    }

    /**
     *         Обрезать по дню
     *
     *  Начало дня                Конец дня
     *     |                          |
     *     | Интервал А    интервал Б |
     *  ---|------------   -----------|----
     *     |          Результат       |
     *     |------------   -----------|
     *
     * @param Collection $intervals
     * @param integer $dayTimestamp
     * @param int $startHours
     * @param int $endHours
     * @return Collection
     */
    public static function cropByDay(Collection $intervals, int $dayTimestamp, int $startHour = 0, int $endHour = 23): Collection
    {
        $dayInterval = static::getDayIntervalObject($dayTimestamp, 0, $startHour, $endHour);
        $resultCollection = new Collection();

        for ($i = 0; $i < $intervals->count(); $i++) {
            /** @var IntervalEntity $interval */
            $interval = $intervals->get($i);
            $intevalStartPoint = $interval->buildStartPoint();
            $intevalEndPoint = $interval->buildEndPoint();
            $intervalBelongsPassedDay = false;

            /**
             * Проверяем на то что отрезок частично пренадлежит переданному дню
             */
            if (static::isIntersect($dayInterval, $interval)) {

                /**
                 * отрезаем время у интервала если оно пренадлежит прошлому дню
                 */
                $startDay = $dayInterval->buildStartPoint();
                if ($startDay->diffInSeconds($intevalStartPoint, false) < 0) {
                    $intevalStartPoint = $startDay;
                }

                $endDay = $dayInterval->buildEndPoint();
                if ($endDay->diffInSeconds($intevalEndPoint, false) > 0) {
                    $intevalEndPoint = $endDay;
                }

                $resultIntervalEntity = new IntervalEntity(
                    $intevalStartPoint->getTimestamp(),
                    $intevalEndPoint->getTimestamp()
                );

                $resultCollection->push($resultIntervalEntity);
            }

        }

        return $resultCollection;
    }
}