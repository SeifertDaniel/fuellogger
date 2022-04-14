<?php

namespace Daniels\FuelLogger\Application\Model\Entities;

use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\CustomIdGenerator;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\Table;
use Ramsey\Uuid\Doctrine\UuidGenerator;

#[Entity]
#[Table('prices_archive')]
class PriceArchive
{
    #[Id]
    #[Column(type: 'uuid', unique: true), GeneratedValue(strategy: 'CUSTOM'), CustomIdGenerator(class: UuidGenerator::class)]
    private string $id;

    #[Column(type: 'date')]
    private string $date;

    #[Column(length: 10)]
    private string $type;

    #[Column(type: 'decimal', precision: 4, scale: 3)]
    private float $min;

    #[Column(type: 'decimal', precision: 4, scale: 3)]
    private float $avg;

    #[Column(type: 'decimal', precision: 4, scale: 3)]
    private float $max;

    /**
     * @return string
     */
    public function getId(): string
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getDate(): string
    {
        return $this->date;
    }

    /**
     * @param string $date
     * @return PriceArchive
     */
    public function setDate(string $date): PriceArchive
    {
        $this->date = $date;
        return $this;
    }

    /**
     * @return string
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * @param string $type
     * @return PriceArchive
     */
    public function setType(string $type): PriceArchive
    {
        $this->type = $type;
        return $this;
    }

    /**
     * @return float
     */
    public function getMin(): float
    {
        return $this->min;
    }

    /**
     * @param float $min
     * @return PriceArchive
     */
    public function setMin(float $min): PriceArchive
    {
        $this->min = $min;
        return $this;
    }

    /**
     * @return float
     */
    public function getAvg(): float
    {
        return $this->avg;
    }

    /**
     * @param float $avg
     * @return PriceArchive
     */
    public function setAvg(float $avg): PriceArchive
    {
        $this->avg = $avg;
        return $this;
    }

    /**
     * @return float
     */
    public function getMax(): float
    {
        return $this->max;
    }

    /**
     * @param float $max
     * @return PriceArchive
     */
    public function setMax(float $max): PriceArchive
    {
        $this->max = $max;
        return $this;
    }
}