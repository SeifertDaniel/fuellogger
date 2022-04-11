<?php

namespace Daniels\FuelLogger\Application\Model\NotifyFilters\Interfaces;

use Daniels\FuelLogger\Application\Model\PriceUpdates\UpdatesItem;
use Daniels\FuelLogger\Application\Model\PriceUpdates\UpdatesList;

abstract class FilterCollection extends AbstractFilter implements LowEfficencyFilter
{
    abstract public function getFilterList(): array;

    /** @var AbstractFilter */
    public AbstractFilter $currentFilter;

    /**
     * @return bool
     */
    public function isItemFilter(): bool
    {
        return $this->currentFilter instanceof ItemFilter;
    }

    public function filterPriceUpdates(UpdatesList $priceUpdates): UpdatesList
    {
        /** @var AbstractFilter $filter */
        foreach ($this->sortFiltersByEfficency($this->getFilterList()) as $filter) {
            $filter->setNotifier($this->getNotifier());
            $this->currentFilter = $filter;

            $priceUpdates = $filter->filterPriceUpdates($priceUpdates);
        }

        return $priceUpdates;
    }

    /**
     * @param UpdatesItem $item
     * @return bool
     */
    public function filterItem(UpdatesItem $item): bool
    {
        return true;
    }

    /**
     * @param $filters
     * @return array
     */
    public function sortFiltersByEfficency($filters): array
    {
        $highEfficency = [];
        $mediumEfficency = [];
        $lowEfficency = [];

        foreach ($filters as $filter) {
            switch (true) {
                case $filter instanceof HighEfficencyFilter:
                    $highEfficency[] = $filter;
                    break;
                case $filter instanceof MediumEfficencyFilter:
                    $mediumEfficency[] = $filter;
                    break;
                default:
                    $lowEfficency[] = $filter;
            }
        }

        return array_merge($highEfficency, $mediumEfficency, $lowEfficency);
    }
}