<?php

namespace Daniels\FuelLogger\Application\Model\NotifyFilters;

abstract class AbstractFilter
{
    protected bool $isInverted = false;

    protected string $debugMessage = 'no debug message set';

    abstract public function canNotifiy(string $fuelType, float $price): bool;

    /**
     * @return $this
     */
    public function invert(): static
    {
        $this->isInverted = true;

        return $this;
    }

    /**
     * @param string $fuelType
     * @param float  $price
     *
     * @return bool
     */
    public function isNotifiable(string $fuelType, float $price) : bool
    {
        $canNotifiy = $this->canNotifiy($fuelType, $price);

        return $this->isInverted ? !$canNotifiy : $canNotifiy;
    }

    /**
     * @param string $message
     */
    public function setDebugMessage(string $message)
    {
        $this->debugMessage = $message;
    }

    /**
     * @return string
     */
    public function getDebugMessage(): string
    {
        return $this->debugMessage;
    }
}