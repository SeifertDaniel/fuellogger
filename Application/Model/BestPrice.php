<?php

namespace Daniels\FuelLogger\Application\Model;

use Daniels\FuelLogger\Application\Model\Entities\openingTimes;
use Daniels\FuelLogger\Application\Model\Entities\Price;
use Daniels\FuelLogger\Application\Model\Entities\Station;
use Daniels\FuelLogger\Core\Registry;
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
        $em = Registry::getEntityManager();

        $stationTable = $em->getClassMetadata( Station::class)->getTableName();
        $priceTable = $em->getClassMetadata( Price::class)->getTableName();
        $openingTimesTable = $em->getClassMetadata( openingTimes::class)->getTableName();

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
            ->leftJoin('st', $openingTimesTable, 'ot', 'st.id = ot.stationid AND WEEKDAY & 1 << WEEKDAY(NOW()) = 1 << WEEKDAY(NOW()) AND TIME(NOW()) BETWEEN `FROM` AND `TO`')
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
     * @param array $filter
     * @return QueryBuilder
     * @throws Exception
     */
    public function getTimeDiffSortedQueryBuilder(string $type = Fuel::TYPE_E10, array $filter = []): QueryBuilder
    {
        $qb = $this->getQueryBuilder($type);
        foreach ($filter as $field => $value) {
            if ($value) $qb->andWhere($qb->expr()->eq($field, $value));
        }
        $qb->addOrderBy('timediff', 'DESC');
        $qb->addOrderBy('st.id', 'DESC');

        return $qb;
    }
}