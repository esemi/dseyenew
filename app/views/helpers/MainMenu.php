<?php

/*
 * рисует разные уровни основного меню
 * строится автоматически по наличию флага $active
 */
class Zend_View_Helper_MainMenu extends Zend_View_Helper_Abstract
{

	public function MainMenu( $level, $active = true )
	{
		switch ($level)
		{
			case 'login':
				$this->printAuthMenu();
				break;
			case 'profile':
				$this->printUserMenu();
				break;
			case 1:
				$this->printMainMenu($active);
				break;
			case 2:
				$this->printWorldMenu($active);
				break;
			case 3:
				$this->printAllianceMenu($active);
				break;
			default:
				throw new Exception('Undefined identity for menu level');
				break;
		}
	}

	/*
	 * меню авторизации
	 * для авторизованных юзеров возвращает шапку с их ником, кнопкой выход и меню по внутренним сервисам
	 * для гостей - кнопки войти и регистрация
	 */
	protected function printAuthMenu()
	{
		$auth = Zend_Auth::getInstance();

		if ( $auth->hasIdentity() )
		{
			$popupMenu = $this->_getUserMenu();

			$user = $auth->getStorage()->read();
			echo sprintf("<div class='float-right text-center mrg-top--27  mrg-right-21 pos-rel'><div id='user'>
					<a class='bold font-17 color-logo-hover no-underline' href='%s'>%s</a>
					<div class='arrow-block small-arrow-down'></div>
					<ul class='user-drop-menu'>", $this->view->url(array(),'userProfile',true), $this->view->escape($user->login));
			foreach( $popupMenu as $point )
				echo "<li><a class='no-underline color-logo-hover' href='{$point['href']}'>{$point['name']}</a></li>";
			echo "</ul></div><a class='font-13' href='" . $this->view->url(array(),'staticLogout',true) . "'>выйти</a></div>";
		}else{
			echo '<div class="float-right text-right mrg-top--27 mrg-right-21"><a class="font-17" href="' . $this->view->url(array(),'staticLogin',true) . '">Войти</a><br>
				  <a class="font-13" href="' . $this->view->url(array(),'staticRegistration',true) . '">регистрация</a>
				  </div>';

		}
	}

	/*
	 * главное меню
	 * всегда активно
	 */
	protected function printMainMenu( $active )
	{
		$menu = array(
			array( 'href' => $this->view->url(array( ), 'staticIndex', true), 'name' => 'главная', 'cntr' => 'index', 'act' => 'index' ),
			array( 'href' => $this->view->url(array( ), 'newsIndex', true), 'name' => 'новости', 'cntr' => 'index', 'act' => 'news' ),
			array( 'href' => $this->view->url(array( ), 'staticAbout', true), 'name' => 'о проекте', 'cntr' => 'index', 'act' => 'about' ),
			array( 'href' => $this->view->url(array( ), 'staticContacts', true), 'name' => 'контакты', 'cntr' => 'index', 'act' => 'contact' ),
			array( 'href' => $this->view->url(array( ), 'staticHelp', true), 'name' => 'справка', 'cntr' => 'index', 'act' => 'help' ),
			array( 'name' => 'сервисы', 'cntr' => 'service', 'ul' => array(
				array('href' => $this->view->url(array( ), 'globalSearch', true), 'name' => 'поиск игроков'),
				array('href' => $this->view->url(array( ), 'archeologyRank', true), 'name' => 'археология'),
				array('href' => $this->view->url(array( ), 'armyCalc', true), 'name' => 'время раунда'),
				array('href' => $this->view->url(array( ), 'onlineStat', true), 'name' => 'статистика online'),
				array('href' => $this->view->url(array( ), 'staticDev', true), 'name' => 'разработчикам'),
				))
			);

		$class = ($active === true) ? 'menu-1-on pad-bg' : 'menu-1-off pad-bg light-gray';

		$controller = Zend_Controller_Front::getInstance()->getRequest()->getControllerName();
		$action = Zend_Controller_Front::getInstance()->getRequest()->getActionName();

		echo "<ul class='{$class} font-17'>";
		foreach($menu as $point)
		{
			$selected = ( $controller == $point['cntr'] && ( !isset($point['act']) || $point['act'] == $action) )
						? 'color-psol bold' : '';
			if( !isset($point['ul']) )
			{
				echo "<li><a class='{$selected} no-underline psol-hover' href='{$point['href']}'>{$point['name']}</a></li>";
			}else{
				echo "<li id='servises'><span class='{$selected} psol-hover'>{$point['name']}</span><ul class='drop-menu'>";
				foreach( $point['ul'] as $li )
				{
					 echo "<li><a href='{$li['href']}' class='psol-hover'>{$li['name']}</a></li>";
				}
				echo '</ul></li>';
			}
		}

		if( $active === true )
		{
			echo '<li><select size="1" class="js-world-select">';

			if( !isset($this->view->idWorld) )
				echo '<option disabled="disabled" selected="selected">Выберите мир</option>';

			if( isset($this->view->listWorlds) )
			{
				foreach($this->view->listWorlds as $world)
				{
					$select = ( isset($this->view->idWorld) && $world->id == $this->view->idWorld ) ? 'selected="selected"' : '';
					$href = $this->view->url(array( 'idW' => $world->id ), 'worldIndex', true);
					echo "<option {$select} value='{$href}'>{$world->name}</option>";
				}
			}
			echo '</select></li>';
		}
		echo '</ul>';
	}

	/*
	 * меню мира
	 * когда активно - дополняется селектом быстрого перехода
	 */
	protected function printWorldMenu( $active )
	{
		$menu = ($active === true) ?
				array(
			array( 'href' => $this->view->url(array( 'idW' => $this->view->idWorld ), 'worldIndex', true), 'name' => 'о&nbsp;мире', 'cntr' => 'worlds', 'act' => 'index' ),
			array( 'href' => $this->view->url(array( 'idW' => $this->view->idWorld ), 'worldHistory', true), 'name' => 'изменения', 'cntr' => 'worlds', 'act' => 'history' ),
			array( 'href' => $this->view->url(array( 'idW' => $this->view->idWorld ), 'worldStat', true), 'name' => 'статистика', 'cntr' => 'worlds', 'act' => 'stat' ),
			array( 'href' => $this->view->url(array( 'idW' => $this->view->idWorld ), 'worldMap', true), 'name' => 'карта', 'cntr' => 'worlds', 'act' => 'map' ),
			array( 'href' => $this->view->url(array( 'idW' => $this->view->idWorld ), 'worldSearch', true), 'name' => 'поиск', 'cntr' => 'worlds', 'act' => 'search' ),
			array( 'href' => $this->view->url(array( 'idW' => $this->view->idWorld ), 'worldAlliances', true), 'name' => 'альянсы', 'cntr' => 'worlds', 'act' => 'alliances' ),
			array( 'href' => $this->view->url(array( 'idW' => $this->view->idWorld ), 'worldPlayers', true), 'name' => 'игроки', 'cntr' => 'worlds', 'act' => 'players' )
				) :
				array(
			array( 'href' => null, 'name' => 'о мире' ),
			array( 'href' => null, 'name' => 'изменения' ),
			array( 'href' => null, 'name' => 'статистика' ),
			array( 'href' => null, 'name' => 'карта' ),
			array( 'href' => null, 'name' => 'поиск' ),
			array( 'href' => null, 'name' => 'альянсы' ),
			array( 'href' => null, 'name' => 'игроки' )
				);

		$class = ($active === true) ? 'menu-2-on pad-bg' : 'menu-2-off pad-bg light-gray';

		$controller = Zend_Controller_Front::getInstance()->getRequest()->getControllerName();
		$action = Zend_Controller_Front::getInstance()->getRequest()->getActionName();


		echo "<ul class='{$class} font-17'>";
		foreach($menu as $point)
		{
			$selected = ( ( !isset($point['cntr']) || $controller == $point['cntr']) && ( !isset($point['act']) || $point['act'] == $action) )
						? 'color-voran bold' : '';

			echo ($active === true) ?
					"<li><a class='{$selected} no-underline voran-hover' href='{$point['href']}'>{$point['name']}</a></li>" :
					"<li><span>{$point['name']}</span></li>";
		}

		echo '</ul>';
	}

	/*
	 * меню альянсов
	 * активно/неактивно
	 */
	protected function printAllianceMenu( $active )
	{
		$menu = ($active === true) ?
				array(
			array( 'href' => $this->view->url(array( 'idW' => $this->view->idWorld, 'idA' => $this->view->idAlliance ), 'allianceIndex', true), 'name' => 'об&nbsp;альянсе', 'cntr' => 'alliance', 'act' => 'index' ),
			array( 'href' => $this->view->url(array( 'idW' => $this->view->idWorld, 'idA' => $this->view->idAlliance ), 'alliancePlayers', true), 'name' => 'игроки', 'cntr' => 'alliance', 'act' => 'players' ),
			array( 'href' => $this->view->url(array( 'idW' => $this->view->idWorld, 'idA' => $this->view->idAlliance ), 'allianceColony', true), 'name' => 'колонии', 'cntr' => 'alliance', 'act' => 'colony' ),
			array( 'href' => $this->view->url(array( 'idW' => $this->view->idWorld, 'idA' => $this->view->idAlliance ), 'allianceStat', true), 'name' => 'статистика', 'cntr' => 'alliance', 'act' => 'stat' )
				) :
				array(
			array( 'href' => null, 'name' => 'об альянсе' ),
			array( 'href' => null, 'name' => 'игроки' ),
			array( 'href' => null, 'name' => 'колонии' ),
			array( 'href' => null, 'name' => 'статистика' )
				);


		$controller = Zend_Controller_Front::getInstance()->getRequest()->getControllerName();
		$action = Zend_Controller_Front::getInstance()->getRequest()->getActionName();

		$class = ($active === true) ? 'menu-3-on pad-bg' : 'menu-3-off pad-bg light-gray';

		echo "<ul class='{$class} font-17'>";
		foreach($menu as $point)
		{
			$selected = ( isset($point['cntr']) && $controller == $point['cntr'] && ( !isset($point['act']) || $point['act'] == $action) )
						? 'color-liens bold' : '';

			echo ($active === true) ?
					"<li><a class='{$selected} no-underline liens-hover' href='{$point['href']}'>{$point['name']}</a></li>" :
					"<li><span>{$point['name']}</span></li>";
		}
		echo '</ul>';
	}


	/*
	 * меню пользователя
	 * отталкивается от прав
	 */
	protected function printUserMenu(  )
	{
		if ( !Zend_Auth::getInstance()->hasIdentity() )
			return;

		$menu = $this->_getUserMenu();

		$action = Zend_Controller_Front::getInstance()->getRequest()->getActionName();
		$cnt = Zend_Controller_Front::getInstance()->getRequest()->getControllerName();

		echo '<div class="world-list float-left mrg-left-21 font-17"><ul>';
		foreach($menu as $point)
		{
			$selected = (
					$cnt == $point['cntr'] &&
					(
						!isset($point['act']) ||
						( is_array($point['act']) && in_array($action, $point['act']) ) ||
						$point['act'] == $action
					)
				 ) ? 'color-voran' : '';
			echo "<li><a class='no-underline color-main voran-hover {$selected}' href='{$point['href']}'>{$point['name']}</a></li>";
		}
		echo '</ul></div>';
	}

	protected function _getUserMenu()
	{
		$menu = array();
		$access = Zend_Controller_Action_HelperBroker::getStaticHelper('CheckAccess');
		if($access->check('profile', 'view', 'return'))
			$menu[] = array(
				'href' => $this->view->url(array( ), 'userProfile', true),
				'name' => 'Профиль',
				'cntr' => 'user',
				'act' => array('profile', 'password-change') );

		if($access->check('autosearch', 'view', 'return'))
			$menu[] = array(
				'href' => $this->view->url(array( ), 'userAutosearch', true),
				'name' => 'Автопоиск',
				'cntr' => 'user',
				'act' => 'autosearch' );

		if($access->check('profile', 'view', 'return'))
			$menu[] = array(
				'href' => $this->view->url(array( ), 'userHistory', true),
				'name' => 'История',
				'cntr' => 'user',
				'act' => 'history');

		if($access->check('logs', 'view', 'return'))
			$menu[] = array(
				'href' => $this->view->url(array( ), 'moderLogs', true),
				'name' => 'Логи',
				'cntr' => 'logs' );
		return $menu;
	}
}
