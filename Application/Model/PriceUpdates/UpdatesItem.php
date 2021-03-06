<?php

namespace Daniels\FuelLogger\Application\Model\PriceUpdates;

class UpdatesItem
{
    public string $stationId;
    public string $stationPostCode;
    public string $stationBrand;
    public string $fuelType;
    public string $fuelPrice;
    public string $stationName;

    /**
     * @return string
     */
    public function getStationId(): string
    {
        return $this->stationId;
    }

    /**
     * @param string $stationId
     */
    public function setStationId(string $stationId): void
    {
        $this->stationId = $stationId;
    }

    /**
     * @return string
     */
    public function getStationPostCode(): string
    {
        return $this->stationPostCode;
    }

    /**
     * @param string $stationPostCode
     */
    public function setStationPostCode(string $stationPostCode): void
    {
        $this->stationPostCode = $stationPostCode;
    }

    /**
     * @return string
     */
    public function getStationBrand(): string
    {
        return $this->stationBrand;
    }

    /**
     * @param string $stationBrand
     */
    public function setStationBrand(string $stationBrand): void
    {
        $this->stationBrand = $stationBrand;
    }

    /**
     * @return string
     */
    public function getFuelType(): string
    {
        return $this->fuelType;
    }

    /**
     * @param string $fuelType
     */
    public function setFuelType(string $fuelType): void
    {
        $this->fuelType = $fuelType;
    }

    /**
     * @return float
     */
    public function getFuelPrice(): float
    {
        return (float) $this->fuelPrice;
    }

    /**
     * @param float $fuelPrice
     */
    public function setFuelPrice(float $fuelPrice): void
    {
        $this->fuelPrice = $fuelPrice;
    }

    /**
     * @return string
     */
    public function getStationName(): string
    {
        return $this->stationName;
    }

    /**
     * @param string $stationName
     */
    public function setStationName(string $stationName): void
    {
        $this->stationName = $stationName;
    }
}