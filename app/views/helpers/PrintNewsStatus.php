<?php
/*
 * выводит тип новости (апдейт/багфикс)
 */
class Zend_View_Helper_PrintNewsStatus extends Zend_View_Helper_Abstract
{
    public function printNewsStatus( $status )
    { 
        printf('(<span class="%s">%s</span>)', 
                ($status == 'update') ? 'color-green' : 'color-red', 
                ($status == 'update') ? 'обновление' : 'исправление');
    }
}
