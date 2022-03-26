<?php

namespace Daniels\FuelLogger\Application\Model\NotifyFilters;

class FuelTypeFilter extends AbstractFilter
{
    protected array $fuelTypes = [];

    /**
     * @param array $fuelTypes
     */
    public function __construct(array $fuelTypes)
    {
        $this->fuelTypes = $fuelTypes;
    }

    /**
     * @param       $fuelType
     * @param float $price
     *
     * @return bool
     */
    public function canNotifiy($fuelType, float $price) : bool
    {
        return in_array($fuelType, $this->fuelTypes);
    }
}