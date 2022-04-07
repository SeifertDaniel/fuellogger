<?php

namespace Daniels\FuelLogger\Application\Model\NotifyFilters;

use Daniels\FuelLogger\Application\Model\PriceUpdates\UpdatesItem;
use Daniels\FuelLogger\Core\Registry;

class FuelTypeFilter extends AbstractFilter
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
}