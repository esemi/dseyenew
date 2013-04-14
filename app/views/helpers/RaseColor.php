<?php
class Zend_View_Helper_RaseColor extends Zend_View_Helper_Abstract
{
    public function RaseColor( $idR, $isLink=false )
    {
         $classes = array('color-voran','color-liens','color-psol');
         $forLink = ($isLink) ? 'bold' : '';
         return (isset($classes[$idR-1]))? "{$classes[$idR-1]} {$forLink}" : '';
    }
}
