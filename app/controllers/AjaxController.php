<?php

/*
 * контроллер для всех ajax запросов
 */

class AjaxController extends Zend_Controller_Action
{

	protected
		$_onlineMap = null,
		$_dshelpMap = null;

	public function init()
	{
		//только аякс запросы
		if( !$this->_request->isXmlHttpRequest() )
			throw new Exception('AJAX only');

		//проверка на реферер
		$url = $this->view->serverUrl();
		if( $url !== substr($this->_request->getServer('HTTP_REFERER', ''), 0, strlen($url) ) )
			throw new Exception('AJAX referer');

		//логирование всех запросов, кроме секурных
		$this->_helper->Logger()->ajaxAccess();


		$this->_helper->getHelper('AjaxContext')
							->addActionContext('map', 'json')
							->addActionContext('player-info', 'json')
							->addActionContext('monitor-add', 'json')
							->addActionContext('monitor-del', 'json')
							->addActionContext('autosearch-save-new', 'json')
							->addActionContext('autosearch-save-as', 'json')
							->addActionContext('autosearch-del', 'json')
							->addActionContext('search', 'html')
							->addActionContext('graph-player', 'json')
							->addActionContext('graph-world', 'json')
							->addActionContext('graph-alliance', 'json')
							->addActionContext('graph-online', 'json')
							->addActionContext('graph-current-players-count', 'json')
							->addActionContext('army-battle-time-compute', 'json')
							->initContext();
	}

	/*
	 * Добавление игрока в мониторинг
	 */
	public function monitorAddAction()
	{
		if( !$this->_helper->checkAccess('monitoring','manage') )
		{
			$this->view->error = 'Сессия устарела. Перезайдите в систему и попробуйте снова';
			return;
		}

		if( !$this->_helper->tokenCheck($this->_request->getPost('csrf')) )
		{
			$this->view->error = 'Токен устарел. Перезагрузите исходную страницу';
			return;
		}

		//проверим ид игрока
		$idP = (int)$this->_request->getPost('idP');
		if( !$this->_helper->modelLoad('Players')->validate($idP) )
		{
			$this->view->error = 'Некорректный идентификатор игрока';
			$this->_helper->Logger()->customError($this->view->error);
			return;
		}

		//добавляем игрока в мониторинг, если его там нет
		$player = $this->_helper->modelLoad('Players')->getInfo($idP);
		$user = Zend_Auth::getInstance()->getStorage()->read();

		if( $this->_helper->modelLoad('MonitorItems')->issetByUser($idP, $user->id) )
		{
			$this->view->error = 'Данный игрок уже добавлен в мониторинг';
			$this->_helper->Logger()->customError($this->view->error);
			return;
		}

		$this->_helper->modelLoad('MonitorItems')->add( $idP, $user->id );

		$this->_helper->modelLoad('UsersHistory')->add(
				$user->id,
				$this->view->partial( 'Partials/history/monitorAddPlayer.phtml', array( 'player' => $player ) ),
				$this->_request);
		$this->view->html = $this->view->partial( 'Partials/message/monitorAddPlayer.phtml', array( 'idP' => $idP ) );
	}


	/*
	 * Удаление игрока из мониторинга
	 */
	public function monitorDelAction()
	{
		if( !$this->_helper->checkAccess('monitoring','manage') )
		{
			$this->view->error = 'Сессия устарела. Перезайдите в систему и попробуйте снова';
			return;
		}

		if( !$this->_helper->tokenCheck($this->_request->getPost('csrf')) )
		{
			$this->view->error = 'Токен устарел. Перезагрузите исходную страницу';
			return;
		}

		$idP = (int)$this->_request->getPost('idP',0);
		$user = Zend_Auth::getInstance()->getStorage()->read();

		if( !$this->_helper->modelLoad('MonitorItems')->issetByUser($idP, $user->id) )
		{
			$this->view->error = 'Данный игрок не найден в вашем мониторинге';
			$this->_helper->Logger()->customError($this->view->error);
			return;
		}

		$this->_helper->modelLoad('MonitorItems')->del( $idP, $user->id );

		$player = $this->_helper->modelLoad('Players')->getInfo($idP);

		$this->_helper->modelLoad('UsersHistory')->add(
				$user->id,
				$this->view->partial( 'Partials/history/monitorDelPlayer.phtml', array( 'player' => $player ) ),
				$this->_request);
		$this->view->html = $this->view->partial( 'Partials/message/monitorDelPlayer.phtml', array( 'idP' => $idP ) );
	}


