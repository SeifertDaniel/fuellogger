<?php

namespace Daniels\FuelLogger\Application\Model\NotifyFilters;

use Daniels\FuelLogger\Application\Model\NotifyFilters\Interfaces\AbstractFilter;
use Daniels\FuelLogger\Application\Model\NotifyFilters\Interfaces\HighEfficencyFilter;
use Daniels\FuelLogger\Application\Model\PriceUpdates\UpdatesItem;
use Daniels\FuelLogger\Core\Registry;
use DateTime;

class DateFilter extends AbstractFilter implements HighEfficencyFilter
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

    public function filterItem(UpdatesItem $item): bool
    {
        $f = DateTime::createFromFormat('!Y-m-d', $this->from);
        $t = DateTime::createFromFormat('!Y-m-d', $this->till);
        $i = (new DateTime())->setDate($f->format('Y'),$f->format('m'), $f->format('d'));

        $canNotify = $f <= $i && $i <= $t;

        if (false === $canNotify) {
            Registry::getLogger()->debug(get_class($this));
            Registry::getLogger()->debug(
                "Date ".$i->format('Y-m-d')." is not between ".
                $f->format('Y-m-d')." and ".$t->format('Y-m-d')
            );
        }

        return !$canNotify;
    }
}