<?php

namespace Daniels\FuelLogger\PublicDir;

use Daniels\FuelLogger\Application\Model\BestPriceNotifier;
use Daniels\FuelLogger\Application\Model\Fuel;
use Daniels\FuelLogger\Application\Model\Price;
use Daniels\FuelLogger\Application\Model\Station;
use Daniels\FuelLogger\Core\Registry;
use DanielS\Tankerkoenig\ApiClient;
use DanielS\Tankerkoenig\ApiException;
use DanielS\Tankerkoenig\PetrolStation;
use Doctrine\DBAL\Exception as DoctrineException;
use Dotenv\Dotenv;
use Exception;
use GuzzleHttp\Exception\GuzzleException;

require_once __DIR__.'/../../vendor/autoload.php';

class fuelPricesCron
{
    protected ApiClient $api;

    public function __construct()
    {
        $dotenv = Dotenv::createImmutable(__DIR__."/../..");
        $dotenv->load();
        $dotenv->required(['DBHOST', 'DBNAME', 'DBUSER', 'DBPASS', 'DBDRIVER', 'TKAPIKEY', 'LOCATIONLAT', 'LOCATIONLNG'])->notEmpty();

        $this->api = new ApiClient($_ENV['TKAPIKEY']);
    }

    /**
     * @return void
     * @throws ApiException
     * @throws DoctrineException
     * @throws GuzzleException
     */
    public function addCurrent()
    {
        $updatePrices = [];

        foreach ($this->getStations() as $stationTkId => $stationData) {
            $station = new Station();
            $stationId = $station->getIdByTkId($stationTkId);

            if (false == $stationId ) {
                $details = $this->getDetails($stationTkId);
                $stationId = $station->insert(
                    $details->id,
                    $details->name,
                    $details->brand,
                    $details->street,
                    $details->houseNumber,
                    $details->postCode,
                    $details->place,
                    $details->openingTimes,
                    $details->lat,
                    $details->lng,
                    $details->state
                );
            }

            $price = new Price();

            foreach (Fuel::getTypes() as $type) {
                $updatePrices[$type] = [];
                if ($price->getLastPrice($stationId, $type) != $stationData[$type]) {
                    $price->insert(
                        $stationId,
                        $type,
                        $stationData[$type]
                    );

                    $updatePrices[$type][] = $stationData[$type];
                }
            }
        }

        Registry::getLogger()->debug(serialize($updatePrices));

        new BestPriceNotifier($updatePrices);
    }

    /**
     * @return array
     * @throws ApiException
     * @throws GuzzleException
     */
    public function getStations(): array
    {
        return $this->api->search(
            $_ENV['LOCATIONLAT'],
            $_ENV['LOCATIONLNG'],
            ApiClient::TYPE_ALL,
            4,
            ApiClient::SORT_DIST
        );
    }

    /**
     * @param $stationId
     *
     * @return PetrolStation
     * @throws ApiException
     * @throws GuzzleException
     */
    public function getDetails($stationId): PetrolStation
    {
        return $this->api->detail($stationId);
    }
}

try {
    $runner = new fuelPricesCron();
    $runner->addCurrent();
} catch (Exception|GuzzleException $e) {
    Registry::getLogger()->error($e->getMessage());
}