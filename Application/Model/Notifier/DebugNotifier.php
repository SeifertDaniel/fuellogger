<?php

namespace Daniels\FuelLogger\Application\Model\Notifier;

use Daniels\FuelLogger\Application\Model\Exceptions\filterPreventsNotificationException;
use Daniels\FuelLogger\Application\Model\PriceUpdates\UpdatesItem;
use Daniels\FuelLogger\Application\Model\PriceUpdates\UpdatesList;
use Daniels\FuelLogger\Core\Registry;
use Doctrine\DBAL\Exception as DoctrineException;

class DebugNotifier extends AbstractNotifier implements NotifierInterface
{
    /**
     * @param UpdatesList $priceUpdates
     *
     * @return bool
     * @throws filterPreventsNotificationException
     */
    public function notify(UpdatesList $priceUpdates) : bool
    {
        startProfile(__METHOD__);

        $this->setUpdateList($priceUpdates);

        Registry::getLogger()->debug(__METHOD__.__LINE__);

        $this->filterUpdates();

        $message = '';
        /** @var UpdatesItem $item */
        foreach ($this->getUpdateList()->getList() as $item) {
            $message .= 'Preis ' . ucfirst($item->getFuelType()) . ': ' . $item->getFuelPrice() . ' ' . $item->getStationName().PHP_EOL;
        }

        Registry::getLogger()->debug(__METHOD__ . __LINE__);
        Registry::getLogger()->debug($message);

        stopProfile(__METHOD__);

        return true;
    }
}