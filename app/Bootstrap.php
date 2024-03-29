<?php
class Bootstrap extends Zend_Application_Bootstrap_Bootstrap
{
	protected function _initRoute()
	{
		if ( APPLICATION_ENV == 'cli' ) return false;


		$front = Zend_Controller_Front::getInstance();
		$router = $front->getRouter();

		//отрубаем стандартный роутер
		$router->removeDefaultRoutes();

		//index
		$router->addRoute('staticIndex',
				new Zend_Controller_Router_Route_Static('/',
						array( 'controller' => 'index', 'action' => 'index' )));
		$router->addRoute('staticHelp',
				new Zend_Controller_Router_Route_Static('/help.html',
						array( 'controller' => 'index', 'action' => 'help' )));
		$router->addRoute('helpView',
				new Zend_Controller_Router_Route_Regex('/help.html\#(\w+)',
						array( 'controller' => 'index', 'action' => 'news'),
						array( 'id' => 1), 'help.html#%s' ));
		$router->addRoute('staticAbout',
				new Zend_Controller_Router_Route_Static('/about.html',
						array( 'controller' => 'index', 'action' => 'about' )));
		$router->addRoute('staticContacts',
				new Zend_Controller_Router_Route_Static('/contacts.html',
						array( 'controller' => 'index', 'action' => 'contact' )));
		$router->addRoute('staticBot',
				new Zend_Controller_Router_Route_Static('/bot.html',
						array( 'controller' => 'index', 'action' => 'bot' )));

		//ajax
		$router->addRoute('allAjax',
				new Zend_Controller_Router_Route('/ajax/:action',
						array( 'controller' => 'ajax') ));

		//auth
		$router->addRoute('staticLogin',
				new Zend_Controller_Router_Route_Static('/login.html',
						array( 'controller' => 'auth', 'action' => 'login' )));
		$router->addRoute('staticLogout',
				new Zend_Controller_Router_Route_Static('/logout.html',
						array( 'controller' => 'auth', 'action' => 'logout' )));

		$router->addRoute('staticRegistration',
				new Zend_Controller_Router_Route_Static('/register.html',
						array( 'controller' => 'auth', 'action' => 'register' )));
		$router->addRoute('staticEmailApprove',
				new Zend_Controller_Router_Route_Static('/email-approve.html',
						array( 'controller' => 'auth', 'action' => 'email-approve' )));
		$router->addRoute('staticEmailApproveRetry',
				new Zend_Controller_Router_Route_Static('/email-approve-retry.html',
						array( 'controller' => 'auth', 'action' => 'email-approve-retry' )));
		$router->addRoute('staticPasswordRemember',
				new Zend_Controller_Router_Route_Static('/password-remember.html',
						array( 'controller' => 'auth', 'action' => 'password-remember' )));
		$router->addRoute('staticPasswordRememberActivate',
				new Zend_Controller_Router_Route_Static('/password-remember-activate.html',
						array( 'controller' => 'auth', 'action' => 'password-remember-activate' )));

		//user
		$router->addRoute('userProfile',
				new Zend_Controller_Router_Route_Static('/profile/',
						array( 'controller' => 'user', 'action' => 'profile' )));
		$router->addRoute('userPasswordChange',
				new Zend_Controller_Router_Route_Static('/profile/password-change/',
						array( 'controller' => 'user', 'action' => 'password-change' )));
		$router->addRoute('userHistory',
				new Zend_Controller_Router_Route('/profile/history/',
						array( 'controller' => 'user', 'action' => 'history')));
		$router->addRoute('userAutosearch',
				new Zend_Controller_Router_Route('/profile/autosearch/',
						array( 'controller' => 'user', 'action' => 'autosearch' ) ));
		$router->addRoute('userAutosearchFind',
				new Zend_Controller_Router_Route('/profile/autosearch/:idA',
						array( 'controller' => 'user', 'action' => 'autosearch-find' ),
						array( 'idA' => '\d+' )));

		//moder
		$router->addRoute('moderLogs',
				new Zend_Controller_Router_Route('/logs/',
						array( 'controller' => 'logs', 'action' => 'index' ) ));
		$router->addRoute('moderLogView',
				new Zend_Controller_Router_Route('/logs/view/:idL/',
						array( 'controller' => 'logs', 'action' => 'view' ),
						array( 'idL' => '\d+' )));

		//news
		$router->addRoute('newsIndex',
				new Zend_Controller_Router_Route_Static('/news.html',
						array( 'controller' => 'index', 'action' => 'news' )));
		$router->addRoute('newsRss',
				new Zend_Controller_Router_Route_Static('/news.rss',
						array( 'controller' => 'index', 'action' => 'newsfeed' )));
		$router->addRoute('newsView',
				new Zend_Controller_Router_Route_Regex('/news.html\#item(\d+)',
						array( 'controller' => 'index', 'action' => 'news'),
						array( 'idN' => 1),
						'news.html#item%d'
						));

		//services
		$router->addRoute('onlineStat',
				new Zend_Controller_Router_Route_Static('/services/onlinestat.html',
						array( 'controller' => 'service', 'action' => 'online' )));
		$router->addRoute('globalSearch',
				new Zend_Controller_Router_Route_Static('/services/players/search.html',
						array( 'controller' => 'service', 'action' => 'search' ) ));
		$router->addRoute('staticDev',
				new Zend_Controller_Router_Route_Static('/dev.html',
						array( 'controller' => 'service', 'action' => 'dev' )));
		$router->addRoute('archeologyRank',
				new Zend_Controller_Router_Route_Static('/services/archeology.html',
						array( 'controller' => 'service', 'action' => 'archeology' )));
		$router->addRoute('armyCalc',
				new Zend_Controller_Router_Route_Static('/services/army.html',
						array( 'controller' => 'service', 'action' => 'army' )));
		$router->addRoute('addonPage',
				new Zend_Controller_Router_Route_Static('/services/addon.html',
						array( 'controller' => 'service', 'action' => 'addon-page' )));

		//addon callbacks
		$router->addRoute('addonSearch',
				new Zend_Controller_Router_Route_Static('/addon-api/search.html',
						array( 'controller' => 'addon-api', 'action' => 'search' )));
		$router->addRoute('addonStat',
				new Zend_Controller_Router_Route_Static('/addon-api/stat-add.html',
						array( 'controller' => 'addon-api', 'action' => 'stat-add' )));


		//world main menu
		$router->addRoute('worldIndex',
				new Zend_Controller_Router_Route('/world/:idW/index.html',
						array( 'controller' => 'worlds', 'action' => 'index' ),
						array( 'idW' => '\d+' )));
		$router->addRoute('worldStat',
				new Zend_Controller_Router_Route('/world/:idW/stat.html',
						array( 'controller' => 'worlds', 'action' => 'stat'),
						array( 'idW' => '\d+' )));
		$router->addRoute('worldHistory',
				new Zend_Controller_Router_Route('/world/:idW/history/:date/view.html',
						array( 'controller' => 'worlds', 'action' => 'history', 'date' => date('d-m-Y') ),
						array( 'idW' => '\d+', 'date' => '^[\d]{2}-[\d]{2}-[\d]{4}$' )));
		$router->addRoute('worldMap',
				new Zend_Controller_Router_Route('/world/:idW/map.html',
						array( 'controller' => 'worlds', 'action' => 'map' ),
						array( 'idW' => '\d+' )));
		$router->addRoute('worldSearch',
				new Zend_Controller_Router_Route('/world/:idW/:save/:sort/search.html',
						array( 'controller' => 'worlds', 'action' => 'search', 'save'=>'new', 'sort' => 'adr_r', ),
						array( 'idW' => '\d+',
							'save' => '^[\w]+$',
							'sort' => '^(nik|nik_r|adr|adr_r|rank_old|rank_old_r|rank_new|rank_new_r|bo|bo_r|ra|ra_r|nra|nra_r|level|level_r|liga|liga_r|arch|arch_r|build|build_r|scien|scien_r)$')));
		$router->addRoute('worldAlliances',
				new Zend_Controller_Router_Route('/world/:idW/page/:page/:sort/:count/alliances.html',
						array( 'page' => 1, 'controller' => 'worlds', 'action' => 'alliances', 'sort' => 'rank_old', 'count'=> 20  ),
						array( 'idW' => '\d+', 'page' => '\d+',
							'count' => '^(10|20|50)$',
							'sort' => '^(count|count_r|count_colony|count_colony_r|rank_old|rank_old_r|rank_new|rank_new_r|bo|bo_r|avg_bo|avg_bo_r|avg_rank_old|avg_rank_old_r|avg_ra|avg_ra_r|avg_nra|avg_nra_r|arch|arch_r|build|build_r|scien|scien_r)$'
							)));
		$router->addRoute('worldPlayers',
				new Zend_Controller_Router_Route('/world/:idW/page/:page/:sort/:count/players.html',
						array( 'page' => 1, 'controller' => 'worlds', 'action' => 'players', 'sort' => 'rank_old', 'count'=> 20 ),
						array( 'idW' => '\d+', 'page' => '\d+',
							'count' => '^(10|20|50)$',
							'sort' => '^(nik|nik_r|dom|dom_r|rank_old|rank_old_r|rank_new|rank_new_r|delta_rank|delta_rank_r|bo|bo_r|delta_bo|delta_bo_r|ra|ra_r|nra|nra_r|level|level_r|liga|liga_r|arch|arch_r|build|build_r|scien|scien_r)$'
							)));


		//alliance
		$router->addRoute('allianceIndex',
				new Zend_Controller_Router_Route('/world/:idW/alliance/:idA/index.html',
						array( 'controller' => 'alliance', 'action' => 'index' ),
						array( 'idW' => '\d+', 'idA' => '\d+' )));
		$router->addRoute('alliancePlayers',
				new Zend_Controller_Router_Route('/world/:idW/alliance/:idA/:page/:sort/:count/players.html',
						array( 'controller' => 'alliance', 'action' => 'players', 'page' => 1, 'sort' => 'rank_old', 'count'=> 20  ),
						array(
							'idW' => '\d+', 'idA' => '\d+', 'page' => '\d+',
							'count' => '^(10|20|50)$',
							'sort' => '^(nik|nik_r|dom|dom_r|rank_old|rank_old_r|rank_new|rank_new_r|delta_rank|delta_rank_r|bo|bo_r|delta_bo|delta_bo_r|ra|ra_r|nra|nra_r|level|level_r|liga|liga_r|arch|arch_r|build|build_r|scien|scien_r)$'
							)));
		$router->addRoute('allianceColony',
				new Zend_Controller_Router_Route('/world/:idW/alliance/:idA/:page/:sort/:count/colony.html',
						array( 'controller' => 'alliance', 'action' => 'colony', 'page' => 1, 'sort' => 'colony_r', 'count'=> 20 ),
						array( 'idW' => '\d+', 'idA' => '\d+', 'page' => '\d+',
							'count' => '^(10|20|50)$',
							'sort' => '^(nik|nik_r|colony|colony_r|rank_old|rank_old_r|rank_new|rank_new_r|delta_rank|delta_rank_r|bo|bo_r|delta_bo|delta_bo_r|ra|ra_r|nra|nra_r|level|level_r|liga|liga_r|arch|arch_r|build|build_r|scien|scien_r)$'
							)));


		//player
		$router->addRoute('playerStat',
				new Zend_Controller_Router_Route('/world/:idW/player/:idP/index.html',
						array( 'controller' => 'player', 'action' => 'index' ),
						array( 'idW' => '\d+', 'idP' => '\d+' )));
		$router->addRoute('playerSearch',
				new Zend_Controller_Router_Route('/world/:idW/player/search/:nik/',
						array( 'controller' => 'player', 'action' => 'quick' ),
						array( 'idW' => '\d+', 'nik' => '[_а-яА-ЯёЁ\w]+' )));
		$router->addRoute('playerForumSearch',
				new Zend_Controller_Router_Route('/world/:idW/player/:idP/forum.html',
						array( 'controller' => 'player', 'action' => 'forumsearch' ),
						array( 'idW' => '\d+', 'idP' => '\d+' )));
	}

