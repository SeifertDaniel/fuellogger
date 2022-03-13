<?php

namespace Daniels\Benzinlogger\Application\Model;

use Ramsey\Uuid\Uuid;

class Price
{
    public function getCoreTableName()
    {
        return 'prices';
    }

    public function getLastPrice($stationId, $type)
    {
        $conn = DBConnection::getConnection();
        $qb = $conn->createQueryBuilder();
        $qb->select('price')
            ->from($this->getCoreTableName())
            ->where(
                $qb->expr()->and(
                    $qb->expr()->eq(
                        'stationid',
                        $qb->createNamedParameter($stationId)
                    ),
                    $qb->expr()->eq(
                        'type',
                        $qb->createNamedParameter($type)
                    )
                )
            )->orderBy('timestamp', 'DESC')
            ->setMaxResults(1);

        return $qb->fetchOne();
    }

    public function insert(
        $stationid,
        $type,
        $price
    ) {
        $uuid = Uuid::uuid4();

        $conn = DBConnection::getConnection();
        $qb = $conn->createQueryBuilder();
        $qb->insert($this->getCoreTableName())
            ->values([
                'id'    => $qb->createNamedParameter($uuid->toString()),
                'stationid'  => $qb->createNamedParameter($stationid),
                'type'  => $qb->createNamedParameter($type),
                'price'  => $qb->createNamedParameter($price),
                'datetime'  => $qb->createNamedParameter(date('Y-m-d H:i:s'))
            ]);

        $qb->executeQuery();

        return $uuid;
    }
}