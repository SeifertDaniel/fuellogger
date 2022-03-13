<?php

namespace Daniels\Benzinlogger\Application\Model\TimeController;

class FromTo extends AbstractTimeController
{
    public function __construct($from, $till)
    {
        $this->from = $from;
        $this->till = $till;
    }
}