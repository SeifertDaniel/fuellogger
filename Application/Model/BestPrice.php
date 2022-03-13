<?php

namespace Daniels\Benzinlogger\Application\Model;

class BestPrice
{
    public function getQueryBuilder()
    {
        $stationTable = (new Station())->getCoreTableName();
        $priceTable = (new Price())->getCoreTableName();

        $conn = DBConnection::getConnection();

        $subQb = $conn->createQueryBuilder();
        $subQb->select('MAX(p2.datetime)')
            ->from($priceTable, 'p2')
            ->where(
                $subQb->expr()->eq(
                    'p2.stationid',
                    'pr.stationid'
                )
            );

        $qb = $conn->createQueryBuilder();
        $qb->select('st.id', 'st.name', 'st.place', 'pr.price', 'TIME_FORMAT(TIMEDIFF(NOW(), pr.datetime), \'%H:%i\') as timediff')
            ->from($stationTable, 'st')
            ->leftJoin('st', $priceTable, 'pr', 'st.id = pr.stationid')
            ->where(
                $qb->expr()->and(
                    $qb->expr()->eq(
                        'pr.datetime',
                        '('.$subQb->getSQL().')'
                    ),
                    $qb->expr()->lt(
                        'TIME_FORMAT(TIMEDIFF(NOW(), pr.datetime), \'%H\')',
                        24
                    )
                )
            )
            ->orderBy('pr.price', 'ASC');

        return $qb;
    }
}