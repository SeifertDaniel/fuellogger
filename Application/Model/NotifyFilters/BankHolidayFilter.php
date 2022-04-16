<?php

namespace Daniels\FuelLogger\Application\Model\NotifyFilters;

use Daniels\FuelLogger\Application\Model\DBConnection;
use Daniels\FuelLogger\Application\Model\NotifyFilters\Interfaces\AbstractFilter;
use Daniels\FuelLogger\Application\Model\NotifyFilters\Interfaces\DatabaseQueryFilter;
use Daniels\FuelLogger\Application\Model\NotifyFilters\Interfaces\HighEfficencyFilter;
use Daniels\FuelLogger\Application\Model\PriceUpdates\UpdatesItem;
use Daniels\FuelLogger\Core\Registry;
use DateTime;
use Doctrine\DBAL\Exception;
use Yasumi\Provider\Germany\Saxony;
use Yasumi\Yasumi;

class BankHolidayFilter extends AbstractFilter implements DatabaseQueryFilter, HighEfficencyFilter
{
    const ISBANKHOLIDAY = true;
    const ISNOBANKHOLIDAY = false;

    public bool $isBankHoliday;

    /**
     * @param bool $isBankHoliday
     */
    public function __construct(bool $isBankHoliday = self::ISBANKHOLIDAY)
    {
        $this->isBankHoliday = $isBankHoliday;
    }

    public function filterItem(UpdatesItem $item): bool
    {
        startProfile(__METHOD__);

        $doFilter = $this->getDoFilter();

        if ($doFilter) {
            $comparison = $this->isBankHoliday ? '' : ' no ';
            $message = "Date  is ".$comparison." bank holiday";
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
        return $this->getDoFilter() ? '1' : '0';
    }

    /**
     * @return bool
     */
    public function getDoFilter(): bool
    {
        $holidays = Yasumi::create(Saxony::class, (new DateTime())->format('Y'));
        $isHoliday = $holidays->isHoliday(new DateTime());

        return $this->isBankHoliday ? $isHoliday : !$isHoliday;
    }
}