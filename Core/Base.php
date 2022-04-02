<?php

namespace Daniels\FuelLogger\Core;

class Base
{
    protected float $timeStart;
    protected float $timeEnd;

    public function __construct()
    {
        if (Debug::showProfiling()) {
            $this->timeStart = microtime(true);
        }
    }

    public function finalize()
    {
        if (Debug::showProfiling()) {
            $this->timeEnd = microtime(true);

            $debugInfo = new Debug();
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