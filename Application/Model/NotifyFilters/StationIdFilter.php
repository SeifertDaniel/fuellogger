<?php

namespace Daniels\FuelLogger\Application\Model\NotifyFilters;

use Daniels\FuelLogger\Application\Model\DBConnection;
use Daniels\FuelLogger\Application\Model\NotifyFilters\Interfaces\AbstractFilter;
use Daniels\FuelLogger\Application\Model\NotifyFilters\Interfaces\DatabaseQueryFilter;
use Daniels\FuelLogger\Application\Model\NotifyFilters\Interfaces\ItemFilter;
use Daniels\FuelLogger\Application\Model\NotifyFilters\Interfaces\MediumEfficencyFilter;
use Daniels\FuelLogger\Application\Model\PriceUpdates\UpdatesItem;
use Daniels\FuelLogger\Core\Registry;
use Doctrine\DBAL\Exception as DoctrineException;

class StationIdFilter extends AbstractFilter implements ItemFilter, DatabaseQueryFilter, MediumEfficencyFilter
{
    protected array $stationIds = [];

    /**
     * @param array $stationIds
     */
    public function __construct(array $stationIds)
    {
        $this->stationIds = $stationIds;
    }

    /**
     * @param UpdatesItem $item
     * @return bool
     */
    public function filterItem(UpdatesItem $item): bool
    {
        $doFilter = !in_array($item->getStationId(), $this->stationIds);

        if ($doFilter) {
            $message = "Stations ".implode(', ', $this->stationIds) . " do not match " . $item->getStationId();
            Registry::getLogger()->debug(get_class($this));
            Registry::getLogger()->debug($message);
            $this->setDebugMessage($message);
        }

        dumpvar(__METHOD__.__LINE__);
        dumpvar($doFilter);

        return $doFilter;
    }

    /**
     * @param string $priceTableAlias
     * @param string $stationTableAlias
     *
     * @return string
     * @throws DoctrineException
     */
    public function getFilterQuery(string $priceTableAlias, string $stationTableAlias): string
    {
        $connection = DBConnection::getConnection();
        return $stationTableAlias.'.id IN ('.implode(', ', array_map([$connection, 'quote'], $this->stationIds)) . ')';
    }
}