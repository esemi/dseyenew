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


	private function _getHiddenWorlds()
	{
		$conf = $this->getFrontController()->getParam('bootstrap')->getOption('worldListing');
		return ( empty($conf['hiddenWorld']) ) ? array() : $conf['hiddenWorld'];
	}

	private function _setWorldListIntoView()
	{
		$hidden = $this->_getHiddenWorlds();
		$worlds = array();
		foreach( $this->getActionController()->getHelper('ModelLoad')->load('Worlds')->listing() as $world )
		{
			if( !in_array($world->id, $hidden) )
				$worlds[] = $world;
		}

		$viewRenderer = Zend_Controller_Action_HelperBroker::getStaticHelper('viewRenderer');
		$viewRenderer->view->listWorlds = $worlds;
	}

	public function direct()
	{
		$this->_setWorldListIntoView();
	}

}
