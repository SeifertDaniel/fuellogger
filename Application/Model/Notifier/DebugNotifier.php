<?php

namespace Daniels\FuelLogger\Application\Model\Notifier;

use Daniels\FuelLogger\Application\Model\NotifyFilters\filterPreventsNotificationException;
use Daniels\FuelLogger\Application\Model\PriceUpdates\emptyUpdatesListException;
use Daniels\FuelLogger\Application\Model\PriceUpdates\UpdatesList;
use Daniels\FuelLogger\Core\Registry;

class DebugNotifier extends AbstractNotifier implements NotifierInterface
{
    /**
     * @param UpdatesList $priceUpdates
     * @return bool
     * @throws emptyUpdatesListException
     */
    public function notify(UpdatesList $priceUpdates) : bool
    {
        Registry::getLogger()->debug(__METHOD__.__LINE__);

        $priceUpdates = $this->getFilteredUpdates($priceUpdates);
        // $this->checkForPassedFilters($fuelType, $price);

        Registry::getLogger()->debug(__METHOD__.__LINE__);
        Registry::getLogger()->debug(serialize($priceUpdates));

        return true;
    }
}