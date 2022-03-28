<?php

namespace Daniels\FuelLogger\Application\Model\Notifier;

use Daniels\FuelLogger\Application\Model\NotifyFilters\filterPreventsNotificationException;
use Daniels\FuelLogger\Core\Registry;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;

/**
 * https://www.callmebot.com/
 */
class CallMeBot extends AbstractNotifier implements NotifierInterface
{
    public string $phoneNumber;
    public string $apiKey;

    public function __construct(string $phoneNumber, string $apiKey)
    {
        $this->phoneNumber = $phoneNumber;
        $this->apiKey = $apiKey;
    }

    /**
     * @param string $fuelType
     * @param float $price
     * @param string $stations
     *
     * @return bool
     * @throws filterPreventsNotificationException
     */
    public function notify(string $fuelType, float $price, string $stations) : bool
    {
        try {
            $this->checkForPassedFilters($fuelType, $price);

            Registry::getLogger()->debug(get_class($this).' notifies');
            $message = 'Preis ' . ucfirst($fuelType) . ': ' . $price . ' ' . $stations;

            $url = 'https://api.callmebot.com/whatsapp.php?source=php&phone=' . $this->phoneNumber . '&text=' . urlencode($message) . '&apikey=' . $this->apiKey;

            $client = new Client();
            $response = $client->get($url);

            return $response->getStatusCode() == 200;
        } catch (GuzzleException $e) {
            Registry::getLogger()->error($e->getMessage());
            return false;
        }
    }
}