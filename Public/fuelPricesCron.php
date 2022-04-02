<?php

namespace Daniels\FuelLogger\PublicDir;

use Daniels\FuelLogger\Application\Model\BestPriceNotifier;
use Daniels\FuelLogger\Application\Model\Fuel;
use Daniels\FuelLogger\Application\Model\Price;
use Daniels\FuelLogger\Application\Model\Station;
use Daniels\FuelLogger\Core\Base;
use Daniels\FuelLogger\Core\Registry;
use DanielS\Tankerkoenig\ApiClient;
use DanielS\Tankerkoenig\ApiException;
use DanielS\Tankerkoenig\PetrolStation;
use Doctrine\DBAL\Exception as DoctrineException;
use Exception;
use GuzzleHttp\Exception\GuzzleException;

require_once dirname(__FILE__) . "/../bootstrap.php";

class fuelPricesCron extends Base
{
    protected ApiClient $api;

    /**
     * @throws ApiException
     * @throws DoctrineException
     * @throws GuzzleException
     */
    public function __construct()
    {
        parent::__construct();

        $this->api = new ApiClient($_ENV['TKAPIKEY']);

        $this->addCurrent();

        $this->finalize();
    }

    /**
     * @return void
     * @throws ApiException
     * @throws DoctrineException
     * @throws GuzzleException
     */
    public function addCurrent()
    {
        startProfile(__METHOD__);

        $updatePrices = [];
        foreach (Fuel::getTypes() as $type) {
            $updatePrices[$type] = [];
        };

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

        stopProfile(__METHOD__);
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
    new fuelPricesCron();
} catch (Exception $e) {
    Registry::getLogger()->error($e->getMessage());
    Registry::getLogger()->error($e->getTraceAsString());
}