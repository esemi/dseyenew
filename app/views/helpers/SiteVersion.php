<?php

class Zend_View_Helper_SiteVersion extends Zend_View_Helper_Abstract
{
	public function siteVersion()
	{
		return Zend_Controller_Front::getInstance()->getParam('bootstrap')->getOption('appVersion');
	}
}
