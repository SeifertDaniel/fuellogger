<?php

namespace Daniels\FuelLogger\Application\Model\NotifyFilters;

use Daniels\FuelLogger\Application\Model\DBConnection;
use Daniels\FuelLogger\Application\Model\NotifyFilters\Interfaces\AbstractQueryFilter;
use Daniels\FuelLogger\Application\Model\NotifyFilters\Interfaces\DatabaseQueryFilter;
use Daniels\FuelLogger\Application\Model\NotifyFilters\Interfaces\ItemFilter;
use Daniels\FuelLogger\Application\Model\NotifyFilters\Interfaces\LowEfficencyFilter;
use Daniels\FuelLogger\Application\Model\Price;
use Daniels\FuelLogger\Application\Model\PriceUpdates\UpdatesItem;
use Daniels\FuelLogger\Core\Registry;
use Doctrine\DBAL\Exception;

class DailyBestPriceFilter extends AbstractQueryFilter implements ItemFilter, LowEfficencyFilter
{
    /**
     * @param UpdatesItem $item
     * @return bool
     * @throws Exception
     */
    public function filterItem(UpdatesItem $item): bool
    {
        $bestPriceBeforeUpdate = $this->getBestPriceBeforeUpdate($item->getFuelType());
        $canNotify = $item->getFuelPrice() < $bestPriceBeforeUpdate;

        if (false === $canNotify) {
            Registry::getLogger()->debug(get_class($this));
            Registry::getLogger()->debug("price ".$item->getFuelPrice()." is not lower than $bestPriceBeforeUpdate");
        }

        return !$canNotify;
    }

    /**
     * @param $fuelType
     *
     * @return float
     * @throws Exception
     */
    public function getBestPriceBeforeUpdate($fuelType): float
    {
        $qb = DBConnection::getConnection()->createQueryBuilder();
        $qb->select('pr.price')
            ->from((new Price())->getCoreTableName(), 'pr')
            ->where(
                $qb->expr()->and(
                    'pr.datetime BETWEEN DATE_FORMAT(NOW(), "%Y-%m-%d 00:00:00") AND DATE_FORMAT(DATE_SUB(NOW(), INTERVAL 2 MINUTE), "%Y-%m-%d %H:%i:%s")',
                    $qb->expr()->eq(
                        'pr.type',
                        $qb->createNamedParameter($fuelType)
                    ),
                    $this->getFilterQuery()
                )
            )
            ->orderBy('pr.price', 'ASC')
            ->setMaxResults(1);

        return (float) $qb->fetchOne() ?: 10.0;
    }

    /**
     * @return string
     */
    public function getFilterQuery(): string
    {
        return count($this->getNotifier()->getQueryFilters()) ?
            implode(' AND ', array_map(
                function (DatabaseQueryFilter $filter) {

                    return $filter->getFilterQuery('pr', 'st');
                },
                $this->getNotifier()->getQueryFilters()
            )) :
            '1';
    }
}