<?php

namespace Daniels\FuelLogger\Application\Model;

class PriceArchive
{
    public function getCoreTableName(): string
    {
        return 'prices_archive';
    }
}