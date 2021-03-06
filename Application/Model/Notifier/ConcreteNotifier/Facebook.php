<?php

namespace Daniels\FuelLogger\Application\Model\Notifier\ConcreteNotifier;

use Daniels\FuelLogger\Application\Model\Notifier\CallMeBot;

class Facebook extends CallMeBot
{
    public string $endpoint = 'facebook/send.php';

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
                'text'      => $message,
                'apikey'    => $this->apiKey
            ]
        );
    }
}