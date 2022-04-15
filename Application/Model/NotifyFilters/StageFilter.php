<?php

namespace Daniels\FuelLogger\Application\Model\NotifyFilters;

use Daniels\FuelLogger\Application\Model\NotifyFilters\Interfaces\AbstractFilter;
use Daniels\FuelLogger\Application\Model\NotifyFilters\Interfaces\HighEfficencyFilter;
use Daniels\FuelLogger\Application\Model\PriceUpdates\UpdatesItem;
use Daniels\FuelLogger\Core\Registry;

class StageFilter extends AbstractFilter implements HighEfficencyFilter
{
    protected array $stageTypes = [];

    /**
     * @param array $stageTypes
     */
    public function __construct(array $stageTypes)
    {
        $this->stageTypes = $stageTypes;
    }

    /**
     * @param UpdatesItem $item
     * @return bool
     */
    public function filterItem(UpdatesItem $item): bool
    {
        startProfile(__METHOD__);

        $doFilter = !in_array($_ENV['STAGE'], $this->stageTypes);

        if ($doFilter) {
            $message = "stageTypes ".implode(', ', $this->stageTypes)." do not match ".$_ENV['STAGE'];
            Registry::getLogger()->debug(get_class($this));
            Registry::getLogger()->debug($message);
            $this->setDebugMessage($message);
        }

        stopProfile(__METHOD__);

        return $doFilter;
    }
}