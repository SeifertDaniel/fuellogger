<?php

namespace Daniels\FuelLogger\Application\Model\Entities;

use Daniels\FuelLogger\Application\Model\DBConnection;
use Daniels\FuelLogger\Core\Registry;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Exception as DoctrineException;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\CustomIdGenerator;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\OneToMany;
use Doctrine\ORM\Mapping\Table;
use Doctrine\ORM\ORMException;
use Ramsey\Uuid\Doctrine\UuidGenerator;

#[Entity]
#[Table('stations')]
class Station
{
    #[Id]
    #[Column(type: 'uuid', unique: true), GeneratedValue(strategy: 'CUSTOM'), CustomIdGenerator(class: UuidGenerator::class)]
    private string $id;

    #[Column(type: 'uuid')]
    private string $tkid;

    #[Column(length: 100)]
    private string $name;

    #[Column(length: 100)]
    private string $brand;

    #[Column(length: 100)]
    private string $street;

    #[Column(length: 10)]
    private string $housenumber;

    #[Column(length: 10)]
    private string $postcode;

    #[Column(length: 50)]
    private string $place;

    #[Column(type: 'text')]
    private string $openingtimes;

    #[Column(type: 'decimal', precision: 8, scale: 6)]
    private float $lat;

    #[Column(type: 'decimal', precision: 8, scale: 6)]
    private float $lon;

    #[Column(length: 10)]
    private string $state;

    #[OneToMany(mappedBy: 'station', targetEntity: Price::class, cascade: ['persist', 'remove'])]
    private Collection $prices;

    #[OneToMany(mappedBy: 'station', targetEntity: openingTimes::class, cascade: ['persist', 'remove'])]
    private Collection $openingTimesCollection;

    public function __construct()
    {
        $this->prices = new ArrayCollection();
        $this->openingTimesCollection = new ArrayCollection();
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getTkid(): string
    {
        return $this->tkid;
    }

    public function setTkid(string $tkid): Station
    {
        $this->tkid = $tkid;
        return $this;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): Station
    {
        $this->name = $name;
        return $this;
    }

    public function getBrand(): string
    {
        return $this->brand;
    }

    public function setBrand(string $brand): Station
    {
        $this->brand = $brand;
        return $this;
    }

    public function getStreet(): string
    {
        return $this->street;
    }

    public function setStreet(string $street): Station
    {
        $this->street = $street;
        return $this;
    }

    public function getHousenumber(): string
    {
        return $this->housenumber;
    }

    public function setHousenumber(string $housenumber): Station
    {
        $this->housenumber = $housenumber;
        return $this;
    }

    public function getPostcode(): string
    {
        return $this->postcode;
    }

    public function setPostcode(string $postcode): Station
    {
        $this->postcode = $postcode;
        return $this;
    }

    public function getPlace(): string
    {
        return $this->place;
    }

    public function setPlace(string $place): Station
    {
        $this->place = $place;
        return $this;
    }

    /**
     * @return string
     */
    public function getOpeningtimes(): string
    {
        return $this->openingtimes;
    }

    /**
     * @param array $openingtimes
     *
     * @return Station
     */
    public function setOpeningtimes( array $openingtimes ): Station
    {
        $this->openingtimes = serialize($openingtimes);

        return $this;
    }

    /**
     * @return float
     */
    public function getLat(): float
    {
        return $this->lat;
    }

    /**
     * @param float $lat
     *
     * @return Station
     */
    public function setLat( float $lat ): Station
    {
        $this->lat = $lat;

        return $this;
    }

    /**
     * @return float
     */
    public function getLon(): float
    {
        return $this->lon;
    }

    /**
     * @param float $lon
     *
     * @return Station
     */
    public function setLon( float $lon ): Station
    {
        $this->lon = $lon;

        return $this;
    }

    /**
     * @return string
     */
    public function getState(): string
    {
        return $this->state;
    }

    /**
     * @param string $state
     *
     * @return Station
     */
    public function setState( string $state ): Station
    {
        $this->state = $state;

        return $this;
    }

    /**
     * @return ArrayCollection|Collection
     */
    public function getPrices(): ArrayCollection|Collection
    {
        return $this->prices;
    }

    /**
     * @param Price $price
     * @return Station
     */
    public function addPrice(Price $price): Station
    {
        $price->setStation($this);
        $this->prices->add($price);

        return $this;
    }

    /**
     * @return ArrayCollection|Collection
     */
    public function getOpeningTimesCollection(): ArrayCollection|Collection
    {
        return $this->openingTimesCollection;
    }

    /**
     * @param openingTimes $openingTimes
     * @return Station
     */
    public function addOpeningTimeToCollection(openingTimes $openingTimes): Station
    {
        $openingTimes->setStation($this);
        $this->openingTimesCollection->add($openingTimes);

        return $this;
    }

    public function exists()
    {}

    /**
     * @param $stationId
     * @return string
     * @throws DoctrineException
     * @throws ORMException
     */
    public function getIdByTkId($stationId) : string
    {
        $conn = DBConnection::getConnection();
        $qb = $conn->createQueryBuilder();

        $em = Registry::getEntityManager();
        $stationTable = $em->getClassMetadata( Station::class)->getTableName();

        $qb->select('id')
           ->from($stationTable)
           ->where(
               $qb->expr()->eq(
                   'tkid',
                   $qb->createNamedParameter($stationId)
               )
           )
           ->setMaxResults(1);

        return $qb->fetchOne();
    }

    /**
     * @param $stationId
     * @return bool
     * @throws DoctrineException
     * @throws ORMException
     */
    public function existsByStationId($stationId): bool
    {
        $conn = DBConnection::getConnection();
        $qb = $conn->createQueryBuilder();

        $em = Registry::getEntityManager();
        $stationTable = $em->getClassMetadata( Station::class)->getTableName();

        $qb->select(1)
           ->from($stationTable)
           ->where(
               $qb->expr()->eq(
                   'tkid',
                   $qb->createNamedParameter($stationId)
               )
           );

        return (bool) $qb->fetchOne();
    }
}