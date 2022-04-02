<?php

namespace Daniels\FuelLogger\Application\Model\NotifyFilters;

use Daniels\FuelLogger\Application\Model\DBConnection;
use Daniels\FuelLogger\Application\Model\Price;
use Doctrine\DBAL\Exception;

class DailyBestPriceFilter extends AbstractQueryFilter
{
    /**
     * @param string $fuelType
     * @param float $price
     *
     * @return bool
     * @throws Exception
     */
    public function canNotifiy( string $fuelType, float $price ): bool
    {
        $bestPriceBeforeUpdate = $this->getBestPriceBeforeUpdate($fuelType);
        $canNotify = $price < $bestPriceBeforeUpdate;

        if (false === $canNotify) {
            $this->setDebugMessage("price $price is not lower than $bestPriceBeforeUpdate");
        }

        return $canNotify;
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

        return (float) $qb->fetchOne();
    }

    /**
     * @return string
     */
    public function getFilterQuery(): string
    {
        return count($this->getQueryFilters()) ?
            implode(' AND ', array_map(
                function (DatabaseQueryFilter $filter) {
                    return $filter->getFilterQuery('pr.datetime');
                },
                $this->getQueryFilters()
            )) :
            '1';
    }
}