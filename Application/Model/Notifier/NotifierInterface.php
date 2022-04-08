<?php

namespace Daniels\FuelLogger\Application\Model\Notifier;

use Daniels\FuelLogger\Application\Model\Exceptions\filterPreventsNotificationException;
use Daniels\FuelLogger\Application\Model\PriceUpdates\UpdatesList;

interface NotifierInterface
{
    /**
     * @param UpdatesList $priceUpdates
     * @return bool
     * @throws filterPreventsNotificationException
     */
    public function notify(UpdatesList $priceUpdates) : bool;
}