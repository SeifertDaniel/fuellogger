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
    public function render(): string
    {
        startProfile(__METHOD__);

        $stationId = Registry::getRequest()->getRequestEscapedParameter('stationId');
        $conn = DBConnection::getConnection();

        Registry::getTwig()->addGlobal('openingTimes', $this->getOpeningTimes($stationId));
        Registry::getTwig()->addGlobal('station', $this->getStation($conn, $stationId));
        Registry::getTwig()->addGlobal('currPrices', $this->getCurrentPrices($stationId, $conn));
        Registry::getTwig()->addGlobal('requestUrl', Registry::getRequest()->getRequestUrl());
        Registry::getTwig()->addGlobal('lists', $this->getPriceStatsLists($stationId, $conn));

        stopProfile(__METHOD__);

        return 'pages/stationPriceList.html.twig';
    }

    /**
     * @param $stationId
     * @return array
     * @throws DoctrineException
     */
    public function getOpeningTimes($stationId): array
    {
        startProfile(__METHOD__);

        $ot = new openingTimes($stationId);
        $list = $ot->getOpeningTimesList();

        stopProfile(__METHOD__);

        return $list;
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

            $graph->renderToOutput( 1200, 600 );
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

    /**
     * @param string $stationId
     * @param Connection $conn
     * @return array
     * @throws DoctrineException
     */
    protected function getPriceStatsLists(string $stationId, Connection $conn): array
    {
        $lists = [];
        foreach (Fuel::getTypes() as $type) {
            $pricestat = new PriceStatistics();
            $qb = $pricestat->getLowPriceStatsByStation($stationId, $type);
            $lists[$type]['stat'] = $qb->fetchAllAssociative();
        }

        foreach (Fuel::getTypes() as $type) {
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
                ->orderBy('t1.datetime', 'DESC');
            $lists[$type]['prices'] = $qb->fetchAllAssociative();
        }
        return $lists;
    }

    /**
     * @param Connection $conn
     * @param string $stationId
     * @return array
     * @throws DoctrineException
     */
    protected function getStation(Connection $conn, string $stationId): array
    {
        $station = new Station();
        $stationTable = $station->getCoreTableName();

        $qbs = $conn->createQueryBuilder();
        $qbs->select('*', 'CONCAT(name, " (", place, ")") as stationname')
            ->from($stationTable)
            ->where($qbs->expr()->eq(
                'id',
                $qbs->createNamedParameter($stationId)
            ))
            ->setMaxResults(1);
        return array_change_key_case($qbs->fetchAssociative(), CASE_LOWER);
    }

    /**
     * @param string $stationId
     * @param Connection $conn
     * @return array
     * @throws DoctrineException
     */
    public function getCurrentPrices(string $stationId, Connection $conn): array
    {
        ini_set('display_errors', 1);
        $price = new Price();
        $priceTable = $price->getCoreTableName();

        $qbs = $conn->createQueryBuilder();
        $qbs->select('pr.type', 'MAX(pr.datetime) as datetime')
            ->from($priceTable, 'pr')
            ->where(
                $qbs->expr()->eq(
                    'pr.stationid',
                    $conn->quote($stationId)
                )
            )
            ->groupBy('pr.type');

        $qb = $conn->createQueryBuilder();
        $qb->select('p2.id', 'p2.type', 'p2.price')
            ->from($priceTable, 'p2')
            ->innerJoin(
                'p2',
                "(".$qbs->getSQL().")",
                'p1',
                $qb->expr()->and(
                    $qb->expr()->eq(
                        'p2.stationid',
                        $qb->createNamedParameter($stationId)
                    ),
                    $qb->expr()->eq(
                        'p1.type',
                        'p2.type'
                    ),
                    $qb->expr()->eq(
                        'p1.datetime',
                        'p2.datetime'
                    )
                )
            )
            ->orderBy('FIELD(p2.type,"'.implode('","',Fuel::getTypes()).'")');

        return array_change_key_case($qb->fetchAllAssociativeIndexed(), CASE_LOWER);
    }
}