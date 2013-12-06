<?php
/*
 * сборная солянка из экшенов для крона
 */
class CliController extends Zend_Controller_Action
{
	private
		$_dshelpMap = null,
		$_onlineMap = null,
		$_ranksMap = null,
		$_log = null,
		$_myCSV = null,
		$_upCSV = null,
		$_type = ''; //тип лога, устанавливается каждым экшеном

	public function init()
	{
		if ( APPLICATION_ENV !== 'cli' )
			throw new Exception('Access denied');

		$this->_helper->layout()->disableLayout();
		$this->_helper->viewRenderer->setNoRender();

		$this->_log = new App_Model_Cronlog();
	}

	/*
	 * пишет логи куда надо
	 */
	public function __destruct()
	{
		if( !empty($this->_type) )
			echo $this->_log->save($this->_type);
	}

	/*
	 * сборщик мусора
	 */
	public function scavengerAction()
	{
		$this->_type = 'scavenger';

		$conf = $this->getFrontController()->getParam('bootstrap')->getOption('scav');

		$db = $this->getInvokeArg('bootstrap')->getPluginResource('db')->getDbAdapter();

		$db->beginTransaction(); //СТАРТ ТРАНЗАКЦИИ
		try{
			//удаляем старые логи антибрута
			$count = $this->_helper->modelLoad('Antibrut')->clearOld( $conf['antibrut'] );
			$this->_log->add(sprintf('Удалено %d логов антибрута', $count), true);

			//удаляем старые логи крона
			$count = $this->_helper->modelLoad('CronLogs')->clearOld( $conf['cronlog'] );
			$this->_log->add(sprintf('Удалено %d логов крона', $count), true);

			//удаляем старые сохранённые поиски
			$count = $this->_helper->modelLoad('SearchProps')->clearOld( $conf['search_tinyurl'] );
			$this->_log->add(sprintf('Удалено %d старых настроек поиска', $count), true);

			//удаляем старую статистику онлайна
			$count = $this->_helper->modelLoad('StatOnline')->clearOld( $conf['onlinestat'] );
			$this->_log->add(sprintf('Удалено %d записей статистики онлайна', $count), true);

			//удаляем старую статистику миров
			$count = $this->_helper->modelLoad('StatWorlds')->clearOld( $conf['worldstat'] );
			$this->_log->add(sprintf('Удалено %d записей статистики миров', $count), true);

			//удаляем старую статистику альянсов
			$count = $this->_helper->modelLoad('StatAlliances')->clearOld( $conf['worldstat'] );
			$this->_log->add(sprintf('Удалено %d записей статистики альянсов', $count), true);

			//удаляем переезды-переходы
			$countA = $this->_helper->modelLoad('PlayersTransAlliance')->clearOld( $conf['worldstat'] );
			$countC = $this->_helper->modelLoad('PlayersTransColony')->clearOld( $conf['worldstat'] );
			$countD = $this->_helper->modelLoad('PlayersTransDom')->clearOld( $conf['worldstat'] );
			$countG = $this->_helper->modelLoad('PlayersTransGate')->clearOld( $conf['worldstat'] );
			$countL = $this->_helper->modelLoad('PlayersTransLigue')->clearOld( $conf['worldstat'] );
			$this->_log->add(sprintf('Удалено %d/%d/%d/%d/%d записей переездов игроков (дом/мельс/ал/ворота/лиги)',$countD, $countC, $countA, $countG, $countL), true);

			//удаляем макс дельты игроков
			$countR = $this->_helper->modelLoad('MaxDeltaRankOld')->clearOld($conf['worldstat']);
			$countB = $this->_helper->modelLoad('MaxDeltaBo')->clearOld($conf['worldstat']);
			$this->_log->add(sprintf('Удалено %d/%d макс дельт (рейтинг/БО)', $countR, $countB), true);

			//удаляем пришли/ушли по игрокам
			$countI = $this->_helper->modelLoad('PlayersInput')->clearOld($conf['worldstat']);
			$countO = $this->_helper->modelLoad('PlayersOutput')->clearOld($conf['worldstat']);
			$this->_log->add(sprintf('Удалено %d/%d пришли/ушли', $countI, $countO), true);

			//удаляем старую статистику игроков
			$countRo = $this->_helper->modelLoad('StatRankOld')->clearOld( $conf['playerstat'] );
			$countRn = $this->_helper->modelLoad('StatRankNew')->clearOld( $conf['playerstat'] );
			$countBo = $this->_helper->modelLoad('StatBo')->clearOld( $conf['playerstat'] );
			$countM  = $this->_helper->modelLoad('StatMesto')->clearOld( $conf['playerstat'] );
			$countL  = $this->_helper->modelLoad('StatLevel')->clearOld( $conf['playerstat'] );
			$countRa = $this->_helper->modelLoad('StatRa')->clearOld( $conf['playerstat'] );
			$countNr = $this->_helper->modelLoad('StatNra')->clearOld( $conf['playerstat'] );
			$countA  = $this->_helper->modelLoad('StatArch')->clearOld( $conf['playerstat'] );
			$countB  = $this->_helper->modelLoad('StatBuild')->clearOld( $conf['playerstat'] );
			$countS  = $this->_helper->modelLoad('StatScien')->clearOld( $conf['playerstat'] );
			$this->_log->add(sprintf(
					'Удалено %d/%d/%d/%d/%d/%d/%d/%d/%d/%d записей статистики игроков',
					$countRo,$countRn,$countBo,$countM,$countL,$countRa,$countNr,$countA,$countB,$countS),
					true);

			$db->commit();
		}catch(Exception $e){
			$db->rollBack();
			$this->_log->add('<b>Транзакция отменена</b>');
			$this->_log->add($e->getMessage());
			$this->_log->setResultError();
			return;
		}


		//оптимизация таблиц
		$tables = $db->listTables();
		foreach( $tables as $tableName )
		{
			$db->query('OPTIMIZE TABLE '. $db->quoteTableAs($tableName) );
			$this->_log->add(sprintf('%s optimized', $tableName), true);
		}

		//очистка локов крона
		$db->query('TRUNCATE TABLE `cron_lock`');
		$this->_log->add('cron_lock truncated');


		//удаляем архивы csv
		$pathGame = realpath($this->getFrontController()->getParam('bootstrap')->getOption('csv_game_archive_path'));
		$pathOur = realpath($this->getFrontController()->getParam('bootstrap')->getOption('csv_our_archive_path'));
		if( $pathGame !== false )
		{
			$output = array();
			exec(sprintf("find %s -mtime +%d -exec rm -f {} \;", $pathGame, $conf['csv_archive'] ), $output);
			$this->_log->add('старые архивы игровых csv удалены', true);
		}
		if( $pathOur !== false )
		{
			$output = array();
			exec(sprintf("find %s -mtime +%d -exec rm -f {} \;", $pathOur, $conf['csv_archive'] ), $output);
			$this->_log->add('старые архивы наших csv удалены', true);
		}

		$this->_log->setResultSuccess();
	}


