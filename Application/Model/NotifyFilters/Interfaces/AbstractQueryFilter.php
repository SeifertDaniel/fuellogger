<?php

namespace Daniels\FuelLogger\Application\Model\NotifyFilters\Interfaces;

abstract class AbstractQueryFilter extends AbstractFilter
{
    protected array $filters = [];

    /**
     * @param DatabaseQueryFilter $filter
     * @return $this
     */
    public function addQueryFilter(DatabaseQueryFilter $filter): self
    {
        $this->filters[] = $filter;

        return $this;
    }

    /**
     * @return array
     */
    public function getQueryFilters(): array
    {
        return $this->filters;
    }
}