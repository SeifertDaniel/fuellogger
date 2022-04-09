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

class DateFilter extends AbstractFilter implements DatabaseQueryFilter, HighEfficencyFilter
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
        startProfile(__METHOD__);

        $f = DateTime::createFromFormat('!Y-m-d', $this->from);
        $t = DateTime::createFromFormat('!Y-m-d', $this->till);
        $i = (new DateTime())->setDate($f->format('Y'),$f->format('m'), $f->format('d'));

        $doFilter = !($f <= $i && $i <= $t);

        if ($doFilter) {
            $message = "Date ".$i->format('Y-m-d')." is not between ".
                       $f->format('Y-m-d')." and ".$t->format('Y-m-d');
            Registry::getLogger()->debug(get_class($this));
            Registry::getLogger()->debug($message);
            $this->setDebugMessage($message);
        }

        stopProfile(__METHOD__);

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
        return 'DATE_FORMAT('.$priceTableAlias.'.datetime, "%d:%m:%Y") BETWEEN '.$connection->quote($this->from).' AND '.$connection->quote($this->till);
    }
}