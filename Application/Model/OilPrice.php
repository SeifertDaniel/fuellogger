<?php

namespace Daniels\Benzinlogger\Application\Model;

use Doctrine\DBAL\Exception;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;

class OilPrice
{
    public function getCoreTableName()
    {
        return 'oilprices';
    }

    /**
     * @param $date
     * @return false|mixed
     * @throws Exception
     */
    public function existForDate($date)
    {
        $qb = DBConnection::getConnection()->createQueryBuilder();
        $qb->select('1')
            ->from($this->getCoreTableName(), 'pr')
            ->where(
                $qb->expr()->eq(
                    'pr.date',
                    $qb->createNamedParameter($date)
                )
            )
            ->setMaxResults(1);

        return $qb->fetchOne();
    }

    /**
     * @param $price
     * @return UuidInterface
     * @throws Exception
     */
    public function insert($price)
    {
        $uuid = Uuid::uuid4();

        $conn = DBConnection::getConnection();
        $qb = $conn->createQueryBuilder();
        $qb->insert($this->getCoreTableName())
            ->values([
                'id'    => $qb->createNamedParameter($uuid->toString()),
                'price'  => $qb->createNamedParameter($price),
                'date'  => $qb->createNamedParameter(date('Y-m-d'))
            ]);

        $qb->executeQuery();

        return $uuid;
    }
}