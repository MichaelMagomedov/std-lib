<?php

namespace Booking\Stdlib\Interval\Entity;

use Booking\Stdlib\Interval\Contract\Interval as Contract;
use Booking\Stdlib\Traits\Strategy\IntervalDefaultStrategy;
use Carbon\Carbon;
use JsonSerializable;
use Structure\Base\Entity\Traits\EntityJsonSerializable;

class Interval implements Contract,JsonSerializable
{
    use IntervalDefaultStrategy,EntityJsonSerializable;

    /**
     * @var int
     */
    protected $startTime;

    /**
     * @var int
     */
    protected $endTime;

    /**
     * Interval constructor.
     * @param int $startTime
     * @param int $endTime
     */
    public function __construct(int $startTime, int $endTime)
    {
        $this->startTime = $startTime;
        $this->endTime = $endTime;
    }

}