	/*
	 * сборщик статистики по онлайну
	 */
	public function onlinestatAction()
	{
		$this->_type = 'onlineStatus';

		$this->_onlineMap = new App_Model_Online();

		$props = $this->_helper->modelLoad('GameVersions')->getAllForStat();

		if(count($props) === 0)
		{
			$this->_log->add('Версий игры для статистики онлайна не найдено');
			$this->_log->setResultNone();
			exit();
		}

		$flagWarn = false;

		foreach($props as $prop)
		{
			$count = $this->_onlineMap->getCurrentOnline($prop['url']);

			if($count === false)
			{
				$this->_log->add(sprintf('Ошибка CURL - %s',$prop['url']));
				$this->_log->add($this->_onlineMap->getErrors());
				$flagWarn = true;
			}else{
				$this->_helper->modelLoad('StatOnline')->addStat($prop['id'], $count);
				$this->_log->add(sprintf('Версия %d, онлайн %d', $prop['id'], $count));
			}
		}

		$this->getFrontController()
				->getParam('bootstrap')
				->getResource('cachemanager')
				->getCache('up')
				->clean('matchingTag', array('onlinestat'));

		if($flagWarn)
			$this->_log->setResultWarn();
		else
			$this->_log->setResultSuccess();
	}


	/*
	 * обновлялка собственных csv
	 */
	public function csvAction()
	{
		$this->_type = 'csv';
		$flagWarn = false;

		//получаем все живые миры
		$worlds = $this->_helper->modelLoad('Worlds')->listing()->toArray();

		//обновлять нечего
		if( count($worlds) === 0 )
		{
			$this->_log->add('Миров для генерации csv не найдено');
			$this->_log->setResultNone();
			exit();
		}

		$prop = $this->getFrontController()->getParam('bootstrap')->getOption('csv');
		$this->_myCSV = new App_Model_MyCSV(
				$prop['pagerlimit'],
				$prop['gziplevel'],
				$this->getFrontController()->getParam('bootstrap')->getOption('csv_path'),
				$this->getFrontController()->getParam('bootstrap')->getOption('csv_our_archive_path'));

		foreach ($worlds as $world)
		{
			$this->_log->add(sprintf('Мир <b>%s</b>',$world['name']));
			$res = $this->_myCSV->createMain( $this->_helper->modelLoad('Players'), $world['id'], $world['name'] );
			if($res !== true){
				$this->_log->add('архив не создан');
				$flagWarn = true;
			}else{
				$this->_log->add('csv обновлены');
			}
		}

		if($flagWarn)
			$this->_log->setResultWarn();
		else
			$this->_log->setResultSuccess();
	}

	/**
	 * обновление расширенных статусов ворот
	 */
	public function gateAction()
	{
		$this->_type = 'gate';

		$prop = $this->getFrontController()->getParam('bootstrap')->getOption('cronupd');
		$clientProp = $this->getFrontController()->getParam('bootstrap')->getOption('game_client');

		//пробуем получить мир для обновения
		$worldProp = $this->_helper->modelLoad('WorldsGameParse')->getWorldForParse($prop['gate']);
		if( is_null($worldProp) )
		{
			$this->_log->add('Миров для обновления не найдено');
			$this->_log->setResultNone();
			exit();
		}

		//проверяем локи
		$lockProp = $this->getFrontController()->getParam('bootstrap')->getOption('lock_boost');
		$curThreadCount = $this->_helper->modelLoad('CronLock')->getCurrentCounter('gate');
		$this->_log->add(sprintf('Нашли %d текущих скриптов (%d лимит)', $curThreadCount, $lockProp['gate']));
		if( $curThreadCount >= $lockProp['gate'] )
		{
			$this->_log->add('Превышен лимит локов');
			$this->_log->setResultWarn();
			exit();
		}
		$this->_helper->modelLoad('CronLock')->incCounter('gate');

		//обновляем время проверки
		$this->_helper->modelLoad('WorldsGameParse')->updCheck($worldProp['id_world']);

		//различные данные по миру
		$worldData = $this->_helper->modelLoad('Worlds')->getData($worldProp['id_world']);
		$versionData = $this->_helper->modelLoad('GameVersions')->getData($worldData['id_version']);
		$this->_log->add(sprintf('Мир <b>%s</b>', $worldData['name']));

		//взяли комплексы по кольцам для обновления
		$compls = $this->_helper->modelLoad('Players')->getUsedCompls($worldProp['id_world']);
		//shuffle($compls);
		$this->_log->add(sprintf('нашли %d сочетаний компл-кольцо', count($compls)));

		$countUpdCompls = 0; // количество обработанных комплов
		$countUpd = 0; // количество обновлённых игроков
		$countWarn = 0; // количество варнингов (не смогли распарсить соту)
		$flagFail = false;
		$flagWarn = false;

		$db = $this->getInvokeArg('bootstrap')->getPluginResource('db')->getDbAdapter();
		$gameClient = new App_Model_GameClient($versionData['game_url'], $this->_log);

		//логинимся
		$this->_log->add('первичный вход в мир');
		$res = $gameClient->doEnter($worldProp['login'], $worldProp['password']);
		if( $res !== true )
		{
			$this->_log->add('не смогли войти в мир');
			$flagFail = true;
		}else{
			$loginTryLimit = $clientProp['relogin_try_limit'];
			$currentComplNum = 0;
			$countCompls = count($compls);
			while( $currentComplNum < $countCompls )
			{
				$compl = $compls[$currentComplNum];

				//грузим компл
				$this->_log->add(sprintf('грузим компл %d - %d', $compl['ring'], $compl['compl']));
				$res = $gameClient->viewCompl($compl['ring'], $compl['compl']);
				if( $res !== true )
				{
					$this->_log->add('не смогли получить данные о комплексе - релогинимся');
					$this->_log->add(sprintf('осталось попыток релогина %d', $loginTryLimit));

					//релогинимся, если есть лимит
					$connectedFlag = false;
					while($loginTryLimit > 0 && $connectedFlag === false)
					{
						$loginTryLimit--;
						$connectedFlag = $gameClient->doEnter($worldProp['login'], $worldProp['password'], true);
						$this->_log->add(sprintf('осталось попыток релогина %d', $loginTryLimit));
					}

					if( $connectedFlag === false )
					{
						$flagFail = true;
						$this->_log->add('релогин не сработал (или не осталось лимитов)');
						break;
					}else{
						$this->_log->add('релогин сработал - повторим последний комплекс');
						continue;
					}
				}

				$currentComplNum++; //чтобы ни случилось переходим к следующему комплу
				$countUpdCompls++;

				//парсим ответ
				$res = $gameClient->parseComplData();
				if($res === false)
				{
					$this->_log->add('не смогли распарсить ответ');
					$flagWarn = true;
					$countWarn++;
					continue;
				}
				$this->_log->add(sprintf('нашли %d сот для обновления', count($res)));

				//обновляем игроков в транзакции
				$db->beginTransaction(); //СТАРТ ТРАНЗАКЦИИ
				try{
					foreach($res as $player){
						$updateRes = $this->_helper->modelLoad('Players')->updateGateStatuses(
								$worldProp['id_world'],
								$player->nik,
								$player->shield,
								$player->newbee,
								$player->ban,
								$player->premium);

						$countUpd += $updateRes;

						if($updateRes === 1)
							$this->_log->add(sprintf('Игрок %s обновлён ("%s", "%s", "%s", "%s")',$player->nik,$player->shield,$player->newbee,$player->ban,$player->premium));
						else
							$this->_log->add(sprintf('Игрок %s релевантен ("%s", "%s", "%s", "%s")',$player->nik,$player->shield,$player->newbee,$player->ban,$player->premium));
					}
					$db->commit();
				}catch(Exception $e){
					$db->rollBack();
					$this->_log->add('<b>Транзакция отменена</b>');
					$this->_log->add($e->getMessage());
					$flagFail = true;
				}
			}
		}

		if($countUpd > 0)
		{
			$this->_updateWorldParams($worldProp['id_world']);
			$this->getFrontController()
					->getParam('bootstrap')
					->getResource('cachemanager')
					->getCache('up')
					->clean('matchingTag', array('gate'));
		}

		$this->_helper->modelLoad('CronLock')->decCounter('gate');
		$this->_helper->modelLoad('WorldsGameParse')->updCheck($worldProp['id_world']);

		$this->_log->add(sprintf('Комплексов выбрано %d;Комплов загружено %d; Игроков обновлено %d; Ошибок парсинга сот %d.',
				count($compls),
				$countUpdCompls,
				$countUpd,
				$countWarn));

		if($flagFail)
			$this->_log->setResultError();
		elseif($flagWarn)
			$this->_log->setResultWarn();
		else
			$this->_log->setResultSuccess();
	}


