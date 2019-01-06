<?php

namespace Booking\Stdlib\Traits\Strategy;

use Booking\Stdlib\Interval\Contract\Interval;
use Booking\Stdlib\Interval\Utils\TimeIntervalUtil;
use Carbon\Carbon;
use Illuminate\Support\Collection;

trait IntervalDefaultStrategy
{

    /**
     * @var int
     */
    protected $startHour;

    /**
     * @var int
     */
    protected $startMinute;

    /**
     * @var int
     */
    protected $endHour;

    /**
     * @var int
     */
    protected $endMinute;

    /**
     * @var int
     */
    protected $startTime;

    /**
     * @var int
     */
    protected $endTime;

    /**
     * @var int
     */
    private $dayTimestamp;

    /**
     * Получить timestamp из даты
     *
     * @param int $hour
     * @param int $minute
     * @param int|null $day
     * @return Carbon
     */
    protected function createFromPoints(int $hour, int $minute, int $day = null): Carbon
    {
        if (empty($day)) {
            $day = $this->dayTimestamp;
        }

        return Carbon::createFromTimestamp($day)->hour($hour)->minute($minute);
    }

    /**
     * Получить начальную точку времени
     *
     * @param int|null $dayTimestamp
     * @return Carbon
     */
    public function buildStartPoint(int $dayTimestamp = null): Carbon
    {
        if (isset($this->startHour) && isset($this->startMinute)) {
            return $this->createFromPoints($this->startHour, $this->startMinute, $dayTimestamp);
        }
        return Carbon::createFromTimestamp($this->startTime);
    }

    /**
     * Получить конечную точку точку времени
     *
     * @param int|null $dayTimestamp
     * @return Carbon
     */
    public function buildEndPoint(int $dayTimestamp = null): Carbon
    {
        $endPoint = null;
        if (isset($this->endHour) && isset($this->endMinute)) {
            $endPoint = $this->createFromPoints($this->endHour, $this->endMinute, $dayTimestamp);
        } else {
            $endPoint = Carbon::createFromTimestamp($this->endTime);
        }
        /**
         * Если конечное значение меньше начального то +1 день.
         */
        $startPoint = $this->buildStartTimestamp($dayTimestamp);
        if ($startPoint >= $endPoint->getTimestamp()) {
            return $endPoint->addDay();
        }
        return $endPoint;

    }

    /**
     * @param int|null $dayTimestamp
     * @return int
     */
    public function buildStartTimestamp(int $dayTimestamp = null): int
    {
        return $this->buildStartPoint($dayTimestamp)->getTimestamp();
    }

    /**
     * @param int|null $dayTimestamp
     * @return int
     */
    public function buildEndTimestamp(int $dayTimestamp = null): int
    {
        return $this->buildEndPoint($dayTimestamp)->getTimestamp();
    }

    /**
     * День, в который будут проставлены точки, если не переданы starttime и endtime
     *
     * @param int|null $dayTimestamp
     */
    public function setDayTimestamp(?int $dayTimestamp)
    {
        $this->dayTimestamp = $dayTimestamp;
    }

    /**
     * Проверка на пересечение интервалов
     *
     * @param Collection $intervals
     * @return bool
     */
    public function hasIntervals(Collection $intervals): bool
    {
        /**
         * @var Interval $interval
         */
        foreach ($intervals as $interval) {

            if (
                TimeIntervalUtil::isIntersect($this, $interval)
            ) {
                return true;
            }
        }
        return false;
    }


}