<?php

namespace Daniels\Benzinlogger\Application\Model;

use Daniels\Benzinlogger\Application\Model\Notifier\NotifierInterface;
use Daniels\Benzinlogger\Application\Model\Notifier\NotifierList;
use Daniels\Benzinlogger\Core\Registry;

class BestPriceNotifier
{
    protected array $updatePrices = [];

    public function __construct($updatePrices)
    {
        $this->updatePrices = $updatePrices;

        $this->shouldNotify();
    }

    public function shouldNotify()
    {
        foreach (Fuel::getTypes() as $type) {
            if (0 === count($this->updatePrices[$type])) {
                continue;
            }

            $qb = (new BestPrice())->getQueryBuilder($type);
            $qb->select('pr.price')
               ->andWhere(
                   $qb->expr()->eq(
                       'pr.type',
                       $qb->createNamedParameter($type)
                   ),
                   $qb->expr()->lt(
                       'pr.datetime',
                       'NOW() - INTERVAL 1 MINUTE'
                   )
               )
               ->setMaxResults(1);

            $lowestUpdatePrice = $this->getLowestUpdatePrice($type);
            Registry::getLogger()->debug(__METHOD__.__LINE__);
            Registry::getLogger()->debug($type .' => '.$qb->fetchOne().' > '.$lowestUpdatePrice);
            Registry::getLogger()->debug($qb->getSQL());
            Registry::getLogger()->debug($qb->getParameters());
            if (isset($lowestUpdatePrice) && $qb->fetchOne() > $lowestUpdatePrice) {
                $this->notify($lowestUpdatePrice, $type);
            }
        }
    }

    protected function getLowestUpdatePrice($type = Fuel::TYPE_E10)
    {
        return is_array($this->updatePrices) && count($this->updatePrices) && is_array($this->updatePrices[$type]) && count($this->updatePrices[$type]) ?
            min(
                array_filter(
                    $this->updatePrices[$type],
                    function ($price) {
                        return (float) $price > 0.0;
                    }
                )
            ) :
            null;
    }

    protected function notify($bestPrice, $type = Fuel::TYPE_E10)
    {
        $stationList = $this->getCheapestStationList($type);

        /** @var NotifierInterface $notifier */
        foreach((new NotifierList())->getList() as $notifier) {
            $notifier->notify(
                $type,
                $bestPrice,
                $stationList
            );
        }
    }

    protected function getCheapestStationList($type = Fuel::TYPE_E10)
    {
        $subQb = (new BestPrice())->getQueryBuilder($type);

        $conn = DBConnection::getConnection();
        $qb = $conn->createQueryBuilder();
        $qb->select('CONCAT(tmpTbl.name, " (", tmpTbl.place, ")")')
            ->from('('.$subQb->getSQL().')', 'tmpTbl')
            ->where(
                $qb->expr()->eq(
                    'tmpTbl.price',
                    "(".$subQb->select('MIN(pr.price)')->getSQL().")"
                )
            );

        return implode(' + ', $qb->fetchNumeric());
    }
}