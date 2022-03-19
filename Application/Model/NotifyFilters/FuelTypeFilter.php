<?php

namespace Daniels\Benzinlogger\Application\Model\NotifyFilters;

class FuelTypeFilter extends AbstractFilter
{
    protected $fuelTypes = [];

    public function __construct(array $fuelTypes)
    {
        $this->fuelTypes = $fuelTypes;
    }

    public function canNotifiy($fuelType, float $price) : bool
    {
        return in_array($fuelType, $this->fuelTypes);
    }
}