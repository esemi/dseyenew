<?php

class Zend_View_Helper_CacheVersion extends Zend_View_Helper_Abstract
{
    public function cacheVersion()
    {
        $params = Zend_Controller_Front::getInstance()->getParam('bootstrap')->getOption('staticCache');

        return $params['version'];
    }
}
