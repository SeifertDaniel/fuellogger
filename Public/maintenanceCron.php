<?php

namespace Daniels\FuelLogger\PublicDir;

use Daniels\FuelLogger\Application\Model\DBConnection;
use Daniels\FuelLogger\Application\Model\Entities\OilPrice;
use Daniels\FuelLogger\Application\Model\Entities\Price;
use Daniels\FuelLogger\Application\Model\Entities\PriceArchive;
use Daniels\FuelLogger\Application\Model\Exceptions\OilPriceAlreadyExistException;
use Daniels\FuelLogger\Application\Model\Oilprices\CommoditiesApi;
use Daniels\FuelLogger\Application\Model\Oilprices\CommoditiesApiException;
use Daniels\FuelLogger\Core\Base;
use Daniels\FuelLogger\Core\Registry;
use DateTime;
use Doctrine\DBAL\Exception as DoctrineException;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Exception;

require_once dirname(__FILE__) . "/../bootstrap.php";

class maintenanceCron extends Base
{
    const DATES_TO_ARCHIVE = 14;

    protected CommoditiesApi $api;

    /**
     * @throws CommoditiesApiException
     * @throws DoctrineException
     * @throws ORMException
     * @throws OptimisticLockException
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
     * @throws ORMException
     * @throws OptimisticLockException
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

            $oilPrice->setPrice(1 / $rates->{CommoditiesApi::SYMBOL_BRENTOIL});

            $em = Registry::getEntityManager();
            $em->persist($oilPrice);
            $em->flush();
        } catch (OilPriceAlreadyExistException $e) {
            Registry::getLogger()->error($e->getMessage());
            Registry::getLogger()->error($e->getTraceAsString());
        }

        stopProfile(__METHOD__);
    }

    /**
     * @throws ORMException
     */
    public function transferPricesToArchive()
    {
        startProfile(__METHOD__);

        try {
            $em = Registry::getEntityManager();

            $priceTable = $em->getClassMetadata( Price::class)->getTableName();
            $priceArchiveTable = $em->getClassMetadata( PriceArchive::class)->getTableName();

            $conn = DBConnection::getConnection();

            $queries = [
                'START TRANSACTION;',
                "INSERT INTO " . $conn->quoteIdentifier($priceArchiveTable) . " (id, date, type, min, avg, max)
                    SELECT UUID(), DATE_FORMAT(datetime, '%Y-%m-%d') as date, type, MIN(price) as min, AVG(price) as avg, MAX(price) as max 
                    FROM " . $conn->quoteIdentifier($priceTable) . "
                    WHERE DATE_FORMAT(datetime, '%Y-%m-%d') < DATE_SUB(NOW(), INTERVAL ".self::DATES_TO_ARCHIVE." DAY)
                    GROUP BY date, type;
                ",
                "DELETE FROM " . $conn->quoteIdentifier($priceTable) . "
                    WHERE DATE_FORMAT(datetime, '%Y-%m-%d') < DATE_SUB(NOW(), INTERVAL ".self::DATES_TO_ARCHIVE." DAY);
                ",
                "OPTIMIZE TABLE ".$conn->quoteIdentifier($priceTable).";",
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