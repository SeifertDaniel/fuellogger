<?php

namespace Daniels\FuelLogger\Application\Model\NotifyFilters\Interfaces;

abstract class AbstractQueryFilter extends AbstractFilter
{
    protected array $filters = [];

    /**
     * @param string $priceTableAlias
     * @param string $stationTableAlias
     * @return string
     */
    public function getFilterQuery(string $priceTableAlias = 'pr', string $stationTableAlias = 'st'): string
    {
        $queryFilters = $this->hasParentFilter()
            ? $this->getParentFilter()->getQueryFilters()
            : $this->getNotifier()->getQueryFilters();

        return count($queryFilters) ?
            "(".implode(') AND (', array_map(
                function (DatabaseQueryFilter $filter) use ($priceTableAlias, $stationTableAlias) {
                    return $filter->getFilterQuery($priceTableAlias, $stationTableAlias);
                },
                $queryFilters
            )).")" :
            '1';
    }
}