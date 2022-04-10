<?php

namespace Daniels\FuelLogger\Application\Model\NotifyFilters;

use Daniels\FuelLogger\Application\Model\DBConnection;
use Daniels\FuelLogger\Application\Model\NotifyFilters\Interfaces\AbstractQueryFilter;
use Daniels\FuelLogger\Application\Model\NotifyFilters\Interfaces\DatabaseQueryFilter;
use Daniels\FuelLogger\Application\Model\NotifyFilters\Interfaces\ItemFilter;
use Daniels\FuelLogger\Application\Model\NotifyFilters\Interfaces\LowEfficencyFilter;
use Daniels\FuelLogger\Application\Model\Price;
use Daniels\FuelLogger\Application\Model\PriceUpdates\UpdatesItem;
use Daniels\FuelLogger\Application\Model\Station;
use Daniels\FuelLogger\Core\Registry;
use Doctrine\DBAL\Exception;

class DailyBestPriceFilter extends AbstractQueryFilter implements ItemFilter, LowEfficencyFilter
{
    public array $bestPriceCache = [];

    /**
     * @param UpdatesItem $item
     * @return bool
     * @throws Exception
     */
    public function filterItem(UpdatesItem $item): bool
    {
        startProfile(__METHOD__);

        Registry::getLogger()->debug(__METHOD__);

        $dailyBestPriceBeforeUpdate = $this->getBestPriceBeforeUpdate();
        $lowestUpdatePrice = $this->getNotifier()->getUpdateList()->getLowestPrice();

        $doFilter = $item->getFuelPrice() >= $dailyBestPriceBeforeUpdate ||
            $item->getFuelPrice() > $lowestUpdatePrice;

        if ($doFilter) {
            Registry::getLogger()->debug(get_class($this));
            Registry::getLogger()->debug("price ".$item->getFuelPrice()." is not lower than $dailyBestPriceBeforeUpdate");
        }

        stopProfile(__METHOD__);

        return $doFilter;
    }

    /**
     * @return float
     * @throws Exception
     */
    public function getBestPriceBeforeUpdate(): float
    {
        startProfile(__METHOD__);

        $qb = DBConnection::getConnection()->createQueryBuilder();
        $qb->select('pr.price')
            ->from((new Price())->getCoreTableName(), 'pr')
            ->leftJoin('pr', (new Station())->getCoreTableName(), 'st', 'pr.stationid = st.id')
            ->where(
                $qb->expr()->and(
                    'pr.datetime BETWEEN DATE_FORMAT(NOW(), "%Y-%m-%d 00:00:00") AND DATE_FORMAT(DATE_SUB(NOW(), INTERVAL 2 MINUTE), "%Y-%m-%d %H:%i:%s")',
                    $this->getFilterQuery()
                )
            )
            ->orderBy('pr.price', 'ASC')
            ->setMaxResults(1);

        $queryHash = md5($qb);

        if (!isset($this->bestPriceCache[$queryHash]) || !$this->bestPriceCache[$queryHash]) {
            startProfile(__METHOD__.'::notCached');
            $this->bestPriceCache[$queryHash] = (float) $qb->fetchOne() ?: 10.0;
            stopProfile(__METHOD__.'::notCached');
        }

        $return = $this->bestPriceCache[$queryHash];

        stopProfile(__METHOD__);

        return $return;
    }
}