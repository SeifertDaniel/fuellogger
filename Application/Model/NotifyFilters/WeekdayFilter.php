<?php

declare(strict_types=1);

namespace Daniels\FuelLogger\Application\Model\NotifyFilters;

use Daniels\FuelLogger\Application\Model\DBConnection;
use Daniels\FuelLogger\Application\Model\NotifyFilters\Interfaces\AbstractFilter;
use Daniels\FuelLogger\Application\Model\NotifyFilters\Interfaces\DatabaseQueryFilter;
use Daniels\FuelLogger\Application\Model\NotifyFilters\Interfaces\HighEfficencyFilter;
use Daniels\FuelLogger\Application\Model\PriceUpdates\UpdatesItem;
use Daniels\FuelLogger\Core\Registry;
use DateTime;
use Doctrine\DBAL\Exception;

class WeekdayFilter extends AbstractFilter implements DatabaseQueryFilter, HighEfficencyFilter
{
    const MONDAY = 1;
    const TUESDAY = 2;
    const WEDNESDAY = 3;
    const THURSDAY = 4;
    const FRIDAY = 5;
    const SATURDAY = 6;
    const SUNDAY = 7;

    private array $weekdays;

    /**
     * @param array $weekdays
     */
    public function __construct(array $weekdays)
    {
        $this->weekdays = $weekdays;
    }

    /**
     * @param UpdatesItem $item
     * @return bool
     */
    public function filterItem(UpdatesItem $item): bool
    {
        startProfile(__METHOD__);

        $currentWeekDay = (new DateTime())->format('N');
        $doFilter = !in_array($currentWeekDay, $this->weekdays);

        if ($doFilter) {
            $message = "Weekdays ".implode(', ', $this->weekdays)." do not match $currentWeekDay";
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
        return 'WEEKDAY('.$priceTableAlias.'.datetime) + 1 IN ('.implode(', ', array_map([$connection, 'quote'], $this->weekdays)).')';
    }
}