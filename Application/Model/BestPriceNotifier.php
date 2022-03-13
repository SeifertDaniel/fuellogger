<?php

namespace Daniels\Benzinlogger\Application\Model;

use Daniels\Benzinlogger\Application\Model\Notifier\NotifierInterface;
use Daniels\Benzinlogger\Application\Model\Notifier\NotifierList;

class BestPriceNotifier
{
    protected $updatePrices = [];

    public function __construct($updatePrices)
    {
        $this->updatePrices = $updatePrices;

        $this->shouldNotify();
    }

    public function shouldNotify()
    {
        $qb = (new BestPrice())->getQueryBuilder();
        $qb->select('pr.price')
            ->andWhere(
                $qb->expr()->lt(
                    'pr.datetime',
                    'NOW() - INTERVAL 1 MINUTE'
                )
            )
            ->setMaxResults(1);

        $lowestUpdatePrice = $this->getLowestUpdatePrice();

        if (isset($lowestUpdatePrice) && $qb->fetchOne() > $lowestUpdatePrice) {
            $this->notify($lowestUpdatePrice);
        }
    }

    protected function getLowestUpdatePrice()
    {
        return count($this->updatePrices) ?
            min(
                array_filter(
                    $this->updatePrices,
                    function ($price) {
                        return (float) $price > 0.0;
                    }
                )
            ) :
            null;
    }

    protected function notify($bestPrice)
    {
        $stationList = $this->getCheapestStationList();

        /** @var NotifierInterface $notifier */
        foreach((new NotifierList())->getList() as $notifier) {
            $notifier->notify(
                'Preisupdate:',
                $bestPrice,
                $stationList
            );
        }
    }

    protected function getCheapestStationList()
    {
        $subQb = (new BestPrice())->getQueryBuilder();

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