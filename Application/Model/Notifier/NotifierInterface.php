<?php

namespace Daniels\FuelLogger\Application\Model\Notifier;

interface NotifierInterface
{
    public function notify(string $fuelType, float $price, string $stations) : bool;
}