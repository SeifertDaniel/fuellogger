<?php

namespace Daniels\FuelLogger\Application\Model\PriceUpdates;

class UpdatesList
{
    protected array $array = [];

    public function add($stationId, $postCode, $brand, $type, $price, $station)
    {
        $itm = new UpdatesItem();
        $itm->setStationId($stationId);
        $itm->setStationPostCode($postCode);
        $itm->setStationBrand($brand);
        $itm->setFuelType($type);
        $itm->setFuelPrice((float) $price);
        $itm->setStationName($station);
        $this->array[] = $itm;
    }

    /**
     * @return array
     */
    public function getList(): array
    {
        return $this->array;
    }

    /**
     * @param array $list
     * @return void
     */
    public function setList(array $list)
    {
        foreach ($list as $item) {
            if (!$item instanceof UpdatesItem) {
                throw new \RuntimeException('UpdatesList items must be instance of UpdatesItem');
            }
        }

        $this->array = $list;
    }

    public function remove($id)
    {
        unset($this->array[$id]);
    }

    /**
     * @return int
     */
    public function count(): int
    {
        return count($this->array);
    }

    public function clear()
    {
        $this->array = [];
    }

    /**
     * @return float
     */
    public function getLowestPrice(): float
    {
        $lowest = null;

        /** @var UpdatesItem $item */
        foreach ($this->getList() as $item) {
            if ($lowest === null || $lowest > $item->getFuelPrice()) {
                $lowest = $item->getFuelPrice();
            }
        }

        return $lowest;
    }
}