<?php

namespace Daniels\Benzinlogger;

use Daniels\Benzinlogger\Application\Model\BestPriceNotifier;
use Daniels\Benzinlogger\Application\Model\Price;
use Daniels\Benzinlogger\Application\Model\Station;
use Dotenv\Dotenv;
use Lang\Tankerking\ApiClient;

require_once '../vendor/autoload.php';

class cron
{
    protected $api;

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

            if ($price->getLastPrice($stationId, ApiClient::TYPE_E10) != $stationData['price']) {
                $price->insert(
                    $stationId,
                    ApiClient::TYPE_E10,
                    $stationData['price']
                );
                $updatePrices[] = $stationData['price'];
            }
        }

        new BestPriceNotifier($updatePrices);
    }

    public function getStations()
    {
        return $this->api->search(
            $_ENV['LOCATIONLAT'],
            $_ENV['LOCATIONLNG'],
            ApiClient::TYPE_E10,
            4,
            ApiClient::SORT_DIST
        );
/*
        return [
            '51d4b48a-a095-1aa0-e100-80009459e03a'  => [
                'name'  => 'JET STOLLBERG ZU DEN TEICHEN 2',
                'brand' => 'JET',
                'dist'  => 0.6,
                'price' => 2.139,
                'street'    => 'ZU DEN TEICHEN',
                'houseNumber'   => '2',
                'postCode'  => '9366',
                'place'     => 'STOLLBERG'
            ],
            '005056ba-7cb6-1ed2-bceb-c115e44f8d50'  => [
                'name'  => 'star Tankstelle',
                'brand' => 'STAR',
                'dist'  => 1.3,
                'price' => 2.139,
                'street'    => 'Hohensteiner Straße',
                'houseNumber'   => '58',
                'postCode'  => '9366',
                'place'     => 'Stollberg'
            ]
        ];
*/
    }

    public function getDetails($stationId)
    {
        return $this->api->detail($stationId);
/*
        $details = new \stdClass();
        $details->id = '51d4b48a-a095-1aa0-e100-80009459e03a';
        $details->name = 'JET STOLLBERG ZU DEN TEICHEN 2';
        $details->brand = 'JET';
        $details->street = 'ZU DEN TEICHEN';
        $details->houseNumber = '2';
        $details->postCode = '9366';
        $details->place = 'STOLLBERG';
        $details->openingTimes = [
            [
                'text' => 'täglich ausser Sonn- und Feiertagen',
                'start' => '06:00:00',
                'end'   => '22:00:00'
            ],
            [
                'text'  => 'Sonntag, Feiertag',
                'start' => '07:00:00',
                'end'   => '22:00:00'
            ]
        ];
        $details->overrides = [];
        $details->wholeDay  = null;
        $details->isOpen    = 1;
        $details->e5    = 2.199;
        $details->e10   = 2.139;
        $details->diesel = 2.309;
        $details->lat   = 50.7162;
        $details->lng   = 12.782923;
        $details->state = 'deSN';

        return $details;
*/
    }
}

$runner = new cron();
$runner->addCurrent();