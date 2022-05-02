<?php

declare(strict_types=1);

namespace Daniels\FuelLogger\Application\Model\NotifyFilters;

use Daniels\FuelLogger\Application\Model\DBConnection;
use Daniels\FuelLogger\Application\Model\NotifyFilters\Interfaces\AbstractFilter;
use Daniels\FuelLogger\Application\Model\NotifyFilters\Interfaces\DatabaseQueryFilter;
use Daniels\FuelLogger\Application\Model\NotifyFilters\Interfaces\ItemFilter;
use Daniels\FuelLogger\Application\Model\NotifyFilters\Interfaces\MediumEfficencyFilter;
use Daniels\FuelLogger\Application\Model\PriceUpdates\UpdatesItem;
use Daniels\FuelLogger\Core\Registry;
use Doctrine\DBAL\Exception as DoctrineException;

class FuelTypeFilter extends AbstractFilter implements ItemFilter, DatabaseQueryFilter, MediumEfficencyFilter
{
    private array $fuelTypes;

    /**
     * @param array $fuelTypes
     */
    public function __construct(array $fuelTypes)
    {
        $this->fuelTypes = $fuelTypes;
    }

    /**
     * @param UpdatesItem $item
     * @return bool
     */
    public function filterItem(UpdatesItem $item): bool
    {
        startProfile(__METHOD__);

        $doFilter = !in_array($item->getFuelType(), $this->fuelTypes);

        if ($doFilter) {
            $message = "fuelTypes ".implode(', ', $this->fuelTypes)." do not match ".$item->getFuelType();
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
     * @throws DoctrineException
     */
    public function getFilterQuery(string $priceTableAlias, string $stationTableAlias): string
    {
        $connection = DBConnection::getConnection();
        return $priceTableAlias.'.type IN ('.implode(', ', array_map([$connection, 'quote'], $this->fuelTypes)).')';
    }
}