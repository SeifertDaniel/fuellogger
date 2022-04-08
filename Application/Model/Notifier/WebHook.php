<?php

namespace Daniels\FuelLogger\Application\Model\Notifier;

use Daniels\FuelLogger\Application\Model\Exceptions\filterPreventsNotificationException;
use Daniels\FuelLogger\Application\Model\PriceUpdates\UpdatesList;
use Daniels\FuelLogger\Core\Registry;
use Doctrine\DBAL\Exception as DoctrineException;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;

class WebHook extends AbstractNotifier implements NotifierInterface
{
    public string $url;

    /**
     * @param string $url
     */
    public function __construct(string $url)
    {
        $this->url = $url;
    }

    /**
     * @param UpdatesList $priceUpdates
     *
     * @return bool
     * @throws DoctrineException
     * @throws filterPreventsNotificationException
     */
    public function notify(UpdatesList $priceUpdates) : bool
    {
        try {
            $priceUpdates = $this->getFilteredUpdates($priceUpdates);

            $message = 'Preis ' . ucfirst($fuelType) . ': ' . $price . ' ' . $stations;
            $message = preg_replace('/' . PHP_EOL . '/', ' ', $message);

            $client = new Client();
            $response = $client->request(
                'POST',
                $this->url,
                $this->getSubmittedOptions($message)
            );

            return $response->getStatusCode() === 200;
        } catch (GuzzleException $e) {
            Registry::getLogger()->error($e->getMessage());
            Registry::getLogger()->error($e->getTraceAsString());
            return false;
        }
    }

    /**
     * @param $message
     * @return array
     */
    public function getSubmittedOptions($message): array
    {
        return [
            'curl'  => [
                CURLOPT_RETURNTRANSFER  => false,
                CURLOPT_SSL_VERIFYPEER  => false,
            ],
            'connect_timeout' => 15,
            'read_timeout' => 45,
            'timeout' => 60,

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