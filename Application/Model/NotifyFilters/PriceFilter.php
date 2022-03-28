<?php

namespace Daniels\FuelLogger\Application\Model\NotifyFilters;

class PriceFilter extends AbstractFilter
{
    public float $from;
    public float $till;

    /**
     * @param float $from
     * @param float $till
     */
    public function __construct(float $from, float $till)
    {
        $this->from = $from;
        $this->till = $till;
    }

    /**
     * @param string $fuelType
     * @param float $price
     *
     * @return bool
     */
    public function canNotifiy(string $fuelType, float $price): bool
    {
        return $this->from <= $price && $price <= $this->till;
    }
}