<?php

namespace Daniels\FuelLogger\Application\Controller;

class disclaimer implements controllerInterface
{
    public function init()
    {
    }

    public function render()
    {
        return 'pages/disclaimer.html.twig';
    }
}