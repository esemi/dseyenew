<?php

/*
 * контроллер основного меню (по мирам)
 */

class WorldsController extends Zend_Controller_Action
{

	protected $idW = null;

	public function init()
	{
		if( $this->_helper->modelLoad('Worlds')->validate((int) $this->_getParam('idW')) !== true )
			throw new Mylib_Exception_NotFound('World not found');
		$this->view->idWorld = $this->idW = (int) $this->_getParam('idW');

		//имя мира
		$this->view->nameWorld = $this->_helper->modelLoad('Worlds')->getName($this->idW);
		$this->view->headTitle("Мир {$this->view->nameWorld}");

		$this->view->rubberPage = true;//резиновый шаблон

		//@TODO add context into bootstrap (?) and single export controller (?)
		$this->_helper->getHelper('contextSwitch')
				->addContext('export-csv', array(
					'suffix' => 'csv',
					'headers' => array(
						'Content-Type' => 'application/csv',
						'Content-Disposition' => 'attachment; filename="export.csv"',
						'Pragma' => 'no-cache',
						'Expires' => '0',)))
				->addActionContext('search', array('export-csv'))
				->initContext();
	}

	/*
	 * вывод общей инфы по конкретному миру
	 */
	public function indexAction()
	{
		$this->view->keywords = "{$this->view->nameWorld}, Мир, Описание, Показатели";
		$this->view->description = "Мир {$this->view->nameWorld}, описание и основные показатели";
		$this->view->headTitle('О мире');

		//основные данные
		$this->view->mainData = $this->_helper->modelLoad('Worlds')->getData($this->idW);
		$versionData = $this->_helper->modelLoad('GameVersions')->getData($this->view->mainData['id_version']);
		$this->view->mainData['version'] = $versionData['name'];
		$this->view->mainData['date_main_upd'] = $this->_helper->modelLoad('WorldsCsv')->getUpdDate($this->idW);
		$this->view->mainData['date_old_ranks'] = $this->_helper->modelLoad('WorldsOldranks')->getUpdDate($this->idW);
		$this->view->mainData['date_new_ranks'] = $this->_helper->modelLoad('WorldsNewranks')->getUpdDate($this->idW);
		$this->view->mainData['date_dshelp'] = $this->_helper->modelLoad('WorldsDshelp')->getUpdDate($this->idW);
		$this->view->mainData['date_game_parse'] = $this->_helper->modelLoad('WorldsGameParse')->getUpdDate($this->idW);

		//параметры
		$this->view->propData = $this->_helper->modelLoad('WorldsProperty')->getProp($this->idW);

		$this->view->input = $this->_helper->modelLoad('PlayersInput')->getInputWorld($this->idW, 20);
		$this->view->output = $this->_helper->modelLoad('PlayersOutput')->getOutputWorld($this->idW, 20);

		$this->view->transSots = $this->_helper->modelLoad('PlayersTransSots')->getTransByWorld($this->idW, 10);
		$this->view->transOthers = $this->_helper->modelLoad('PlayersTransOthers')->getTransByWorld($this->idW, 10);

		$this->view->maxRankDelts = $this->_helper->modelLoad('MaxDeltaRankOld')->getDeltsByWorld($this->idW, 10);
		$this->view->maxBoDelts = $this->_helper->modelLoad('MaxDeltaBo')->getDeltsByWorld($this->idW, 10);
	}

