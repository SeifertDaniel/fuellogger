<?php

namespace Daniels\FuelLogger\Application\Controller;

use Daniels\FuelLogger\Application\Model\DBConnection;
use Daniels\FuelLogger\Application\Model\OilPrice;
use Daniels\FuelLogger\Application\Model\Price;
use Daniels\FuelLogger\Core\Registry;
use Doctrine\DBAL\Connection as Connection;
use Doctrine\DBAL\Exception as DoctrineException;
use Exception;
use ezcGraph;
use ezcGraphArrayDataSet;
use ezcGraphAxisRotatedLabelRenderer;
use ezcGraphChartElementNumericAxis;
use ezcGraphColor;
use ezcGraphLineChart;
use ezcGraphPaletteBlack;

class priceTrend implements controllerInterface
{
    public function __construct()
    {
        ini_set('display_errors', 1);
    }

    public function init() {}

    public function render()
    {
        Registry::getTwig()->addGlobal('requestUrl', Registry::getRequest()->getRequestUrl());

        return 'pages/priceTrend.html.twig';
    }

    /**
     * @throws DoctrineException
     */
    public function getGraph()
    {
        try {
            $conn = DBConnection::getConnection();

            $fuelStats = $this->getFuelStats( $conn );
            $oilStats  = $this->getOilStats( $conn );

            $allStats = array_merge( $fuelStats, $oilStats );

            $graph = new ezcGraphLineChart();

            // colors
            //$graph->background->background = new ezcGraphColor(['red'   => 255, 'green' => 255, 'blue'  => 255]);
            $graph->palette          = $palette = new ezcGraphPaletteBlack();
            $palette->minorGridColor = new ezcGraphColor( [ 'red'   => 255,
                                                            'green' => 255,
                                                            'blue'  => 255,
                                                            'alpha' => 255
                                                          ] );

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

            // data
            foreach ( $allStats as $fuelType => $data ) {
                $data                     = new ezcGraphArrayDataSet( $data );
                $graph->data[ $fuelType ] = $data;
            }

            // additional axis
            $graph->additionalAxis['oilprice']        = $nAxis = new ezcGraphChartElementNumericAxis();
            $nAxis->position                          = ezcGraph::BOTTOM;
            $nAxis->chartPosition                     = 1;
            $nAxis->label                             = 'Oelpreis';
            $graph->data[ ucfirst( 'brent' ) ]->yAxis = $nAxis;

            $graph->renderToOutput( 1000, 400 );
        } catch ( Exception $e) {
            Registry::getLogger()->error($e->getMessage());
        }

        die();
    }

    /**
     * @param Connection|null $conn
     *
     * @return array
     * @throws DoctrineException
     */
    protected function getFuelStats( ?Connection $conn ): array
    {
        $prices     = new Price();
        $priceTable = $prices->getCoreTableName();

        $qb = $conn->createQueryBuilder();
        $qb->select( 'AVG(pr.price) as price', 'DATE_FORMAT(pr.datetime, "%Y-%m-%d") as date', 'pr.type' )
           ->from( $priceTable, 'pr' )
           ->where(
               $qb->expr()->lt(
                   'DATE_FORMAT(pr.datetime, "%Y-%m-%d")',
                   'DATE_FORMAT(NOW(), "%Y-%m-%d")'
               )
           )->groupBy( 'DATE_FORMAT(pr.datetime, "%Y-%m-%d")', 'pr.type' )
            ->orderBy( 'DATE_FORMAT(pr.datetime, "%Y-%m-%d")', 'ASC' );

        $prices = $qb->fetchAllAssociative();

        $fuelStats = [];
        foreach ( $prices as $price ) {
            $fuelStats[ ucfirst($price['type']) ][ $price['date'] ] = $price['price'];
        }

        return $fuelStats;
    }

    /**
     * @param Connection|null $conn
     *
     * @return array
     * @throws DoctrineException
     */
    protected function getOilStats( ?Connection $conn ): array
    {
        $prices     = new OilPrice();
        $priceTable = $prices->getCoreTableName();

        $qb = $conn->createQueryBuilder();
        $qb->select( 'pr.price', 'pr.date' )
           ->from( $priceTable, 'pr' )
           ->where(
               $qb->expr()->lt(
                   'pr.date',
                   'DATE_FORMAT(NOW(), "%Y-%m-%d")'
               )
           )
           ->orderBy( 'pr.date', 'ASC' );

        $prices = $qb->fetchAllAssociative();

        $oilStats = [];
        foreach ( $prices as $price ) {
            $oilStats[ ucfirst('brent') ][ $price['date'] ] = $price['price'];
        }

        return $oilStats;
    }
}