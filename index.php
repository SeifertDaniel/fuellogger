<?php

namespace Daniels\FuelLogger;

use Daniels\FuelLogger\Application\Controller\controllerInterface;
use Daniels\FuelLogger\Application\Model\ControllerMapper;
use Daniels\FuelLogger\Core\Registry;
use Dotenv\Dotenv;

require_once '../vendor/autoload.php';

class index
{
    public function __construct()
    {
        $callRender = true;
        $dotenv = Dotenv::createImmutable(__DIR__."/..");
        $dotenv->load();
        $dotenv->required(['DBHOST', 'DBNAME', 'DBUSER', 'DBPASS', 'DBDRIVER'])->notEmpty();

        $cl = Registry::getRequest()->getRequestEscapedParameter('cl') ?: 'bestPriceList';
        $fnc = Registry::getRequest()->getRequestEscapedParameter('fnc') ?: false;

        $mapper = new ControllerMapper();
        $fqns = $mapper->getFQNS($cl);

        /** @var controllerInterface $controller */
        $controller = new $fqns;
        $controller->init();
        if ($fnc) {
            $callRender = !call_user_func_array([$controller, $fnc], []);
        }
        if ($callRender) {
            $tpl = $controller->render();
            $template = Registry::getTwig()->load($tpl);
            echo $template->render();
        }
    }
}

try {
    new index();
} catch (\Exception $e) {
    Registry::getLogger()->error($e->getMessage());
}