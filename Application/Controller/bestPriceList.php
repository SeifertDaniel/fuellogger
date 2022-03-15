<?php

namespace Daniels\Benzinlogger\Application\Controller;

use Daniels\Benzinlogger\Application\Model\BestPrice;

class bestPriceList implements controllerInterface
{
    public function init()
    {

    }

    public function render()
    {
        $qb = (new BestPrice())->getQueryBuilder();

        echo "<table style='border: 1px solid silver'>";
        echo "<tr>";
        echo "<th>Tankstelle</th>";
        echo "<th>Ort</th>";
        echo "<th>aktueller Preis</th>";
        echo "<th>&Auml;nderung vor (Std:Min)</th>";
        echo "<th>Historie</th>";
        echo "</tr>";

        foreach ($qb->fetchAllAssociative() as $priceItem) {
            echo "<tr>";
            echo "<td>".$priceItem['name']."</td>";
            echo "<td>".$priceItem['place']."</td>";
            echo "<td>".$priceItem['price']."</td>";
            echo "<td>".$priceItem['timediff']."</td>";
            echo "<td><a href='index.php?cl=stationPriceList&stationId=".$priceItem['id']."'>Entwicklung</a></td>";
            echo "</tr>";
        }
        echo "</table>";
    }
}