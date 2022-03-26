<?php

namespace Daniels\FuelLogger\PublicDir;

use Daniels\FuelLogger\Application\Model\OilPrice;
use Daniels\FuelLogger\Core\Registry;
use Daniels\FuelLogger\Application\Model\Oilprices\CommoditiesApi;
use DateTime;
use Dotenv\Dotenv;

require_once __DIR__.'/../../vendor/autoload.php';

class oilPricesCron
{
    protected CommoditiesApi $api;

    public function __construct()
    {
        $dotenv = Dotenv::createImmutable(__DIR__."/../..");
        $dotenv->load();
        $dotenv->required(['DBHOST', 'DBNAME', 'DBUSER', 'DBPASS', 'DBDRIVER', 'TKAPIKEY', 'LOCATIONLAT', 'LOCATIONLNG', 'COMMODITIESAPIKEY'])->notEmpty();

        $this->api = new CommoditiesApi($_ENV['COMMODITIESAPIKEY']);
    }

    public function addCurrent()
    {
        $oilPrice = new OilPrice();
        $checkDate = (new DateTime())->format('Y-m-d');

        if ($oilPrice->existForDate($checkDate)) {
            throw new \Exception('oil price exists already for '.$checkDate);
        }

        $rates = $this->api->request([CommoditiesApi::SYMBOL_BRENTOIL]);

        $oilPrice->insert(1 / $rates->{CommoditiesApi::SYMBOL_BRENTOIL});
    }
}

try {
    $runner = new oilPricesCron();
    $runner->addCurrent();
} catch (\Exception $e) {
    Registry::getLogger()->error($e->getMessage());
}