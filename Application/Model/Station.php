<?php

namespace Daniels\FuelLogger\Application\Model;

use Ramsey\Uuid\Uuid;

class Station extends BaseModel
{
    public function exists()
    {}

    public function getIdByTkId($stationId)
    {
        $conn = DBConnection::getConnection();
        $qb = $conn->createQueryBuilder();

        $qb->select('id')
            ->from($this->getCoreTableName())
            ->where(
                $qb->expr()->eq(
                    'tkid',
                    $qb->createNamedParameter($stationId)
                )
            )
            ->setMaxResults(1);

        return $conn->fetchOne($qb->getSQL(), $qb->getParameters());
    }

    public function existsByStationId($stationId)
    {
        $conn = DBConnection::getConnection();
        $qb = $conn->createQueryBuilder();

        $qb->select(1)
            ->from($this->getCoreTableName())
            ->where(
                $qb->expr()->eq(
                    'tkid',
                    $qb->createNamedParameter($stationId)
                )
            );

        return (bool) $conn->fetchOne($qb->getSQL(), $qb->getParameters());
    }

    public function getCoreTableName()
    {
        return 'stations';
    }

    public function insert(
        $id,
        $name,
        $brand,
        $street,
        $houseNumber,
        $postCode,
        $place,
        $openingTimes,
        $lat,
        $lng,
        $state
    ) {
        $uuid = Uuid::uuid4();

        $conn = DBConnection::getConnection();
        $qb = $conn->createQueryBuilder();
        $qb->insert($this->getCoreTableName())
            ->values([
                'id'    => $qb->createNamedParameter($uuid->toString()),
                'tkid'  => $qb->createNamedParameter($id),
                'name'  => $qb->createNamedParameter($name),
                'brand'  => $qb->createNamedParameter($brand),
                'street'  => $qb->createNamedParameter($street),
                'housenumber'  => $qb->createNamedParameter($houseNumber),
                'postcode'  => $qb->createNamedParameter($postCode),
                'place'  => $qb->createNamedParameter($place),
                'openingtimes'  => $qb->createNamedParameter(serialize($openingTimes)),
                'lat'  => $qb->createNamedParameter($lat),
                'lon'  => $qb->createNamedParameter($lng),
                'state'  => $qb->createNamedParameter($state),
            ]);

        $qb->executeQuery();

        return $uuid;
    }
}