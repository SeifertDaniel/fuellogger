<?php

namespace Daniels\FuelLogger\Application\Model\ezcGraph;

class svgFixer
{
    public function fixHeaderToHtml()
    {
        header( 'Content-Type: text/html');
    }

    public function makeResponsive($svg)
    {
        // set width and height to 100% and transfer absolute values to viewBox
        $re = '/(<svg)(.*width=("|\'))(.*?)((?3).*?height=(?3))(.*?)((?3).*?>)/m';
        $subst = '$1 viewBox="0 0 $4 $6" ${2}100%${5}100%$7';

        return preg_replace($re, $subst, $svg);
    }
}