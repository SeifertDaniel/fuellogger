<?php

namespace Daniels\FuelLogger\Application\Model\Notifier;

use Daniels\FuelLogger\Application\Model\NotifyFilters\filterPreventsNotificationException;

interface NotifierInterface
{
    /**
     * @param string $fuelType
     * @param float  $price
     * @param string $stations
     *
     * @throws filterPreventsNotificationException
     * @return bool
     */
    public function notify(string $fuelType, float $price, string $stations) : bool;
}