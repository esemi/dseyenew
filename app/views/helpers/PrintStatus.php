<?php

/*
 * выводит читаемый статус игровых сущностей (миры/альянсы/игроки)
 */
class Zend_View_Helper_PrintStatus extends Zend_View_Helper_Abstract
{
    public function printStatus( $status )
    { 
        printf('<span class="%s">%s</span>', 
                ($status == 'active') ? 'color-green' : 'color-red', 
                ($status == 'active') ? 'активен' : 'удалён');
    }
}
