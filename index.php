<?php

namespace Daniels\FuelLogger;

use Daniels\FuelLogger\Application\Controller\controllerInterface;
use Daniels\FuelLogger\Application\Model\ControllerMapper;
use Daniels\FuelLogger\Core\Base;
use Daniels\FuelLogger\Core\Registry;
use Exception;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;

try {
    require_once dirname(__FILE__) . "/bootstrap.php";
} catch (Exception $e) {
    Registry::getLogger()->error($e->getMessage());
    Registry::getLogger()->error($e->getTraceAsString());
}
class index extends Base
{
    /**
     * @throws LoaderError
     * @throws RuntimeError
     * @throws SyntaxError
     */
    public function __construct()
    {
        parent::__construct();

        startProfile(__METHOD__);

        $callRender = true;

        $cl = Registry::getRequest()->getRequestEscapedParameter('cl') ?: 'bestPriceList';
        Registry::getTwig()->addGlobal('className', $cl);
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
            startProfile(__METHOD__.' (rendering)');
            $template = Registry::getTwig()->load($tpl);
            echo $template->render();
            stopProfile(__METHOD__.' (rendering)');
        }

        stopProfile(__METHOD__);

        $this->finalize();
    }
}

try {
    new index();
} catch (Exception $e) {
    Registry::getLogger()->error($e->getMessage());
    Registry::getLogger()->error($e->getTraceAsString());
}