	/*
	 * История изменений параметров игроков мира по дням
	 * @TODO DateTime php >= 5.3
	 */
	public function historyAction()
	{
		$this->view->helpLink = $this->view->url( array('id'=>'world_history'), 'helpView', true );

		$this->view->curDate = $selectDate = $this->_getParam('date');

		$this->view->keywords = "{$this->view->nameWorld}, Мир, История, Изменения";
		$this->view->description = "Мир {$this->view->nameWorld}, изменения за {$selectDate}";
		$this->view->headTitle("История изменений за {$selectDate}");

		$this->view->minDate = $minDate = $this->_helper->modelLoad('StatWorlds')->getMinStatDate($this->idW);
		$this->view->maxDate = $maxDate = date('d-m-Y');

		$curDate = strtotime($selectDate);

		//слишком старая дата
		if( $curDate < strtotime($minDate) )
		{
			$this->_helper->redirector->setCode(301);
			$this->_helper->redirector->gotoRouteAndExit(array( 'idW' => $this->idW, 'date' => $minDate), 'worldHistory', true);
		}

		//дата из будущего
		if( $curDate > strtotime($maxDate) )
			$this->_helper->redirector->gotoRouteAndExit(array('idW' => $this->idW), 'worldHistory', true);

		//есть ли куда двигаться в прошлое?
		if( strtotime('-1 day',$curDate) >= strtotime($minDate) )
			$this->view->prevDate = date('d-m-Y', strtotime('-1 day',$curDate));

		//можно ли двигаться в будущее?
		if( strtotime('+1 day',$curDate) <= strtotime($maxDate) )
			$this->view->nextDate = date('d-m-Y', strtotime('+1 day',$curDate));

		$conf = $this->getFrontController()->getParam('bootstrap')->getOption('limits');

		$this->view->limit = $limit = (!$this->_helper->checkAccess('others','world_history_unlimit')) ? $conf['history'] : null;

		$this->view->input = $this->_helper->modelLoad('PlayersInput')->getInputWorld($this->idW, $limit, $selectDate, false);
		$this->view->output = $this->_helper->modelLoad('PlayersOutput')->getOutputWorld($this->idW, $limit, $selectDate, false);

		$this->view->transSots = $this->_helper->modelLoad('PlayersTransSots')->getTransByWorld($this->idW, $limit, $selectDate, false);
		$this->view->transOthers = $this->_helper->modelLoad('PlayersTransOthers')->getTransByWorld($this->idW, $limit, $selectDate, false);
		$this->view->maxRankDelts = $this->_helper->modelLoad('MaxDeltaRankOld')->getDeltsByWorld($this->idW, $limit, $selectDate, false);
		$this->view->maxBoDelts = $this->_helper->modelLoad('MaxDeltaBo')->getDeltsByWorld($this->idW, $limit, $selectDate, false);
	}


	/*
	 * вывод статистики по миру
	 */
	public function statAction()
	{
		$this->view->helpLink = $this->view->url( array('id'=>'graph'), 'helpView', true );

		$this->view->headTitle("Статистика");
		$this->view->linkCanonical($this->view->url(array('idW' => $this->idW), 'worldStat', true));
		$this->view->keywords = "{$this->view->nameWorld}, Мир, Статистика";
		$this->view->description = "Мир {$this->view->nameWorld}. Статистика";

		$this->view->extendedGateStatus = $this->_helper->modelLoad('WorldsGameParse')->statusAvaliable( $this->idW );
	}

	/*
	 * листинг игроков мира
	 */
	public function playersAction()
	{
		$this->view->helpLink = $this->view->url( array('id'=>'lists'), 'helpView', true );
		$page = (int) $this->_getParam('page', 1);
		$this->view->sort = $sort = $this->_getParam('sort');
		$limit = (int)$this->_getParam('count');

		$count = $this->_helper->modelLoad('WorldsProperty')->getPlayersCount($this->idW);
		$this->view->paginator = $paginator = $this->_helper->modelLoad('Players')->listWorldPlayers(
				$this->idW, $page, $count, $sort, $limit );

		$page = $paginator->getCurrentPageNumber();

		$this->view->countPerPage = $paginator->getItemCountPerPage();

		$this->view->numbered = ($page - 1) * $paginator->getItemCountPerPage() + 1;

		$this->view->keywords = "{$this->view->nameWorld}, Мир, Игроки";
		$this->view->description = "Мир {$this->view->nameWorld}, список игроков (страница {$page})";
		$this->view->headTitle("Список игроков, страница {$page}");
	}

	/*
	 * листинг алов мира
	 */
	public function alliancesAction()
	{
		$this->view->helpLink = $this->view->url( array('id'=>'lists'), 'helpView', true );

		$page = (int) $this->_getParam('page', 1);
		$this->view->sort = $sort = $this->_getParam('sort');

		$count = $this->_helper->modelLoad('WorldsProperty')->getAllianceCount($this->idW);
		$this->view->paginator = $paginator = $this->_helper->modelLoad('Alliances')->listWorldAlliances(
				$this->idW, $page, $count, $sort, (int)$this->_getParam('count'));

		$page = $paginator->getCurrentPageNumber();

		$this->view->countPerPage = $paginator->getItemCountPerPage();
		$this->view->numbered = ($page - 1) * $paginator->getItemCountPerPage() + 1;

		$this->view->keywords = "{$this->view->nameWorld}, Мир, Альянсы";
		$this->view->description = "Мир {$this->view->nameWorld}, список альянсов (страница {$page})";
		$this->view->headTitle("Список альянсов, страница {$page}");
	}