	/**
	 * обновление РА игроков одого мира
	 */
	public function dshelpraAction()
	{
		$this->_type = 'dshelpRA';

		$flagFail = false;
		$flagWarn = false;

		$prop = $this->getFrontController()->getParam('bootstrap')->getOption('cronupd');
		$curlProp = $this->getFrontController()->getParam('bootstrap')->getOption('curl');

		//пробуем получить мир для обновения
		$worldProp = $this->_helper->modelLoad('WorldsDshelp')->getOldRaWorld($prop['ra']);
		if( is_null($worldProp) )
		{
			$this->_log->add('Миров для обновления не найдено');
			$this->_log->setResultNone();
			exit();
		}

		//проверяем локи
		$lockProp = $this->getFrontController()->getParam('bootstrap')->getOption('lock_boost');
		$curThreadCount = $this->_helper->modelLoad('CronLock')->getCurrentCounter('dshelp');
		$this->_log->add(sprintf('Нашли %d текущих скриптов (%d лимит)', $curThreadCount, $lockProp['ra']));
		if( $curThreadCount >= $lockProp['ra'] )
		{
			$this->_log->add('Превышен лимит локов');
			$this->_log->setResultWarn();
			exit();
		}
		$this->_helper->modelLoad('CronLock')->incCounter('dshelp');

		//обновляем время проверки
		$this->_helper->modelLoad('WorldsDshelp')->updCheck($worldProp['id_world']);

		//различные данные по миру
		$worldData = $this->_helper->modelLoad('Worlds')->getData($worldProp['id_world']);

		$this->_log->add(sprintf('Мир <b>%s</b>', $worldData['name']));

		$this->_dshelpMap = new App_Model_Dshelp( $worldProp['name'] );
		$countFind = 0; //количество найденных строк игроков
		$countParse = 0; // количество отпарсеных игроков
		$countUpd = 0; // количество обновлённых игроков
		$countFail = 0; //количество ошибок загрузки страницы

		$db = $this->getInvokeArg('bootstrap')->getPluginResource('db')->getDbAdapter();

		//цикл по страницам, пока оные не кончатся (max = 200 pages)
		for($i=0;$i<App_Model_Abstract_RemoteRanks::MAX_PAGES;$i++)
		{
			$this->_log->add("страница <b>{$i}</b>");

			//получаем исходник
			$source = $this->_dshelpMap->getPageSource($i);
			if( $source === false )
			{
				$countFail++;
				$this->_log->add('недоступна');
				if( $countFail >= $curlProp['max_fail_count'])
				{
					$flagFail = true;
					$this->_log->add("прерываем обновление - слишком много ошибок");
					break;
				}
				continue;
			}

			//парсим на строки с игроками
			$table = $this->_dshelpMap->parsePlayersPage($source);
			if( count($table) === 0 )
			{
				$this->_log->add('пуста и неинтересна');
				break;
			}

			$this->_log->add(sprintf('получено %d строк таблички', count($table)));

			$db->beginTransaction(); //СТАРТ ТРАНЗАКЦИИ
			try{
				//построчно парсим на ник-ра
				foreach($table as $str)
				{
					$countFind++;
					$result = $this->_dshelpMap->parsePlayersStr($str);

					if( $result->success === true )
					{
						$countParse++;

						//обновляем игрока в БД
						$res = $this->_helper->modelLoad('Players')->updateRa( $result->data, $worldProp['id_world'] );
						$countUpd += $res;

						if($res === 1)
							$this->_log->add(sprintf('Игрок %s обновлён',$result->data['nik']));
						else
							$this->_log->add(sprintf('Игрок %s релевантен',$result->data['nik']));

					}else{ //ВНИМАНИЕ, ОШИБКИ ПАРСИНГА!
						$this->_log->add("ошибки парсинга");
						$this->_log->add($result->error);
						$flagFail = true;
					}
				}
				$db->commit();
				//$db->rollBack(); //КОНЕЦ ТРАНЗАКЦИИ
			}catch(Exception $e){
				$db->rollBack();
				$this->_log->add('<b>Транзакция отменена</b>');
				$this->_log->add($e->getMessage());
				$flagFail = true;
			}
		}

		//обновляем параметры альянсов и мира (новые рейтинги то обновились, да?)
		if($countUpd > 0)
		{
			$this->_compareAlliance($worldProp['id_world']);
			$this->_updateWorldParams($worldProp['id_world']);

			$this->getFrontController()
					->getParam('bootstrap')
					->getResource('cachemanager')
					->getCache('up')
					->clean('matchingTag', array('dshelpra'));
		}


		//обновляем время проверки
		$this->_helper->modelLoad('CronLock')->decCounter('dshelp');
		$this->_helper->modelLoad('WorldsDshelp')->updCheck($worldProp['id_world']);
		$this->_log->add(sprintf('Страниц %d; Получено %d; Распарсено %d; Обновлено %d.', $i, $countFind, $countParse, $countUpd));

		$errors = $this->_dshelpMap->getErrors();
		if(count($errors) > 0)
		{
			$this->_log->add('Ошибки cURL');
			$this->_log->add($errors);
			$flagWarn = true;
		}


		if($flagFail)
			$this->_log->setResultError();
		elseif($flagWarn)
			$this->_log->setResultWarn();
		else
			$this->_log->setResultSuccess();
	}


