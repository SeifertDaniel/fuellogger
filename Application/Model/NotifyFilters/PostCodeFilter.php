<?php

namespace Daniels\FuelLogger\Application\Model\NotifyFilters;

use Daniels\FuelLogger\Application\Model\PriceUpdates\UpdatesItem;
use Daniels\FuelLogger\Core\Registry;

class PostCodeFilter extends AbstractFilter
{
    protected array $postCodes = [];

    /**
     * @param array $postCodes
     */
    public function __construct(array $postCodes)
    {
        $this->postCodes = $postCodes;
    }

    /**
     * @param UpdatesItem $item
     * @return bool
     */
    public function filterItem(UpdatesItem $item): bool
    {
        $doFilter = !in_array($item->getStationPostCode(), $this->postCodes);

        if ($doFilter) {
            Registry::getLogger()->debug(get_class($this));
            Registry::getLogger()->debug("Post codes ".implode(', ', $this->postCodes)." do not match ".$item->getStationPostCode());
        }

        return $doFilter;
    }
}