	/*
	 * форма поиска и расширенного поиска + запоминалка ссылки
	 */
	public function searchAction()
	{
		$this->view->helpLink = $this->view->url( array('id'=>'full_search'), 'helpView', true );
		$this->view->rubberPage = true;

		$this->view->keywords = "{$this->view->nameWorld}, Мир, Игроки, Поиск, Кормушки";
		$this->view->description = "Мир {$this->view->nameWorld}, поиск игроков и кормушек по миру";
		$this->view->headTitle("Поиск игроков");
		$this->view->linkCanonical($this->view->url(array('idW' => $this->idW), 'worldSearch', true));

		$this->view->searchProp = new stdClass();

		//получаем максимальные значения критериев (для слайдера)
		$maxParams = array();
		$maxParams = $this->_helper->modelLoad('Players')->getMaxRanks( $this->idW );
		$maxParams['compl'] = $this->_helper->modelLoad('WorldsProperty')->getGreatCompl( $this->idW );
		$this->view->maxParams = $maxParams;

		$conf = $this->getFrontController()->getParam('bootstrap')->getOption('limits');
		$this->view->limitFast = ($this->_helper->checkAccess('others','fast_search_limit_x2')) ? 2 * $conf['fastSearch'] : $conf['fastSearch'];

		//получаем id-name доступных рас
		$this->view->rases = $rases = $this->_helper->modelLoad('Rases')->getRasesForSearch();

		//получаем id-name альянсов для фильтра
		$this->view->filterAlliances = $this->_helper->modelLoad('Alliances')->getFilterAlliance( $this->idW, 20 );

		//доступность расширенного статуса ворот и према
		$this->view->extendedGateStatus = $this->_helper->modelLoad('WorldsGameParse')->statusAvaliable( $this->idW );

		if( $this->_request->isPost() )
		{
			$searchProp = $this->_parseSearchForm();
		}elseif( $this->_getParam('save') !== 'new' ){
			$savedProps = $this->_helper->modelLoad('SearchProps')->getByUid($this->_getParam('save'));
			$save = ( !is_null($savedProps) ) ? @unserialize($savedProps->prop) : false;
			if( $save === false )
			{
				//@TODO ошибку в логи
				$this->view->error = 'Ваша ссылка испортилась =( Надо поиск повторить и новую ссылку сохранить.';
			}else{
				$searchProp = $save;
			}
		}

		//настройки откуда то взялись
		if( isset($searchProp) )
		{
			//сворачиваем настройки в вёрстке
			$this->view->post = true;

			//чистим настройки от ничего не значащих полей
			$searchProp = $this->_helper->modelLoad('Players')->_prepareSearchProp($searchProp, $maxParams, $rases);

			//валидируем объект настроек
			if( $this->_helper->modelLoad('Players')->_validateFullSearchProp( $searchProp, $rases ) === false )
			{
				//@TODO ошибку в логи
				$this->view->error = 'Некоторые поля заполненны ошибочно - повторите поиск или сообщите разработчикам';
			}else{
				//лимит игроков
				$this->view->limitFull = $limit = ($this->_helper->checkAccess('others','full_search_limit_x5'))
						? 5 * $conf['fullSearch'] : $conf['fullSearch'];

				//сортировка
				$this->view->sort = $sort = $this->_getParam('sort');

				//выполняем поиск
				$this->view->players = $this->_helper->modelLoad('Players')->fullSearch( $this->idW, $searchProp, $sort, $limit );

				//передаём настройки для построения формы
				$this->view->searchProp = $searchProp;

				//запоминаем настройки в таблице
				$this->view->saveLink = $this->_helper->modelLoad('SearchProps')->insertOrUpdate($searchProp);

				//ссылка для запоминания в автопоиске игрока
				if( $this->_helper->checkAccess('autosearch','add') )
				{
					$user = Zend_Auth::getInstance()->getStorage()->read();

					//составим хеш для хранения в автопоиске
					$tmp = $searchProp;
					$tmp->sort = $sort;
					$this->view->autoSearchProp = urlencode(serialize($tmp));

					//получим записи
					$this->view->autoSearchNames = $this->_helper->modelLoad('UsersSearch')->getUserList( $user->id, $this->idW );
				}

			}
		}

	}


