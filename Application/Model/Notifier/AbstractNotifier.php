<?php

namespace Daniels\Benzinlogger\Application\Model\Notifier;

use Daniels\Benzinlogger\Application\Model\NotifyFilters\AbstractFilter;

abstract class AbstractNotifier implements NotifierInterface
{
    protected array $filters;

    public function setFilter(AbstractFilter $filter): self
    {
        $this->filters[] = $filter;

        return $this;
    }

    public function getFilters()
    {
        return $this->filters;
    }

    /**
     * @param $fuelType
     * @param float $price
     * @return bool
     */
    public function canNotify($fuelType, float $price)
    {
        return false === in_array(
            false,
            array_map(
                function (AbstractFilter $filter) use ($fuelType, $price) {
                    return $filter->isNotifiable($fuelType, $price);
                },
                $this->getFilters()
            )
        );
    }
}