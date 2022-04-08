<?php

namespace Daniels\FuelLogger\Application\Model\Exceptions;

use Daniels\FuelLogger\Application\Model\NotifyFilters\Interfaces\AbstractFilter;
use Exception;

class filterPreventsNotificationException extends Exception
{
    /**
     * @param AbstractFilter $filter
     */
    public function __construct( AbstractFilter $filter )
    {
        parent::__construct(get_class($filter)." ".$filter->getDebugMessage());
    }
}