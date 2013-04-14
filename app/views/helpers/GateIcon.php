<?php

/*
 * возвращает значёк ворот
 */
class Zend_View_Helper_GateIcon extends Zend_View_Helper_Abstract
{
    public function GateIcon( $status, $otherClass = '' )
    {          
        return sprintf( '<div  class="%s %s inline-block"></div>', 
                ($status == 0) ? 'gate_closed' : 'gate_open',
                $otherClass );
    }
}