	/*
	 * Добавление нового автопоиска
	 */
	public function autosearchSaveNewAction()
	{
		if( !$this->_helper->checkAccess('autosearch','add') )
		{
			$this->view->error = 'Сессия устарела. Перезайдите в систему и попробуйте снова';
			return;
		}

		if( !$this->_helper->tokenCheck($this->_request->getPost('csrf')) )
		{
			$this->view->error = 'Токен устарел. Перезагрузите исходную страницу';
			return;
		}

		//проверим мир
		$idW = (int)$this->_request->getPost('idW', 0);
		if( $this->_helper->modelLoad('Worlds')->validate( (int)$idW ) !== true )
		{
			$this->view->error = 'Некорректный идентификатор мира';
			$this->_helper->Logger()->customError($this->view->error);
			return;
		}

		//проверим имя
		$name = $this->_request->getPost('newname');
		if( empty($name) )
		{
			$this->view->error = 'Некорректное имя';
			$this->_helper->Logger()->customError($this->view->error);
			return;
		}

		//проверим настройки
		$rases = $this->_helper->modelLoad('Rases')->getRasesForSearch();
		$prop = @urldecode($this->_request->getPost('prop'));
		$prop = @unserialize($prop);
		if( $prop === false || $this->_helper->modelLoad('Players')->_validateFullSearchProp($prop, $rases) !== true )
		{
			$this->view->error = 'Некорректные настройки поиска';
			$this->_helper->Logger()->customError($this->view->error);
			return;
		}


		//сохраняем
		$user = Zend_Auth::getInstance()->getStorage()->read();

		try{
			$idA = $this->_helper->modelLoad('UsersSearch')->add( $user->id, $idW, $name, $prop );
		}catch (Exception $e){
			$this->view->error = $e->getMessage();
			$this->_helper->Logger()->customError($this->view->error);
			return;
		}

		$this->_helper->modelLoad('UsersHistory')->add(
					$user->id,
					$this->view->partial( 'Partials/history/autosearchAdd.phtml', array( 'idA' => $idA, 'name' => $name ) ),
					$this->_request);
		$this->view->success = 'Новый автопоиск успешно добавлен';
	}

	/*
	 * Редактирование записи старого автопоиска
	 */
	public function autosearchSaveAsAction()
	{
		if( !$this->_helper->checkAccess('autosearch','add') )
		{
			$this->view->error = 'Сессия устарела. Перезайдите в систему и попробуйте снова';
			return;
		}

		if( !$this->_helper->tokenCheck($this->_request->getPost('csrf')) )
		{
			$this->view->error = 'Токен устарел. Перезагрузите исходную страницу';
			return;
		}

		$user = Zend_Auth::getInstance()->getStorage()->read();

		//проверим ид поиска и принадлежность
		$idA = (int)$this->_request->getPost('idA');
		if( !$this->_helper->modelLoad('UsersSearch')->validateAccess( $idA, $user->id ) )
		{
			$this->view->error = 'Некорректный идентификатор поиска';
			$this->_helper->Logger()->customError($this->view->error);
			return;
		}

		//проверим настройки
		$rases = $this->_helper->modelLoad('Rases')->getRasesForSearch();
		$prop = @urldecode($this->_request->getPost('prop'));
		$prop = @unserialize($prop);
		if( $prop === false || $this->_helper->modelLoad('Players')->_validateFullSearchProp($prop, $rases) !== true )
		{
			$this->view->error = 'Некорректные настройки поиска';
			$this->_helper->Logger()->customError($this->view->error);
			return;
		}


		//сохраняем
		try{
			$this->_helper->modelLoad('UsersSearch')->upd( $idA, $prop );
		}catch (Exception $e){
			$this->view->error = $e->getMessage();
			$this->_helper->Logger()->customError($this->view->error);
			return;
		}

		$search = $this->_helper->modelLoad('UsersSearch')->getOne( $idA, $user->id );
		$this->_helper->modelLoad('UsersHistory')->add(
				$user->id,
				$this->view->partial( 'Partials/history/autosearchEdit.phtml', array( 'idA' => $idA, 'name' => $search['name'] ) ),
				$this->_request);
		$this->view->success = 'Автопоиск успешно изменён';
	}

