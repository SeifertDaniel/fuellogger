<?php

namespace Daniels\FuelLogger\Application\Model;

class StationList extends ListModel
{
    protected string $objectsInListName = Station::class;

    /**
     * @return array
     */
    public function getTKStationIds(): array
    {
        $list = [];

        foreach ($this->getArray() as $station) {
            $list[] = $station->tkid;
        }

        return $list;
    }
}