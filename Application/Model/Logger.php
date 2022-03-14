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

use Monolog\Handler\StreamHandler;
use Monolog\Logger as MonologLogger;

class Logger
{
    /**
     * @return MonologLogger
     */
    public function getLogger()
    {
        $logger = new MonologLogger( 'fuelLogger');
        $this->pushHandler($logger);

        return $logger;
    }

    /**
     * @param MonologLogger $logger
     */
    public function pushHandler(&$logger)
    {
        $logger->pushHandler(new StreamHandler('log/error.log', MonologLogger::ERROR));
        $logger->pushHandler(new StreamHandler('log/debug.log', MonologLogger::DEBUG));
    }
}