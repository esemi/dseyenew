<?php

/*
 * хелпер для передачи списка миров во вьюшник
 *
 */
class Action_Helper_WorldsListing extends Zend_Controller_Action_Helper_Abstract
{
	public function postDispatch()
	{
		if( !$this->getRequest()->isXmlHttpRequest() )
			$this->getActionController()->view->listWorlds = $this->getActionController()->getHelper('ModelLoad')->load('Worlds')->listing();
	}

}
