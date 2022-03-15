<?php

namespace Daniels\Benzinlogger\Application\Controller;

use Daniels\Benzinlogger\Application\Model\DBConnection;
use Daniels\Benzinlogger\Application\Model\Price;
use Daniels\Benzinlogger\Application\Model\PriceStatistics;
use Daniels\Benzinlogger\Application\Model\Station;
use Daniels\Benzinlogger\Core\Registry;
use ezcGraphArrayDataSet;
use ezcGraphDataSetColorProperty;
use ezcGraphLineChart;
use ezcGraphRenderer3d;

class stationPriceList implements controllerInterface
{
    public function init()
    {

    }

    public function render()
    {
        echo '<img src="'.Registry::getRequest()->getRequestUrl().'&amp;fnc=getGraph'.'">';

        $stationId = Registry::getRequest()->getRequestEscapedParameter('stationId');

        $pricestat = new PriceStatistics();
        $qb = $pricestat->getLowPriceStatsByStation($stationId);

        echo "<table style='border: 1px solid silver'>";
        echo "<tr>";
        echo "<th>Datum</th>";
        echo "<th>durchschn. Erh&ouml;hung</th>";
        echo "<th>durchschn. Haltezeit Tiefpreis</th>";
        echo "</tr>";

        foreach ($qb->fetchAllAssociative() as $statItem) {
            echo "<tr>";
            echo "<td>".$statItem['date']."</td>";
            echo "<td>".$statItem['pricediff']."</td>";
            echo "<td>".$statItem['timediff']." Min.</td>";
            echo "</tr>";
        }
        echo "</table>";

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
            )->orderBy("pr.datetime", 'DESC')
            ->setMaxResults(20);

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

    public function getGraph()
    {
        $stationId = Registry::getRequest()->getRequestEscapedParameter('stationId');
        $conn = DBConnection::getConnection();

        $station = new Station();
        $stationTable = $station->getCoreTableName();
        $prices = new Price();
        $priceTable = $prices->getCoreTableName();

        $qb = $conn->createQueryBuilder();
        $qb->select('name')
            ->from($stationTable)
            ->where($qb->expr()->eq(
                'id',
                $qb->createNamedParameter($stationId)
            ));
        $stationName = $qb->fetchOne();

        $graph = new ezcGraphLineChart(['stackBars' => false]);
        $graph->title = $stationName;

        $qb = $conn->createQueryBuilder();
        $qb->select('st.name', 'st.place', "pr.price", 'pr.datetime')
           ->from($stationTable, 'st')
           ->leftJoin('st', $priceTable, 'pr', 'st.id = pr.stationid')
           ->where(
               $qb->expr()->eq(
                   "pr.stationid",
                   $qb->createNamedParameter($stationId)
               )
           )->orderBy("pr.datetime", 'ASC');

        $fetched = $qb->fetchAllAssociative();

        $firstDate = current($fetched)['datetime'];

        $rawValues = [];
        foreach ($qb->fetchAllAssociative() as $priceItem) {
            $dt = new \DateTime($priceItem['datetime']);
            $formatted = $dt->format('d.m. H:i');
            $rawValues[$formatted] = $priceItem['price'];
        }

        $currentPrice = 200;
        $dValues = [];
        $period = new \DatePeriod(new \DateTime($firstDate), new \DateInterval('PT1M'), new \DateTime());
        foreach($period as $date) {
            if (isset($rawValues[$date->format("d.m. H:i")])) {
                $currentPrice = $rawValues[$date->format("d.m. H:i")]*100;
            }
            $dValues[$date->format("d.m. H:i")] = $currentPrice;
        }

        $source = [
            'E10'   => $dValues
        ];

        // Add data
        foreach ( $source as $fuelType => $data )
        {
            $data = new ezcGraphArrayDataSet( $data );
            $graph->data[$fuelType] = $data;
        }

        $graph->renderToOutput( 700, 300 );

        die();
    }
}