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
use Doctrine\DBAL\Exception;

class PostCodeFilter extends AbstractFilter implements DatabaseQueryFilter, ItemFilter, MediumEfficencyFilter
{
    private array $postCodes;

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
        startProfile(__METHOD__);

        $doFilter = !in_array($item->getStationPostCode(), $this->postCodes);

        if ($doFilter) {
            $message = "Post codes ".implode(', ', $this->postCodes)." do not match ".$item->getStationPostCode();
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
     * @throws Exception
     */
    public function getFilterQuery(string $priceTableAlias, string $stationTableAlias): string
    {
        $connection = DBConnection::getConnection();
        return $stationTableAlias.'.postcode IN ('.implode(', ', array_map([$connection, 'quote'], $this->postCodes)).')';
    }
}