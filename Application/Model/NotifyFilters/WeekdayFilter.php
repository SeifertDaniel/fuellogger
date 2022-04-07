<?php

namespace Daniels\FuelLogger\Application\Model\NotifyFilters;

use Daniels\FuelLogger\Application\Model\PriceUpdates\UpdatesItem;
use Daniels\FuelLogger\Core\Registry;
use DateTime;

class WeekdayFilter extends AbstractFilter implements GlobalFilter
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
     * @param UpdatesItem $item
     * @return bool
     */
    public function filterItem(UpdatesItem $item): bool
    {
        $currentWeekDay = (new DateTime())->format('N');
        $canNotify = in_array($currentWeekDay, $this->weekdays);

        if (false === $canNotify) {
            Registry::getLogger()->debug(get_class($this));
            Registry::getLogger()->debug("Weekdays ".implode(', ', $this->weekdays)." do not match $currentWeekDay");
        }

        return !$canNotify;
    }
}