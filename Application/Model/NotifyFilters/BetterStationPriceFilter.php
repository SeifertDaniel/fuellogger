<?php

namespace Daniels\FuelLogger\Application\Model\NotifyFilters;

use Daniels\FuelLogger\Application\Model\DBConnection;
use Daniels\FuelLogger\Application\Model\Entities\Price;
use Daniels\FuelLogger\Application\Model\Entities\Station;
use Daniels\FuelLogger\Application\Model\NotifyFilters\Interfaces\AbstractQueryFilter;
use Daniels\FuelLogger\Application\Model\NotifyFilters\Interfaces\ItemFilter;
use Daniels\FuelLogger\Application\Model\NotifyFilters\Interfaces\LowEfficencyFilter;
use Daniels\FuelLogger\Application\Model\PriceUpdates\UpdatesItem;
use Daniels\FuelLogger\Core\Registry;
use Doctrine\DBAL\Exception;

class BetterStationPriceFilter extends AbstractQueryFilter implements ItemFilter, LowEfficencyFilter
{
    public array $priceBeforeUpdateCache = [];

    /**
     * @param UpdatesItem $item
     * @return bool
     * @throws Exception
     */
    public function filterItem(UpdatesItem $item): bool
    {
        startProfile(__METHOD__);

        $betterStationPrice = $this->getPriceBeforeUpdate($item->getStationId());

        $doFilter = $item->getFuelPrice() >= $betterStationPrice;

        if ($doFilter) {
            Registry::getLogger()->debug(get_class($this));
            Registry::getLogger()->debug("price ".$item->getFuelPrice()." is not lower than $betterStationPrice");
        }

        stopProfile(__METHOD__);

        return $doFilter;
    }

    /**
     * @param $stationId
     * @return float
     * @throws Exception
     */
    public function getPriceBeforeUpdate($stationId): float
    {
        startProfile(__METHOD__);

        $em = Registry::getEntityManager();
        $priceTable = $em->getClassMetadata( Price::class)->getTableName();
        $stationTable = $em->getClassMetadata( Station::class)->getTableName();

        $conn = DBConnection::getConnection();
        $qb = $conn->createQueryBuilder();
        $qb->select('pr.price')
            ->from($priceTable, 'pr')
            ->leftJoin('pr', $stationTable, 'st', 'pr.stationid = st.id')
            ->where(
                $qb->expr()->and(
                    'pr.datetime BETWEEN DATE_FORMAT(NOW(), "%Y-%m-%d 00:00:00") AND DATE_FORMAT(DATE_SUB(NOW(), INTERVAL 2 MINUTE), "%Y-%m-%d %H:%i:%s")',
                    'pr.stationid = '.$conn->quote($stationId),
                    $this->getFilterQuery()
                )
            )
            ->orderBy('pr.datetime', 'DESC')
            ->setMaxResults(1);

        $queryHash = md5($qb);

        if (!isset($this->priceBeforeUpdateCache[$queryHash]) || !$this->priceBeforeUpdateCache[$queryHash]) {
            startProfile(__METHOD__.'::notCached');
            $this->priceBeforeUpdateCache[$queryHash] = (float) $qb->fetchOne() ?: 10.0;
            stopProfile(__METHOD__.'::notCached');
        }

        $return = $this->priceBeforeUpdateCache[$queryHash];

        stopProfile(__METHOD__);

        return $return;
    }
}