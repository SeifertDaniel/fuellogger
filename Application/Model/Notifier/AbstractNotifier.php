<?php

namespace Daniels\FuelLogger\Application\Model\Notifier;

use Daniels\FuelLogger\Application\Model\Exceptions\filterPreventsNotificationException;
use Daniels\FuelLogger\Application\Model\NotifyFilters\Interfaces\AbstractFilter;
use Daniels\FuelLogger\Application\Model\NotifyFilters\Interfaces\DatabaseQueryFilter;
use Daniels\FuelLogger\Application\Model\NotifyFilters\Interfaces\HighEfficencyFilter;
use Daniels\FuelLogger\Application\Model\NotifyFilters\Interfaces\MediumEfficencyFilter;
use Daniels\FuelLogger\Application\Model\PriceUpdates\UpdatesList;
use Doctrine\DBAL\Exception;

abstract class AbstractNotifier implements NotifierInterface
{
    protected array $filters = [];
    protected bool $filtersAreSorted = false;

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
        if (!$this->filtersAreSorted) {
            $this->sortFiltersByEfficency();
        }

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
     *
     * @return UpdatesList
     * @throws Exception
     * @throws filterPreventsNotificationException
     */
    public function getFilteredUpdates(UpdatesList $priceUpdates) : UpdatesList
    {
        /** @var AbstractFilter $filter */
        foreach ($this->getFilters() as $filter) {
            $filter->setNotifier($this);
            $priceUpdates = $filter->filterPriceUpdates($priceUpdates);
        }

        return $priceUpdates;
    }

    public function sortFiltersByEfficency()
    {
        $highEfficency = [];
        $mediumEfficency = [];
        $lowEfficency = [];

        foreach ($this->filters as $filter) {
            switch (true) {
                case $filter instanceof HighEfficencyFilter:
                    $highEfficency[] = $filter;
                    break;
                case $filter instanceof MediumEfficencyFilter:
                    $mediumEfficency[] = $filter;
                    break;
                default:
                    $lowEfficency[] = $filter;
            }
        }

        $this->filters = array_merge($highEfficency, $mediumEfficency, $lowEfficency);

        $this->filtersAreSorted = true;
    }
}