	/*
	 * обновление новых рейтинов игроков одного мира
	 */
	public function newranksAction()
	{
		$this->_type = 'newRanks';

		$flagFail = false;
		$flagWarn = false;

		$propCron = $this->getFrontController()->getParam('bootstrap')->getOption('cronupd');

		//пробуем получить мир для обновения
		$worldProp = $this->_helper->modelLoad('WorldsNewranks')->getOldRanksWorld($propCron['newranks']);

		if( is_null($worldProp) )
		{
			$this->_log->add('Миров для обновления не найдено');
			$this->_log->setResultNone();
			exit();
		}

		//проверяем локи
		$lockProp = $this->getFrontController()->getParam('bootstrap')->getOption('lock_boost');
		$curThreadCount = $this->_helper->modelLoad('CronLock')->getCurrentCounter('newranks');
		$this->_log->add(sprintf('Нашли %d текущих скриптов (%d лимит)', $curThreadCount, $lockProp['newranks']));
		if( $curThreadCount >= $lockProp['newranks'] )
		{
			$this->_log->add('Превышен лимит локов');
			$this->_log->setResultWarn();
			exit();
		}
		$this->_helper->modelLoad('CronLock')->incCounter('newranks');

		//обновляем время проверки
		$this->_helper->modelLoad('WorldsNewranks')->updCheck($worldProp['id_world']);

		//различные данные по миру
		$versionData = $this->_helper->modelLoad('GameVersions')->getData($this->_helper->modelLoad('Worlds')->getVersion($worldProp['id_world']));
		$this->_log->add(sprintf("Мир <b>%s</b>", $this->_helper->modelLoad('Worlds')->getName($worldProp['id_world'])));

		$prop = Zend_Controller_Front::getInstance()->getParam('bootstrap')->getOption('curl');
		$this->_ranksMap = new App_Model_NewRanks(
			"{$versionData['new_ranks_rep']}{$worldProp['url']}",
			$prop['newranks']);

		$countFind = 0; //количество найденных строк игроков
		$countParse = 0; // количество отпарсеных игроков
		$countUpd = 0; // количество обновлённых игроков
		$countFail = 0; //количество ошибок загрузки страницы

		$db = $this->getInvokeArg('bootstrap')->getPluginResource('db')->getDbAdapter();

		//цикл по страницам, пока оные не кончатся (max = 200 pages)
		for($i=1;$i<App_Model_Abstract_RemoteRanks::MAX_PAGES;$i++)
		{
			$this->_log->add("страница <b>{$i}</b>");

			//получаем исходник
			$source = $this->_ranksMap->getPageSource($i);
			if( $source === false )
			{
				$countFail++;
				$this->_log->add("недоступна");

				if( $countFail >= $prop['max_fail_count'])
				{
					$flagFail = true;
					$this->_log->add("прерываем обновление - слишком много ошибок");
					break;
				}

				continue;
			}

			//получаем строки с игроками
			$trs = $this->_ranksMap->parsePlayers($source);
			if( $trs->count() === 0 )
			{
				$this->_log->add("пуста и неинтересна");
				break;
			}
			$this->_log->add(sprintf("найдено %d строк с игроками", $trs->count()));

			$db->beginTransaction(); //СТАРТ ТРАНЗАКЦИИ
			try{
				//построчно парсим на параметры
				foreach($trs as $item)
				{
					$countFind++;
					$result = $this->_ranksMap->parsePlayerStr($item);

					//успешно отпарсили игрока
					if($result->success === true)
					{
						$countParse++;

						//обновляем игрока в БД
						$res = $this->_helper->modelLoad('Players')->updateNewRanks( $result->data, $worldProp['id_world'] );
						$countUpd += $res;

						if($res === 1)
							$this->_log->add(sprintf('Игрок %s обновлён',$result->data['nik']));
						else
							$this->_log->add(sprintf('Игрок %s релевантен',$result->data['nik']));

					}else{ //ВНИМАНИЕ, ОШИБКИ ПАРСИНГА!
						$this->_log->add("ошибки парсинга");
						$this->_log->add($result);
						$flagFail = true;
					}
				}
				$db->commit();
				//$db->rollBack(); //КОНЕЦ ТРАНЗАКЦИИ
			}catch(Exception $e){
				$db->rollBack();
				$this->_log->add('<b>Транзакция отменена</b>');
				$this->_log->add($e->getMessage());
				$flagFail = true;
			}
		}

		//обновляем параметры альянсов и мира (новые рейтинги то обновились, да?)
		if($countUpd > 0)
		{
			$this->_compareAlliance($worldProp['id_world']);
			$this->_updateWorldParams($worldProp['id_world']);

			$this->getFrontController()
					->getParam('bootstrap')
					->getResource('cachemanager')
					->getCache('up')
					->clean('matchingTag', array('ranks'));
		}


		//обновляем время проверки
		$this->_helper->modelLoad('CronLock')->decCounter('newranks');
		$this->_helper->modelLoad('WorldsNewranks')->updCheck($worldProp['id_world']);
		$this->_log->add(sprintf('Страниц %d; Всего живых игроков: %d; Получено %d; Распарсено %d; Обновлено %d.',
			$i,
			$this->_helper->modelLoad('WorldsProperty')->getPlayersCount($worldProp['id_world']),
			$countFind,
			$countParse,
			$countUpd));

		$errors = $this->_ranksMap->getErrors();
		if(count($errors) > 0)
		{
			$this->_log->add('Ошибки cURL');
			$this->_log->add($errors);
			$flagWarn = true;
		}


		if($flagFail)
			$this->_log->setResultError();
		elseif($flagWarn)
			$this->_log->setResultWarn();
		else
			$this->_log->setResultSuccess();

	}


