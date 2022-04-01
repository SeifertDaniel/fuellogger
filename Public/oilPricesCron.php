<?php

namespace Daniels\FuelLogger\PublicDir;

use Daniels\FuelLogger\Application\Model\OilPrice;
use Daniels\FuelLogger\Core\Base;
use Daniels\FuelLogger\Core\Registry;
use Daniels\FuelLogger\Application\Model\Oilprices\CommoditiesApi;
use DateTime;
use Exception;

require_once dirname(__FILE__) . "/../bootstrap.php";

class oilPricesCron extends Base
{
    protected CommoditiesApi $api;

    /**
     * @throws Exception
     */
    public function __construct()
    {
        parent::__construct();

        $this->api = new CommoditiesApi($_ENV['COMMODITIESAPIKEY']);

        $this->addCurrent();

        $this->finalize();
    }

    public function addCurrent()
    {
        $oilPrice = new OilPrice();
        $checkDate = (new DateTime())->format('Y-m-d');

        try {
            if ($oilPrice->existForDate($checkDate)) {
                throw new Exception('oil price exists already for ' . $checkDate);
            }

            $rates = $this->api->request([CommoditiesApi::SYMBOL_BRENTOIL]);

            $oilPrice->insert(1 / $rates->{CommoditiesApi::SYMBOL_BRENTOIL});
        } catch (Exception $e) {
            Registry::getLogger()->error($e->getMessage());
        }

    }
}

try {
    new oilPricesCron();
} catch (Exception $e) {
    Registry::getLogger()->error($e->getMessage());
}