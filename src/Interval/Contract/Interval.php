<?php

namespace Booking\Stdlib\Interval\Contract;

use Carbon\Carbon;
use Illuminate\Support\Collection;

interface Interval
{
    /**
     * Получить начальную точку времени
     *
     * @param int|null $dayTimestamp
     * @return Carbon
     */
    public function buildStartPoint(int $dayTimestamp = null): Carbon;

    /**
     * Получить конечную точку точку времени
     *
     * @param int|null $dayTimestamp
     * @return Carbon
     */
    public function buildEndPoint(int $dayTimestamp = null): Carbon;

    /**
     * День, в который будут проставлены точки, если не переданы starttime и endtime
     *
     * @param int|null $dayTimestamp
     */
    public function setDayTimestamp(?int $dayTimestamp);

    /**
     * Получить начальную точку времени
     *
     * @param int|null $dayTimestamp
     * @return int
     */
    public function buildStartTimestamp(int $dayTimestamp = null): int;

    /**
     * Получить конечную точку точку времени
     *
     * @param int|null $dayTimestamp
     * @return int
     */
    public function buildEndTimestamp(int $dayTimestamp = null): int;



    /**
     * Проверка на пересечение интервалов
     *
     * @param Collection $intervals
     * @return bool
     */
    public function hasIntervals(Collection $intervals): bool;

}