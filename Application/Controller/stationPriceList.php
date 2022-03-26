<?php

namespace Daniels\FuelLogger\Application\Controller;

use Daniels\FuelLogger\Application\Model\DBConnection;
use Daniels\FuelLogger\Application\Model\ezcGraph\ezcFlexibleColor2DRenderer;
use Daniels\FuelLogger\Application\Model\ezcGraph\ezcGraphArrayDataSetOpneningTimesColors;
use Daniels\FuelLogger\Application\Model\Fuel;
use Daniels\FuelLogger\Application\Model\openingTimes;
use Daniels\FuelLogger\Application\Model\Price;
use Daniels\FuelLogger\Application\Model\PriceStatistics;
use Daniels\FuelLogger\Application\Model\Station;
use Daniels\FuelLogger\Core\Registry;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Exception as DoctrineException;
use Exception;
use ezcGraph;
use ezcGraphAxisRotatedLabelRenderer;
use ezcGraphColor;
use ezcGraphDataSetColorProperty;
use ezcGraphLineChart;
use ezcGraphPaletteBlack;

class stationPriceList implements controllerInterface
{
    public function __construct()
    {
        ini_set('display_errors', 1);
    }

    public function init() {}

    /**
     * @throws DoctrineException
     */
    public function render()
    {
        $stationId = Registry::getRequest()->getRequestEscapedParameter('stationId');
        $conn = DBConnection::getConnection();

        $station = new Station();
        $stationTable = $station->getCoreTableName();

        $qbs = $conn->createQueryBuilder();
        $qbs->select('CONCAT(name, " (", place, ")")')
            ->from($stationTable)
            ->where($qbs->expr()->eq(
                'id',
                $qbs->createNamedParameter($stationId)
            ));
        $stationName = $qbs->fetchOne();

        echo "<h1>".$stationName."</h1>";

        echo '<img src="'.Registry::getRequest()->getRequestUrl().'&amp;fnc=getGraph'.'">';

        $stationId = Registry::getRequest()->getRequestEscapedParameter('stationId');
        $conn = DBConnection::getConnection();

        foreach (Fuel::getTypes() as $type) {

            $pricestat = new PriceStatistics();
            $qb = $pricestat->getLowPriceStatsByStation($stationId, $type);

            echo "<h1>".ucfirst($type)."</h1>";
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

            $qb = $conn->createQueryBuilder();

            $prices = new Price();
            $priceTable = $prices->getCoreTableName();

            $qb->select("DATE_FORMAT(t1.datetime, '%d.%m. %H:%i') ts1", "DATE_FORMAT(IF(min(t2.datetime) IS NULL, NOW(), min(t2.datetime)), '%d.%m %H:%i') ts2", "t1.price")
               ->from($priceTable, 't1')
               ->leftJoin('t1', $priceTable, 't2', 't1.stationid = t2.stationid and t1.datetime < t2.datetime and t1.type = t2.type')
               ->where(
                   $qb->expr()->and(
                       $qb->expr()->eq(
                           't1.stationid',
                           $qb->createNamedParameter($stationId)
                       ),
                       $qb->expr()->eq(
                           't1.type',
                           $qb->createNamedParameter($type)
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
    }

    public function getGraph()
    {
        try {
            $stationId = Registry::getRequest()->getRequestEscapedParameter( 'stationId' );
            $conn      = DBConnection::getConnection();

            $source       = $this->getPriceSourceForChart( $conn, $stationId );
            $openingTimes = new openingTimes( $stationId );

            $graph           = new ezcGraphLineChart();
            $graph->renderer = new ezcFlexibleColor2DRenderer();

            // colors
            //$graph->background->background = new ezcGraphColor(['red'   => 255, 'green' => 255, 'blue'  => 255]);
            $graph->palette          = $palette = new ezcGraphPaletteBlack();
            $palette->minorGridColor = new ezcGraphColor( [
                                                              'red'   => 255,
                                                              'green' => 255,
                                                              'blue'  => 255,
                                                              'alpha' => 255
                                                          ] );
            $palette->dataSetSymbol = [ezcGraph::NO_SYMBOL];

            // axis
            $graph->yAxis->label                    = 'Benzinpreis';
            $graph->xAxis->axisLabelRenderer        = new ezcGraphAxisRotatedLabelRenderer();
            $graph->xAxis->axisLabelRenderer->angle = 0;
            $graph->xAxis->axisSpace                = .25;

            // legend
            $graph->legend->position      = ezcGraph::BOTTOM;
            $graph->legend->landscapeSize = .1;
            $graph->legend->border        = new ezcGraphColor( [ 'red'   => 255,
                                                                 'green' => 255,
                                                                 'blue'  => 255,
                                                                 'alpha' => 255
                                                               ] );

            // Add data
            foreach ( $source as $fuelType => $data ) {
                $data  = new ezcGraphArrayDataSetOpneningTimesColors( $data );
                $color = new ezcGraphDataSetColorProperty( $data );
                foreach ( $data->getKeys() as $key ) {
                    if ( $openingTimes->isClosedCached( $key, $openingTimes->getWeekdayByDate( $key ) ) ) {
                        $color->offsetSet( $key, new ezcGraphColor( [ 'red' => 190, 'blue' => 190, 'green' => 190 ] ) );
                    }
                }
                $data->setProperty( 'color', $color );

                $graph->data[ $fuelType ] = $data;
            }

            $graph->renderToOutput( 1000, 400 );
        } catch ( Exception $e) {
            print_r($e->getMessage());
        }

        die();
    }

    /**
     * @param Connection|null $conn
     * @param string                          $stationId
     *
     * @return array
     * @throws DoctrineException
     */
    protected function getPriceSourceForChart( ?Connection $conn, string $stationId ): array
    {
        $interval         = 5; // minutes
        $intervalSec      = $interval * 60; // secondes
        $duration         = 1; // week
        $intervalsPerWeek = 60 / $interval * 24 * ( 7 * $duration );

        $prices     = new Price();
        $priceTable = $prices->getCoreTableName();

        $source = [];
        foreach ( Fuel::getTypes() as $type ) {
            $subQb1 = $conn->createQueryBuilder();
            $subQb1->select(
                'date_sub(DATE_SUB(now(),INTERVAL MOD(unix_timestamp(now()),' . $intervalSec . ') SECOND), interval ' . $duration . ' WEEK) + interval (seq * ' . $interval . ') Minute as hh'
            )
            ->from( 'seq_0_to_' . $intervalsPerWeek );

            $subQb2 = $conn->createQueryBuilder();
            $subQb2->select( 't1.price', 't1.datetime ts1', 'IF(min(t2.datetime) IS NULL, NOW(), min(t2.datetime)) ts2' )
                   ->from( $priceTable, 't1' )
                   ->leftJoin( 't1', $priceTable, 't2', 't1.stationid = t2.stationid and t1.datetime < t2.datetime and t1.type = t2.type' )
                   ->where( 't1.stationid = ' . $conn->quote( $stationId ) )
                   ->andWhere( 't1.type = ' . $conn->quote( $type ) )
                   ->andWhere( 't1.datetime > DATE_SUB(NOW(), INTERVAL 1 WEEK)' )
                   ->groupBy( 't1.stationid', 't1.datetime' );

            $qb = $conn->createQueryBuilder();
            $qb->select( 'DATE_FORMAT(sequence.hh, "%d.%m.%Y %H:%i") as datetime', 'priceseries.price * 100' )
               ->from( '(' . $subQb1->getSQL() . ')', 'sequence' )
               ->join( 'sequence', '(' . $subQb2->getSQL() . ')', 'priceseries', 'sequence.hh BETWEEN priceseries.ts1 AND priceseries.ts2' );

            $fetched = $qb->fetchAllKeyValue();

            $source[ ucfirst( $type ) ] = $fetched;
        }

        return $source;
    }
}