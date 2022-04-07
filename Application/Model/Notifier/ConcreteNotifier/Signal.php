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

namespace Daniels\FuelLogger\Application\Model\Notifier\ConcreteNotifier;

use Daniels\FuelLogger\Application\Model\Notifier\CallMeBot;

class Signal extends CallMeBot
{
    public string $endPoint = 'signal/send.php';

    /**
     * @param $message
     *
     * @return string
     */
    public function getQuery($message): string
    {
        return http_build_query(
            [
                'source'    => 'php',
                'phone'     => $this->phoneNumber,
                'text'      => $message,
                'apikey'    => $this->apiKey
            ]
        );
    }
}