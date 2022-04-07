<?php

namespace Daniels\FuelLogger\Application\Model\NotifyFilters;

use Daniels\FuelLogger\Application\Model\PriceUpdates\UpdatesItem;
use Daniels\FuelLogger\Core\Registry;

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

    public function filterItem(UpdatesItem $item): bool
    {
        $canNotify = $this->from <= $item->getFuelPrice() && $item->getFuelType() <= $this->till;

        if (false === $canNotify) {
            Registry::getLogger()->debug(get_class($this));
            Registry::getLogger()->debug("price ".$item->getFuelPrice()." is not between $this->from and $this->till");
        }

        return !$canNotify;
    }
}