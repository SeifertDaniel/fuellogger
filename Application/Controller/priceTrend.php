<?php

namespace Daniels\Benzinlogger\Application\Controller;

use Daniels\Benzinlogger\Application\Model\DBConnection;
use Daniels\Benzinlogger\Application\Model\Price;
use Daniels\Benzinlogger\Core\Registry;
use ezcGraphArrayDataSet;
use ezcGraphLineChart;

class priceTrend implements controllerInterface
{
    public function init()
    {
        ini_set('display_errors', 1);
    }

    public function render()
    {
        echo '<img src="'.Registry::getRequest()->getRequestUrl().'&amp;fnc=getGraph'.'">';
    }

    public function getGraph()
    {
        $conn = DBConnection::getConnection();

        $prices = new Price();
        $priceTable = $prices->getCoreTableName();

        $qb = $conn->createQueryBuilder();
        $qb->select('AVG(pr.price) as price', 'DATE_FORMAT(pr.datetime, "%Y-%m-%d") as date', 'pr.type')
            ->from($priceTable, 'pr')
            ->where(
                $qb->expr()->lt(
                    'DATE_FORMAT(pr.datetime, "%Y-%m-%d")',
                    'DATE_FORMAT(NOW(), "%Y-%m-%d")'
                )
            )
            ->groupBy('DATE_FORMAT(pr.datetime, "%Y-%m-%d")', 'pr.type')
            ->orderBy('DATE_FORMAT(pr.datetime, "%Y-%m-%d")', 'ASC');

        $prices = $qb->fetchAllAssociative();

        $stats = [];
        foreach ($prices as $price) {
            $stats[$price['type']][$price['date']] = $price['price'];
        }

        $graph = new ezcGraphLineChart();
        $graph->title = 'mittelfr. Preisentwicklung';

        // Add data
        foreach ( $stats as $fuelType => $data )
        {
            $data = new ezcGraphArrayDataSet( $data );
            $graph->data[$fuelType] = $data;
        }

        $graph->renderToOutput( 1000, 300 );

        die();
    }
}