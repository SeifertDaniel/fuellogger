<?php

namespace Daniels\Benzinlogger;

use Daniels\Benzinlogger\Application\Controller\controllerInterface;
use Daniels\Benzinlogger\Application\Model\ControllerMapper;
use Daniels\Benzinlogger\Core\Registry;
use Dotenv\Dotenv;

require_once '../vendor/autoload.php';

class index
{
    public function __construct()
    {
        $dotenv = Dotenv::createImmutable(__DIR__."/..");
        $dotenv->load();
        $dotenv->required(['DBHOST', 'DBNAME', 'DBUSER', 'DBPASS', 'DBDRIVER'])->notEmpty();

        $cl = Registry::getRequest()->getRequestEscapedParameter('cl') ?: 'bestPriceList';

        $mapper = new ControllerMapper();
        $fqns = $mapper->getFQNS($cl);

        /** @var controllerInterface $controller */
        $controller = new $fqns;
        $controller->render();
    }
}

new index();