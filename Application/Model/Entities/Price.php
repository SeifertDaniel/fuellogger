<?php

namespace Daniels\FuelLogger\Application\Model\Entities;

use Daniels\FuelLogger\Application\Model\DBConnection;
use Daniels\FuelLogger\Core\Registry;
use DateTime;
use Doctrine\DBAL\Exception;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\CustomIdGenerator;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Mapping\Table;
use Doctrine\ORM\ORMException;
use Ramsey\Uuid\Doctrine\UuidGenerator;

#[Entity]
#[Table('prices')]
class Price
{
    #[Id]
    #[Column(type: 'uuid', unique: true), GeneratedValue(strategy: 'CUSTOM'), CustomIdGenerator(class: UuidGenerator::class)]
    private string $id;

    #[ManyToOne(targetEntity: Station::class)]
    #[Column(type: 'uuid')]
    private string $stationid;

    #[Column]
    private string $type;

    #[Column(type: 'decimal', precision: 4, scale: 3)]
    private float $price;

    #[Column]
    private DateTime $datetime;

    public function getId(): string
    {
        return $this->id;
    }

    public function getStationid(): string
    {
        return $this->stationid;
    }

    public function setStationid( string $stationid ): Price
    {
        $this->stationid = $stationid;

        return $this;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function setType( string $type ): Price
    {
        $this->type = $type;

        return $this;
    }

    public function getPrice(): float
    {
        return $this->price;
    }

    public function setPrice( float $price ): Price
    {
        $this->price = $price;

        return $this;
    }

    public function getDatetime(): DateTime
    {
        return $this->datetime;
    }

    public function setDatetime(): Price
    {
        $this->datetime = new DateTime("now");

        return $this;
    }

    /**
     * @param $stationId
     * @param $type
     *
     * @return float
     * @throws Exception
     * @throws ORMException
     */
    public function getLastPrice($stationId, $type) :float
    {
        $em = Registry::getEntityManager();

        $conn = DBConnection::getConnection();
        $qb = $conn->createQueryBuilder();
        $qb->select('price')
           ->from($em->getClassMetadata( Price::class)->getTableName())
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
}