	/*
	 * Удаление записи старого автопоиска
	 */
	public function autosearchDelAction()
	{
		if( !$this->_helper->checkAccess('autosearch','del') )
		{
			$this->view->error = 'Сессия устарела. Перезайдите в систему и попробуйте снова';
			return;
		}

		if( !$this->_helper->tokenCheck($this->_request->getPost('csrf')) )
		{
			$this->view->error = 'Токен устарел. Перезагрузите исходную страницу';
			return;
		}

		$user = Zend_Auth::getInstance()->getStorage()->read();

		//проверим ид поиска и принадлежность
		$idA = (int)$this->_request->getPost('idA');
		if( !$this->_helper->modelLoad('UsersSearch')->validateAccess( $idA, $user->id  ) )
		{
			$this->view->error = 'Некорректный идентификатор поиска';
			$this->_helper->Logger()->customError($this->view->error);
			return;
		}

		$search = $this->_helper->modelLoad('UsersSearch')->getOne( $idA, $user->id );
		try{
			$this->view->del = $this->_helper->modelLoad('UsersSearch')->del( $idA );
		}catch (Exception $e){
			$this->view->error = $e->getMessage();
			$this->_helper->Logger()->customError($this->view->error);
			return;
		}

		$this->_helper->modelLoad('UsersHistory')->add(
				$user->id,
				$this->view->partial( 'Partials/history/autosearchDel.phtml', array( 'name' => $search['name'] ) ),
				$this->_request);
		$this->view->success = 'Автопоиск успешно удалён';
	}

	/*
	 * поиск игроков по нику/соте (по миру или глобальный)
	 * @var term = ник%
	 */
	public function searchAction()
	{
		$term = $this->_request->getPost('term','');
		$idW = $this->_request->getPost('idW', 0);

		if( ($this->_helper->modelLoad('Worlds')->validate( (int)$idW ) === true || $idW == 0  ) && preg_match('/^[\wА-ЯЁа-яё.-\s]{3,100}$/ui', $term) )
		{
			$conf = $this->getFrontController()->getParam('bootstrap')->getOption('limits');
			$limit = ($this->_helper->checkAccess('others','fast_search_limit_x2')) ? 2 * $conf['fastSearch'] : $conf['fastSearch'];
			$this->view->players = $this->_helper->modelLoad('Players')->fastSearch( $term, $limit, $idW );
		}else{
			$this->view->error = 'Некорректные параметры';
			$this->_helper->Logger()->customError($this->view->error);
		}
	}

	/*
	 * генерим карту кольца+
	 */
	public function mapAction()
	{
		$this->view->idWorld  = $idW = (int) $this->_request->getPost('idW');
		$this->view->ring     = $ring = (int) $this->_request->getPost('ring');

		$this->view->first    = $first = (int) $this->_request->getPost('first');
		$this->view->last     = $last = (int) $this->_request->getPost('last');


		//проверки
		if( ($ring < 1) || ($ring > 4) || $this->_helper->modelLoad('Worlds')->validate( $idW ) !== true )  //кольцо левое или мир хз какой
		{
			$this->view->error = 'Некорректные параметры (кольцо или мир)';
			$this->_helper->Logger()->customError($this->view->error);
			return;
		} elseif( $first < 1 || $last < 1 || $first == $last) { //параметры карты не дошли
			$this->view->error = 'Некорректные значения шагов';
			$this->_helper->Logger()->customError($this->view->error);
			return;
		}

		//получаем границы деления на стадии развития по основным параметрам
		$ranks = Zend_Controller_Front::getInstance()->getParam('bootstrap')->getOption('map');
		if( count($ranks) != 5 ) {
			$this->view->error = 'Некорректные границы групп развития';
			$this->_helper->Logger()->customError($this->view->error);
			return;
		}

		//получаем макс компл на этом кольце
		$this->view->numMax = $maxNum = $this->_helper->modelLoad('WorldsProperty')->getMaxComple($idW, $ring);
		if( $maxNum === 0 ) {
			$this->view->error = 'Не найден максимальный комплекс на выбранном кольце';
			$this->_helper->Logger()->customError($this->view->error);
			return;
		}

		$this->view->map = $this->view->partial(
				'Partials/map.phtml',
				array(
					'map' => $this->_helper->modelLoad('Players')->getRingMap($idW, $first, $last, $ring),
					'first' => $first, 'last' => $last,  'ring' => $ring,
					'idW' => $idW, 'max' => $maxNum,
					'ranks' => $ranks ));
	}


