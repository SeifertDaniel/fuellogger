<?php

namespace Daniels\FuelLogger\Application\Model\Notifier;

use Daniels\FuelLogger\Application\Model\NotifyFilters\AbstractFilter;
use Daniels\FuelLogger\Application\Model\NotifyFilters\DatabaseQueryFilter;
use Daniels\FuelLogger\Application\Model\NotifyFilters\filterPreventsNotificationException;
use Daniels\FuelLogger\Application\Model\PriceUpdates\emptyUpdatesListException;
use Daniels\FuelLogger\Application\Model\PriceUpdates\UpdatesList;

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
     * @return array
     */
    public function getQueryFilters(): array
    {
        $queryFilters = [];

        /** @var AbstractFilter $filter */
        foreach ($this->filters as $filter) {
            if ($filter instanceof DatabaseQueryFilter) {
                $queryFilters[] = $filter;
            }
        }

        return $queryFilters;
    }

    /**
     * @param UpdatesList $priceUpdates
     * @return UpdatesList
     * @throws emptyUpdatesListException
     */
    public function getFilteredUpdates(UpdatesList $priceUpdates) : UpdatesList
    {
        /** @var AbstractFilter $filter */
        foreach ($this->getFilters() as $filter) {
            $filter->setNotifier($this);
            $priceUpdates = $filter->filterPriceUpdates($priceUpdates);
        }

        if (! (bool) $priceUpdates->count()) {
            throw new emptyUpdatesListException();
        }

        return $priceUpdates;
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