	protected function _initZFDebug()
	{
		return false;

		//для крона дебаг не работает
		if( APPLICATION_ENV === 'cli' )
			return false;

		//открываем на боевом только для домашнего адреса
		if( APPLICATION_ENV === 'production' && $_SERVER['REMOTE_ADDR'] !== '93.100.78.165' )
			return false;

		$autoloader = Zend_Loader_Autoloader::getInstance();
		$autoloader->registerNamespace('ZFDebug');

		$options = array(
			'plugins' => array(
				'Variables',
				'File' => array( 'base_path' => APPLICATION_PATH ),
				'Memory',
				'Time',
				'Registry',
				'Exception',
				'Html',
			)
		);

		// Настройка плагина для адаптера базы данных
		if( $this->hasPluginResource('db') ) {
			$this->bootstrap('db');
			$db = $this->getPluginResource('db')->getDbAdapter();
			$options['plugins']['Database']['adapter'] = $db;
		}

		/*if( $this->hasPluginResource('multidb') ) {
			$this->bootstrap('multidb');
			$db = $this->getPluginResource('multidb')->getDefaultDb();
			//var_dump($db);
			$options['plugins']['Database']['adapter'] = $db;
		}*/


		$debug = new ZFDebug_Controller_Plugin_Debug($options);

		$this->bootstrap('frontController');
		$frontController = $this->getResource('frontController');
		$frontController->registerPlugin($debug);
	}

