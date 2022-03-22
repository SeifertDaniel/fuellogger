<?php

namespace Daniels\Benzinlogger;

use Daniels\Benzinlogger\Application\Model\BestPriceNotifier;
use Daniels\Benzinlogger\Application\Model\Fuel;
use Daniels\Benzinlogger\Application\Model\Price;
use Daniels\Benzinlogger\Application\Model\Station;
use Daniels\Benzinlogger\Core\Registry;
use Dotenv\Dotenv;
use Lang\Tankerking\ApiClient;
use Lang\Tankerking\GasStation;

require_once '../vendor/autoload.php';

class cron
{
    protected ApiClient $api;

    public function __construct()
    {
        $dotenv = Dotenv::createImmutable(__DIR__."/..");
        $dotenv->load();
        $dotenv->required(['DBHOST', 'DBNAME', 'DBUSER', 'DBPASS', 'DBDRIVER', 'TKAPIKEY', 'LOCATIONLAT', 'LOCATIONLNG'])->notEmpty();

        $this->api = new ApiClient($_ENV['TKAPIKEY']);
    }

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
     * @throws \Exception
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
     * @return GasStation
     * @throws \Exception
     */
    public function getDetails($stationId): GasStation
    {
        return $this->api->detail($stationId);
    }
}

try {
    $runner = new cron();
    $runner->addCurrent();
} catch (\Exception $e) {
    Registry::getLogger()->error($e->getMessage());
}