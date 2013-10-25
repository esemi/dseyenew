<?php

class AllianceController extends Zend_Controller_Action
{

	protected
		$idW = null,
		$idA = null;

	public function init()
	{
		if( $this->_helper->modelLoad('Worlds')->validate((int) $this->_getParam('idW')) !== true )
			throw new Mylib_Exception_NotFound('World not found');
		$this->view->idWorld = $this->idW = (int) $this->_getParam('idW');
		$this->view->nameWorld = $this->_helper->modelLoad('Worlds')->getName($this->idW);

		if( $this->_helper->modelLoad('Alliances')->validate((int) $this->_getParam('idA'), $this->idW) !== true ) {
			$this->_helper->redirector->gotoRouteAndExit(array( 'idW' => $this->idW ), 'worldAlliances', true);
		}

		$this->view->idAlliance = $this->idA = (int) $this->_getParam('idA');
		$this->view->nameAlliance = $this->_helper->modelLoad('Alliances')->getName($this->idA);

		$this->view->headTitle("Альянс {$this->view->nameAlliance}");

		//резиновый шаблон
		$this->view->rubberPage = true;
	}

	/*
	 * последние изменения и общие данные альянса
	 */
	public function indexAction()
	{
		$this->view->helpLink = $this->view->url( array('id'=>'alliance_page'), 'helpView', true );

		$this->view->keywords = "{$this->view->nameAlliance}, Альянс, Информация";
		$this->view->description = "Альянс {$this->view->nameAlliance}, общая информация";
		$this->view->headTitle("Об альянсе");
		$this->view->linkCanonical($this->view->url(array('idW' => $this->idW, 'idA' => $this->idA), 'allianceIndex', true));

		$this->view->mainProperty = $this->_helper->modelLoad('Alliances')->getData($this->idA);

		//параметры
		$this->view->propData = $this->_helper->modelLoad('AlliancesProperty')->getProp($this->idA);

		$this->view->transAlliance = $this->_helper->modelLoad('PlayersTransAlliance')->getTransByAlliance($this->idA, 20);
		$this->view->transSots = $this->_helper->modelLoad('PlayersTransSots')->getTransByAlliance($this->idA, 20);
		$this->view->transGate = $this->_helper->modelLoad('PlayersTransGate')->getTransByAlliance($this->idA, 20);
		$this->view->transLigue = $this->_helper->modelLoad('PlayersTransLigue')->getTransByAlliance($this->idA, 20);
	}

	/*
	 * список игроков альянса
	 */
	public function playersAction()
	{
		$this->view->helpLink = $this->view->url( array('id'=>'lists'), 'helpView', true );

		$page = (int) $this->_getParam('page', 1);
		$this->view->sort = $sort = $this->_getParam('sort');

		$this->view->paginator = $paginator =  $this->_helper->modelLoad('Players')->listAlliancePlayers(
				$this->idA,
				$page,
				$this->_helper->modelLoad('AlliancesProperty')->getPlayersCount($this->idA),
				$sort,
				(int)$this->_getParam('count') );

		$page = $paginator->getCurrentPageNumber();

		$this->view->countPerPage = $paginator->getItemCountPerPage();
		$this->view->numbered = ($page - 1) * $paginator->getItemCountPerPage() + 1;

		$this->view->keywords = "{$this->view->nameAlliance}, Альянс, Игроки";
		$this->view->description = "Альянс {$this->view->nameAlliance}, список игроков (страница {$page})";
		$this->view->headTitle("Список игроков, страница {$page}");

	}

	/*
	 * список колоний альянса
	 */
	public function colonyAction()
	{
		$this->view->helpLink = $this->view->url( array('id'=>'alliance_colony'), 'helpView', true );

		$page = (int) $this->_getParam('page', 1);
		$this->view->sort = $sort = $this->_getParam('sort');

		$this->view->paginator = $paginator =  $this->_helper->modelLoad('Players')->listAllianceColony(
				$this->idA,
				$page,
				$this->_helper->modelLoad('AlliancesProperty')->getColonyCount($this->idA),
				$sort,
				(int)$this->_getParam('count') );

		$page = $paginator->getCurrentPageNumber();

		$this->view->countPerPage = $paginator->getItemCountPerPage();
		$this->view->numbered = ($page - 1) * $paginator->getItemCountPerPage() + 1;

		$this->view->keywords = "{$this->view->nameAlliance}, Альянс, Колонии";
		$this->view->description = "Альянс {$this->view->nameAlliance}, список колоний (страница {$page})";
		$this->view->headTitle("Список колоний, страница {$page}");

	}


}
