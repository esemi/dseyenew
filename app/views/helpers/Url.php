<?php

class Zend_View_Helper_Url extends Zend_View_Helper_Abstract
{
	public function url(array $urlOptions = array(), $name = null, $reset = false, $encode = true, $fullHref = true)
	{
		$router = Zend_Controller_Front::getInstance()->getRouter();
		return ($fullHref == true) ?
				$this->view->serverUrl().$router->assemble($urlOptions, $name, $reset, $encode)
				:
				$router->assemble($urlOptions, $name, $reset, $encode);
	}
}
