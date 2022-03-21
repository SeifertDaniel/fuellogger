<?php

namespace Daniels\Benzinlogger\Application\Model;

class PriceStatistics
{
    public function getLowPriceStatsByStation($stationId, $type = Fuel::TYPE_E10)
    {
        $connection = DBConnection::getConnection();

        $priceTable = (new Price())->getCoreTableName();

        $subsub1Qb = $connection->createQueryBuilder();
        $subsub1Qb->select('price')
            ->from($priceTable, 'x')
            ->where(
                $subsub1Qb->expr()->and(
                    $subsub1Qb->expr()->lt(
                        'x.datetime',
                        'l.datetime'
                    ),
                    $subsub1Qb->expr()->eq(
                        'x.stationid',
                        'l.stationid'
                    ),
                    $subsub1Qb->expr()->eq(
                        'x.type',
                        'l.type'
                    )
                )
            )
            ->orderBy('datetime', 'DESC')
            ->setMaxResults(1);

        $subsub2Qb = DBConnection::getConnection()->createQueryBuilder();
        $subsub2Qb->select('datetime')
            ->from($priceTable, 'x')
            ->where(
                $subsub2Qb->expr()->and(
                    $subsub2Qb->expr()->lt(
                        'x.datetime',
                        'l.datetime'
                    ),
                    $subsub2Qb->expr()->eq(
                        'x.stationid',
                        'l.stationid'
                    ),
                    $subsub2Qb->expr()->eq(
                        'x.type',
                        'l.type'
                    )
                )
            )
            ->orderBy('datetime', 'DESC')
            ->setMaxResults(1);


        $subQb = DBConnection::getConnection()->createQueryBuilder();
        $subQb->select(
            'DATE_FORMAT(l.datetime, \'%Y-%m-%d\') as date',
            'l.price - ('.$subsub1Qb->getSQL().') as pricediff',
            'TIMESTAMPDIFF(MINUTE, ('.$subsub2Qb->getSQL().'), l.datetime) as timediff'
        )
            ->from($priceTable, 'l')
            ->where(
                $subQb->expr()->and(
                    $subQb->expr()->eq(
                        'l.stationid',
                        $connection->quote($stationId)
                    ),
                    $subQb->expr()->eq(
                        'l.type',
                        $connection->quote($type)
                    ),
                    $subQb->expr()->gt(
                        'l.datetime',
                        'DATE_FORMAT(DATE_SUB(NOW(), INTERVAL 3 DAY), "%Y-%m-%d 00:00:00")'
                    )
                )
            )
            ->having(
                $subQb->expr()->and(
                    $subQb->expr()->gt(
                        'pricediff',
                        '0'
                    ),
                    $subQb->expr()->lt(
                        'timediff',
                        '300'
                    )
                )
            )
            ->orderBy('l.datetime', 'ASC');

        $qb = DBConnection::getConnection()->createQueryBuilder();
        $qb->select('date', 'AVG(tmp.pricediff) as pricediff', 'ROUND(AVG(tmp.timediff)) as timediff')
            ->from('('.$subQb->getSQL().')', 'tmp')
            ->groupBy('tmp.date');

        return $qb;
    }
}