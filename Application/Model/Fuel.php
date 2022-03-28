<?php

namespace Daniels\FuelLogger\Application\Model;

use DanielS\Tankerkoenig\ApiClient;

class Fuel
{
    const TYPE_E10 = ApiClient::TYPE_E10;
    const TYPE_E5 = ApiClient::TYPE_E5;
    const TYPE_DIESEL = ApiClient::TYPE_DIESEL;

    public static function getTypes()
    {
        return [
            self::TYPE_E10,
            self::TYPE_E5,
            self::TYPE_DIESEL
        ];
    }
}