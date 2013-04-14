<?php

/*
 * контроллер профиля юзера
 *
 */
class UserController extends Zend_Controller_Action
{

	protected
		$_users = null,
		$_history = null,
		$_usersSearch = null;

	/*
	 * профиль
	 */
	public function profileAction()
	{
		$this->view->helpLink = $this->view->url( array('id'=>'profile'), 'helpView', true );

		$this->_helper->checkAccess('profile','view','redirect');

		$user = Zend_Auth::getInstance()->getStorage()->read();

		$this->view->title = "Личный кабинет пользователя {$user->login}";
		$this->view->keywords = "Профиль, Личный кабинет, {$user->login}";
		$this->view->description = "Личный кабинет пользователя {$user->login}";

		//доступ к изменению пароля
		$this->view->accessEdit = $this->_helper->checkAccess('profile','edit');

		//Личные данные
		$this->view->user = $this->_helper->modelLoad('Users')->getInfo( $user->id );

		//История юзера
		$this->view->history = $this->_helper->modelLoad('UsersHistory')->lastOf( $user->id, 5 );

		//Автопоиск юзера
		if( $this->_helper->checkAccess('autosearch','view') )
		{
			$this->view->autoSearchData = $this->_helper->modelLoad('UsersSearch')->lastUsed( $user->id, 5 );
		}

		//с регистрации люди приходят с сообщением
		$mes = $this->_helper->flashMessenger->getMessages();
		if( count($mes) > 0 && is_array($mes) )
		{
			$mes = array_shift($mes);
			$keys = array_keys($mes);
			$this->view->messType = array_shift( $keys );
			$this->view->messText = array_shift( $mes );
		}
	}

	/*
	 * история действий юзера с пагинацией и полными данными
	 */
	public function historyAction()
	{
		$this->view->helpLink = $this->view->url( array('id'=>'profile_history'), 'helpView', true );
		$this->_helper->checkAccess('profile','view','redirect');

		$this->view->user = $user = Zend_Auth::getInstance()->getStorage()->read();

		$this->view->title = "История действий пользователя {$user->login}";
		$this->view->keywords = "Профиль, Личный кабинет, История действий, {$user->login}";
		$this->view->description = "История действий пользователя {$user->login}";

		$this->view->history = $this->_helper->modelLoad('UsersHistory')->lastOf($user->id,200);
	}

	/*
	 * все записи автопоиска юзера
	 */
	public function autosearchAction()
	{
		$this->view->helpLink = $this->view->url( array('id'=>'autosearch'), 'helpView', true );
		$this->_helper->checkAccess('autosearch','view','redirect');

		$this->view->user = $user = Zend_Auth::getInstance()->getStorage()->read();

		$this->view->title = "Автопоиск пользователя {$user->login}";
		$this->view->keywords = "Автопоиск, Личный кабинет, История действий, {$user->login}";
		$this->view->description = "Автопоиск (сохранённые настройки поиска) пользователя {$user->login}";

		$this->view->accessDelete = $this->_helper->checkAccess('autosearch','del');

		$this->view->autoSearchList = $this->_helper->modelLoad('UsersSearch')->listAll( $user->id );
	}

	/*
	 * редирект на поиск с сохранёнными настройками
	 */
	public function autosearchFindAction()
	{
		$this->_helper->checkAccess('autosearch','view','redirect');
		$this->view->user = $user = Zend_Auth::getInstance()->getStorage()->read();

		//ищем данные по поиску (заодно проверяем принадлежность)
		$data = $this->_helper->modelLoad('UsersSearch')->getOne( $this->_getParam('idA'), $user->id  );
		if( is_null($data) )
			throw new Mylib_Exception_NotFound('Некорректный идентификатор поиска');

		$this->_helper->modelLoad('UsersSearch')->touch($this->_getParam('idA'));

		//подготовим данные и редиректим валидно или заведомо нет
		$prop = unserialize($data['prop']);
		if( $prop !== false )
		{
			//создаём тиниурл для данного поиска
			$uid = $this->_helper->modelLoad('SearchProps')->insertOrUpdate($prop);

			$this->_helper->redirector->gotoRouteAndExit(
					array(
						'idW' => $data['id_world'],
						'save' => $uid,
						'sort' => $prop->sort ),
					'worldSearch', true);
		}else{
			$this->_helper->redirector->gotoRouteAndExit(
					array(
						'idW' => $data['id_world'],
						'save' => 'invalid' ),
					'worldSearch', true);
		}
	}

	public function passwordChangeAction()
	{
		$this->_helper->checkAccess('profile','edit','redirect');

		$this->view->helpLink = $this->view->url( array('id'=>'password_change'), 'helpView', true );

		$user = Zend_Auth::getInstance()->getStorage()->read();

		$this->view->title = "Изменение пароля пользователя {$user->login}";
		$this->view->keywords = "Изменение пароля, Профиль, Личный кабинет, {$user->login}";
		$this->view->description = "Изменение пароля пользователя {$user->login}";

		if( $this->_request->isPost() )
		{
			$pass = $this->_request->getPost('pass');
			$res = $this->_helper->modelLoad('Users')->validatePass($pass, $this->_request->getPost('repass'),$user->id, $this->_request->getPost('oldpass'));

			if( $res === true ){
				$this->_helper->modelLoad('UsersHistory')->add($user->id,'Смена пароля из личного кабинета',$this->_request);
				$this->_helper->modelLoad('Users')->updPass( $user->id, $pass );
				$this->_helper->flashMessenger->addMessage(array( 'success' => "Пароль успешно изменён") );
				$this->_helper->redirector->gotoRouteAndExit(array(), 'userProfile', true);
			}else{
				$this->_helper->modelLoad('UsersHistory')->add($user->id,"Попытка смены пароля из личного кабинета ({$res})",$this->_request);
				$this->view->messType = 'error';
				$this->view->messText = $res;
			}
		}
	}

	public function monitoringAction()
	{
		$this->_helper->checkAccess('autosearch','view','redirect');

	}
}





