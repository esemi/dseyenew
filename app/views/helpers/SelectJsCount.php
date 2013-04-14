<?php

/*
 * возвращает яваскрипт меню выбора количества отображаемых итемов в таблице
 */

class Zend_View_Helper_SelectJsCount extends Zend_View_Helper_Abstract
{

    public function selectJsCount( $curVal )
    {
        $values = array(10,20,50);
        
        echo '<select size="1" class="js-count-select">';
        foreach ($values as $val)
        {
            $select = ( $curVal == $val ) ? 'selected="selected"' : '';
            echo "<option $select value='" 
            . $this->view->url(array('count' => $val, 'page' => 1)) 
            . "'>{$val} на странице</option>";
        }
        echo '</select>';
    }

}
