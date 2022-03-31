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
        $fuelPrices = [];
        foreach (Fuel::getTypes() as $type) {
            $qb = (new BestPrice())->getTimeDiffSortedQueryBuilder($type);
            $fuelPrices[$type] = $qb->fetchAllAssociative();
        }

        Registry::getTwig()->addGlobal('fuelPrices', $fuelPrices);

        return 'pages/bestPriceList.html.twig';
    }
}