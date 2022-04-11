<?php

namespace Daniels\FuelLogger\Application\Model\NotifyFilters\Collections;

use Daniels\FuelLogger\Application\Model\Fuel;
use Daniels\FuelLogger\Application\Model\NotifyFilters\DailyBestPriceFilter;
use Daniels\FuelLogger\Application\Model\NotifyFilters\FuelTypeFilter;
use Daniels\FuelLogger\Application\Model\NotifyFilters\Interfaces\FilterCollection;
use Daniels\FuelLogger\Application\Model\NotifyFilters\StationIdFilter;
use Daniels\FuelLogger\Application\Model\NotifyFilters\TimeFilter;
use Daniels\FuelLogger\Application\Model\NotifyFilters\WeekdayFilter;

class TlhE10AviaWorkDay extends FilterCollection
{
    /**
     * @return array
     */
    public function getFilterList(): array
    {
        return [
            new TimeFilter('11:00:00', '16:30:00'),
            new FuelTypeFilter([Fuel::TYPE_E10]),
            new StationIdFilter(['d4e7bc0c-54f9-4e1a-8463-4a913e26adee']),
            new DailyBestPriceFilter(),
            new WeekdayFilter([
                WeekdayFilter::MONDAY,
                WeekdayFilter::TUESDAY,
                WeekdayFilter::WEDNESDAY,
                WeekdayFilter::THURSDAY,
                WeekdayFilter::FRIDAY
            ])
        ];
    }
}