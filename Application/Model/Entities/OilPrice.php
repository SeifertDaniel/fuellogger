<?php

namespace Daniels\FuelLogger\Application\Model\Entities;

use Daniels\FuelLogger\Application\Model\DBConnection;
use Daniels\FuelLogger\Core\Registry;
use Doctrine\DBAL\Exception;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\CustomIdGenerator;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\Table;
use Doctrine\ORM\ORMException;
use Ramsey\Uuid\Doctrine\UuidGenerator;

#[Entity]
#[Table('oilprices')]
class OilPrice
{
    #[Id]
    #[Column(type: 'uuid', unique: true), GeneratedValue(strategy: 'CUSTOM'), CustomIdGenerator(class: UuidGenerator::class)]
    private string $id;

    #[Column(type: 'decimal', precision: 7, scale: 4)]
    private float $price;

    /**
     * @return string
     */
    public function getId(): string
    {
        return $this->id;
    }

    /**
     * @return float
     */
    public function getPrice(): float
    {
        return $this->price;
    }

    /**
     * @param float $price
     * @return OilPrice
     */
    public function setPrice(float $price): OilPrice
    {
        $this->price = $price;
        return $this;
    }

    /**
     * @param $date
     * @return false|mixed
     * @throws Exception
     * @throws ORMException
     */
    public function existForDate($date): mixed
    {
        $em = Registry::getEntityManager();
        $oilPriceTable = $em->getClassMetadata( OilPrice::class)->getTableName();

        $qb = DBConnection::getConnection()->createQueryBuilder();
        $qb->select('1')
            ->from($oilPriceTable, 'pr')
            ->where(
                $qb->expr()->eq(
                    'pr.date',
                    $qb->createNamedParameter($date)
                )
            )
            ->setMaxResults(1);

        return $qb->fetchOne();
    }
}