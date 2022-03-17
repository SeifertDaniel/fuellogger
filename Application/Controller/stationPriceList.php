<?php

namespace Daniels\Benzinlogger\Application\Controller;

use Daniels\Benzinlogger\Application\Model\DBConnection;
use Daniels\Benzinlogger\Application\Model\ezcGraph\ezcFlexibleColor2DRenderer;
use Daniels\Benzinlogger\Application\Model\ezcGraph\ezcGraphArrayDataSetOpneningTimesColors;
use Daniels\Benzinlogger\Application\Model\openingTimes;
use Daniels\Benzinlogger\Application\Model\Price;
use Daniels\Benzinlogger\Application\Model\PriceStatistics;
use Daniels\Benzinlogger\Application\Model\Station;
use Daniels\Benzinlogger\Core\Registry;
use ezcGraphDataSetColorProperty;
use ezcGraphLineChart;

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

        $prices = new Price();
        $priceTable = $prices->getCoreTableName();

        $qb->select("DATE_FORMAT(t1.datetime, '%d.%m. %H:%i') ts1", "DATE_FORMAT(IF(min(t2.datetime) IS NULL, NOW(), min(t2.datetime)), '%d.%m %H:%i') ts2", "t1.price")
            ->from($priceTable, 't1')
            ->leftJoin('t1', $priceTable, 't2', 't1.stationid = t2.stationid and t1.datetime < t2.datetime')
            ->where(
                $qb->expr()->and(
                    $qb->expr()->eq(
                        't1.stationid',
                        $qb->createNamedParameter($stationId)
                    ),
                    $qb->expr()->gt(
                        't1.datetime',
                        'date_sub(NOW(), interval 1 day)'
                    )
                )
            )
            ->groupBy('t1.stationid', 't1.datetime')
            ->orderBy('ts1', 'DESC');

        echo "<table style='border: 1px solid silver'>";
        echo "<tr>";
        echo "<th>von</th>";
        echo "<th>bis</th>";
        echo "<th>Preis</th>";
        echo "</tr>";

        foreach ($qb->fetchAllAssociative() as $priceItem) {
            echo "<tr>";
            echo "<td>".$priceItem['ts1']."</td>";
            echo "<td>".$priceItem['ts2']."</td>";
            echo "<td>".$priceItem['price']."</td>";
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

        $qbs = $conn->createQueryBuilder();
        $qbs->select('CONCAT(name, " (", place, ")")')
            ->from($stationTable)
            ->where($qbs->expr()->eq(
                'id',
                $qbs->createNamedParameter($stationId)
            ));
        $stationName = $qbs->fetchOne();

        $graph = new ezcGraphLineChart(['stackBars' => false]);
        $graph->renderer = new ezcFlexibleColor2DRenderer();
        $graph->title = $stationName;

        $interval = 5; // minutes
        $intervalSec = $interval * 60; // secondes
        $duration = 1; // week
        $intervalsPerWeek = 60 / $interval * 24 * (7 * $duration);

        $subQb1 = $conn->createQueryBuilder();
        $subQb1->select('date_sub(DATE_SUB(now(),INTERVAL MOD(unix_timestamp(now()),'.$intervalSec.') SECOND), interval '.$duration.' WEEK) + interval (seq * '.$interval.') Minute as hh')
            ->from('seq_0_to_'.$intervalsPerWeek);

        $subQb2 = $conn->createQueryBuilder();
        $subQb2->select('t1.price', 't1.datetime ts1', 'IF(min(t2.datetime) IS NULL, NOW(), min(t2.datetime)) ts2')
            ->from($priceTable, 't1')
            ->leftJoin('t1', $priceTable, 't2', 't1.stationid = t2.stationid and t1.datetime < t2.datetime')
            ->where('t1.stationid = '.$conn->quote($stationId))
            ->groupBy('t1.stationid', 't1.datetime');

        $qb = $conn->createQueryBuilder();
        $qb->select('DATE_FORMAT(sequence.hh, "%d.%m.%Y %H:%i") as datetime', 'priceseries.price * 100')
            ->from('('.$subQb1->getSQL().')', 'sequence')
            ->join('sequence', '('.$subQb2->getSQL().')', 'priceseries', 'sequence.hh BETWEEN priceseries.ts1 AND priceseries.ts2');
        $fetched = $qb->fetchAllKeyValue();

        $source = [
            'E10'   => $fetched
        ];

        $openingTimes = new openingTimes($stationId);

        // Add data
        foreach ( $source as $fuelType => $data )
        {
            $data = new ezcGraphArrayDataSetOpneningTimesColors( $data );
            $color = new ezcGraphDataSetColorProperty( $data );
            foreach ($data->getKeys() as $key) {
                if ($openingTimes->isClosedCached($key, $openingTimes->getWeekdayByDate($key))) {
                    $color->offsetSet( $key, new \ezcGraphColor( [ 'red' => 190, 'blue' => 190, 'green' => 190 ] ) );
                }
            }
            $data->setProperty('color', $color);

            $graph->data[$fuelType] = $data;
        }

        $graph->renderToOutput( 1000, 300 );

        die();
    }
}