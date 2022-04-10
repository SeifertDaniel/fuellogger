<?php

namespace Daniels\FuelLogger\Application\Model\NotifyFilters;

use Daniels\FuelLogger\Application\Model\DBConnection;
use Daniels\FuelLogger\Application\Model\Exceptions\filterPreventsNotificationException;
use Daniels\FuelLogger\Application\Model\NotifyFilters\Interfaces\AbstractFilter;
use Daniels\FuelLogger\Application\Model\NotifyFilters\Interfaces\DatabaseQueryFilter;
use Daniels\FuelLogger\Application\Model\NotifyFilters\Interfaces\ItemFilter;
use Daniels\FuelLogger\Application\Model\NotifyFilters\Interfaces\MediumEfficencyFilter;
use Daniels\FuelLogger\Application\Model\PriceUpdates\UpdatesItem;
use Daniels\FuelLogger\Core\Registry;
use Doctrine\DBAL\Exception;

class PriceFilter extends AbstractFilter implements DatabaseQueryFilter, ItemFilter, MediumEfficencyFilter
{
    const LOWERTHAN = '<';
    const LOWERTHANEQUALS = '<=';
    const EQUALS = '=';
    const NOTEQUALS = '<>';
    const HIGHERTHANEQUALS = '>=';
    const HIGHERTHAN = '>';

    public string $operator;
    public float $price;

    /**
     * @param string $operator
     * @param float  $price
     */
    public function __construct(string $operator, float $price)
    {
        $this->operator = $operator;
        $this->price    = $price;
    }

    /**
     * @param UpdatesItem $item
     *
     * @return bool
     * @throws filterPreventsNotificationException
     */
    public function filterItem(UpdatesItem $item): bool
    {
        startProfile(__METHOD__);

        switch ($this->operator) {
            case self::EQUALS:
                $valid = $item->getFuelPrice() === $this->price;
                break;
            case self::HIGHERTHAN:
                $valid = $item->getFuelPrice() > $this->price;
                break;
            case self::HIGHERTHANEQUALS:
                $valid = $item->getFuelPrice() >= $this->price;
                break;
            case self::LOWERTHAN:
                $valid = $item->getFuelPrice() < $this->price;
                break;
            case self::LOWERTHANEQUALS:
                $valid = $item->getFuelPrice() <= $this->price;
                break;
            case self::NOTEQUALS:
                $valid = $item->getFuelPrice() <> $this->price;
                break;
            default:
                $this->setDebugMessage('invalid price comparison operator '.$this->operator);
                throw new filterPreventsNotificationException($this);
        }

        $doFilter = !$valid;

        if ($doFilter) {
            $message = "price ".$item->getFuelPrice()." is ".$this->operator." ". $this->price;
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
     * @throws filterPreventsNotificationException
     */
    public function getFilterQuery(string $priceTableAlias, string $stationTableAlias): string
    {
        $connection = DBConnection::getConnection();
        return $priceTableAlias.'.price '.$this->checkIsValid($this->operator).' '.$connection->quote($this->price);
    }

    /**
     * @param $operator
     *
     * @throws filterPreventsNotificationException
     */
    public function checkIsValid($operator)
    {
        if (!in_array(
            $operator,
            [
                self::LOWERTHAN,
                self::LOWERTHANEQUALS,
                self::EQUALS,
                self::NOTEQUALS,
                self::HIGHERTHANEQUALS,
                self::HIGHERTHAN
            ]
        )) {
            $this->setDebugMessage('invalid price comparison operator '.$this->operator);
            throw new filterPreventsNotificationException($this);
        }

        return $operator;
    }
}