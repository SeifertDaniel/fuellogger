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
use Doctrine\ORM\ORMException;

class PriceInLowest extends AbstractQueryFilter implements ItemFilter, LowEfficencyFilter
{
    const HALF = 0.5;
    const THIRD = 0.33;
    const QUARTER = 0.25;
    const TENPERCENT = 0.1;

    private float $subsection;

    private array $priceCache = [];

    /**
     * @param float $subsection
     */
    public function __construct(float $subsection)
    {
        $this->subsection = $subsection;
    }

    /**
     * @param UpdatesItem $item
     *
     * @return bool
     * @throws Exception
     * @throws ORMException
     */
    public function filterItem(UpdatesItem $item): bool
    {
        startProfile(__METHOD__);

        Registry::getLogger()->debug(__METHOD__);

        $highestSubsectionPriceBeforeUpdate = $this->getHighestSubsectionPriceBeforeUpdate();
        $lowestUpdatePrice = $this->getNotifier()->getUpdateList()->getLowestPrice();

        $doFilter = $item->getFuelPrice() >= $highestSubsectionPriceBeforeUpdate ||
            $item->getFuelPrice() > $lowestUpdatePrice;

        if ($doFilter) {
            Registry::getLogger()->debug(get_class($this));
            Registry::getLogger()->debug("price ".$item->getFuelPrice()." is not lower than $highestSubsectionPriceBeforeUpdate");
        }

        stopProfile(__METHOD__);

        return $doFilter;
    }

    /**
     * @return float
     * @throws Exception
     * @throws ORMException
     */
    public function getHighestSubsectionPriceBeforeUpdate(): float
    {
        startProfile(__METHOD__);

        $em = Registry::getEntityManager();
        $priceTable = $em->getClassMetadata( Price::class)->getTableName();
        $stationTable = $em->getClassMetadata( Station::class)->getTableName();

        $qb = DBConnection::getConnection()->createQueryBuilder();
        $qb->select('MIN(pr.price) + ((MAX(pr.price) - MIN(pr.price)) * '.$this->subsection.')')
            ->from($priceTable, 'pr')
            ->leftJoin('pr', $stationTable, 'st', 'pr.stationid = st.id')
            ->where(
                $qb->expr()->and(
                    'pr.datetime BETWEEN DATE_FORMAT(NOW(), "%Y-%m-%d 00:00:00") AND DATE_FORMAT(DATE_SUB(NOW(), INTERVAL 2 MINUTE), "%Y-%m-%d %H:%i:%s")',
                    $this->getFilterQuery()
                )
            )
            ->orderBy('pr.price', 'ASC')
            ->setMaxResults(1);

        Registry::getLogger()->debug($qb->getSQL());
        $queryHash = md5($qb);

        if ( !isset( $this->priceCache[ $queryHash]) || ! $this->priceCache[ $queryHash]) {
            Registry::getLogger()->debug('not from cache');
            startProfile(__METHOD__.'::notCached');
            $this->priceCache[ $queryHash] = (float) $qb->fetchOne() ?: 10.0;
            stopProfile(__METHOD__.'::notCached');
        }

        $return = $this->priceCache[ $queryHash];

        stopProfile(__METHOD__);

        return $return;
    }
}