<?php

namespace Daniels\Benzinlogger\Application\Model\Notifier;

interface NotifierInterface
{
    public function notify($fuelType, $price, $stations);
}