<?php

namespace Daniels\FuelLogger\Application\Model;

use Daniels\FuelLogger\Application\Controller\bestPriceList;
use Daniels\FuelLogger\Application\Controller\disclaimer;
use Daniels\FuelLogger\Application\Controller\priceTrend;
use Daniels\FuelLogger\Application\Controller\stationPriceList;

class ControllerMapper
{
    public $map = [
        'bestPriceList' => bestPriceList::class,
        'disclaimer' => disclaimer::class,
        'stationPriceList' => stationPriceList::class,
        'priceTrend' => priceTrend::class
    ];

    public function __construct()
    {
        $this->map = array_change_key_case($this->map, CASE_LOWER);
    }

    public function getFQNS($ident)
    {
        return $this->map[strtolower($ident)] ?? $this->map['bestpricelist'];
    }
}