	/*
	 * вывод карты мира
	 */
	public function mapAction()
	{
		$this->view->helpLink = $this->view->url( array('id'=>'world_map'), 'helpView', true );

		$this->view->keywords = "{$this->view->nameWorld}, Мир, Карта, Кольца, Карта колец";
		$this->view->description = "Мир {$this->view->nameWorld}, интерактивная карта колец";
		$this->view->headTitle("Карта колец");
		$this->view->linkCanonical($this->view->url(array('idW' => $this->idW), 'worldMap', true));

		$avalRings = array();
		$rings = $this->_helper->modelLoad('Rases')->getRings();
		foreach( $rings as $num => $ring ){
			$maxCompl = $this->_helper->modelLoad('WorldsProperty')->getMaxComple($this->idW, $num);
			if( $maxCompl > 0 ){
				$avalRings[] = array('compl' => $maxCompl, 'id' => $num, 'name' => $ring);
			}
		}
		$this->view->rings = $avalRings;

		//получаем значения раскрасски по умолчанию
		$this->view->ranks = Zend_Controller_Front::getInstance()->getParam('bootstrap')->getOption('map');
	}



	/*
	 * парсим форму поиска в объект настроек (без валидации)
	 * @return stdClass of saved preferences search
	 */
	protected function _parseSearchForm()
	{
		$saveProp = new stdClass();

		$saveProp->gate = $this->_request->getPost('gate');
		$saveProp->ring = $this->_request->getPost('ring');
		$saveProp->liga = $this->_request->getPost('liga');
		if( is_array($saveProp->liga) )
			$saveProp->liga = array_unique($saveProp->liga);

		$saveProp->rase = $this->_request->getPost('rase');
		if( is_array($saveProp->rase) )
			$saveProp->rase = array_unique($saveProp->rase);

		$saveProp->alliance = $this->_request->getPost('alliance');
		$saveProp->filterAlliance = $this->_request->getPost('filter_alliance');
		if( is_array($saveProp->filterAlliance) )
			$saveProp->filterAlliance = array_unique($saveProp->filterAlliance);
		$saveProp->filterAllianceMod = $this->_request->getPost('filter_alliance_mod');

		$saveProp->complMin = $this->_request->getPost('compl_min');
		$saveProp->complMax = $this->_request->getPost('compl_max');

		$saveProp->rankoldMin  = $this->_request->getPost('rankold_min');
		$saveProp->rankoldMax  = $this->_request->getPost('rankold_max');

		$saveProp->ranknewMin  = $this->_request->getPost('ranknew_min');
		$saveProp->ranknewMax  = $this->_request->getPost('ranknew_max');

		$saveProp->boMin    = $this->_request->getPost('bo_min');
		$saveProp->boMax    = $this->_request->getPost('bo_max');

		$saveProp->raMin    = $this->_request->getPost('ra_min');
		$saveProp->raMax    = $this->_request->getPost('ra_max');

		$saveProp->nraMin    = $this->_request->getPost('nra_min');
		$saveProp->nraMax    = $this->_request->getPost('nra_max');

		$saveProp->levelMin = $this->_request->getPost('level_min');
		$saveProp->levelMax = $this->_request->getPost('level_max');

		$saveProp->archMin  = $this->_request->getPost('arch_min');
		$saveProp->archMax  = $this->_request->getPost('arch_max');

		$saveProp->buildMin = $this->_request->getPost('build_min');
		$saveProp->buildMax = $this->_request->getPost('build_max');

		$saveProp->scienMin = $this->_request->getPost('scien_min');
		$saveProp->scienMax = $this->_request->getPost('scien_max');

		$saveProp->onlyGateAvaliable = $this->_request->getPost('onlyGateAvaliable');
		$saveProp->premium = $this->_request->getPost('premium');

		return $saveProp;
	}

}
