<?php

namespace Daniels\Benzinlogger\Application\Model;

use Daniels\Benzinlogger\Application\Controller\bestPriceList;
use Daniels\Benzinlogger\Application\Controller\stationPriceList;

class ControllerMapper
{
    public $map = [
        'bestPriceList' => bestPriceList::class,
        'stationPriceList' => stationPriceList::class
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