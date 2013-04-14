<?php

class Zend_View_Helper_MenuLink extends Zend_View_Helper_Abstract
{    
    public function menuLink( $url, $value, $name, $class )
    {
        printf("<a href='%s' class='%s'>%s</a>",
                $url,
                (strpos($url, "/{$value}/") !== false) ? $class : '',
                $name);
    }
}
