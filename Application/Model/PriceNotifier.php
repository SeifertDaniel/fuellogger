<?php

namespace Daniels\FuelLogger\Application\Model;

use Daniels\FuelLogger\Application\Model\Exceptions\filterPreventsNotificationException;
use Daniels\FuelLogger\Application\Model\Notifier\NotifierInterface;
use Daniels\FuelLogger\Application\Model\Notifier\NotifierList;
use Daniels\FuelLogger\Application\Model\PriceUpdates\UpdatesItem;
use Daniels\FuelLogger\Application\Model\PriceUpdates\UpdatesList;
use Daniels\FuelLogger\Core\Registry;
use Doctrine\DBAL\Exception;

class PriceNotifier
{
    protected UpdatesList $updatePrices;

    /**
     * @param UpdatesList $updatePrices
     * @throws Exception
     */
    public function __construct(UpdatesList $updatePrices)
    {
        startProfile(__METHOD__);

        Registry::getLogger()->debug(__METHOD__);

        $this->updatePrices = $updatePrices;

        $this->sortUpdateItemsByPrice();

        $this->notify();

        stopProfile(__METHOD__);
    }

    public function sortUpdateItemsByPrice()
    {
        startProfile(__METHOD__);

        $updatePriceList = $this->updatePrices->getList();

        usort(
            $updatePriceList,
            function ($a, $b) {
                /** @var $a UpdatesItem */
                /** @var $b UpdatesItem */
                return strcmp($a->getFuelPrice(), $b->getFuelPrice());
            }
        );

        $this->updatePrices->setList($updatePriceList);

        stopProfile(__METHOD__);
    }

    /**
     * @return void
     */
    protected function notify()
    {
        startProfile(__METHOD__);

        Registry::getLogger()->debug(__METHOD__);

        /** @var NotifierInterface $notifier */
        foreach((new NotifierList())->getList() as $notifier) {
            try {
                Registry::getLogger()->debug(__METHOD__);
                $notifier->notify( clone $this->updatePrices );
            } catch (filterPreventsNotificationException $e) {
                Registry::getLogger()->debug($e->getMessage());
            }
        }

        stopProfile(__METHOD__);
    }
}