	/*
	 * подробная инфа по игроку для карты+
	 */
	public function playerInfoAction()
	{
		//получаем инфу по игроку
		$info = $this->_helper->modelLoad('Players')->getInfo((int) $this->_request->getPost('idP'));
		if( $info === false )
		{
			$this->view->error = 'Некорректный идентификатор игрока';
			$this->_helper->Logger()->customError($this->view->error);
			return;
		}

		$extended = $this->_helper->modelLoad('WorldsGameParse')->statusAvaliable( $info['id_world'] );
		$this->view->html = $this->view->partial( 'Partials/mapPlayer.phtml', array( 'player' => $info, 'extendedStatusAvaliable' => $extended ) );
	}

	/*
	 * получение данных для графиков игроков
	 *
	 * @todo дельты из БД
	 */
	public function graphPlayerAction()
	{
		$idP = (int) $this->_request->getPost('idP');
		$type = $this->_request->getPost('type');
		//особая обработка графика с хелпера
		if( $type === 'dshelp' )
		{
			$info = $this->_helper->modelLoad('Players')->getInfo( $idP );
			if( $info === false )
			{
				$this->view->error = 'Игрок не найден';
				$this->_helper->Logger()->customError($this->view->error);
				return;
			}

			if( !$this->_helper->modelLoad('WorldsDshelp')->graphAvailable( $info['id_world'] ) )
			{
				$this->view->error = 'Данный график недоступен для выбранного мира';
				$this->_helper->Logger()->customError($this->view->error);
				return;
			}

			$nameHelp = $this->_helper->modelLoad('WorldsDshelp')->getName( $info['id_world']);
			$this->_dshelpMap = new App_Model_Dshelp($nameHelp);
			$url = $this->_dshelpMap->getUrl( $info['nik'], $idP );
			if( $url === false )
			{
				$this->view->error = 'График временно недоступен';
				return;
			}
			$this->view->url = $url;
			return;
		}

		//рядовые графики статистики по одному парамметру
		list($data, $decimal) = $this->_getPlayerStatData($type, $idP);
		if( is_null($data) ){
			$this->view->error = 'Не выбран тип графика';
			$this->_helper->Logger()->customError($this->view->error);
		}elseif( count($data) === 0 ){
			$this->view->error = 'Данные отсутствуют';
		}else{
			$this->view->series = $this->_prepareStandartSingleGraph($type, $data);
			$this->view->decimal = $decimal;
		}
	}

	/*
	 * выбор соответствующей таблички и выборка статистик по игроку
	 * @return mixed array|null
	 */
	protected function _getPlayerStatData($type, $idP)
	{
		$data = null;
		$decimals = false;
		switch( $type )
		{
			case 'rank_old':
				$data = $this->_helper->modelLoad('StatRankOld')->getStat($idP);
				break;
			case 'rank_new':
				$data = $this->_helper->modelLoad('StatRankNew')->getStat($idP);
				break;
			case 'bo':
				$data = $this->_helper->modelLoad('StatBo')->getStat($idP);
				$decimals = true;
				break;
			case 'ra':
				$data = $this->_helper->modelLoad('StatRa')->getStat($idP);
				$decimals = true;
				break;
			case 'nra':
				$data = $this->_helper->modelLoad('StatNra')->getStat($idP);
				$decimals = true;
				break;
			case 'level':
				$data = $this->_helper->modelLoad('StatLevel')->getStat($idP);
				break;
			case 'archeology':
				$data = $this->_helper->modelLoad('StatArch')->getStat($idP);
				break;
			case 'building':
				$data = $this->_helper->modelLoad('StatBuild')->getStat($idP);
				break;
			case 'science':
				$data = $this->_helper->modelLoad('StatScien')->getStat($idP);
				break;
		}
		return array($data, $decimals);
	}


