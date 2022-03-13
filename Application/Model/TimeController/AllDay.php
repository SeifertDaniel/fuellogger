<?php

namespace Daniels\Benzinlogger\Application\Model\TimeController;

class AllDay extends AbstractTimeController
{
    public function __construct()
    {
        $this->from = '00:00:00';
        $this->till = '23:59:59';
    }
}