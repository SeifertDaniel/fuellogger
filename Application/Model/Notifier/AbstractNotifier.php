<?php

namespace Daniels\Benzinlogger\Application\Model\Notifier;

use Daniels\Benzinlogger\Application\Model\TimeController\AllDay;
use Daniels\Benzinlogger\Application\Model\TimeController\TimeControllerInterface;

abstract class AbstractNotifier implements NotifierInterface
{
    /** @var TimeControllerInterface */
    public $timeControl;

    public function setTimeControl(TimeControllerInterface $timeControl)
    {
        $this->timeControl = $timeControl;

        return $this;
    }

    public function getTimeControl()
    {
        return $this->timeControl ?? new AllDay();
    }
}