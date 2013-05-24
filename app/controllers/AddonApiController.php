<?php

/**
 * Addon callbacks
 */
class AddonApiController extends Zend_Controller_Action
{

	public function init()
	{
		$this->_helper->contextSwitch()
			->setActionContext('stat-add', array('json'))
			->initContext();
	}

	/**
	 * Поиск сущности из аддона
	 */
	public function searchAction()
	{
		$term = mb_substr(trim( urldecode($this->_request->getParam('term', ''))), 0, 50);
		$this->_helper->modelLoad('AddonStat')->add('search', $this->_request, array('term' => $term));
		$url = $this->view->url(array(), 'globalSearch') . '?term=' . urlencode($term);
		$this->_helper->getHelper('Redirector')->gotoUrlAndExit($url);
	}

	/**
	 * Вызов статистики из аддона
	 *
	 */
	public function statAddAction()
	{
		$this->_helper->contextSwitch()->initContext('json');

		$action = trim($this->_request->getPost('action', ''));
		$counter = trim($this->_request->getPost('counter',''));
		if( !empty($action) )
		{
			$this->_helper->modelLoad('AddonStat')->add($action, $this->_request, array('counter' => $counter));
			$this->view->result = array('status' => 'success');
		}else{
			$this->_helper->Logger()->customError('Empty action');
			$this->view->result = array('status' => 'fail');
		}
	}
}

?>