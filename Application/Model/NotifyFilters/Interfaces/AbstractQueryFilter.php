<?php

namespace Daniels\FuelLogger\Application\Model\NotifyFilters\Interfaces;

abstract class AbstractQueryFilter extends AbstractFilter
{
    protected array $filters = [];

    /**
     * @param DatabaseQueryFilter $filter
     * @return $this
     */
/*
    public function addQueryFilter(DatabaseQueryFilter $filter): self
    {
        $this->filters[] = $filter;

        return $this;
    }
*/
    /**
     * @return array
     */
/*
    public function getQueryFilters(): array
    {
        return $this->filters;
    }
*/

    /**
     * @param string $priceTableAlias
     * @param string $stationTableAlias
     * @return string
     */
    public function getFilterQuery(string $priceTableAlias = 'pr', string $stationTableAlias = 'st'): string
    {
        return count($this->getNotifier()->getQueryFilters()) ?
            "(".implode(') AND (', array_map(
                function (DatabaseQueryFilter $filter) use ($priceTableAlias, $stationTableAlias) {

                    return $filter->getFilterQuery($priceTableAlias, $stationTableAlias);
                },
                $this->getNotifier()->getQueryFilters()
            )).")" :
            '1';
    }
}