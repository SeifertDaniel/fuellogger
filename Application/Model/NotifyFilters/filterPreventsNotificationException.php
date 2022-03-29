<?php

namespace Daniels\FuelLogger\Application\Model\NotifyFilters;

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