	/**
	 * обновление старых рейтинов игроков одного мира
	 */
	public function oldranksAction()
	{
		$this->_type = 'oldRanks';

		$flagFail = false;
		$flagWarn = false;

		$propCron = $this->getFrontController()->getParam('bootstrap')->getOption('cronupd');

		//пробуем получить мир для обновения
		$worldProp = $this->_helper->modelLoad('WorldsOldranks')->getOldRanksWorld($propCron['oldranks']);
		if( is_null($worldProp) )
		{
			$this->_log->add('Миров для обновления не найдено');
			$this->_log->setResultNone();
			exit();
		}

		//проверяем локи
		$lockProp = $this->getFrontController()->getParam('bootstrap')->getOption('lock_boost');
		$curThreadCount = $this->_helper->modelLoad('CronLock')->getCurrentCounter('oldranks');
		$this->_log->add(sprintf('Нашли %d текущих скриптов (%d лимит)', $curThreadCount, $lockProp['oldranks']));
		if( $curThreadCount >= $lockProp['oldranks'] )
		{
			$this->_log->add('Превышен лимит локов');
			$this->_log->setResultWarn();
			exit();
		}
		$this->_helper->modelLoad('CronLock')->incCounter('oldranks');

		//обновляем время проверки
		$this->_helper->modelLoad('WorldsOldranks')->updCheck($worldProp['id_world']);

		//различные данные по миру
		$versionData = $this->_helper->modelLoad('GameVersions')->getData($this->_helper->modelLoad('Worlds')->getVersion($worldProp['id_world']));

		$this->_log->add(sprintf("Мир <b>%s</b>", $this->_helper->modelLoad('Worlds')->getName($worldProp['id_world'])));

		$prop = Zend_Controller_Front::getInstance()->getParam('bootstrap')->getOption('curl');
		$this->_ranksMap = new App_Model_OldRanks(
			"{$versionData['old_ranks_rep']}&{$worldProp['url']}",
			$prop['oldranks']);

		$countFind = 0; //количество найденных строк игроков
		$countParse = 0; // количество отпарсеных игроков
		$countUpd = 0; // количество обновлённых игроков
		$countFail = 0; //количество ошибок загрузки страницы

		$db = $this->getInvokeArg('bootstrap')->getPluginResource('db')->getDbAdapter();

		//цикл по страницам, пока оные не кончатся (max = 200 pages)
		for($i=1;$i<App_Model_Abstract_RemoteRanks::MAX_PAGES;$i++)
		{
			$this->_log->add("страница <b>{$i}</b>");

			//получаем исходник
			$source = $this->_ranksMap->getPageSource($i);
			if( $source === false )
			{
				$countFail++;
				$this->_log->add('недоступна');
				if( $countFail >= $prop['max_fail_count'])
				{
					$flagFail = true;
					$this->_log->add("прерываем обновление - слишком много ошибок");
					break;
				}
				continue;
			}

			//получаем строки с игроками
			$trs = $this->_ranksMap->parsePlayers($source);
			if( $trs->count() === 0 )
			{
				$this->_log->add("пуста и неинтересна");
				break;
			}
			$this->_log->add(sprintf("найдено %d строк с игроками", $trs->count()));

			$db->beginTransaction(); //СТАРТ ТРАНЗАКЦИИ
			try{
				//построчно парсим на параметры
				foreach($trs as $item)
				{
					$countFind++;
					$result = $this->_ranksMap->parsePlayerStr($item);

					//успешно отпарсили игрока
					if($result->success === true)
					{
						$countParse++;

						//обновляем игрока в БД
						$res = $this->_helper->modelLoad('Players')->updateOldRanks( $result->data, $worldProp['id_world'] );
						$countUpd += $res;

						if($res === 1)
							$this->_log->add(sprintf('Игрок %s обновлён',$result->data['nik']));
						else
							$this->_log->add(sprintf('Игрок %s релевантен',$result->data['nik']));

					}else{ //ВНИМАНИЕ, ОШИБКИ ПАРСИНГА!
						$this->_log->add("ошибки парсинга");
						$this->_log->add($result);
						$flagFail = true;
					}
				}
				$db->commit();
				//$db->rollBack(); //КОНЕЦ ТРАНЗАКЦИИ
			}catch(Exception $e){
				$db->rollBack();
				$this->_log->add('<b>Транзакция отменена</b>');
				$this->_log->add($e->getMessage());
				$flagFail = true;
			}
		}

		//обновляем параметры альянсов и мира и макс дельты игроков
		if($countUpd > 0)
		{
			//обновляем последние дельты игроков в табличке players (денормализация ради скорости работы списков игроков):
			//дельты кешируются на конфигурируемое количество часов и обнуляются по времени (ради удаления слишком старых дельт из списков)
			$this->_updatePlayersDelts($worldProp['id_world']);

			$this->_compareAlliance($worldProp['id_world']);
			$this->_updateWorldParams($worldProp['id_world']);
			$this->_updateMaxDelts($worldProp['id_world']);

			$this->getFrontController()
				->getParam('bootstrap')
				->getResource('cachemanager')
				->getCache('up')
				->clean('matchingTag', array('ranks'));
		}

		//обновляем время проверки
		$this->_helper->modelLoad('CronLock')->decCounter('oldranks');
		$this->_helper->modelLoad('WorldsOldranks')->updCheck($worldProp['id_world']);
		$this->_log->add(sprintf('Страниц %d; Всего живых игроков в мире: %d; Получено %d; Распарсено %d; Обновлено %d.',
			$i,
			$this->_helper->modelLoad('WorldsProperty')->getPlayersCount($worldProp['id_world']),
			$countFind,
			$countParse,
			$countUpd));

		$errors = $this->_ranksMap->getErrors();
		if(count($errors) > 0)
		{
			$this->_log->add('Ошибки cURL');
			$this->_log->add($errors);
			$flagWarn = true;
		}

		if($flagFail)
			$this->_log->setResultError();
		elseif($flagWarn)
			$this->_log->setResultWarn();
		else
			$this->_log->setResultSuccess();

	}


	/*
	 * Ежедневное обновление
	 * сбор статистики по мирам и альянсам
	 */
	public function dayAction()
	{
		$this->_type = 'day';

		//ищем миры для обновления
		$worlds = $this->_helper->modelLoad('Worlds')->listing()->toArray();

		if(count($worlds) === 0)
		{
			$this->_log->add('Миров для сбора статистики не найдено');
			$this->_log->setResultNone();
			exit();
		}

		$db = $this->getInvokeArg('bootstrap')->getPluginResource('db')->getDbAdapter();
		$errorFlag = false;

		//обновляем каждый мир и его альянсы
		foreach( $worlds as $world )
		{
			$this->_log->add(sprintf('Мир <b>%s</b>', $world['name']));
			$idW = $world['id'];

			$db->beginTransaction(); //СТАРТ ТРАНЗАКЦИИ

			try{
				//добавляем статистику по миру
				$this->_addWorldsStat($idW);

				//добавляем статистику альянсов мира
				$this->_addAllianceStat($idW);

				$db->commit(); //$db->rollBack(); //КОНЕЦ ТРАНЗАКЦИИ
			}catch(Exception $e){
				$db->rollBack();
				$this->_log->add('<b>Транзакция отменена</b>');
				$this->_log->add($e->getMessage());
				$errorFlag = true;
			}
		}

		$this->getFrontController()
				->getParam('bootstrap')
				->getResource('cachemanager')
				->getCache('up')
				->clean('matchingTag', array('day'));

		if($errorFlag)
			$this->_log->setResultError();
		else
			$this->_log->setResultSuccess();
	}

	/*
	 * сбор и добавление статистики по миру
	 */
	private function _addWorldsStat($idW)
	{
		$data = $this->_prepareWorldData($idW, true);
		$this->_helper->modelLoad('StatWorlds')->addStat($idW, $data);
		$this->_log->add('Статистика мира добавлена');
	}

	/*
	 * сбор и добавление статистики альянсов одного мира
	 */
	private function _addAllianceStat($idW)
	{
		$data = $this->_prepareAllianceData($idW);
		$this->_log->add(sprintf('Найдено %d параметров альянсов', count($data)));

		if(count($data) > 0)
		{
			$this->_helper->modelLoad('StatAlliances')->addStat($data);
			$this->_log->add('Статистика добавлена');
		}
	}

