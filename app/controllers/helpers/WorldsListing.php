<?php

/*
 * хелпер для передачи списка миров во вьюшник
 *
 */
class Action_Helper_WorldsListing extends Zend_Controller_Action_Helper_Abstract
{
	public function preDispatch()
	{
		if( !$this->getRequest()->isXmlHttpRequest() &&  $this->getRequest()->getActionName() !== 'stat-add' ) //@FIXME replace on call single method
			$this->getActionController()->view->listWorlds = $this->getActionController()->getHelper('ModelLoad')->load('Worlds')->listing();
	}

}
