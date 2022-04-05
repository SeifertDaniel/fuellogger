<?php

namespace Daniels\FuelLogger\PublicDir;

use Daniels\FuelLogger\Application\Model\BestPriceNotifier;
use Daniels\FuelLogger\Application\Model\DBConnection;
use Daniels\FuelLogger\Application\Model\Fuel;
use Daniels\FuelLogger\Application\Model\Price;
use Daniels\FuelLogger\Application\Model\Station;
use Daniels\FuelLogger\Application\Model\StationList;
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

        $updatePrices = $this->addFromSurroundingSearch();

        new BestPriceNotifier($updatePrices);

        stopProfile(__METHOD__);
    }

    /**
     * @return array
     * @throws ApiException
     * @throws DoctrineException
     * @throws GuzzleException
     */
    public function addFromSurroundingSearch(): array
    {
        $updatePrices = [];
        foreach (Fuel::getTypes() as $type) {
            $updatePrices[$type] = [];
        }

        foreach ($this->getStations() as $stationTkId => $stationData) {
            $station = new Station();
            $stationId = $station->getIdByTkId($stationTkId);

            if (false == $stationId) {
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
        return $updatePrices;
    }

    /**
     * @return array
     * @throws ApiException
     * @throws DoctrineException
     * @throws GuzzleException
     */
    public function addFromStationList(): array
    {
        $updatePrices = [];
        foreach (Fuel::getTypes() as $type) {
            $updatePrices[$type] = [];
        }

        foreach ($this->api->prices($this->getStationIds()) as $priceInfo) {
            foreach (Fuel::getTypes() as $type) {
                $price = new Price();
                if ($priceInfo[$type] && $price->getLastPrice($priceInfo['stationId'], $type) != $priceInfo[$type]) {
                    $price->insert(
                        $priceInfo['stationId'],
                        $type,
                        $priceInfo[$type]
                    );

                    $updatePrices[$type][] = $priceInfo[$type];
                }
            }

        }

        return $updatePrices;
    }

    /**
     * @return array
     * @throws DoctrineException
     */
    public function getStationIds(): array
    {
        $qb = DBConnection::getConnection()->createQueryBuilder();
        $qb->select('st.id', 'st.tkid')
            ->from((new Station())->getCoreTableName(), 'st')
            ->where('1')
            ->setMaxResults(8);

        $list = new StationList();
        $list->selectString($qb->getSQL(), $qb->getParameters());

        return $list->getTKStationIds();
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