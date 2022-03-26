<?php

namespace Daniels\FuelLogger\Application\Model\Notifier;

use Daniels\FuelLogger\Application\Model\Fuel;
use Daniels\FuelLogger\Application\Model\Notifier\ConcreteNotifier\Ifttt;
use Daniels\FuelLogger\Application\Model\Notifier\ConcreteNotifier\WhatsApp;
use Daniels\FuelLogger\Application\Model\NotifyFilters\DailyBestPriceFilter;
use Daniels\FuelLogger\Application\Model\NotifyFilters\FuelTypeFilter;
use Daniels\FuelLogger\Application\Model\NotifyFilters\TimeFilter;

class NotifierList
{
    public function getList()
    {
        return [
            new DebugNotifier(),
            (new Ifttt($_ENV['C001_IFTTT_URL'])),
            (new WhatsApp($_ENV['C001_WHAPP_PHONE'], $_ENV['C001_WHAPP_APIK']))
                ->addFilter(new TimeFilter('08:00:00', '22:00:00'))
                ->addFilter(new FuelTypeFilter([Fuel::TYPE_E10]))
                ->addFilter(new DailyBestPriceFilter())
        ];
    }
}