<?php

namespace Daniels\FuelLogger\Application\Model\Notifier;

use Daniels\FuelLogger\Application\Model\Fuel;
use Daniels\FuelLogger\Application\Model\Notifier\ConcreteNotifier\Ifttt;
use Daniels\FuelLogger\Application\Model\Notifier\ConcreteNotifier\WhatsApp;
use Daniels\FuelLogger\Application\Model\NotifyFilters\BetterStationPriceFilter;
use Daniels\FuelLogger\Application\Model\NotifyFilters\Collections\StlE10ByDayCollection;
use Daniels\FuelLogger\Application\Model\NotifyFilters\Collections\TlhE10AviaWorkDay;
use Daniels\FuelLogger\Application\Model\NotifyFilters\DailyBestPriceFilter;
use Daniels\FuelLogger\Application\Model\NotifyFilters\FuelTypeFilter;
use Daniels\FuelLogger\Application\Model\NotifyFilters\PostCodeFilter;
use Daniels\FuelLogger\Application\Model\NotifyFilters\PriceFilter;

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
            'Stl + Tlh' => (new Ifttt($_ENV['C001_IFTTT_URL']))
                ->addFilter(new FuelTypeFilter([Fuel::TYPE_E10]))
                ->addFilter(new PostCodeFilter(['09366', '09380']))
                ->addFilter(new BetterStationPriceFilter())
            ,
            'Stollberg' => (new WhatsApp($_ENV['C001_WHAPP_PHONE'], $_ENV['C001_WHAPP_APIK']))
                //->addFilter(new TimeFilter('08:00:00', '22:00:00'))
                //->addFilter(new FuelTypeFilter([Fuel::TYPE_E10]))
                //->addFilter(new PostCodeFilter(['09366']))
                //->addFilter(new StationIdFilter(['3616e5f1-c318-43a5-a28b-4eb81c168c61', '182a58cd-6153-4a53-99a6-652ec670568e']))
                //->addFilter(new DailyBestPriceFilter())
                ->addFilter(new StlE10ByDayCollection())
            ,
            'Avia Thalheim' => (new WhatsApp($_ENV['C001_WHAPP_PHONE'], $_ENV['C001_WHAPP_APIK']))
                ->addFilter(new TlhE10AviaWorkDay())
            ,
        ];
    }
}