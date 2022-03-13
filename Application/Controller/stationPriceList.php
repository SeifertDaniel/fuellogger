<?php

namespace Daniels\Benzinlogger\Application\Controller;

use Daniels\Benzinlogger\Application\Model\DBConnection;
use Daniels\Benzinlogger\Application\Model\Price;
use Daniels\Benzinlogger\Application\Model\Station;

class stationPriceList implements controllerInterface
{
    public function render()
    {
        $stationId = $_GET['stationId'];

        $conn = DBConnection::getConnection();
        $qb = $conn->createQueryBuilder();

        $station = new Station();
        $stationTable = $station->getCoreTableName();
        $prices = new Price();
        $priceTable = $prices->getCoreTableName();

        $qb->select('st.name', 'st.place', "pr.price", 'pr.datetime')
            ->from($stationTable, 'st')
            ->leftJoin('st', $priceTable, 'pr', 'st.id = pr.stationid')
            ->where(
                $qb->expr()->eq(
                    "pr.stationid",
                    $qb->createNamedParameter($stationId)
                )
            )->orderBy("pr.datetime", 'ASC');

        echo "<table style='border: 1px solid silver'>";
        echo "<tr>";
        echo "<th>Tankstelle</th>";
        echo "<th>Ort</th>";
        echo "<th>aktueller Preis</th>";
        echo "<th>&Auml;nderung vor (Std:Min)</th>";
        echo "</tr>";

        foreach ($qb->fetchAllAssociative() as $priceItem) {
            echo "<tr>";
            echo "<td>".$priceItem['name']."</td>";
            echo "<td>".$priceItem['place']."</td>";
            echo "<td>".$priceItem['price']."</td>";
            echo "<td>".$priceItem['datetime']."</td>";
            echo "</tr>";
        }
        echo "</table>";
    }
}