	/*
	 * преобразует данные в формат для одиночного графика игрока
	 */
	private function _prepareStandartSingleGraph($type, $data, $returnArray = true)
	{
		$items = array(
			'rank_old' => 'Рейтинг (стар.)',
			'rank_new' => 'Рейтинг (нов.)',
			'bo' => 'БО',
			'ra' => 'РА',
			'nra' => 'НРА',
			'archeology' => 'Археология',
			'building' => 'Строительство',
			'science' => 'Наука',
			'level' => 'Уровень',
			'count_alliance' => 'Всего',
			'count_premium' => 'Всего',
			'count_notavaliable_gate' => 'Всего');

		$out = new stdClass();
		$out->name = $items[$type];
		$out->realname = $type;
		$out->visible = true;
		$out->data = array();

		foreach( $data as $val )
			$out->data[] = array($val['date'], floatval($val['value']));

		return ($returnArray) ? array($out) : $out;
	}

	/*
	 * преобразует данные для колоночного графика (2 колонки)
	 */
	private function _prepareIOWorldGraph($data)
	{
		$input = new stdClass();
		$input->name = 'Пришли';
		$input->realname = 'input';
		$input->visible = true;
		$input->data = array();

		$output = new stdClass();
		$output->name = 'Ушли';
		$output->realname = 'output';
		$output->visible = true;
		$output->data = array();

		foreach( $data as $val )
		{
			$input->data[] = array($val['date'], floatval($val['input']));
			$output->data[] = array($val['date'], floatval($val['output']));
		}

		return array($input, $output);
	}

	/**
	 * преобразует данные для графика по рассам
	 */
	private function _prepareStandartRaseGraph($data, $hideRase = true)
	{
		$items = array(
			'all' => 'Всего',
			'voran' => 'Воранеры',
			'liens' => 'Лиенсу',
			'psol' => 'Псолао');

		$result = array();

		foreach( $items as $realname => $name )
		{
			$$realname = new stdClass();
			$$realname->name = $name;
			$$realname->realname = $realname;
			$$realname->data = array();
			$$realname->visible = (count($result) == 0 || !$hideRase);
			$result[] = $$realname;
		}

		foreach( $data as $val )
			foreach( $items as $realname => $name )
				$$realname->data[] = array($val['date'], floatval($val[$realname]));

		return $result;
	}

