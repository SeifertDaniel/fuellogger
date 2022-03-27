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

namespace Daniels\FuelLogger\Application\Model;

use Monolog\Handler\FilterHandler;
use Monolog\Handler\RotatingFileHandler;
use Monolog\Logger as MonologLogger;

class Logger
{
    /**
     * @return MonologLogger
     */
    public function getLogger()
    {
        $logger = new MonologLogger( 'fuelLogger');
        return $this->pushHandler($logger);
    }

    /**
     * @param MonologLogger $logger
     *
     * @return MonologLogger
     */
    public function pushHandler(MonologLogger $logger): MonologLogger
    {
        if ($_ENV['DEBUGMODE']) {
            $logger->pushHandler(new RotatingFileHandler($_ENV['ROOTDIR'] . 'src/log/debug.log', 5, MonologLogger::DEBUG, false));
        }
        $logger->pushHandler(new RotatingFileHandler($_ENV['ROOTDIR'].'src/log/error.log', 10, MonologLogger::ERROR));

        $infoStreamHandler = new RotatingFileHandler($_ENV['ROOTDIR'].'src/log/info.log', 5, MonologLogger::INFO);
        $infoFilterHandler = new FilterHandler(
            $infoStreamHandler,
            MonologLogger::INFO,
            MonologLogger::WARNING);
        $logger->pushHandler($infoFilterHandler);

        return $logger;
    }
}