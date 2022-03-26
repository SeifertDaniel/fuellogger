<?php

namespace Daniels\Benzinlogger\Application\Model\Notifier;

use Daniels\Benzinlogger\Core\Registry;
use GuzzleHttp\Client;

/**
 * https://www.callmebot.com/
 */
class CallMeBot extends AbstractNotifier implements NotifierInterface
{
    public $phoneNumber;
    public $apiKey;

    public function __construct($phoneNumber, $apiKey)
    {
        $this->phoneNumber = $phoneNumber;
        $this->apiKey = $apiKey;
    }

    /**
     * @param $message
     * @param $price
     * @param $stations
     *
     * @return bool
     */
    public function notify($fuelType, $price, $stations) : bool
    {
        try {
            if (false === $this->canNotify($fuelType, $price)) {
                return false;
            }

            $message = 'Preis ' . ucfirst($fuelType) . ': ' . $price . ' ' . $stations;

            $url = 'https://api.callmebot.com/whatsapp.php?source=php&phone=' . $this->phoneNumber . '&text=' . urlencode($message) . '&apikey=' . $this->apiKey;

            $client = new Client();
            $response = $client->get($url);

            $return = $response->getStatusCode() == 200;
        } catch (\Exception $e) {
            Registry::getLogger()->error($e->getMessage());
            $return = false;
        }

        return $return;
    }
}