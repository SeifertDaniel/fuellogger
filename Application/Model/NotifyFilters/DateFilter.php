<?php

namespace Daniels\FuelLogger\Application\Model\NotifyFilters;

use DateTime;

class DateFilter extends AbstractFilter
{
    public string $from;
    public string $till;

    /**
     * @param string $from
     * @param string $till
     */
    public function __construct(string $from, string $till)
    {
        $this->from = $from;
        $this->till = $till;
    }

    /**
     * @param string $fuelType
     * @param float $price
     *
     * @return bool
     */
    public function canNotifiy(string $fuelType, float $price) : bool
    {
        $f = DateTime::createFromFormat('!Y-m-d', $this->from);
        $t = DateTime::createFromFormat('!Y-m-d', $this->till);
        $i = (new DateTime())->setDate($f->format('Y'),$f->format('m'), $f->format('d'));

        return $f <= $i && $i <= $t;
    }
}