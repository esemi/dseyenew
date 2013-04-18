<?php

/*
 * хелпер для передачи списка миров во вьюшник
 *
 */
class Action_Helper_WorldsListing extends Zend_Controller_Action_Helper_Abstract
{
	public function preDispatch()
	{
		if( !in_array($this->getRequest()->getControllerName(), $this->_getExcludedControllers()) )
			$this->_setWorldListIntoView();
	}

	private function _getExcludedControllers()
	{
		$conf = $this->getFrontController()->getParam('bootstrap')->getOption('worldListing');
		return ( is_null($conf['excludeController']) ) ? array() : $conf['excludeController'];
	}


	private function _setWorldListIntoView()
	{
		$this->getActionController()->view->listWorlds = $this->getActionController()->getHelper('ModelLoad')->load('Worlds')->listing();
	}

	public function direct()
	{
		$this->_setWorldListIntoView();
	}

}