	/*
	 * получение данных для графиков мира
	 */
	public function graphWorldAction()
	{
		$idW= (int) $this->_request->getPost('idW');
		$type = $this->_request->getPost('type');

		$titles = array(
			'in_out' => 'Пришли/ушли',
			'count_player' => 'Количество игроков',
			'count_colony' => 'Количество колоний',
			'count_alliance' => 'Количество альянсов',
			'rank_old_sum' => 'Суммарный рейтинг (стар.)',
			'rank_old_avg' => 'Средний рейтинг (стар.)',
			'bo_sum' => 'Суммарный боевой рейтинг',
			'bo_avg' => 'Средний боевой рейтинг',
			'nra_sum' => 'Суммарный новый рейтинг активности',
			'nra_avg' => 'Средний новый рейтинг активности',
			'ra_sum' => 'Суммарный рейтинг активности',
			'ra_avg' => 'Средний рейтинг активности',
			'level_avg' => 'Средний уровень',
			'rank_new_sum' => 'Суммарный рейтинг (нов.)',
			'rank_new_avg' => 'Средний рейтинг (нов.)',
			'arch_sum' => 'Суммарная археология',
			'arch_avg' => 'Средняя археология',
			'build_sum' => 'Суммарное строительство',
			'build_avg' => 'Среднее строительство',
			'scien_sum' => 'Суммарная наука',
			'scien_avg' => 'Средняя наука',
			'premium' => 'Количество премиум аккаунтов',
			'gate_not_avaliable' => 'Количество недоступных игроков (щит, бан, новичок, ...)',
		);

		switch( $type )
		{
			case 'in_out':
				$data = $this->_helper->modelLoad('StatWorlds')->getIO($idW);
				$series = $this->_prepareIOWorldGraph($data);
				break;
			case 'count_player':
				$data = $this->_helper->modelLoad('StatWorlds')->getCountPlayers($idW);
				$series = $this->_prepareStandartRaseGraph($data);
				break;
			case 'count_colony':
				$data = $this->_helper->modelLoad('StatWorlds')->getCountColonies($idW);
				$series = $this->_prepareStandartRaseGraph($data);
				break;
			case 'count_alliance':
				$data = $this->_helper->modelLoad('StatWorlds')->getCountAlliances($idW);
				$series = $this->_prepareStandartSingleGraph('count_alliance', $data);
				break;
			case 'rank_old_sum':
				$data = $this->_helper->modelLoad('StatWorlds')->getSumRankOld($idW);
				$series = $this->_prepareStandartRaseGraph($data);
				break;
			case 'rank_old_avg':
				$data = $this->_helper->modelLoad('StatWorlds')->getAvgRankOld($idW);
				$series = $this->_prepareStandartRaseGraph($data, false);
				break;
			case 'bo_sum':
				$data = $this->_helper->modelLoad('StatWorlds')->getSumBO($idW);
				$series = $this->_prepareStandartRaseGraph($data);
				break;
			case 'bo_avg':
				$data = $this->_helper->modelLoad('StatWorlds')->getAvgBO($idW);
				$series = $this->_prepareStandartRaseGraph($data, false);
				break;
			case 'ra_sum':
				$data = $this->_helper->modelLoad('StatWorlds')->getSumRA($idW);
				$series = $this->_prepareStandartRaseGraph($data);
				break;
			case 'ra_avg':
				$data = $this->_helper->modelLoad('StatWorlds')->getAvgRA($idW);
				$series = $this->_prepareStandartRaseGraph($data, false);
				break;
			case 'nra_sum':
				$data = $this->_helper->modelLoad('StatWorlds')->getSumNRA($idW);
				$series = $this->_prepareStandartRaseGraph($data);
				break;
			case 'nra_avg':
				$data = $this->_helper->modelLoad('StatWorlds')->getAvgNRA($idW);
				$series = $this->_prepareStandartRaseGraph($data, false);
				break;
			case 'level_avg':
				$data = $this->_helper->modelLoad('StatWorlds')->getAvgLevel($idW);
				$series = $this->_prepareStandartRaseGraph($data, false);
				break;
			case 'rank_new_sum':
				$data = $this->_helper->modelLoad('StatWorlds')->getSumRankNew($idW);
				$series = $this->_prepareStandartRaseGraph($data);
				break;
			case 'rank_new_avg':
				$data = $this->_helper->modelLoad('StatWorlds')->getAvgRankNew($idW);
				$series = $this->_prepareStandartRaseGraph($data, false);
				break;
			case 'arch_sum':
				$data = $this->_helper->modelLoad('StatWorlds')->getSumArch($idW);
				$series = $this->_prepareStandartRaseGraph($data);
				break;
			case 'arch_avg':
				$data = $this->_helper->modelLoad('StatWorlds')->getAvgArch($idW);
				$series = $this->_prepareStandartRaseGraph($data, false);
				break;
			case 'build_sum':
				$data = $this->_helper->modelLoad('StatWorlds')->getSumBuild($idW);
				$series = $this->_prepareStandartRaseGraph($data);
				break;
			case 'build_avg':
				$data = $this->_helper->modelLoad('StatWorlds')->getAvgBuild($idW);
				$series = $this->_prepareStandartRaseGraph($data, false);
				break;
			case 'scien_sum':
				$data = $this->_helper->modelLoad('StatWorlds')->getSumScien($idW);
				$series = $this->_prepareStandartRaseGraph($data);
				break;
			case 'scien_avg':
				$data = $this->_helper->modelLoad('StatWorlds')->getAvgScien($idW);
				$series = $this->_prepareStandartRaseGraph($data, false);
				break;
			case 'premium':
				$data = $this->_helper->modelLoad('StatWorlds')->getCountPremium($idW);
				$series = $this->_prepareStandartSingleGraph('count_premium', $data);
				break;
			case 'gate_not_avaliable':
				$data = $this->_helper->modelLoad('StatWorlds')->getCountNotAvaliableGate($idW);
				$series = $this->_prepareStandartSingleGraph('count_notavaliable_gate', $data);
				break;
			default:
				$this->view->error = 'Не выбран тип графика';
				$this->_helper->Logger()->customError($this->view->error);
				return;
				break;
		}

		if(count($data) === 0){
			$this->view->error = 'Данные отсутствуют';
		}else{
			$this->view->series = $series;
			$this->view->title = $titles[$type];
		}

	}


