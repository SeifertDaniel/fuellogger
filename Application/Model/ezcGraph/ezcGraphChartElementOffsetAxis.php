<?php

namespace Daniels\FuelLogger\Application\Model\ezcGraph;

use ezcBaseValueException;
use ezcGraphChartElementNumericAxis;

class ezcGraphChartElementOffsetAxis extends ezcGraphChartElementNumericAxis
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

                $this->properties['xoffset'] = (int) $propertyValue;
                break;
            default:
                parent::__set( $propertyName, $propertyValue );
                break;
        }
    }
}