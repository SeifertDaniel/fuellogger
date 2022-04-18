<?php

namespace Daniels\FuelLogger\Application\Model\ezcGraph;

use ezcBaseValueException;
use ezcGraphAxisRotatedLabelRenderer;
use ezcGraphChartElementAxis;
use ezcGraphCoordinate;
use ezcGraphRenderer;

class ezcGraphAxisRotatedOffsetLabelRenderer extends ezcGraphAxisRotatedLabelRenderer
{
    public function __construct( array $options = array() )
    {
        parent::__construct( $options );
        $this->properties['yoffset']  = 0;
        $this->properties['xoffset'] = 0;
    }

    public function __set( $propertyName, $propertyValue )
    {
        switch ( $propertyName )
        {
            case 'yoffset':
                if ( !is_int( $propertyValue ) )
                {
                    throw new ezcBaseValueException( $propertyName, $propertyValue, 'int' );
                }

                $this->properties['yoffset'] = (int) $propertyValue;
                break;

            case 'xoffset':
                if ( !is_int( $propertyValue ) )
                {
                    throw new ezcBaseValueException( $propertyName, $propertyValue, 'int' );
                }

                $this->properties[$propertyName] = (int) $propertyValue;
                break;

            default:
                parent::__set( $propertyName, $propertyValue );
        }
    }

    /**
     * @param ezcGraphRenderer $renderer
     * @param ezcGraphChartElementAxis $axis
     * @param ezcGraphCoordinate $position
     * @param $label
     * @param $degTextAngle
     * @param $labelLength
     * @param $labelSize
     * @param $lengthReducement
     * @return void
     */
    protected function renderLabelText( ezcGraphRenderer $renderer, ezcGraphChartElementAxis $axis, ezcGraphCoordinate $position, $label, $degTextAngle, $labelLength, $labelSize, $lengthReducement )
    {
        $position->y += $this->properties['yoffset'];
        $position->x += $this->properties['xoffset'];

        parent::renderLabelText($renderer, $axis, $position, $label, $degTextAngle, $labelLength, $labelSize, $lengthReducement);
    }
}