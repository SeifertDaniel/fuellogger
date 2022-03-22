<?php

namespace Daniels\Benzinlogger\Application\Model\NotifyFilters;

use DateTime;

class DateFilter extends AbstractFilter
{
    public $from;
    public $till;

    /**
     * @param $from
     * @param $till
     */
    public function __construct($from, $till)
    {
        $this->from = $from;
        $this->till = $till;
    }

    /**
     * @param       $fuelType
     * @param float $price
     *
     * @return bool
     */
    public function canNotifiy($fuelType, float $price) : bool
    {
        $f = DateTime::createFromFormat('!Y-m-d', $this->from);
        $t = DateTime::createFromFormat('!Y-m-d', $this->till);
        $i = (new DateTime())->setDate($f->format('Y'),$f->format('m'), $f->format('d'));

        return $f <= $i && $i <= $t;
    }
}