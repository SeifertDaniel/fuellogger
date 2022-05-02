<?php

namespace Daniels\FuelLogger\Application\Model\Notifier;

use Daniels\FuelLogger\Application\Model\Fuel;
use Daniels\FuelLogger\Application\Model\Notifier\ConcreteNotifier\Ifttt;
use Daniels\FuelLogger\Application\Model\Notifier\ConcreteNotifier\WhatsApp;
use Daniels\FuelLogger\Application\Model\NotifyFilters\BetterStationPriceFilter;
use Daniels\FuelLogger\Application\Model\NotifyFilters\Collections\StlE10ByDayCollection;
use Daniels\FuelLogger\Application\Model\NotifyFilters\Collections\StlE10GoodPriceCollection;
use Daniels\FuelLogger\Application\Model\NotifyFilters\Collections\TlhE10AviaWorkDay;
use Daniels\FuelLogger\Application\Model\NotifyFilters\DailyBestPriceFilter;
use Daniels\FuelLogger\Application\Model\NotifyFilters\FuelTypeFilter;
use Daniels\FuelLogger\Application\Model\NotifyFilters\PostCodeFilter;
use Daniels\FuelLogger\Application\Model\NotifyFilters\PriceFilter;
use Daniels\FuelLogger\Application\Model\NotifyFilters\StageFilter;
use Daniels\FuelLogger\Core\Stage;

class NotifierList
{
    /**
     * @return array
     */
    public function getList(): array
    {
        return [
            'guenstigste ueberhaupt'    => (new DebugNotifier())
                ->addFilter(new FuelTypeFilter([Fuel::TYPE_E10]))
                ->addFilter(new DailyBestPriceFilter())
                ->addFilter(new PriceFilter(PriceFilter::HIGHERTHAN, 0.50))
                ->addFilter(new PriceFilter(PriceFilter::LOWERTHANEQUALS, 3.50))
            ,
            'Stollberg dev' => (new WhatsApp($_ENV['C001_WHAPP_PHONE'], $_ENV['C001_WHAPP_APIK']))
                ->addFilter(new StlE10ByDayCollection())
                ->addFilter(new StageFilter([Stage::DEVELOPMENT]))
            ,
            'Avia Thalheim dev' => (new WhatsApp($_ENV['C001_WHAPP_PHONE'], $_ENV['C001_WHAPP_APIK']))
                ->addFilter(new TlhE10AviaWorkDay())
                ->addFilter(new StageFilter([Stage::DEVELOPMENT]))
            ,
            'Stollberg prod' => (new Ifttt($_ENV['C001_IFTTT_URL']))
                ->addFilter(new StlE10GoodPriceCollection())
                ->addFilter(new StageFilter([Stage::PRODUCTION]))
            ,
            'Avia Thalheim prod' => (new Ifttt($_ENV['C001_IFTTT_URL']))
                ->addFilter(new TlhE10AviaWorkDay())
                ->addFilter(new StageFilter([Stage::PRODUCTION]))
            ,
        ];
    }
}