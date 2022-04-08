<?php

namespace Daniels\FuelLogger\Application\Model\NotifyFilters\Interfaces;

interface DatabaseQueryFilter
{
    public function getFilterQuery(string $priceTableAlias, string $stationTableAlias) : string;
}