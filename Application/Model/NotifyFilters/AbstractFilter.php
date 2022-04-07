<?php

namespace Daniels\FuelLogger\Application\Model\NotifyFilters;

use Daniels\FuelLogger\Application\Model\PriceUpdates\UpdatesItem;
use Daniels\FuelLogger\Application\Model\PriceUpdates\UpdatesList;

abstract class AbstractFilter
{
    protected bool $isInverted = false;

    protected string $debugMessage = 'no debug message set';

    abstract public function filterItem(UpdatesItem $item): bool;

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

    public function filterPriceUpdates(UpdatesList $priceUpdates): UpdatesList
    {
        /** @var UpdatesItem $priceUpdate */
        foreach ($priceUpdates->getList() as $id => $priceUpdate) {
            $filtered = $this->filterItem($priceUpdate);
            $filtered = $this->isInverted ? !$filtered : $filtered;
            if ($filtered) {
                $priceUpdates->remove($id);
            }
        }

        return $priceUpdates;
    }
}