<?php

namespace Daniels\FuelLogger\Application\Model\Notifier;

use Daniels\FuelLogger\Application\Model\NotifyFilters\filterPreventsNotificationException;
use Daniels\FuelLogger\Core\Registry;

class DebugNotifier extends AbstractNotifier implements NotifierInterface
{
    /**
     * @param string $fuelType
     * @param float $price
     * @param string $stations
     * @return bool
     * @throws filterPreventsNotificationException
     */
    public function notify(string $fuelType, float $price, string $stations) : bool
    {
        Registry::getLogger()->debug(__METHOD__.__LINE__);

        $this->checkForPassedFilters($fuelType, $price);

        Registry::getLogger()->debug(__METHOD__.__LINE__);
        Registry::getLogger()->debug($fuelType .' => '.$price.' => '.$stations);

        return true;
    }
}