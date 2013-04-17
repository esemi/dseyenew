<?php

class ServiceController extends Zend_Controller_Action
{
	public function init()
	{
		$this->view->title = 'Сервисы';
	}

	/*
	 * график онлайна за всё время
	 */
	public function onlineAction()
	{
		$this->view->helpLink = $this->view->url( array('id'=>'service_online'), 'helpView', true );

		$this->view->keywords = 'Сервисы, Статистика';
		$this->view->description = "Статистика игроков онлайн";
		$this->view->actTitle = 'Статистика игроков онлайн';

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
		$this->view->actTitle = 'Таблица археологического рейтинга артефактов';
	}


	/*
	 * глобальный поиск игроков по нику (во всех мирах)
	 */
	public function searchAction()
	{
		$this->view->helpLink = $this->view->url( array('id'=>'fast_search'), 'helpView', true );

		$this->view->keywords = 'Поиск, Поиск игроков';
		$this->view->description = 'Поиск игроков по всем мирам';
		$this->view->actTitle = 'Глобальный поиск игроков';

		$conf = $this->getFrontController()->getParam('bootstrap')->getOption('limits');
		$this->view->limitFast = ($this->_helper->checkAccess('others','fast_search_limit_x2')) ? 2 * $conf['fastSearch'] : $conf['fastSearch'];
	}

	/*
	 * инструменты для разработчиков
	 */
	public function devAction()
	{
		$this->view->helpLink = $this->view->url( array('id'=>'dev'), 'helpView', true );

		$this->view->keywords = 'Инструменты, Разработка, API';
		$this->view->description = 'Описание инструментов для сторонних разработчиков';
		$this->view->actTitle = 'Инструменты разработчиков';
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
		$this->view->actTitle = 'Военный калькулятор (вес армии, время раунда)';
	}

	/**
	 * форматер сообщений КК
	 */
	public function redbuttonAction()
	{
		$this->view->helpLink = $this->view->url( array('id'=>'redbutton_formater'), 'helpView', true );

		$this->view->keywords = 'Красная кнопка, Форматер, Сообщение';
		$this->view->description = 'Сервис форматирования сообщений красной кнопки';
		$this->view->actTitle = 'Форматер красной кнопки (вес армии, время раунда)';
	}

	/**
	 * Страница с описанием аддона для браузеров
	 */
	public function addonPageAction()
	{
		$this->view->helpLink = $this->view->url( array('id'=>'addon'), 'helpView', true );

		$this->view->addonUrls = $this->getFrontController()->getParam('bootstrap')->getOption('addon');

		$this->view->keywords = 'Аддон firefox, Аддон для браузера, Быстрый поиск игроков';
		$this->view->description = 'Аддон для популярных браузеров, позволяющий быстро искать игроков не уходя со страницы стороннего сайта';
		$this->view->actTitle = 'Аддон для браузера от нашего проекта';
	}

	/**
	 * Поиск сущности из аддона
	 * если нашлась одна сущность - редиректим на страницу оной
	 * если несколько или ни одной - оставляем тут и показываем список формой
	 * @TODO делить поиск по адресам и нику/имени_соты
	 */
	public function addonSearchAction()
	{
		$this->view->helpLink = $this->view->url( array('id'=>'addon'), 'helpView', true );

		$this->view->keywords = 'Аддон firefox, Аддон для браузера, Быстрый поиск игроков, Быстрый поиск сот';
		$this->view->description = 'Быстрый поиск игроков по адресу, нику и названии соты';
		$this->view->actTitle = 'Аддон для браузера от нашего проекта';

		//если есть гет параметр
		$term = trim($this->_request->getParam('term', ''));
		if( !empty($term) )
		{
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

}
