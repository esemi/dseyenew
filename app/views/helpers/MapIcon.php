<?php

/*
 * рисует иконки меню
 */

class Zend_View_Helper_MapIcon extends Zend_View_Helper_Abstract
{

    public function MapIcon( $val, $type, $min, $mid )
    {
        if($val > $mid)
            $step = 3;
        elseif($val > $min)
            $step = 2;
        else
            $step = 1;                

        return "map-icon-{$type}-{$step}";
    }

}
