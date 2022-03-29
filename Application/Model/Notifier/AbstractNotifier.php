<?php

namespace Daniels\FuelLogger\Application\Model\Notifier;

use Daniels\FuelLogger\Application\Model\NotifyFilters\AbstractFilter;
use Daniels\FuelLogger\Application\Model\NotifyFilters\filterPreventsNotificationException;

abstract class AbstractNotifier implements NotifierInterface
{
    protected array $filters = [];

    /**
     * @param AbstractFilter $filter
     * @return $this
     */
    public function addFilter(AbstractFilter $filter): self
    {
        $this->filters[] = $filter;

        return $this;
    }

    /**
     * @return array
     */
    public function getFilters(): array
    {
        return $this->filters;
    }

    /**
     * @param $fuelType
     * @param float $price
     * @throws filterPreventsNotificationException
     */
    public function checkForPassedFilters($fuelType, float $price)
    {
        foreach ($this->getFilters() as $filter) {
            if (false === $filter->isNotifiable($fuelType, $price)) {
                throw new filterPreventsNotificationException($filter);
            }
        }
    }
}