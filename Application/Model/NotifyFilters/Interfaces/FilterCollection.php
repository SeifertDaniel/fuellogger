<?php

declare(strict_types=1);

namespace Daniels\FuelLogger\Application\Model\NotifyFilters\Interfaces;

use Daniels\FuelLogger\Application\Model\PriceUpdates\UpdatesItem;
use Daniels\FuelLogger\Application\Model\PriceUpdates\UpdatesList;

abstract class FilterCollection extends AbstractFilter implements DatabaseQueryFilter, LowEfficencyFilter
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

    /**
     * @param string $priceTableAlias
     * @param string $stationTableAlias
     * @return string
     */
    public function getFilterQuery(string $priceTableAlias, string $stationTableAlias): string
    {
        return implode(
            ' AND ',
            array_map(
                function (DatabaseQueryFilter $filter) use ($priceTableAlias, $stationTableAlias) {
                    return $filter->getFilterQuery($priceTableAlias, $stationTableAlias);
                },
                $this->getQueryFilters()
            )
        );
    }

    /**
     * @return array
     */
    public function getQueryFilters(): array
    {
        $queryFilters = [];

        /** @var AbstractFilter $filter */
        foreach ($this->getFilterList() as $filter) {
            $filter->setParentFilter($this);
            if ($filter instanceof DatabaseQueryFilter) {
                $queryFilters[] = $filter;
            }
        }

        return $queryFilters;
    }
}