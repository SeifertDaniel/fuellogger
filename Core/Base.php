<?php

namespace Daniels\FuelLogger\Core;

class Base
{
    protected float $timeStart;
    protected float $timeEnd;

    public function __construct()
    {
        if ($_ENV['DEBUGMODE']) {
            $this->timeStart = microtime(true);
        }
    }

    public function finalize()
    {
        if ($_ENV['DEBUGMODE']) {
            $this->timeEnd = microtime(true);

            $debugInfo = new DebugInfo();
            echo $debugInfo->formatExecutionTime($this->getTotalTime());
        }
    }

    public function getTotalTime(): float
    {
        if ($this->timeEnd && $this->timeStart) {
            return $this->timeEnd - $this->timeStart;
        }

        return 0.0;
    }
}