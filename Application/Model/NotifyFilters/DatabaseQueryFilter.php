<?php

namespace Daniels\FuelLogger\Application\Model\NotifyFilters;

interface DatabaseQueryFilter
{
    public function getFilterQuery(string $priceTableAlias) : string;
}