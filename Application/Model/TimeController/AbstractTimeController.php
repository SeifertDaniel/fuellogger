<?php

namespace Daniels\Benzinlogger\Application\Model\TimeController;

use DateTime;

abstract class AbstractTimeController implements TimeControllerInterface
{
    public $from;

    public $till;

    public function availableAtTheMoment() {
        $f = DateTime::createFromFormat('!H:i:s', $this->from);
        $t = DateTime::createFromFormat('!H:i:s', $this->till);
        $i = (new DateTime())->setDate($f->format('Y'),$f->format('m'), $f->format('d'));
        if ($f > $t) $t->modify('+1 day');
        return ($f <= $i && $i <= $t) || ($f <= $i->modify('+1 day') && $i <= $t);
    }
}