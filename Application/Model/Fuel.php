<?php

/**
 * This Software is the property of Data Development and is protected
 * by copyright law - it is NOT Freeware.
 * Any unauthorized use of this software without a valid license
 * is a violation of the license agreement and will be prosecuted by
 * civil and criminal law.
 * http://www.shopmodule.com
 *
 * @copyright (C) D3 Data Development (Inh. Thomas Dartsch)
 * @author        D3 Data Development - Daniel Seifert <support@shopmodule.com>
 * @link          http://www.oxidmodule.com
 */

namespace Daniels\Benzinlogger\Application\Model;

use Lang\Tankerking\ApiClient;

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