	/**
	 * получение данных для графиков альянса
	 */
	public function graphAllianceAction()
	{
		$idA= (int) $this->_request->getPost('idA');
		$type = $this->_request->getPost('type');

		switch( $type )
		{
			case 'count_player':
				$data = $this->_helper->modelLoad('StatAlliances')->getCountPlayers($idA);
				$series = $this->_prepareStandartRaseGraph($data);
				break;
			case 'count_colony':
				$data = $this->_helper->modelLoad('StatAlliances')->getCountColonies($idA);
				$series = $this->_prepareStandartRaseGraph($data);
				break;
			case 'rank_old_sum':
				$data = $this->_helper->modelLoad('StatAlliances')->getSumRankOld($idA);
				$series = $this->_prepareStandartRaseGraph($data);
				break;
			case 'rank_old_avg':
				$data = $this->_helper->modelLoad('StatAlliances')->getAvgRankOld($idA);
				$series = $this->_prepareStandartRaseGraph($data);
				break;
			case 'bo_sum':
				$data = $this->_helper->modelLoad('StatAlliances')->getSumBO($idA);
				$series = $this->_prepareStandartRaseGraph($data);
				break;
			case 'bo_avg':
				$data = $this->_helper->modelLoad('StatAlliances')->getAvgBO($idA);
				$series = $this->_prepareStandartRaseGraph($data);
				break;
			case 'ra_sum':
				$data = $this->_helper->modelLoad('StatAlliances')->getSumRA($idA);
				$series = $this->_prepareStandartRaseGraph($data);
				break;
			case 'ra_avg':
				$data = $this->_helper->modelLoad('StatAlliances')->getAvgRA($idA);
				$series = $this->_prepareStandartRaseGraph($data);
				break;
			case 'nra_sum':
				$data = $this->_helper->modelLoad('StatAlliances')->getSumNRA($idA);
				$series = $this->_prepareStandartRaseGraph($data);
				break;
			case 'nra_avg':
				$data = $this->_helper->modelLoad('StatAlliances')->getAvgNRA($idA);
				$series = $this->_prepareStandartRaseGraph($data);
				break;
			case 'level_avg':
				$data = $this->_helper->modelLoad('StatAlliances')->getAvgLevel($idA);
				$series = $this->_prepareStandartRaseGraph($data);
				break;
			case 'rank_new_sum':
				$data = $this->_helper->modelLoad('StatAlliances')->getSumRankNew($idA);
				$series = $this->_prepareStandartRaseGraph($data);
				break;
			case 'rank_new_avg':
				$data = $this->_helper->modelLoad('StatAlliances')->getAvgRankNew($idA);
				$series = $this->_prepareStandartRaseGraph($data);
				break;
			case 'arch_sum':
				$data = $this->_helper->modelLoad('StatAlliances')->getSumArch($idA);
				$series = $this->_prepareStandartRaseGraph($data);
				break;
			case 'arch_avg':
				$data = $this->_helper->modelLoad('StatAlliances')->getAvgArch($idA);
				$series = $this->_prepareStandartRaseGraph($data);
				break;
			case 'build_sum':
				$data = $this->_helper->modelLoad('StatAlliances')->getSumBuild($idA);
				$series = $this->_prepareStandartRaseGraph($data);
				break;
			case 'build_avg':
				$data = $this->_helper->modelLoad('StatAlliances')->getAvgBuild($idA);
				$series = $this->_prepareStandartRaseGraph($data);
				break;
			case 'scien_sum':
				$data = $this->_helper->modelLoad('StatAlliances')->getSumScien($idA);
				$series = $this->_prepareStandartRaseGraph($data);
				break;
			case 'scien_avg':
				$data = $this->_helper->modelLoad('StatAlliances')->getAvgScien($idA);
				$series = $this->_prepareStandartRaseGraph($data);
				break;
			default:
				$this->view->error = 'Не выбран тип графика';
				$this->_helper->Logger()->customError($this->view->error);
				return;
				break;
		}

		if(count($data) === 0)
		{
			$this->view->error = 'Данные отсутствуют';
		}else{
			$this->view->series = $series;
		}
	}


