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
     * @param string $fuelType
     * @param float $price
     *
     * @return bool
     */
    public function canNotifiy(string $fuelType, float $price) : bool
    {
        return in_array($fuelType, $this->fuelTypes);
    }
}