	/*
	 * Обновление основных рейтингов игроков одного мира
	 * построчно обрабатывает новые csv
	 * в конце актуализирует альянсы и параметры миров
	 * вся статистика - внутри БД на тригерах
	 * единственное ручное сравнение - при работе с колониями
	 */
	public function upAction()
	{
		$this->_type = 'up';
		$flagWarn = false;

		$prop = $this->getFrontController()->getParam('bootstrap')->getOption('cronupd');

		//получаем мир
		$worldProp = $this->_helper->modelLoad('WorldsCsv')->getOldCsvWorld($prop['up']);
		if( is_null($worldProp) )
		{
			$this->_log->add('Миров для обновления не найдено');
			$this->_log->setResultNone();
			exit();
		}

		//обновляем время проверки
		$this->_helper->modelLoad('WorldsCsv')->updCheck($worldProp['id_world']);

		//различные данные по миру
		$worldData = $this->_helper->modelLoad('Worlds')->getData($worldProp['id_world']);
		$versionData = $this->_helper->modelLoad('GameVersions')->getData($worldData['id_version']);

		$this->_log->add(sprintf("Мир <b>%s</b>", $worldData['name']));

		$this->_upCSV = new App_Model_UpCSV();

		//грузим файл во временную папку
		$res = $this->_upCSV->load( $versionData['main_csv_rep'], $worldProp['url'], $this->getFrontController()->getParam('bootstrap')->getOption('tmp_folder') );
		if($res !== true)
		{
			$this->_log->setResultError();
			$this->_log->add("Ошибка curl {$res}");
			$this->_log->add($this->_upCSV->getInfo());
			exit();
		}

		//изменилось ли что нибудь в файле?
		$hash = $this->_upCSV->getMD5();
		if( $worldProp['hash'] === $hash )
		{
			$this->_log->add("Мир релевантен {$worldProp['hash']}");
			$this->_log->setResultNone();
			exit();
		}

		//обновляем хеш и сохраняем файл для истории
		$res = $this->_upCSV->moveCsvToArchive($worldData['name'], $this->getFrontController()->getParam('bootstrap')->getOption('csv_game_archive_path'));
		if( $res !== true )
		{
			$this->_log->setResultError();
			$this->_log->add("Ошибка сохранения исторических csv");
			exit();
		}

		//декодим файл в память
		$res = $this->_upCSV->decode();
		if( $res !== true )
		{
			$this->_log->setResultError();
			$this->_log->add("Ошибка распаковки файла {$res}");
			exit();
		}

		$source = $this->_upCSV->getData();
		$parser = Mylib_Parser_CSV_Abstract::factory($worldProp['parser'])
				->setRases($this->_helper->modelLoad('Rases')->getAll())
				->setAlliances($this->_helper->modelLoad('Alliances')->getAllByWorld($worldProp['id_world']))
				->setPlayers($this->_helper->modelLoad('Players')->getAllByWorld($worldProp['id_world']));

		//СТАРТ ТРАНЗАКЦИИ (все изменения по игрокам)
		$db = $this->getInvokeArg('bootstrap')->getPluginResource('db')->getDbAdapter();
		$db->beginTransaction();

		try{
			//построчная обработка игроков
			foreach( $source as $str )
			{
				$data = new Mylib_Parser_CSV_Row();
				$parser->setDataContainer($data);

				//парсим строку
				$str = iconv("Windows-1251", "UTF-8", $str);
				if( $parser->parse($str) === false )
				{
					$this->_log->add(sprintf('Ошибка парсинга %s строки %s', $parser->getErr(), $str));
					$flagWarn = true;
					continue;
				}

				$this->_log->add(sprintf('Игрок %s', $data->getParam('nik')));

				//добавляем новый альянс
				if( $data->isNewAlliance() )
				{
					$idA = $this->_helper->modelLoad('Alliances')->add($worldProp['id_world'], $data->getParam('allianceName'));
					$parser->addAlliance($idA, $data->getParam('allianceName'));
					$data->setParam('allianceId', $idA);
					$this->_log->add(sprintf('Добавлен новый альянс %s с id %d', $data->getParam('allianceName'), $idA));
				}

				if( $data->isNewPlayer() )
				{
					//добавляем нового игрока
					$idP = $this->_helper->modelLoad('Players')->add($worldProp['id_world'], $data->exportData());
					$this->_helper->modelLoad('PlayersInput')->add($worldProp['id_world'], $idP);
					$data->setParam('id', $idP);
					$this->_log->add(sprintf('Добавлен как новый с id %d', $idP));
				}else{
					//старых игроков обновляем
					$this->_helper->modelLoad('Players')->upd($data->getParam('id'), $data->exportData());
					$this->_log->add('Параметры обновлены');
				}

				//обновляем колонии
				$this->_compareCols($data);
			}

			$this->_log->add('Игроки обновлены', true);

			//отмечаем удалённых игроков и удаляем их колонии
			$oldPlayers = $parser->getOldPlayersId();
			if(count($oldPlayers)>0)
			{
				$resP = $this->_helper->modelLoad('Players')->del($oldPlayers);
				$resC = $this->_helper->modelLoad('PlayersColony')->delByPlayers($oldPlayers);
				$this->_log->add(sprintf('Необновлённые игроки и их колонии удалены (обработано %d игроков и %d колоний)', $resP, $resC));
			}

			//отмечаем пустые альянсы как удалённые и удаляем их параметры
			$oldAlliances = $parser->getOldAlliancesId();
			if(count($oldAlliances)>0)
			{
				$resA = $this->_helper->modelLoad('Alliances')->del($oldAlliances);
				$resP = $this->_helper->modelLoad('AlliancesProperty')->del($oldAlliances);
				$this->_log->add(sprintf('Пустые альянсы и их параметры удалены (обработано %d альянсов и %d параметров)', $resA, $resP));
			}

			//обновляем параметры альянсов мира
			$this->_compareAlliance($worldProp['id_world']);

			//обновляем параметры мира
			$this->_updateWorldParams($worldProp['id_world']);

			//КОНЕЦ ТРАНЗАКЦИИ
			$db->commit();
			//$db->rollBack();
		}catch(Exception $e){
			$db->rollBack();
			$this->_log->add('<b>Транзакция отменена</b>');
			$this->_log->add($e->getMessage());
			$this->_log->setResultError();
			exit();
		}

		$this->_helper->modelLoad('WorldsCsv')->updHash($worldProp['id_world'], $hash); //обновление хеша в конце ради защиты от прерванной транзакции (ввиду большой конкуренции за игроков каждую минуту)

		$this->_log->add('Начали чистить кеш', true);
		$this->getFrontController()
				->getParam('bootstrap')
				->getResource('cachemanager')
				->getCache('up')
				->clean('matchingTag', array('up'));
		$this->_log->add('Закончили чистить кеш', true);

		if($flagWarn)
			$this->_log->setResultWarn();
		else
			$this->_log->setResultSuccess();
	}

	/*
	 * работа с колониями при основном обновлении
	 */
	private function _compareCols($data)
	{
		$old = $this->_helper->modelLoad('PlayersColony')->getByPlayer($data->getParam('id'));
		$new = ( count($data->getParam('colName')) > 0 )
				? array_combine($data->getParam('colName'), $data->getParam('colAdr')) : array();

		//проверка на изменения
		if( count($old) > 0 )
		{
			if($data->isNewPlayer())
				$this->_log->add('<b>У нового игрока нашлись старые колонии???</b>');

			foreach($old as $oldCol)
			{
				if(isset($new[$oldCol['col_name']])) //старое имя колонии найдено в новых
				{
					$col = $new[$oldCol['col_name']];
					unset($new[$oldCol['col_name']]);
					$res = $this->_helper->modelLoad('PlayersColony')->upd(
							$oldCol['id'],
							$col['compl'],
							$col['sota']);
					if( $res === 1 )
					{
						//запишем переезд
						$this->_helper->modelLoad('PlayersTransColony')->addTransColony(
							$data->getParam('id'),
							$oldCol['compl'],
							$oldCol['sota'],
							$col['compl'],
							$col['sota']);
						$this->_log->add(sprintf('Добавлен переезд колонии %s', $oldCol['col_name']));
					}
				}else{
					//колонию следует удалить
					$res = $this->_helper->modelLoad('PlayersColony')->del($oldCol['id']);
					$this->_helper->modelLoad('PlayersTransColony')->addTransColony(
							$data->getParam('id'),
							$oldCol['compl'],
							$oldCol['sota']);
					$this->_log->add(sprintf('Колония удалена %s', $oldCol['col_name']));
				}
			}
		}

		//оставшиеся новые колонии добавляем
		if( count($new) > 0 )
		{
			foreach($new as $name => $adr)
			{
				$this->_helper->modelLoad('PlayersColony')->add(
						$data->getParam('id'),
						$adr['compl'],
						$adr['sota'],
						$name);
				$this->_log->add(sprintf('Добавлена колония %s',$name));

				//добавляем изменения в статистику для старых игроков
				if( !$data->isNewPlayer() )
				{
					$this->_helper->modelLoad('PlayersTransColony')->addTransColony(
							$data->getParam('id'),
							null,
							null,
							$adr['compl'],
							$adr['sota']);
					$this->_log->add(sprintf('Добавлено приобретение новой колонии %s', $name));
				}
			}
		}
	}

