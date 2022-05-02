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

class BrandFilter extends AbstractFilter implements ItemFilter, DatabaseQueryFilter, MediumEfficencyFilter
{
    private array $brands = [];

    /**
     * @param array $brands
     */
    public function __construct(array $brands)
    {
        $this->brands = $brands;
    }

    /**
     * @param UpdatesItem $item
     * @return bool
     */
    public function filterItem(UpdatesItem $item): bool
    {
        startProfile(__METHOD__);

        $doFilter = !in_array($item->getStationBrand(), $this->brands);

        if ($doFilter) {
            $message = "Brands ".implode(', ', $this->brands) . " do not match " . $item->getStationBrand();
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
        return $stationTableAlias.'.brand IN ('.implode(', ', array_map([$connection, 'quote'], $this->brands)) . ')';
    }
}