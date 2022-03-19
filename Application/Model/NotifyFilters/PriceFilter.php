<?php

namespace Daniels\Benzinlogger\Application\Model\NotifyFilters;

class PriceFilter extends AbstractFilter
{
    public $from;
    public $till;

    public function __construct(float $from, float $till)
    {
        $this->from = $from;
        $this->till = $till;
    }

    public function canNotifiy($fuelType, float $price): bool
    {
        return $this->from <= $price && $price <= $this->till;
    }
}