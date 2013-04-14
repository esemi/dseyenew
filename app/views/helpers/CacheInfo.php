<?php

class Zend_View_Helper_CacheInfo extends Zend_View_Helper_Abstract
{
    public function CacheInfo()
    {
        $manager = Zend_Controller_Front::getInstance()->getParam('bootstrap')->getResource('cachemanager');

        return sprintf('Кеш заполнен на %d%%', $manager->getCache('up')->getFillingPercentage() );
    }
}
