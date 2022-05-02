<?php

declare(strict_types=1);

namespace Daniels\FuelLogger\Application\Model\NotifyFilters\Interfaces;

interface DatabaseQueryFilter
{
    public function getFilterQuery(string $priceTableAlias, string $stationTableAlias) : string;
}