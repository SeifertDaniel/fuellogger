<?php

namespace Daniels\FuelLogger\PublicDir;

use Daniels\FuelLogger\Application\Model\DBConnection;
use Daniels\FuelLogger\Application\Model\Exceptions\OilPriceAlreadyExistException;
use Daniels\FuelLogger\Application\Model\OilPrice;
use Daniels\FuelLogger\Application\Model\Oilprices\CommoditiesApiException;
use Daniels\FuelLogger\Application\Model\Price;
use Daniels\FuelLogger\Application\Model\PriceArchive;
use Daniels\FuelLogger\Core\Base;
use Daniels\FuelLogger\Core\Registry;
use Daniels\FuelLogger\Application\Model\Oilprices\CommoditiesApi;
use DateTime;
use Doctrine\DBAL\Exception as DoctrineException;
use Exception;

require_once dirname(__FILE__) . "/../bootstrap.php";

class maintenanceCron extends Base
{
    const DATES_TO_ARCHIVE = 14;

    protected CommoditiesApi $api;

    /**
     * @throws CommoditiesApiException
     * @throws DoctrineException
     */
    public function __construct()
    {
        parent::__construct();

        $this->api = new CommoditiesApi($_ENV['COMMODITIESAPIKEY']);
        $this->addCurrentOilPrices();

        $this->transferPricesToArchive();

        $this->finalize();
    }

    /**
     * @return void
     * @throws CommoditiesApiException
     * @throws DoctrineException
     */
    public function addCurrentOilPrices()
    {
        startProfile(__METHOD__);

        try {
            $oilPrice = new OilPrice();
            $checkDate = (new DateTime())->format('Y-m-d');

            if ($oilPrice->existForDate($checkDate)) {
                throw new OilPriceAlreadyExistException('oil price exists already for ' . $checkDate);
            }

            $rates = $this->api->request([CommoditiesApi::SYMBOL_BRENTOIL]);

            $oilPrice->insert(1 / $rates->{CommoditiesApi::SYMBOL_BRENTOIL});
        } catch (OilPriceAlreadyExistException $e) {
            Registry::getLogger()->error($e->getMessage());
            Registry::getLogger()->error($e->getTraceAsString());
        }

        stopProfile(__METHOD__);
    }

    public function transferPricesToArchive()
    {
        startProfile(__METHOD__);

        try {
            $price = new Price();
            $priceTable = $price->getCoreTableName();

            $priceArchive = new PriceArchive();
            $priceArchiveTable = $priceArchive->getCoreTableName();

            $conn = DBConnection::getConnection();

            $queries = [
                'START TRANSACTION;',
                "INSERT INTO " . $priceArchiveTable . " (id, date, type, min, avg, max)
                    SELECT UUID(), DATE_FORMAT(datetime, '%Y-%m-%d') as date, type, MIN(price) as min, AVG(price) as avg, MAX(price) as max 
                    FROM " . $priceTable . "
                    WHERE DATE_FORMAT(datetime, '%Y-%m-%d') < DATE_SUB(NOW(), INTERVAL ".self::DATES_TO_ARCHIVE." DAY)
                    GROUP BY date, type;
                ",
                "DELETE FROM " . $priceTable . "
                    WHERE DATE_FORMAT(datetime, '%Y-%m-%d') < DATE_SUB(NOW(), INTERVAL ".self::DATES_TO_ARCHIVE." DAY);
                ",
                "COMMIT;"
            ];

            $conn->executeQuery(implode(' ', $queries));
        } catch (DoctrineException $e) {
            Registry::getLogger()->error($e->getMessage());
            Registry::getLogger()->error($e->getTraceAsString());
        }

        stopProfile(__METHOD__);
    }
}

try {
    new maintenanceCron();
} catch (Exception $e) {
    Registry::getLogger()->error($e->getMessage());
    Registry::getLogger()->error($e->getTraceAsString());
}