<?php

namespace Daniels\FuelLogger\Application\Model;

use Doctrine\DBAL\Exception;
use Doctrine\DBAL\Query\QueryBuilder;

class BestPrice
{
    /**
     * @param string $type
     * @return QueryBuilder
     * @throws Exception
     */
    public function getQueryBuilder(string $type = Fuel::TYPE_E10): QueryBuilder
    {
        $stationTable = (new Station())->getCoreTableName();
        $priceTable = (new Price())->getCoreTableName();
        $openingTimesTable = (new openingTimes('undefined'))->getCoreTableName();

        $conn = DBConnection::getConnection();

        $subQb = $conn->createQueryBuilder();
        $subQb->select('MAX(p2.datetime)')
            ->from($priceTable, 'p2')
            ->where(
                $subQb->expr()->and(
                    $subQb->expr()->eq(
                        'p2.stationid',
                        'pr.stationid'
                    ),
                    $subQb->expr()->eq(
                        'p2.type',
                        'pr.type'
                    )
                )
            );

        $qb = $conn->createQueryBuilder();
        $qb->select('st.id', 'st.name', 'st.place', 'pr.price', 'TIME_FORMAT(TIMEDIFF(NOW(), pr.datetime), \'%H:%i\') as timediff', 'ISNULL(ot.id) as closed')
            ->from($stationTable, 'st')
            ->leftJoin('st', $priceTable, 'pr', 'st.id = pr.stationid')
            ->leftJoin('st', $openingTimesTable, 'ot', 'st.id = ot.stationid AND WEEKDAY & (WEEKDAY(NOW()) + 1) = (WEEKDAY(NOW()) + 1) AND TIME(NOW()) BETWEEN `FROM` AND `TO`')
            ->where(
                $qb->expr()->and(
                    $qb->expr()->eq(
                        'pr.datetime',
                        '('.$subQb->getSQL().')'
                    ),
                    $qb->expr()->eq(
                        'pr.type',
                        $conn->quote($type)
                    ),
                    $qb->expr()->lt(
                        'TIME_FORMAT(TIMEDIFF(NOW(), pr.datetime), \'%H\')',
                        24
                    )
                )
            )
            ->groupBy('st.id')
            ->orderBy('pr.price', 'ASC');

        return $qb;
    }

    /**
     * @param string $type
     *
     * @return QueryBuilder
     * @throws Exception
     */
    public function getTimeDiffSortedQueryBuilder(string $type = Fuel::TYPE_E10): QueryBuilder
    {
        $qb = $this->getQueryBuilder($type);
        $qb->addOrderBy('timediff', 'DESC');

        return $qb;
    }
}