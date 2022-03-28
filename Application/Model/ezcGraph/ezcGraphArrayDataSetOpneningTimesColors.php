<?php

namespace Daniels\FuelLogger\Application\Model\ezcGraph;

use ezcGraphArrayDataSet;

class ezcGraphArrayDataSetOpneningTimesColors extends ezcGraphArrayDataSet
{
    public function setProperty($propertyId, $property)
    {
        $this->properties[ $propertyId ] = $property;
    }

    public function getKeys()
    {
        return array_keys( $this->data );
    }
}