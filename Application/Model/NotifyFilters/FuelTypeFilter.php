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

class FuelTypeFilter extends AbstractFilter implements ItemFilter, DatabaseQueryFilter, MediumEfficencyFilter
{
    protected array $fuelTypes = [];

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
        $doFilter = !in_array($item->getFuelType(), $this->fuelTypes);

        if ($doFilter) {
            Registry::getLogger()->debug(get_class($this));
            Registry::getLogger()->debug("fuelTypes ".implode(', ', $this->fuelTypes)." do not match ".$item->getFuelType());
        }

        return $doFilter;
    }

    /**
     * @param string $priceTableAlias
     * @return string
     * @throws DoctrineException
     */
    public function getFilterQuery(string $priceTableAlias): string
    {
        $connection = DBConnection::getConnection();
        return $priceTableAlias.'.type IN ('.implode(', ', array_map([$connection, 'quote'], $this->fuelTypes)).')';
    }
}