	/**
	 * обновление параметров альянсов
	 * обновляем параметры у всех непустых альянсов и отмечаем их активными
	 * альянсы без параметров отмечать удалёнными необходимо ручками (тут это не делается)
	 */
	private function _compareAlliance($idW)
	{
		$data = $this->_prepareAllianceData($idW);
		$this->_log->add(sprintf('Найдено %d параметров альянсов', count($data)));

		if(count($data) > 0)
		{
			$ids = array();
			$countNew = 0;
			$countUpd = 0;
			//добавляем новые параметры
			foreach($data as $idA => $item)
			{
				$res = $this->_helper->modelLoad('AlliancesProperty')->insertOrUpdate($idA, $item)->rowCount();
				if($res === 1)
					$countNew++;
				elseif($res === 2)
					$countUpd++;
				$ids[] = $idA;
			}
			$this->_log->add(sprintf('Параметры альянсов обновлены: добавлено %d, обновлено %d', $countNew, $countUpd), true);

			//отмечаем все альянсы с параметрами живыми
			$res = $this->_helper->modelLoad('Alliances')->setActive($ids);
			$this->_log->add(sprintf('Отмечено живыми %d альянсов', $res));
		}
	}

	/*
	 * обновление параметров мира
	 */
	private function _updateWorldParams($idW)
	{
		$data = $this->_prepareWorldData($idW, false);
		$res = $this->_helper->modelLoad('WorldsProperty')->insertOrUpdate($idW, $data)->rowCount();
		if($res === 2)
			$this->_log->add('Параметры мира обновлены');
		elseif($res === 1)
			$this->_log->add('Параметры мира добавлены');
		else
			$this->_log->add(sprintf('Параметры мира не изменены (%s)', $res));
	}

	/*
	 * обновление макс дельт по миру
	 */
	private function _updateMaxDelts($idW)
	{
		$prop = $this->getFrontController()->getParam('bootstrap')->getOption('deltaMax');

		//обновляем старые рейтинги
		$del = $this->_helper->modelLoad('MaxDeltaRankOld')->clearToday($idW);
		$this->_log->add(sprintf('Удалено %d старых макс дельт старого рейтинга', $del));

		$data = $this->_helper->modelLoad('StatRankOld')->getMaxDelts($idW, $prop['rank_old']['border'], $prop['rank_old']['limit']);
		$this->_log->add(sprintf('Найдено %d новых макс дельт старого рейтинга', count($data)));

		if(count($data) > 0)
		{
			$this->_helper->modelLoad('MaxDeltaRankOld')->add($idW, $data);
			$this->_log->add('Новые макс дельты старого рейтинга добавлены', true);
		}

		//обновляем БО
		$del = $this->_helper->modelLoad('MaxDeltaBo')->clearToday($idW);
		$this->_log->add(sprintf('Удалено %d старых макс дельт БО', $del));

		$data = $this->_helper->modelLoad('StatBo')->getMaxDelts($idW, $prop['bo']['border'], $prop['bo']['limit'], false);
		$this->_log->add(sprintf('Найдено %d новых макс дельт БО', count($data)));

		if(count($data) > 0)
		{
			$this->_helper->modelLoad('MaxDeltaBo')->add($idW, $data);
			$this->_log->add('Новые макс дельты БО добавлены', true);
		}

	}

	/**
	 * Обновляет дельты в таблице players с учётом кеширования на некоторое количество часов
	 * старые дельты обнуляются
	 * @param int $idW
	 */
	private function _updatePlayersDelts($idW)
	{
		$deltaProp = $this->getFrontController()->getParam('bootstrap')->getOption('scav');

		$this->_log->add('Начинаем обновление дельт игроков', true);
		$updated = $this->_helper->modelLoad('Players')->updateDelts($idW, $deltaProp['player_delts']);
		$this->_log->add(sprintf('Обновлено %d дельт игроков', $updated->rowCount()), true);
	}


	/**
	 * подготовка данных по альянсам для статистики и текущих свойств
	 */
	private function _prepareAllianceData($idW)
	{
		$data = array();
		$main = $this->_helper->modelLoad('Players')->getAllianceParams($idW);
		foreach($main as $row)
		{
			$idA = $row['id_alliance'];
			unset($row['id_alliance']);
			$idR = $row['id_rase'];
			unset($row['id_rase']);

			if(!isset($data[$idA]))
				$data[$idA] = array();

			$index = $this->_helper->modelLoad('Rases')->getRasePrefix($idR);
			$keys = array_keys($row);
			foreach($keys as $key)
				$data[$idA]["{$key}_{$index}"] = $row[$key];
		}

		$cols = $this->_helper->modelLoad('PlayersColony')->getCountByAllianceByRase($idW);
		foreach($cols as $col)
		{
			$index = $this->_helper->modelLoad('Rases')->getRasePrefix($col['id_rase']);
			$data[$col['id_alliance']]["count_colony_{$index}"] = $col['count_colony'];
		}

		return $data;
	}

	/*
	 * подготовка данных по миру для статистики и текущих параметров
	 */
	private function _prepareWorldData($idW, $stat)
	{
		$data = array();
		$data['count_alliance'] = $this->_helper->modelLoad('Alliances')->getCountByWorld($idW);
		$data['count_notavaliable_gate'] = $this->_helper->modelLoad('Players')->getCountNotavaliableGateByWorld($idW);
		$data['count_premium'] = $this->_helper->modelLoad('Players')->getCountPremiumByWorld($idW);

		if( $stat === true )
		{
			$data['input'] = $this->_helper->modelLoad('PlayersInput')->countTodayByWorld($idW);
			$data['output'] = $this->_helper->modelLoad('PlayersOutput')->countTodayByWorld($idW);
		}else{
			$data['compls_mels'] = $this->_helper->modelLoad('PlayersColony')->getMaxCompl($idW);
		}

		$main = $this->_helper->modelLoad('Players')->getMainWorldParams($idW, $stat);
		foreach($main as $row)
		{
			$idR = $row['id_rase'];
			unset($row['id_rase']);

			$index = $this->_helper->modelLoad('Rases')->getRasePrefix($idR);
			$keys = array_keys($row);
			foreach($keys as $key)
				$data["{$key}_{$index}"] = $row[$key];
		}

		$cols = $this->_helper->modelLoad('PlayersColony')->getColonyCountByRases($idW);
		foreach($cols as $col)
		{
			$index = $this->_helper->modelLoad('Rases')->getRasePrefix($col['id_rase']);
			$data["count_colony_{$index}"] = $col['count_colony'];
		}
		return $data;
	}

