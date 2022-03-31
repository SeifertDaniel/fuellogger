<?php

namespace Daniels\FuelLogger\Application\Model;

use Doctrine\DBAL\Exception;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;

class Price
{
    /**
     * @return string
     */
    public function getCoreTableName(): string
    {
        return 'prices';
    }

    /**
     * @param $stationId
     * @param $type
     * @return float
     * @throws Exception
     */
    public function getLastPrice($stationId, $type) :float
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

        return (float) $qb->fetchOne();
    }

    /**
     * @param string $stationid
     * @param string $type
     * @param $price
     * @return UuidInterface
     * @throws Exception
     */
    public function insert(
        string $stationid,
        string $type,
        $price
    ): UuidInterface
    {
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