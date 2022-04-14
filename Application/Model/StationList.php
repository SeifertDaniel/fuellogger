<?php

namespace Daniels\FuelLogger\Application\Model;

use Daniels\FuelLogger\Application\Model\Entities\Station;

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