	/*
	 * данные для пай графика на главной по количеству игроков в мирах
	 */
	public function graphCurrentPlayersCountAction()
	{
		$data = $this->_helper->modelLoad('WorldsProperty')->getAllPlayersCount();

		$series = new stdClass();
		$series->name = 'Количество игроков';
		$series->data = array();
		$series->total = 0;
		foreach($data as $world)
		{
			$series->total = $series->total + intval($world['count']);
			$series->data[] = array(
				'name' => $world['name'],
				'y' => intval($world['count']) );
		}

		$this->view->series = $series;
	}

	/*
	 * данные для графиков статистики игроков онлайн
	 */
	public function graphOnlineAction()
	{
		$version = $this->_helper->modelLoad('GameVersions')->getByName($this->_request->getPost('version',''));

		if(count($version) != 1){
			$this->view->error = 'Неверная версия игры';
			$this->_helper->Logger()->customError($this->view->error);
			return;
		}

		$data = $this->_helper->modelLoad('StatOnline')->getAllOnline($version[0]['id']);
		if(count($data) == 0){
			$this->view->error = 'Данные отсутствуют';
		}else{
			$this->view->series = $this->_helper->modelLoad('StatOnline')->prepareForHourGraph($data);
		}
	}

	/*
	 * Рассчёт времени раунда
	 */
	public function armyBattleTimeComputeAction()
	{
		$w1 = intval($this->_request->getPost('weight1',0));
		$w2 = intval($this->_request->getPost('weight2',0));
		$idW = $this->_request->getPost('idW',0);
		if( !$this->_helper->modelLoad('Worlds')->validate( $idW ) )
		{
			$this->view->error = 'Некорректный идентификатор мира';
			$this->_helper->Logger()->customError($this->view->error);
			return;
		}

		if( $w1 === false || $w2 === false || $w1 <= 0 || $w2 <= 0 )
		{
			$this->view->error = 'Некорректное значение веса армии';
			$this->_helper->Logger()->customError($this->view->error);
			return;
		}

		$worldProp = $this->_helper->modelLoad('WorldsBattle')->getPropsById($idW);
		if( is_null($worldProp) )
		{
			$this->view->error = 'Отсутствуют коэфициенты рассчёта';
			$this->_helper->Logger()->customError($this->view->error);
			return;
		}

		//сама формула
		$A = max(array($w1, $w2)) * 10; //умножение на 10 - приводим продовес к статовесу
		$B = min(array($w1, $w2)) * 10;
		$C = floatval($worldProp->default_time) * intval($worldProp->turn_time) * 60; //дефолтное значение времени раунда в секундах
		$Z = ceil( log($A/1000, 12) );//звёздность армии log12(A) округлёный в большую сторону

		$minTime = floatval($worldProp->min_time) * intval($worldProp->turn_time) * 60;
		$maxTime = floatval($worldProp->max_time) * intval($worldProp->turn_time) * 60;
		$sourceResult = ($B*$C*$Z)/(2*$A);
		$this->view->source = $sourceResult;

		if( $sourceResult > $maxTime )
			$sourceResult = $maxTime;
		else if( $sourceResult < $minTime )
			$sourceResult = $minTime;

		$this->view->html = Mylib_Utils::secondsToTime($sourceResult);
	}
}
