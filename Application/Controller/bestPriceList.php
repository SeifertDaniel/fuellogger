<?php

namespace Daniels\FuelLogger\Application\Controller;

use Daniels\FuelLogger\Application\Model\BestPrice;
use Daniels\FuelLogger\Application\Model\Fuel;
use Daniels\FuelLogger\Core\Registry;

class bestPriceList implements controllerInterface
{
    public function init()
    {
    }

    public function render(): string
    {
        $fuelPrices = [];
        foreach (Fuel::getTypes() as $type) {
            $qb = (new BestPrice())->getQueryBuilder($type);
            $fuelPrices[$type] = $qb->fetchAllAssociative();
        }

        Registry::getTwig()->addGlobal('fuelPrices', $fuelPrices);

        return 'pages/bestPriceList.html.twig';
    }
}