	/**
	 * Обновляет НРА по мирам отталкиваясь от даты последнего обновления
	 *
	 */
	public function nraAction()
	{
		$this->_type = 'nra';

		$prop = $this->getFrontController()->getParam('bootstrap')->getOption('cronupd');

		//получаем миры
		$worldProp = $this->_helper->modelLoad('WorldsNRA')->getWorldsForUpdate($prop['nra']);
		if( is_null($worldProp) )
		{
			$this->_log->add('Миров для обновления НРА не найдено');
			$this->_log->setResultNone();
			exit();
		}

		//проверяем локи
		$lockProp = $this->getFrontController()->getParam('bootstrap')->getOption('lock_boost');
		$curThreadCount = $this->_helper->modelLoad('CronLock')->getCurrentCounter('nra');
		$this->_log->add(sprintf('Нашли %d текущих скриптов (%d лимит)', $curThreadCount, $lockProp['nra']));
		if( $curThreadCount >= $lockProp['nra'] )
		{
			$this->_log->add('Превышен лимит локов');
			$this->_log->setResultWarn();
			exit();
		}
		$this->_helper->modelLoad('CronLock')->incCounter('nra');

		$idW = $worldProp->id_world;
		$this->_helper->modelLoad('WorldsNRA')->updCheck($idW);

		$this->_log->add(sprintf('Мир <b>%s</b>', $this->_helper->modelLoad('Worlds')->getName($idW)));

		//выбираем всех живых игроков мира
		$players = $this->_helper->modelLoad('Players')->getAllByWorld($idW, true);
		$this->_log->add(sprintf('Найдено %d игроков', count($players)));

		$conf = $this->getFrontController()->getParam('bootstrap')->getOption('nra_weights');
		$countUpd = 0;

		foreach( $players as $player )
		{
			$this->_log->add("обновляем игрока {$player['nik']}");

			$components = array();
			$idP = $player['id'];

			//получаем время последнего изменения сот
			$homeSotaChangeDate = $this->_helper->modelLoad('PlayersTransDom')->getLastChangeDate($idP);
			if( !is_null($homeSotaChangeDate) )
			{
				$components[] = $tmp = $this->_prepareNRAComponent($homeSotaChangeDate, $conf['home_sota_change']);
				$this->_log->add("дата изменения домашки {$homeSotaChangeDate} компонент {$tmp}");
			}

			$colSotaChangeDate = $this->_helper->modelLoad('PlayersTransColony')->getLastNewColonyDate($idP);
			if( !is_null($colSotaChangeDate) )
			{
				$components[] = $tmp = $this->_prepareNRAComponent($colSotaChangeDate, $conf['colony_add']);
				$this->_log->add("дата приобритения колонии {$colSotaChangeDate} компонент {$tmp}");
			}

			//последнее изменение альянса
			//@TODO учитывать отдельно выход из ала и вход в новый (при выходе могли просто отчислить)
			$allianceChangeDate = $this->_helper->modelLoad('PlayersTransAlliance')->getLastChangeDate($idP);
			if( !is_null($allianceChangeDate) )
			{
				$components[] = $tmp = $this->_prepareNRAComponent($allianceChangeDate, $conf['alliance_change']);
				$this->_log->add("дата изменения альянса {$allianceChangeDate} компонент {$tmp}");
			}

			//последнее изменение ворот
			$gateChangeDate = $this->_helper->modelLoad('PlayersTransGate')->getLastChangeDate($idP);
			if( !is_null($gateChangeDate) )
			{
				$components[] = $tmp = $this->_prepareNRAComponent($gateChangeDate, $conf['gate_change']);
				$this->_log->add("дата изменения ворот {$gateChangeDate} компонент {$tmp}");
			}

			//последние изменения в новых рейтингах
			$archChangeDate = $this->_helper->modelLoad('StatArch')->getLastChangeDate($idP);
			if( !is_null($archChangeDate) )
			{
				$components[] = $tmp = $this->_prepareNRAComponent($archChangeDate, $conf['arch_change']);
				$this->_log->add("дата изменения археологии {$archChangeDate} компонент {$tmp}");
			}

			$scienChangeDate = $this->_helper->modelLoad('StatScien')->getLastChangeDate($idP);
			if( !is_null($scienChangeDate) )
			{
				$components[] = $tmp = $this->_prepareNRAComponent($scienChangeDate, $conf['scien_change']);
				$this->_log->add("дата изменения науки {$scienChangeDate} компонент {$tmp}");
			}

			$buildPosChangeDate = $this->_helper->modelLoad('StatBuild')->getLastPossibleChangeDate($idP);
			if( !is_null($buildPosChangeDate) )
			{
				$components[] = $tmp = $this->_prepareNRAComponent($buildPosChangeDate, $conf['build_pos_change']);
				$this->_log->add("дата изменения стройки в плюс {$buildPosChangeDate} компонент {$tmp}");
			}

			$buildNegChangeDate = $this->_helper->modelLoad('StatBuild')->getLastNegativeChangeDate($idP);
			if( !is_null($buildNegChangeDate) )
			{
				$components[] = $tmp = $this->_prepareNRAComponent($buildNegChangeDate, $conf['build_neg_change']);
				$this->_log->add("дата изменения стройки в минус {$buildNegChangeDate} компонент {$tmp}");
			}

			//@TODO добавить постоянный рост рейтинга

			//считаем НРА по формуле с весами
			$sourceNRA = array_sum($components)*100;
			$nra = ($sourceNRA > 200) ? 200.00 : round($sourceNRA, 2);

			//сохраняем игрока
			$res = $this->_helper->modelLoad('Players')->updateNRA($nra, $idP);
			$countUpd += $res;
			$this->_log->add("обновили idP {$idP} сырой НРА {$sourceNRA}, НРА {$nra}");
		}

		//обновляем параметры альянсов и мира, чистим кеш
		if($countUpd > 0)
		{
			$this->_compareAlliance($idW);
			$this->_updateWorldParams($idW);

			$this->getFrontController()
					->getParam('bootstrap')
					->getResource('cachemanager')
					->getCache('up')
					->clean('matchingTag', array('nra'));
		}

		$this->_helper->modelLoad('CronLock')->decCounter('nra');
		$this->_log->setResultSuccess();
	}

	/**
	 * Подготовка компоненты для НРА
	 * @param string $date Дата в формате мускуля
	 * @param float $multiplier Множитель веса (от нуля до единицы)
	 * @param int $periodHours Макс период устаревания данных
	 * @return float Возвращает значение рейтинга (от нуля до единицы) (чем больше - тем активнее парень)
	 */
	protected function _prepareNRAComponent($date, $multiplier, $periodHours = 24)
	{
		$oldDate = strtotime($date);
		if( $oldDate === false )
			return 0;

		$deltaSec = time() - $oldDate;
		$deltaHours = round($deltaSec / 3600);
		if( $deltaHours > $periodHours )
			$deltaHours = $periodHours;

		$coef = 1 - ($deltaHours / $periodHours);

		return ($coef * $multiplier);
	}
}
