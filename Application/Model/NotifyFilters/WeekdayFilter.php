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
     * @param string $fuelType
     * @param float $price
     *
     * @return bool
     */
    public function canNotifiy(string $fuelType, float $price) : bool
    {
        $currentWeekDay = (new DateTime())->format('N');
        $canNotify = in_array($currentWeekDay, $this->weekdays);

        if (false === $canNotify) {
            $this->setDebugMessage("Weekdays ".implode(', ', $this->weekdays)." do not match $currentWeekDay");
        }

        return $canNotify;
    }
}