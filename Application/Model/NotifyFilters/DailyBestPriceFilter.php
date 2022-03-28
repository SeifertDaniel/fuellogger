<?php

/**
 * This Software is the property of Data Development and is protected
 * by copyright law - it is NOT Freeware.
 * Any unauthorized use of this software without a valid license
 * is a violation of the license agreement and will be prosecuted by
 * civil and criminal law.
 * http://www.shopmodule.com
 *
 * @copyright (C) D3 Data Development (Inh. Thomas Dartsch)
 * @author        D3 Data Development - Daniel Seifert <support@shopmodule.com>
 * @link          http://www.oxidmodule.com
 */

namespace Daniels\FuelLogger\Application\Model\NotifyFilters;

use Daniels\FuelLogger\Application\Model\DBConnection;
use Daniels\FuelLogger\Application\Model\Price;
use Doctrine\DBAL\Exception;

class DailyBestPriceFilter extends AbstractFilter
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
        return $price < $this->getBestPriceBeforeUpdate($fuelType);
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
                    )
                )
            )
            ->orderBy('pr.price', 'ASC')
            ->setMaxResults(1);
        return (float) $qb->fetchOne();
    }
}