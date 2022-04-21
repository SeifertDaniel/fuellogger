<?php

namespace Daniels\FuelLogger\Application\Model\ezcGraph;

use ezcGraph;
use ezcGraphBoundings;
use ezcGraphChartElementAxis;
use ezcGraphColor;
use ezcGraphContext;
use ezcGraphCoordinate;
use ezcGraphRenderer2d;

class ezcFlexibleColor2DRenderer extends ezcGraphRenderer2d
{
    public function drawDataLine(
        ezcGraphBoundings $boundings,
        ezcGraphContext $context,
        ezcGraphColor $color,
        ezcGraphCoordinate $start,
        ezcGraphCoordinate $end,
        $dataNumber = 1,
        $dataCount = 1,
        $symbol = ezcGraph::NO_SYMBOL,
        ezcGraphColor $symbolColor = null,
        ezcGraphColor $fillColor = null,
        $axisPosition = 0.,
        $thickness = 1. )
    {
        // Perhaps fill up line
        if ( $fillColor !== null &&
            $start->x != $end->x )
        {
            $startValue = $axisPosition - $start->y;
            $endValue = $axisPosition - $end->y;

            if ( ( $startValue == 0 ) ||
                ( $endValue == 0 ) ||
                ( $startValue / abs( $startValue ) == $endValue / abs( $endValue ) ) )
            {
                // Values have the same sign or are on the axis
                $this->driver->drawPolygon(
                    array(
                        new ezcGraphCoordinate(
                            $boundings->x0 + ( $boundings->width ) * $start->x,
                            $boundings->y0 + ( $boundings->height ) * $start->y
                        ),
                        new ezcGraphCoordinate(
                            $boundings->x0 + ( $boundings->width ) * $end->x,
                            $boundings->y0 + ( $boundings->height ) * $end->y
                        ),
                        new ezcGraphCoordinate(
                            $boundings->x0 + ( $boundings->width ) * $end->x,
                            $boundings->y0 + ( $boundings->height ) * $axisPosition
                        ),
                        new ezcGraphCoordinate(
                            $boundings->x0 + ( $boundings->width ) * $start->x,
                            $boundings->y0 + ( $boundings->height ) * $axisPosition
                        ),
                    ),
                    $fillColor,
                    true
                );
            }
            else
            {
                // values are on differente sides of the axis - split the filled polygon
                $startDiff = abs( $axisPosition - $start->y );
                $endDiff = abs( $axisPosition - $end->y );

                $cuttingPosition = $startDiff / ( $endDiff + $startDiff );
                $cuttingPoint = new ezcGraphCoordinate(
                    $start->x + ( $end->x - $start->x ) * $cuttingPosition,
                    $axisPosition
                );

                $this->driver->drawPolygon(
                    array(
                        new ezcGraphCoordinate(
                            $boundings->x0 + ( $boundings->width ) * $start->x,
                            $boundings->y0 + ( $boundings->height ) * $axisPosition
                        ),
                        new ezcGraphCoordinate(
                            $boundings->x0 + ( $boundings->width ) * $start->x,
                            $boundings->y0 + ( $boundings->height ) * $start->y
                        ),
                        new ezcGraphCoordinate(
                            $boundings->x0 + ( $boundings->width ) * $cuttingPoint->x,
                            $boundings->y0 + ( $boundings->height ) * $cuttingPoint->y
                        ),
                    ),
                    $fillColor,
                    true
                );

                $this->driver->drawPolygon(
                    array(
                        new ezcGraphCoordinate(
                            $boundings->x0 + ( $boundings->width ) * $end->x,
                            $boundings->y0 + ( $boundings->height ) * $axisPosition
                        ),
                        new ezcGraphCoordinate(
                            $boundings->x0 + ( $boundings->width ) * $end->x,
                            $boundings->y0 + ( $boundings->height ) * $end->y
                        ),
                        new ezcGraphCoordinate(
                            $boundings->x0 + ( $boundings->width ) * $cuttingPoint->x,
                            $boundings->y0 + ( $boundings->height ) * $cuttingPoint->y
                        ),
                    ),
                    $fillColor,
                    true
                );
            }
        }

        // Draw line
        $this->driver->drawLine(
            new ezcGraphCoordinate(
                $boundings->x0 + ( $boundings->width ) * $start->x,
                $boundings->y0 + ( $boundings->height ) * $start->y
            ),
            new ezcGraphCoordinate(
                $boundings->x0 + ( $boundings->width ) * $end->x,
                $boundings->y0 + ( $boundings->height ) * $end->y
            ),
            $symbolColor ?? $color,
            $thickness
        );

        // Draw line symbol
        if ( $symbol !== ezcGraph::NO_SYMBOL )
        {
            if ( $symbolColor === null )
            {
                $symbolColor = $color;
            }

            $this->linePostSymbols[] = array(
                'boundings' => new ezcGraphBoundings(
                    $boundings->x0 + ( $boundings->width ) * $end->x - $this->options->symbolSize / 2,
                    $boundings->y0 + ( $boundings->height ) * $end->y - $this->options->symbolSize / 2,
                    $boundings->x0 + ( $boundings->width ) * $end->x + $this->options->symbolSize / 2,
                    $boundings->y0 + ( $boundings->height ) * $end->y + $this->options->symbolSize / 2
                ),
                'color' => $symbolColor,
                'context' => $context,
                'symbol' => $symbol,
            );
        }
    }

    protected function drawAxisLabel( ezcGraphCoordinate $position, ezcGraphBoundings $boundings, ezcGraphChartElementAxis $axis )
    {
        if (isset($axis->yoffset) && isset($axis->xoffset)) {
            $position->y += $axis->yoffset;
            $position->x += $axis->xoffset;
        }
        parent::drawAxisLabel($position, $boundings, $axis);
    }
}