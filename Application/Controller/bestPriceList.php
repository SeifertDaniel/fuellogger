<?php

namespace Daniels\FuelLogger\Application\Controller;

use Daniels\FuelLogger\Application\Model\BestPrice;
use Daniels\FuelLogger\Application\Model\Fuel;
use Daniels\FuelLogger\Core\Registry;
use DateTime;
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
        Registry::getTwig()->addGlobal('reloadTime', $this->getReloadTime());

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

        $postCodeFilter = Registry::getRequest()->getRequestEscapedParameter('postcode');

        $filter = [
            'st.postCode' => $postCodeFilter
        ];
        Registry::getTwig()->addGlobal('postCodeFilter', $postCodeFilter);

        $fuelPrices = [];
        foreach (Fuel::getTypes() as $type) {
            $qb = (new BestPrice())->getTimeDiffSortedQueryBuilder($type, $filter);
            $fuelPrices[$type] = $qb->fetchAllAssociative();
        }

        stopProfile(__METHOD__);

        return $fuelPrices;
    }

    /**
     * @return array
     */
    public function getReloadTime(): array
    {
        $datetime = new DateTime();

        $hour = $datetime->format('H');
        $n = $datetime->format('i');
        $x = 7;
        $minute = round(($n + $x / 2)/$x)*$x;

        if ($minute > 59) {
            $minute = 0;
            $hour = $hour >= 23 ? 0 : $hour + 1;
        }

        return ['hour' => $hour, 'minute' => $minute, "second" => 15];
    }
}