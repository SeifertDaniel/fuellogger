<?php

declare(strict_types=1);

namespace Daniels\FuelLogger\Application\Model\NotifyFilters\Collections;

use Daniels\FuelLogger\Application\Model\Fuel;
use Daniels\FuelLogger\Application\Model\NotifyFilters\FuelTypeFilter;
use Daniels\FuelLogger\Application\Model\NotifyFilters\Interfaces\FilterCollection;
use Daniels\FuelLogger\Application\Model\NotifyFilters\PostCodeFilter;
use Daniels\FuelLogger\Application\Model\NotifyFilters\PriceInLowest;
use Daniels\FuelLogger\Application\Model\NotifyFilters\TimeFilter;

class StlE10GoodPriceCollection extends FilterCollection
{
    /**
     * @return array
     */
    public function getFilterList(): array
    {
        return [
            new TimeFilter('08:00:00', '22:00:00'),
            new FuelTypeFilter([Fuel::TYPE_E10]),
            new PostCodeFilter(['09366']),
            new PriceInLowest(PriceInLowest::HALF)
        ];
    }
}