<?php

namespace Daniels\FuelLogger\Application\Model\NotifyFilters;

use Daniels\FuelLogger\Application\Model\DBConnection;
use DateTime;
use Doctrine\DBAL\Exception;

class TimeFilter extends AbstractFilter implements DatabaseQueryFilter
{
    public string $from;
    public string $till;

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
     * @param string $fuelType
     * @param float $price
     *
     * @return bool
     */
    public function canNotifiy(string $fuelType, float $price) : bool
    {
        $f = DateTime::createFromFormat('!H:i:s', $this->from);
        $t = DateTime::createFromFormat('!H:i:s', $this->till);
        $i = (new DateTime())->setDate($f->format('Y'),$f->format('m'), $f->format('d'));
        if ($f > $t) $t->modify('+1 day');
        $canNotify = ($f <= $i && $i <= $t) || ($f <= $i->modify('+1 day') && $i <= $t);

        if (false === $canNotify) {
            $this->setDebugMessage(
                "Time ".$i->format('H:i:s')." is not between ".
                $f->format('H:i:s')." and ".$t->format('H:i:s')
            );
        }

        return $canNotify;
    }

    /**
     * @param string $fieldName
     * @return string
     * @throws Exception
     */
    public function getFilterQuery(string $fieldName): string
    {
        $connection = DBConnection::getConnection();
        return 'DATE_FORMAT(pr.datetime, "%H:%i:%s") BETWEEN '.$connection->quote($this->from).' AND '.$connection->quote($this->till);
    }
}