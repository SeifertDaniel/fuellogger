<?php

namespace Daniels\FuelLogger\Application\Model\Notifier;

use Daniels\FuelLogger\Application\Model\Exceptions\filterPreventsNotificationException;
use Daniels\FuelLogger\Application\Model\PriceUpdates\UpdatesList;
use Daniels\FuelLogger\Core\Registry;
use Doctrine\DBAL\Exception as DoctrineException;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use LogicException;

/**
 * https://www.callmebot.com/
 */
abstract class CallMeBot extends AbstractNotifier implements NotifierInterface
{
    public string $endpoint;
    public string $phoneNumber;
    public string $apiKey;

    public function __construct(string $phoneNumber, string $apiKey)
    {
        if(!isset($this->endpoint)) {
            throw new LogicException( get_class( $this ) . ' must have a enpoint' );
        }

        $this->phoneNumber = $phoneNumber;
        $this->apiKey = $apiKey;
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

            Registry::getLogger()->debug(get_class($this).' notifies');
            $message = 'Preis ' . ucfirst($fuelType) . ': ' . $price . ' ' . $stations;

            $url = 'https://api.callmebot.com/'.$this->endpoint.'?'.$this->getQuery($message);

            $client = new Client();
            $response = $client->get($url, [
                'connect_timeout' => 15,
                'read_timeout' => 45,
                'timeout' => 60
            ]);

            return $response->getStatusCode() == 200;
        } catch (GuzzleException $e) {
            Registry::getLogger()->error($e->getMessage());
            Registry::getLogger()->error($e->getTraceAsString());
            return false;
        }
    }

    abstract public function getQuery($message);
}