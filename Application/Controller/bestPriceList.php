<?php

namespace Daniels\FuelLogger\Application\Controller;

use Daniels\FuelLogger\Application\Model\BestPrice;
use Daniels\FuelLogger\Application\Model\Fuel;
use Daniels\FuelLogger\Core\Registry;
use Doctrine\DBAL\Exception;

class bestPriceList implements controllerInterface
{
    public function init()
    {
    }

    /**
     * @return string
     * @throws Exception
     */
    public function render(): string
    {
        startProfile(__METHOD__);

        Registry::getTwig()->addGlobal('fuelPrices', $this->getBestPrices());

        stopProfile(__METHOD__);

        return 'pages/bestPriceList.html.twig';
    }

    /**
     * @return array
     * @throws Exception
     */
    public function getBestPrices(): array
    {
        startProfile(__METHOD__);

        $fuelPrices = [];
        foreach (Fuel::getTypes() as $type) {
            $qb = (new BestPrice())->getTimeDiffSortedQueryBuilder($type);
            $fuelPrices[$type] = $qb->fetchAllAssociative();
        }

        stopProfile(__METHOD__);

        return $fuelPrices;
    }
}