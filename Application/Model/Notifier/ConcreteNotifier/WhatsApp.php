<?php

namespace Daniels\FuelLogger\Application\Model\Notifier\ConcreteNotifier;

use Daniels\FuelLogger\Application\Model\Notifier\CallMeBot;

class WhatsApp extends CallMeBot
{
    public string $endPoint = 'whatsapp.php';

    /**
     * @param $message
     *
     * @return string
     */
    public function getQuery($message): string
    {
        return http_build_query(
            [
                'source'    => 'php',
                'phone'     => $this->phoneNumber,
                'text'      => $message,
                'apikey'    => $this->apiKey
            ]
        );
    }
}