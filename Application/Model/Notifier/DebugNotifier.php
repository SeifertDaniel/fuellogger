<?php

namespace Daniels\FuelLogger\Application\Model\Notifier;

use Daniels\FuelLogger\Application\Model\Exceptions\filterPreventsNotificationException;
use Daniels\FuelLogger\Application\Model\PriceUpdates\UpdatesList;
use Daniels\FuelLogger\Core\Registry;
use Doctrine\DBAL\Exception as DoctrineException;

class DebugNotifier extends AbstractNotifier implements NotifierInterface
{
    /**
     * @param UpdatesList $priceUpdates
     *
     * @return bool
     * @throws DoctrineException
     * @throws filterPreventsNotificationException
     */
    public function notify(UpdatesList $priceUpdates) : bool
    {
        Registry::getLogger()->debug(__METHOD__.__LINE__);

        $priceUpdates = $this->getFilteredUpdates($priceUpdates);

        Registry::getLogger()->debug(__METHOD__.__LINE__);
        Registry::getLogger()->debug(serialize($priceUpdates));

        return true;
    }
}