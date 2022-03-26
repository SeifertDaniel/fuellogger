<?php

namespace Daniels\FuelLogger\Application\Model\NotifyFilters;

use DateTime;

class WeekdayFilter extends AbstractFilter
{
    const MONDAY = 1;
    const TUESDAY = 2;
    const WEDNESDAY = 3;
    const THURSDAY = 4;
    const FRIDAY = 5;
    const SATURDAY = 6;
    const SUNDAY = 7;

    protected array $weekdays = [];

    /**
     * @param array $weekdays
     */
    public function __construct(array $weekdays)
    {
        $this->weekdays = $weekdays;
    }

    /**
     * @param       $fuelType
     * @param float $price
     *
     * @return bool
     */
    public function canNotifiy($fuelType, float $price) : bool
    {
        return in_array((new DateTime())->format('N'), $this->weekdays);
    }
}