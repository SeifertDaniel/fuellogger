<?php

namespace Daniels\FuelLogger\Application\Model\NotifyFilters;

use Daniels\FuelLogger\Application\Model\DBConnection;
use Daniels\FuelLogger\Application\Model\NotifyFilters\Interfaces\AbstractFilter;
use Daniels\FuelLogger\Application\Model\NotifyFilters\Interfaces\DatabaseQueryFilter;
use Daniels\FuelLogger\Application\Model\NotifyFilters\Interfaces\HighEfficencyFilter;
use Daniels\FuelLogger\Application\Model\PriceUpdates\UpdatesItem;
use Daniels\FuelLogger\Core\Registry;
use DateTime;
use Doctrine\DBAL\Exception;

class TimeFilter extends AbstractFilter implements DatabaseQueryFilter, HighEfficencyFilter
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

    public function filterItem(UpdatesItem $item): bool
    {
        $f = DateTime::createFromFormat('!H:i:s', $this->from);
        $t = DateTime::createFromFormat('!H:i:s', $this->till);
        $i = (new DateTime())->setDate($f->format('Y'),$f->format('m'), $f->format('d'));
        if ($f > $t) $t->modify('+1 day');
        $doFilter = !($f <= $i && $i <= $t) || ($f <= $i->modify('+1 day') && $i <= $t);

        if ($doFilter) {
            $message = "Time ".$i->format('H:i:s')." is not between ".
                       $f->format('H:i:s')." and ".$t->format('H:i:s');
            Registry::getLogger()->debug(get_class($this));
            Registry::getLogger()->debug($message);
            $this->setDebugMessage($message);
        }

        return $doFilter;
    }

    /**
     * @param string $priceTableAlias
     * @param string $stationTableAlias
     *
     * @return string
     * @throws Exception
     */
    public function getFilterQuery(string $priceTableAlias, string $stationTableAlias): string
    {
        $connection = DBConnection::getConnection();
        return 'DATE_FORMAT('.$priceTableAlias.'.datetime, "%H:%i:%s") BETWEEN '.$connection->quote($this->from).' AND '.$connection->quote($this->till);
    }
}