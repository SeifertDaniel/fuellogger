<?php

namespace Daniels\Benzinlogger\Application\Model\Notifier;

use Daniels\Benzinlogger\Core\Registry;

class DebugNotifier extends AbstractNotifier implements NotifierInterface
{
    public function notify($fuelType, $price, $stations)
    {
        Registry::getLogger()->debug($fuelType .' => '.$price.' => '.$stations);

        return true;
    }
}