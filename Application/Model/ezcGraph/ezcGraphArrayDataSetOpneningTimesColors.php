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

namespace Daniels\Benzinlogger\Application\Model\ezcGraph;

class ezcGraphArrayDataSetOpneningTimesColors extends \ezcGraphArrayDataSet
{
    public function setProperty($propertyId, $property)
    {
        $this->properties[ $propertyId ] = $property;
    }

    public function getKeys()
    {
        return array_keys( $this->data );
    }
}