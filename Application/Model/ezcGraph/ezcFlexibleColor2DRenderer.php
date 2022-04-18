<?php

namespace Daniels\FuelLogger\Application\Model\ezcGraph;

use ezcGraphBoundings;
use ezcGraphChartElementAxis;
use ezcGraphCoordinate;
use ezcGraphRenderer2d;

class ezcFlexibleColor2DRenderer extends ezcGraphRenderer2d
{
    protected function drawAxisLabel( ezcGraphCoordinate $position, ezcGraphBoundings $boundings, ezcGraphChartElementAxis $axis )
    {
        if (isset($axis->yoffset) && isset($axis->xoffset)) {
            $position->y += $axis->yoffset;
            $position->x += $axis->xoffset;
        }
        parent::drawAxisLabel($position, $boundings, $axis);
    }
}