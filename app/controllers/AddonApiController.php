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
	 * если нашлась одна сущность - редиректим на страницу оной
	 * если несколько или ни одной - оставляем тут и показываем список формой
	 * @TODO делить поиск по адресам и нику/имени_соты
	 */
	public function searchAction()
	{
		$this->view->helpLink = $this->view->url( array('id'=>'addon'), 'helpView', true );

		$this->view->title = 'Аддон';
		$this->view->keywords = 'Аддон firefox, Аддон для браузера, Быстрый поиск игроков, Быстрый поиск сот';
		$this->view->description = 'Быстрый поиск игроков по адресу, нику и названии соты с помощью аддона для браузера';
		$this->view->actTitle = 'Поиск игроков';

		//если есть гет параметр
		$term = trim($this->_request->getParam('term', ''));
		if( !empty($term) )
		{
			//запоминаем статсу
			$this->_helper->modelLoad('AddonStat')->add('search', $this->_request, array('term' => $term));

			$decodeIds = function($x){ return $x['id']; };
			$findIds = array();

			//строгое совпадение ника
			$res = $this->_helper->modelLoad('Players')->findByNik($term);
			if( count($res) > 0 )
				$findIds = array_merge($findIds, array_map($decodeIds, $res));

			//строгое совпадение домашней соты
			$res = $this->_helper->modelLoad('Players')->findByDomName($term);
			if( count($res) > 0 )
				$findIds = array_merge($findIds, array_map($decodeIds, $res));

			//строгое совпадение адреса дом соты
			$res = $this->_helper->modelLoad('Players')->findByAddress($term);
			if( count($res) > 0 )
				$findIds = array_merge($findIds, array_map($decodeIds, $res));

			//строгое совпадение имени колонии
			$res = $this->_helper->modelLoad('PlayersColony')->findByName($term);
			if( count($res) > 0 )
				$findIds = array_merge($findIds, array_map($decodeIds, $res));

			//строгое совпадение адреса колонии
			$res = $this->_helper->modelLoad('PlayersColony')->findByAddress($term);
			if( count($res) > 0 )
				$findIds = array_merge($findIds, array_map($decodeIds, $res));

			//если ничего не нашли - ищем ник и соты LIKE term%
			if( count($findIds) === 0 )
			{
				//мягкое совпадение ника
				$res = $this->_helper->modelLoad('Players')->findByNik($term, null, false);
				if( count($res) > 0 )
					$findIds = array_merge($findIds, array_map($decodeIds, $res));

				//мягкое совпадение домашней соты
				$res = $this->_helper->modelLoad('Players')->findByDomName($term, false);
				if( count($res) > 0 )
					$findIds = array_merge($findIds, array_map($decodeIds, $res));

				//мягкое совпадение имени колонии
				$res = $this->_helper->modelLoad('PlayersColony')->findByName($term, false);
				if( count($res) > 0 )
					$findIds = array_merge($findIds, array_map($decodeIds, $res));
			}


			//получаем результаты
			$result = array();
			foreach( $findIds as $idP )
			{
				$result[] = $this->_helper->modelLoad('Players')->getInfo($idP);
			}

			//если результат один - редиректим на страницу игрока
			if( count($result) === 1 )
			{
				$this->_helper->redirector->gotoRouteAndExit(array( 'idW' => $result[0]['id_world'], 'idP' => $result[0]['id'] ),'playerStat', true);
			}

			$this->view->results = $result;
		}

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
			$this->_addErrorLog('Empty action');
			$this->view->result = array('status' => 'fail');
		}
	}

	protected function _addErrorLog($error)
	{
		$this->getInvokeArg('bootstrap')->getResource('Log')->error(
				$this->_request->getClientIp()
				.' '
				.$this->_request->getRequestUri()
				.' '
				.$error
				.' '
				. $this->_request->getServer('HTTP_REFERER', '')
				. ' '
				. serialize($this->_request->getPost())
				. ' '
				. $this->_request->getServer('HTTP_USER_AGENT', '')
		);
	}


}

?>