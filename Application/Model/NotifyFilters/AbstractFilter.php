<?php

namespace Daniels\FuelLogger\Application\Model\NotifyFilters;

abstract class AbstractFilter
{
    protected bool $isInverted = false;

    abstract public function canNotifiy($fuelType, float $price): bool;

    public function invert()
    {
        $this->isInverted = true;

        return $this;
    }

    public function isNotifiable($fuelType, float $price) : bool
    {
        $canNotifiy = $this->canNotifiy($fuelType, $price);

        return $this->isInverted ? !$canNotifiy : $canNotifiy;
    }
}