	//cache
	protected function _initCaches()
	{
		$this->bootstrap('cachemanager');

		//кеш метаданных
		Zend_Db_Table_Abstract::setDefaultMetadataCache( $this->getResource('cachemanager')->getCache('long') );
		//Zend_Paginator::setCache(null);
	}

	//custom logs
	protected function _initLogs()
	{
		$this->bootstrap('log');
		$log = $this->getResource('log');

		$log->addPriority('csrf', 9);
		$log->addPriority('error', 10);
	}

	/*
	 * дефолтные значения статиками
	 * экшен хелперы (инстанс ради хуков в диспетчеризацию)
	 * настройки CLI
	 * итд
	 */
	protected function _initOthers()
	{
		/*//костыль на пустое значение сессионной куки (Zend_Session падает)
		$options = $this->getOptions();
		$name = $options['resources']['session']['name'];
		if( isset($_COOKIE[$name]) && empty($_COOKIE[$name]) )
		{
			setcookie ($name, "", time() - 3600);
			$_COOKIE[$name] = 'invalid';
		}*/

		if ( APPLICATION_ENV != 'cli' )
		{
			Zend_Paginator::setDefaultScrollingStyle('Elastic');
			Zend_View_Helper_PaginationControl::setDefaultViewPartial('Partials/pagination.phtml');

			Zend_Session::registerValidator( new Zend_Session_Validator_HttpUserAgent() );
			Zend_Session::registerValidator( new Mylib_Session_Validator_IPAdress() );

			$this->bootstrap('frontcontroller');
			Zend_Controller_Action_HelperBroker::getStaticHelper('WorldsListing');
		}
	}

}
