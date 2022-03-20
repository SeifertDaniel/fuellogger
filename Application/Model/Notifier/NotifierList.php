<?php

namespace Daniels\Benzinlogger\Application\Model\Notifier;

use Daniels\Benzinlogger\Application\Model\Fuel;
use Daniels\Benzinlogger\Application\Model\Notifier\ConcreteNotifier\Ifttt;
use Daniels\Benzinlogger\Application\Model\Notifier\ConcreteNotifier\WhatsApp;
use Daniels\Benzinlogger\Application\Model\NotifyFilters\FuelTypeFilter;
use Daniels\Benzinlogger\Application\Model\NotifyFilters\TimeFilter;

class NotifierList
{
    public function getList()
    {
        $list = [
            new DebugNotifier(),
            (new Ifttt($_ENV['C001_IFTTT_URL'])),
            (new WhatsApp($_ENV['C001_WHAPP_PHONE'], $_ENV['C001_WHAPP_APIK']))
                ->setFilter(new TimeFilter('08:00:00', '23:00:00'))
                ->setFilter(new FuelTypeFilter([Fuel::TYPE_E10]))
        ];

        return $list;
    }
}