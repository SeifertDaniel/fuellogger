<?php

namespace Daniels\Benzinlogger\Application\Model\Notifier;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;

class WebHook extends AbstractNotifier implements NotifierInterface
{
    public $url;

    public function __construct($url)
    {
        $this->url = $url;
    }

    /**
     * @param $message
     * @param $price
     * @param $stations
     *
     * @return bool
     * @throws GuzzleException
     */
    public function notify($fuelType, $price, $stations) : bool
    {
        if (false === $this->canNotify($fuelType, $price)) {
            return false;
        }

        $message = 'Preis '.ucfirst($fuelType).': ' . $price . ' ' . $stations;
        $message = preg_replace('/' . PHP_EOL . '/', ' ', $message);

        $client = new Client();
        $response = $client->request(
            'POST',
            $this->url,
            $this->getSubmittedOptions($message)
        );

        return $response->getStatusCode() === 200;
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