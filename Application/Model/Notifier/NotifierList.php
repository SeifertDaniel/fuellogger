<?php

namespace Daniels\FuelLogger\Application\Model\Notifier;

use Daniels\FuelLogger\Application\Model\Fuel;
use Daniels\FuelLogger\Application\Model\Notifier\ConcreteNotifier\Ifttt;
use Daniels\FuelLogger\Application\Model\Notifier\ConcreteNotifier\WhatsApp;
use Daniels\FuelLogger\Application\Model\NotifyFilters\DailyBestPriceFilter;
use Daniels\FuelLogger\Application\Model\NotifyFilters\DateFilter;
use Daniels\FuelLogger\Application\Model\NotifyFilters\FuelTypeFilter;
use Daniels\FuelLogger\Application\Model\NotifyFilters\PostCodeFilter;
use Daniels\FuelLogger\Application\Model\NotifyFilters\PriceFilter;
use Daniels\FuelLogger\Application\Model\NotifyFilters\TimeFilter;
use Daniels\FuelLogger\Application\Model\NotifyFilters\WeekdayFilter;
use DateTime;

class NotifierList
{
    /**
     * @return array
     */
    public function getList(): array
    {
        return [
            (new DebugNotifier())
                //->addFilter(new DailyBestPriceFilter())
                ->addFilter(new TimeFilter('09:00:00', '22:59:00'))
                ->addFilter(new FuelTypeFilter([Fuel::TYPE_E10]))
                ->addFilter(new WeekdayFilter([WeekdayFilter::FRIDAY]))
                ->addFilter(new PriceFilter(PriceFilter::HIGHERTHAN, 0.50))
                ->addFilter(new PriceFilter(PriceFilter::LOWERTHANEQUALS, 3.80))
                ->addFilter(new PostCodeFilter(['09380']))
                ->addFilter(new DateFilter('2021-08-13', '2023-05-19')),
            (new Ifttt($_ENV['C001_IFTTT_URL']))
                ->addFilter(new FuelTypeFilter([Fuel::TYPE_E10]))
                ->addFilter(new PostCodeFilter(['09366', '09380'])),
            (new WhatsApp($_ENV['C001_WHAPP_PHONE'], $_ENV['C001_WHAPP_APIK']))
                ->addFilter(new TimeFilter('08:00:00', '22:00:00'))
                ->addFilter(new FuelTypeFilter([Fuel::TYPE_E10]))
                ->addFilter(new PostCodeFilter(['09366', '09380']))
                //->addFilter((new DailyBestPriceFilter())
                    //->addQueryFilter(new TimeFilter('08:00:00', (new DateTime())->format('H:i:s'))))

        ];
    }
}