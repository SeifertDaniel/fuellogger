<?php

namespace Daniels\FuelLogger\Application\Model\Notifier;

interface NotifierInterface
{
    public function notify($fuelType, $price, $stations);
}