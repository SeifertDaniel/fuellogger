<?php

namespace Daniels\Benzinlogger\Application\Model\Notifier;

use Daniels\Benzinlogger\Application\Model\Fuel;
use Daniels\Benzinlogger\Application\Model\Notifier\ConcreteNotifier\Ifttt;
use Daniels\Benzinlogger\Application\Model\Notifier\ConcreteNotifier\WhatsApp;
use Daniels\Benzinlogger\Application\Model\TimeController\AllDay;
use Daniels\Benzinlogger\Application\Model\TimeController\FromTo;

class NotifierList
{
    public function getList($type = Fuel::TYPE_E10)
    {
        $list = array(
            Fuel::TYPE_E10   => [
                (new Ifttt($_ENV['C001_IFTTT_URL']))
                    ->setTimeControl(new AllDay()),
                (new WhatsApp($_ENV['C001_WHAPP_PHONE'], $_ENV['C001_WHAPP_APIK']))
                    ->setTimeControl(new FromTo('08:00:00', '22:00:00')),
            ],
            Fuel::TYPE_E5  => [
                (new WhatsApp($_ENV['C001_WHAPP_PHONE'], $_ENV['C001_WHAPP_APIK']))
                    ->setTimeControl(new FromTo('08:00:00', '22:00:00'))
            ],
            Fuel::TYPE_DIESEL  => [
                (new WhatsApp($_ENV['C001_WHAPP_PHONE'], $_ENV['C001_WHAPP_APIK']))
                    ->setTimeControl(new FromTo('08:00:00', '22:00:00'))
            ]
        );

        return $list[$type];
    }
}