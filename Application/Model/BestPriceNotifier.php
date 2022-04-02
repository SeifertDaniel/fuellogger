<?php

namespace Daniels\FuelLogger\Application\Model;

use Daniels\FuelLogger\Application\Model\Notifier\NotifierInterface;
use Daniels\FuelLogger\Application\Model\Notifier\NotifierList;
use Daniels\FuelLogger\Application\Model\NotifyFilters\filterPreventsNotificationException;
use Daniels\FuelLogger\Core\Registry;
use Doctrine\DBAL\Exception;

class BestPriceNotifier
{
    protected array $updatePrices = [];

    /**
     * @param array $updatePrices
     * @throws Exception
     */
    public function __construct(array $updatePrices)
    {
        startProfile(__METHOD__);

        $this->updatePrices = $updatePrices;

        $this->shouldNotify();

        stopProfile(__METHOD__);
    }

    /**
     * @return void
     * @throws Exception
     */
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
                       'NOW() - INTERVAL 2 MINUTE'
                   )
               )
               ->setMaxResults(1);

            $lowestUpdatePrice = $this->getLowestUpdatePrice($type);

            if (isset($lowestUpdatePrice) && $qb->fetchOne() > $lowestUpdatePrice) {
                $this->notify($lowestUpdatePrice, $type);
            }
        }
    }

    /**
     * @param string $type
     * @return mixed|null
     */
    protected function getLowestUpdatePrice(string $type = Fuel::TYPE_E10): mixed
    {
        return count($this->updatePrices) && is_array($this->updatePrices[$type]) && count($this->updatePrices[$type]) ?
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

    /**
     * @param $bestPrice
     * @param string $type
     * @return void
     * @throws Exception
     */
    protected function notify($bestPrice, string $type = Fuel::TYPE_E10)
    {
        startProfile(__METHOD__);

        $stationList = $this->getCheapestStationList($type);

        /** @var NotifierInterface $notifier */
        foreach((new NotifierList())->getList() as $notifier) {
            try {
                $notifier->notify( $type, $bestPrice, $stationList );
            } catch (filterPreventsNotificationException $e) {
                Registry::getLogger()->debug($e->getMessage());
            }
        }

        stopProfile(__METHOD__);
    }

    /**
     * @param string $type
     * @return string
     * @throws Exception
     */
    protected function getCheapestStationList(string $type = Fuel::TYPE_E10): string
    {
        $subQb = (new BestPrice())->getQueryBuilder($type);

        $conn = DBConnection::getConnection();
        $qb = $conn->createQueryBuilder();
        $qb->select('CONCAT(tmpTbl.name, " (", tmpTbl.place, ")")')
            ->from('('.$subQb->getSQL().')', 'tmpTbl')
            ->where(
                $qb->expr()->eq(
                    'tmpTbl.price',
                    "(".$subQb->select('MIN(pr.price)')->setMaxResults(1)->getSQL().")"
                )
            );

        return implode(' + ', $qb->fetchNumeric());
    }
}