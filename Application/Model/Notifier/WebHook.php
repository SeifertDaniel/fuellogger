<?php

namespace Daniels\Benzinlogger\Application\Model\Notifier;

use GuzzleHttp\Client;

class WebHook extends AbstractNotifier implements NotifierInterface
{
    public $url;

    public function __construct($url)
    {
        $this->url = $url;
    }

    public function notify($message, $price, $stations)
    {
        if (false === $this->getTimeControl()->availableAtTheMoment()) {
            return false;
        }

        $message .= ' ' . $price . ' ' . $stations;
        $message = preg_replace('/' . PHP_EOL . '/', ' ', $message);

        $client = new Client();
        $client->request(
            'POST',
            $this->url,
            $this->getSubmittedOptions($message)
        );
    }

    public function getSubmittedOptions($message): array
    {
        return [
            'curl'  => [
                CURLOPT_RETURNTRANSFER  => false,
                CURLOPT_SSL_VERIFYPEER  => false,
            ],

            //'body' => json_encode([$this->message]),
            'body' => '{"value1": "'.$message.'"}',
            'headers' => $this->getHeaders()
        ];
    }

    /**
     * @return string[]
     */
    public function getHeaders(): array
    {
        return ['Content-Type' => 'application/json'];
    }
}