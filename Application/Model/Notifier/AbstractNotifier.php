<?php

namespace Daniels\FuelLogger\Application\Model\Notifier;

use Daniels\FuelLogger\Application\Model\NotifyFilters\AbstractFilter;
use Daniels\FuelLogger\Core\Registry;

abstract class AbstractNotifier implements NotifierInterface
{
    protected array $filters = [];

    public function addFilter(AbstractFilter $filter): self
    {
        $this->filters[] = $filter;

        return $this;
    }

    public function getFilters()
    {
        return $this->filters;
    }

    /**
     * @param $fuelType
     * @param float $price
     * @return bool
     */
    public function canNotify($fuelType, float $price)
    {
        return false === in_array(
            false,
            array_map(
                function (AbstractFilter $filter) use ($fuelType, $price) {
                    return $filter->isNotifiable($fuelType, $price);
                },
                $this->getFilters()
            )
        );
    }
}