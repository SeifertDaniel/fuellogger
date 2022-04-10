<?php

namespace Daniels\FuelLogger\Application\Model\Notifier;

use Daniels\FuelLogger\Application\Model\Exceptions\filterPreventsNotificationException;
use Daniels\FuelLogger\Application\Model\PriceUpdates\UpdatesItem;
use Daniels\FuelLogger\Application\Model\PriceUpdates\UpdatesList;
use Daniels\FuelLogger\Core\Registry;
use Doctrine\DBAL\Exception as DoctrineException;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Exception\TransferException;

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
     * @throws filterPreventsNotificationException
     */
    public function notify(UpdatesList $priceUpdates) : bool
    {
        startProfile(__METHOD__);

        Registry::getLogger()->debug(__METHOD__);

        $this->setUpdateList($priceUpdates);

        try {
            $this->filterUpdates();

            /** @var UpdatesItem $item */
            foreach ($this->getUpdateList()->getList() as $item) {
                Registry::getLogger()->debug(get_class($this).' notifies');
                $message = 'Preis ' . ucfirst($item->getFuelType()) . ': ' . $item->getFuelPrice() . ' ' . $item->getStationName();
                $message = preg_replace('/' . PHP_EOL . '/', ' ', $message);

                startProfile(__METHOD__.'::request');

                $client = new Client();
                $response = $client->request(
                    'POST',
                    $this->url,
                    $this->getSubmittedOptions($message)
                );

                stopProfile(__METHOD__.'::request');

                if ($response->getStatusCode() != 200) {
                    throw new TransferException(get_class($this).' request returns '.$response->getStatusCode().' - '.$this->url);
                }
            }

            return true;
        } catch (GuzzleException $e) {
            Registry::getLogger()->error($e->getMessage());
            Registry::getLogger()->error($e->getTraceAsString());
            return false;
        } finally {
            stopProfile(__METHOD__);
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