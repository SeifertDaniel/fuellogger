<?php

namespace Daniels\FuelLogger\Application\Model\Notifier;

use Daniels\FuelLogger\Core\Registry;

class DebugNotifier extends AbstractNotifier implements NotifierInterface
{
    public function notify($fuelType, $price, $stations)
    {
        Registry::getLogger()->debug(__METHOD__.__LINE__);

        if (false === $this->canNotify($fuelType, $price)) {
            Registry::getLogger()->debug(__METHOD__.__LINE__);
            return false;
        }

        Registry::getLogger()->debug(__METHOD__.__LINE__);
        Registry::getLogger()->debug($fuelType .' => '.$price.' => '.$stations);

        return true;
    }
}