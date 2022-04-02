<?php

namespace Daniels\FuelLogger\Application\Model;

use Daniels\FuelLogger\Core\Debug;
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
        if (Debug::logDebugMessages()) {
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