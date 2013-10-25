<?php

class ServiceController extends Zend_Controller_Action
{
	public function init()
	{
		$this->view->headTitle('Сервисы');
	}

	/*
	 * график онлайна за всё время
	 */
	public function onlineAction()
	{
		$this->view->helpLink = $this->view->url( array('id'=>'service_online'), 'helpView', true );

		$this->view->keywords = 'Сервисы, Статистика';
		$this->view->description = "Статистика игроков онлайн";
		$this->view->headTitle('Статистика игроков онлайн');

		$this->view->menu = $this->_helper->modelLoad('GameVersions')->getAllForStat();
	}

	/**
	 * таблица соответствия рейтинга археологии размерам артов
	 */
	public function archeologyAction()
	{
		$this->view->helpLink = $this->view->url( array('id'=>'archeology'), 'helpView', true );

		$this->view->keywords = 'Рейтинг, Археология, Артефакты';
		$this->view->description = 'Таблица соответствия роста археологического рейтинга размерам и типам артефактов';
		$this->view->headTitle('Таблица археологического рейтинга артефактов');
	}


	/**
	 * глобальный поиск игроков по нику/соте/адресу (во всех мирах)
	 *
	 * если нашлась одна сущность - редиректим на страницу оной
	 * если несколько или ни одной - оставляем тут и показываем список формой
	 * @TODO делить поиск по адресам и нику/имени_соты
	 */
	public function searchAction()
	{
		$this->view->helpLink = $this->view->url( array('id'=>'fast_search'), 'helpView', true );


		$this->view->keywords = 'Поиск, Поиск игроков';
		$this->view->description = 'Поиск игроков по всем мирам';
		$this->view->headTitle('Глобальный поиск игроков');
		$this->view->linkCanonical($this->view->url(array(), 'globalSearch', true));

		$conf = $this->getFrontController()->getParam('bootstrap')->getOption('limits');
		$limit = ($this->_helper->checkAccess('others','fast_search_limit_x2')) ? 2 * $conf['fastSearch'] : $conf['fastSearch'];
		$term = mb_substr(trim($this->_request->getParam('term', '')), 0, 50);
		$result = array();

		if( !empty($term) )
		{
			$decodeIds = function($x){ return $x['id']; };
			$findIds = array();

			//строгое совпадение ника
			$res = $this->_helper->modelLoad('Players')->findByNik($term, $limit);
			if( count($res) > 0 )
				$findIds = array_merge($findIds, array_map($decodeIds, $res));

			//строгое совпадение домашней соты
			$res = $this->_helper->modelLoad('Players')->findByDomName($term, $limit);
			if( count($res) > 0 )
				$findIds = array_merge($findIds, array_map($decodeIds, $res));

			//строгое совпадение адреса дом соты
			$res = $this->_helper->modelLoad('Players')->findByAddress($term);
			if( count($res) > 0 )
				$findIds = array_merge($findIds, array_map($decodeIds, $res));

			//строгое совпадение имени колонии
			$res = $this->_helper->modelLoad('PlayersColony')->findByName($term, $limit);
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
				$res = $this->_helper->modelLoad('Players')->findByNik($term, $limit, null, false);
				if( count($res) > 0 )
					$findIds = array_merge($findIds, array_map($decodeIds, $res));

				//мягкое совпадение домашней соты
				$res = $this->_helper->modelLoad('Players')->findByDomName($term, $limit, false);
				if( count($res) > 0 )
					$findIds = array_merge($findIds, array_map($decodeIds, $res));

				//мягкое совпадение имени колонии
				$res = $this->_helper->modelLoad('PlayersColony')->findByName($term, $limit, false);
				if( count($res) > 0 )
					$findIds = array_merge($findIds, array_map($decodeIds, $res));
			}

			//получаем результаты
			$findIds = array_slice($findIds, 0, $limit);
			foreach( $findIds as $idP ){
				$tmp = $this->_helper->modelLoad('Players')->getInfo($idP);
				$tmp['world'] = $this->_helper->modelLoad('Worlds')->getName($tmp['id_world']);
				$result[] = $tmp;
			}

			//если результат один - редиректим на страницу игрока
			if( count($result) === 1 ){
				$this->_helper->redirector->gotoRouteAndExit(array( 'idW' => $result[0]['id_world'], 'idP' => $result[0]['id'] ),'playerStat', true);
			}
		}

		$this->view->limit = $limit;
		$this->view->term = $term;
		$this->view->results = $result;
	}

	/*
	 * инструменты для разработчиков
	 */
	public function devAction()
	{
		$this->view->helpLink = $this->view->url( array('id'=>'dev'), 'helpView', true );

		$this->view->keywords = 'Инструменты, Разработка, API';
		$this->view->description = 'Описание инструментов для сторонних разработчиков';
		$this->view->headTitle('Инструменты разработчиков');
	}

	/*
	 * рассчёт веса армии и времени раунда
	 */
	public function armyAction()
	{
		$this->view->helpLink = $this->view->url( array('id'=>'battle_calc'), 'helpView', true );

		$this->view->worldIds = $this->_helper->modelLoad('WorldsBattle')->getAllIds();

		$this->view->keywords = 'Рассчёт, Вес армии, Время раунда, Время боя, Калькулятор';
		$this->view->description = 'Сервис рассчёта веса армии и времени раунда';
		$this->view->headTitle('Военный калькулятор (вес армии, время раунда)');
	}

	/**
	 * Страница с описанием аддона для браузеров
	 */
	public function addonPageAction()
	{
		$this->view->helpLink = $this->view->url( array('id'=>'addon'), 'helpView', true );

		$this->view->addonUrls = $this->getFrontController()->getParam('bootstrap')->getOption('addon');

		$this->view->keywords = 'Аддон firefox, Аддон Opera, Аддон Chrome Аддон для браузера, Быстрый поиск игроков';
		$this->view->description = 'Аддон для популярных браузеров, позволяющий быстро искать игроков не уходя со страницы стороннего сайта';
		$this->view->headTitle('Аддон для браузера от нашего проекта');
	}
}
