<?php

namespace Daniels\Benzinlogger\Application\Model\Oilprices;

use stdClass;

class CommoditiesApi
{
    const SYMBOL_RICE = 'RICE';
    const SYMBOL_WHEAT = 'WHEAT';
    const SYMBOL_SUGAR = 'SUGAR';
    const SYMBOL_CORN = 'CORN';
    const SYMBOL_WTIOIL = 'WTIOIL';
    const SYMBOL_BRENTOIL = 'BRENTOIL';
    const SYMBOL_SOYBEAN = 'SOYBEAN';
    const SYMBOL_COFFEE = 'COFFEE';
    const SYMBOL_GOLD = 'XAU';
    const SYMBOL_SILVER = 'XAG';
    const SYMBOL_PALLADIUM = 'XPD';
    const SYMBOL_PLATINUM = 'XPT';
    const SYMBOL_RHODIUM = 'XRH';
    const SYMBOL_RUBBER = 'RUBBER';
    const SYMBOL_ETHANOL = 'ETHANOL';
    const SYMBOL_PALMOIL = 'CPO';
    const SYMBOL_NATURALGAS = 'NG';

    public $currencyBase = 'EUR';
    private $apiKey;

    public function __construct($apiKey)
    {
        $this->apiKey = $apiKey;
    }

    /**
     * @param array $symbols
     * @return mixed
     * @throws CommoditiesApiException
     */
    public function request(array $symbols): mixed
    {
        $url = 'https://www.commodities-api.com/api/latest?access_key='.$this->apiKey;
        $url .= '&base='.$this->currencyBase;
        $url .= '&symbols='.implode('%2C', $symbols);

        $json = file_get_contents($url);

        if ( $json === false ) {
            throw new CommoditiesApiException( "FEHLER - Die Commodities-API konnte nicht abgefragt werden!" );
        }

        /** @var stdClass $data */
        $data = json_decode( $json );

        if ( $data->data->success !== true ) {
            throw new CommoditiesApiException( "FEHLER - Die Commodities-API meldet diesen Fehler: " . $data->data->error->info );
        }

        return $data->data->rates;
    }
}