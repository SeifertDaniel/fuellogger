<?php

namespace Daniels\Benzinlogger\Application\Model\NotifyFilters;

use DateTime;

class TimeFilter extends AbstractFilter
{
    public $from;
    public $till;

    public function __construct($from, $till)
    {
        $this->from = $from;
        $this->till = $till;
    }

    public function canNotifiy($fuelType, float $price) : bool
    {
        $f = DateTime::createFromFormat('!H:i:s', $this->from);
        $t = DateTime::createFromFormat('!H:i:s', $this->till);
        $i = (new DateTime())->setDate($f->format('Y'),$f->format('m'), $f->format('d'));
        if ($f > $t) $t->modify('+1 day');
        return ($f <= $i && $i <= $t) || ($f <= $i->modify('+1 day') && $i <= $t);
    }
}