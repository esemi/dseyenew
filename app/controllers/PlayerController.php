<?php
class PlayerController extends Zend_Controller_Action
{

	protected
		$idW = null,
		$idP = null;

	public function init()
	{
		if( $this->_helper->modelLoad('Worlds')->validate((int) $this->_getParam('idW')) !== true )
				throw new Mylib_Exception_NotFound('World not found');

		$this->view->idWorld = $this->idW = (int) $this->_getParam('idW');

		//имя мира
		$this->view->nameWorld =$this->_helper->modelLoad('Worlds')->getName($this->idW);

		//резиновый шаблон
		$this->view->rubberPage = true;
	}

	/*
	 * главная страница игрока + графики
	 */
	public function indexAction()
	{
		$this->view->helpLink = $this->view->url( array('id'=>'player_page'), 'helpView', true );

		$this->view->idPlayer = $this->idP = (int) $this->_getParam('idP');
		if( $this->_helper->modelLoad('Players')->validate($this->idP, $this->idW) !== true )
			throw new Mylib_Exception_NotFound('Player not found');

		//если не false - доступен график с хелпера
		$this->view->dshelp = $this->_helper->modelLoad('WorldsDshelp')->graphAvailable( $this->idW );

		//если true - доступен поиск сообщений на форуме
		$version = $this->_helper->modelLoad('Worlds')->getVersion($this->idW);
		$this->view->forum = $this->_helper->modelLoad('GameVersions')->availableForumSearch($version);

		//доступен ли мониторинг
		if( $this->_helper->checkAccess('monitoring', 'manage', 'return') )
		{
			$user = Zend_Auth::getInstance()->getStorage()->read();
			$this->view->monitor = $this->_helper->modelLoad('MonitorItems')->issetByUser($this->idP, $user->id);
		}

		$this->view->mainProperty = $info = $this->_helper->modelLoad('Players')->getInfo($this->idP);

		$this->view->otherWorlds = $this->_helper->modelLoad('Players')->sotsWithoutWorld($info['nik'], $this->idW );
		$this->view->neighbors = new stdClass();
		$this->view->neighbors->dom = $this->_helper->modelLoad('Players')->getNeighborsDom( $this->idW, $this->idP, $info['compl'], $info['ring'] );
		$this->view->neighbors->mels = $this->_helper->modelLoad('Players')->getNeighborsMels( $this->idW, $this->idP, $info['colony'] );

		$this->view->transSots = $this->_helper->modelLoad('PlayersTransSots')->getTransByPlayer($this->idP, 20);
		$this->view->transOthers = $this->_helper->modelLoad('PlayersTransOthers')->getTransByPlayer($this->idP, 20);

		$this->view->title = "Игрок {$info['nik']}";
		$this->view->keywords = "{$info['nik']}, Игрок, {$this->view->nameWorld}";
		$this->view->description = "Страница игрока {$info['nik']}. Статистика по основным показателям и графики.";
	}


	/*
	 * быстрый переход к игроку по нику
	 */
	public function quickAction()
	{
		//пробуем получить ник игрока
		$res = $this->_helper->modelLoad('Players')->findByNik( trim($this->_getParam('nik')), 1, $this->idW );

		if( $res === false )
			throw new Mylib_Exception_NotFound('Игрок с данным ником не найден в заданном мире');

		$this->_helper->redirector->gotoRouteAndExit(array( 'idW' => $this->idW, 'idP' => $res[0]['id'] ),'playerStat', true);
	}


	/*
	 * поиск сообщений игрока на форуме
	 */
	public function forumsearchAction()
	{
		$this->idP = (int) $this->_getParam('idP');
		if( $this->_helper->modelLoad('Players')->validate($this->idP, $this->idW) !== true )
			throw new Mylib_Exception_NotFound('Player not found');

		//получить шаблон поиска

		//если true - доступен поиск сообщений на форуме
		$version = $this->_helper->modelLoad('Worlds')->getVersion($this->idW);
		if( !$this->_helper->modelLoad('GameVersions')->availableForumSearch($version) )
			throw new Exception('Forum search pattern not found');

		$pattern = $this->_helper->modelLoad('GameVersions')->getForumSearch( $version );

		//получить инфу (для подстановки в шаблон поиска)
		$info = $this->_helper->modelLoad('Players')->getInfo($this->idP);

		$url = str_replace('{-author-}', $info['nik'], $pattern);

		$this->_helper->redirector->gotoUrlAndExit($url);
	}

}


