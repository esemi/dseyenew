<?php

/*
 * контроллер аутентификации юзера
 *
 */
class AuthController extends Zend_Controller_Action
{

	protected $_recaptcha = null; //объект капчи

	public function init()
	{
		$conf = $this->getFrontController()->getParam('bootstrap')->getOption('recaptcha');
		$this->_recaptcha = new Zend_Service_ReCaptcha($conf['pubkey'],$conf['privkey']);
	}

	/*
	 * вход
	 */
	public function loginAction()
	{
		$this->view->keywords = "Вход, Войти, Залогиниться, Форма входа, Личный кабинет";
		$this->view->description = "Вход в личный кабинет";
		$this->view->title = "Вход в личный кабинет";

		$this->view->helpLink = $this->view->url( array('id'=>'register'), 'helpView', true );

		if( Zend_Auth::getInstance()->hasIdentity() )
		{
			$this->_helper->flashMessenger->addMessage(array( 'success' => "Вы уже вошли в систему. Если вам необходимо зайти под другим ником сперва выйдите.") );
			$this->_helper->redirector->gotoRouteAndExit(array(), 'userProfile', true);
		}

		$mes = $this->_helper->flashMessenger->getMessages();
		if( count($mes) > 0 && is_array($mes) )
		{
			$mes = array_shift($mes);
			$keys = array_keys($mes);
			$this->view->messType = array_shift( $keys );
			$this->view->messText = array_shift( $mes );
		}

		$ip = $this->_request->getClientIp(false);
		if( true !== $this->_helper->modelLoad('Antibrut')->checkIP('login', $ip) )
			$this->view->recaptcha = $recaptcha = $this->_recaptcha;

		if( $this->_request->isPost() )
		{
			$this->view->login = $login = $this->_request->getPost('login');
			$pass = $this->_request->getPost('pass');

			//проверяем капчу (если надо)
			if( isset($recaptcha) )
			{
				if( !$this->_helper->checkCaptcha($recaptcha) )
				{
					$this->view->messType = 'error';
					$this->view->messText = 'Текст с изображения введён неверно';
					return;
				}
			}

			$dbUser = $this->_helper->modelLoad('Users')->findByLogin( $login );
			if( false === $this->_loginUser($login, $pass) )
			{
				$this->_helper->modelLoad('Antibrut')->addIP( 'login', $ip );

				if( true !== $this->_helper->modelLoad('Antibrut')->checkIP('login', $ip) )
					$this->view->recaptcha = $this->_recaptcha;

				$this->view->messType = 'error';
				$this->view->messText = 'Неверная пара логин/пароль';
				if( !is_null($dbUser) )
					$this->_helper->modelLoad('UsersHistory')->add( $dbUser->id, 'Попытка входа в систему с неверным паролем', $this->_request);
				return;
			}

			$this->_helper->modelLoad('UsersHistory')->add( $dbUser->id, 'Вход в систему', $this->_request);

			//умный редирект
			$this->_helper->redirector->gotoUrlAndExit($this->_getParam('return', $this->view->url(array(),'userProfile',true) ));
		}

	}

	/*
	 * выход
	 */
	public function logoutAction()
	{
		$this->_logoutUser();
		$this->_helper->redirector->gotoRouteAndExit(array(), 'staticIndex', true);
	}

	protected function _logoutUser()
	{
		$auth = Zend_Auth::getInstance();
		if( $auth->hasIdentity() )
		{
			$user = $auth->getStorage()->read();
			$this->_helper->modelLoad('UsersHistory')->add($user->id, 'Выход из системы', $this->_request);

			Zend_Auth::getInstance()->clearIdentity();
			Zend_Session::forgetMe();
			Zend_Session::expireSessionCookie();
		}
	}

	/*
	 * Регистрация нового пользователя
	 */
	public function registerAction()
	{
		$this->view->keywords = "Новый пользователь, Регистрация аккаунта, Регистрация";
		$this->view->description = "Регистрация нового пользователя для досупа к расширенныму функционалу";
		$this->view->title = "Регистрация нового пользователя";

		$this->view->helpLink = $this->view->url( array('id'=>'register'), 'helpView', true );

		$ip = $this->_request->getClientIp(false);

		if( true !== $this->_helper->modelLoad('Antibrut')->checkIP('register', $ip) )
			$this->view->recaptcha = $recaptcha = $this->_recaptcha;

		if ( $this->_request->isPost() )
		{
			//добавляем попытку регистрации в антибрут
			$this->_helper->modelLoad('Antibrut')->addIP( 'register', $ip );

			$this->view->post = $post = $this->_request->getPost();
			$result = $this->_helper->modelLoad('Users')->validateNewUser( $post, isset($recaptcha) ? $recaptcha : null );

			if( $result === true )
			{
				//заводим нового юзера
				$idUser = $this->_helper->modelLoad('Users')->addNew( $post );
				$this->_helper->modelLoad('UsersHistory')->add( $idUser, 'Регистрация аккаунта', $this->_request );

				//автовход после регистрации
				$this->_logoutUser();
				$loginResult = $this->_loginUser($post['login'], $post['pass']);
				if( $loginResult !== true )
					throw new Exception('Error autologin after registration');

				//генерим токен и шлём письмо с активацией
				$token = $this->_helper->modelLoad('UsersApproved')->add( $idUser );

				//шлём письмо
				$mail = new Mylib_Mail('utf-8');
				$mail->setHeaderEncoding(Zend_Mime::ENCODING_BASE64);
				$mail->setSubject('Регистрация нового пользователя DSeye.ru');
				$mail->setBodyHtml(
						$this->view->partial(
							'Partials/mail/registration.phtml',
							array(
								'email' => $post['email'],
								'login' => $post['login'],
								'token' => $token,
								'ip' => $ip
								)) );
				$mail->addTo($post['email']);
				$mail->send();

				$this->_helper->flashMessenger->addMessage(  array( 'success' => sprintf("Вы успешно зарегистрировались под ником %s.<br>На указанный вами адрес отправлено письмо с ссылкой для подтверждения адреса электронной почты.", $this->view->escape($post['login'])) ) );
				$this->_helper->redirector->gotoRouteAndExit(array(), 'userProfile', true);
			}else{
				$this->view->messType = 'error';
				$this->view->messText = $result;
			}
		}

	}

	protected function _loginUser($login, $pass)
	{
		$auth = Zend_Auth::getInstance();
		$adapter = $this->_getLoginAdapter($login, $pass);
		if( $auth->authenticate($adapter)->isValid() )
		{
			$res = $adapter->getResultRowObject();
			$user = new stdClass();
			$user->id = $res->id;
			$user->login = $res->login;
			$user->role = $res->role;
			$user->csrf = hash('sha256', uniqid( mt_rand(), true ));
			$auth->getStorage()->write($user);
			Zend_Session::rememberMe();
			return true;
		}else{
			return false;
		}
	}

	protected function _getLoginAdapter($login, $pass)
	{
		$db = $this->getInvokeArg('bootstrap')->getResource('db');
		$adapter = new Zend_Auth_Adapter_DbTable($db);
		$adapter->setTableName('users')
				->setIdentityColumn('login')
				->setCredentialColumn('pass')
				->setCredentialTreatment('SHA1( CONCAT( ?, `salt` ) )')
				->setIdentity( (empty($login)) ? ' ' : $login )
				->setCredential( $pass );
		return $adapter;
	}


	/*
	 * Подтверждение адреса электронной почты по токену
	 */
	public function emailApproveAction()
	{
		$this->view->keywords = "Подтверждение почты, Активация, Новый пользователь";
		$this->view->description = "Подтверждение адреса электронной почты аккаунта";
		$this->view->title = "Подтверждение email адреса";

		$this->view->helpLink = $this->view->url( array('id'=>'email_approve'), 'helpView', true );

		$this->view->token = $token = $this->_getParam('token');

		if( !is_null($token) )
		{
			$idUser = $this->_helper->modelLoad('UsersApproved')->findAndUpdToken( $token );

			//юзер с таким токеном найден
			if( !is_null($idUser) )
			{
				$this->_helper->modelLoad('Users')->approve( $idUser );
				$this->_helper->modelLoad('UsersHistory')->add( $idUser, 'Подтверждение email адреса', $this->_request );

				$this->_helper->flashMessenger->addMessage( array( 'success' => "Адрес электронной почты успешно подтверждён" ) );

				if( Zend_Auth::getInstance()->hasIdentity() )
					$this->_helper->redirector->gotoRouteAndExit(array(), 'userProfile', true);
				else
					$this->_helper->redirector->gotoRouteAndExit(array(), 'staticLogin', true);

			}else{
				$this->view->messType = 'error';
				$this->view->messText = 'Код активации устарел или не верен';
			}
		}
	}

	/*
	 * Потворный запрос подтверждения адреса электронной почты
	 */
	public function emailApproveRetryAction()
	{
		$this->view->keywords = "Подтверждение почты, Активация, Новый пользователь, Повторная активация, Не пришло письмо";
		$this->view->description = "Повторный запрос подтверждения адреса электронной почты";
		$this->view->title = "Повторный запрос подтверждения email адреса";

		$this->view->helpLink = $this->view->url( array('id'=>'email_approve'), 'helpView', true );

		$this->_helper->checkAccess('profile','edit','redirect');

		$user = $this->_helper->modelLoad('Users')->getInfo( Zend_Auth::getInstance()->getStorage()->read()->id );
		if( $user['approved'] == 'yes' )
		{
			$this->_helper->flashMessenger->addMessage( array( 'success' => "Адрес электронной почты уже был активирован ранее" ) );
			$this->_helper->redirector->gotoRouteAndExit(array(), 'userProfile',true);
		}

		$ip = $this->_request->getClientIp(false);
		
		if( true !== $this->_helper->modelLoad('Antibrut')->checkIP('registerretry', $ip) )
			$this->view->recaptcha = $recaptcha = $this->_recaptcha;

		if( $this->_request->isPost() )
		{
			$this->_helper->modelLoad('Antibrut')->addIP( 'registerretry', $ip );

			if( true !== $this->_helper->modelLoad('Antibrut')->checkIP('registerretry', $ip) )
				$this->view->recaptcha = $this->_recaptcha;

			//проверяем капчу (если надо)
			if( isset($recaptcha) )
			{
				if( !$this->_helper->checkCaptcha($recaptcha) )
				{
					$this->view->messType = 'error';
					$this->view->messText = 'Текст с изображения введён неверно';
					return;
				}
			}

			$token = $this->_helper->modelLoad('UsersApproved')->add( $user['id'] );
			$this->_helper->modelLoad('UsersHistory')->add( $user['id'], 'Повторный запрос подтверждения email адреса', $this->_request);

			//шлём письмо
			$mail = new Mylib_Mail('utf-8');
			$mail->setHeaderEncoding(Zend_Mime::ENCODING_BASE64);
			$mail->setSubject('Подтверждение email адреса аккаунта DSeye.ru');
			$mail->setBodyHtml(
					$this->view->partial(
							'Partials/mail/email-approve-retry.phtml',
							array(
								'email' => $user['email'],
								'login' => $user['login'],
								'token' => $token,
								'ip' => $ip
								)) );
			$mail->addTo($user['email']);
			$mail->send();

			//сообщение сохраняем и редиректим
			$this->_helper->flashMessenger->addMessage(  array( 'success' => "Повторный запрос подтверждения email адреса аккаунта успешно получен.<br>В ближайшее время на указанный при регистрации email-адрес придёт письмо с инструкцией по его подтверждению." ) );
			$this->_helper->redirector->gotoRouteAndExit(array(), 'userProfile', true);
		}
	}

	/*
	 * Запрос восстановления пароля
	 * (высылаем ссылку на подтверждение генерации нового пароля)
	 */
	public function passwordRememberAction()
	{
		$this->view->keywords = "Забыл пароль, Восстановление пароля, Remember password, Recovery password";
		$this->view->description = "Восстановление пароля для входа в личный кабинет";
		$this->view->title = "Восстановление пароля";

		$this->view->helpLink = $this->view->url( array('id'=>'password_remember'), 'helpView', true );

		$ip = $this->_request->getClientIp(false);
		$this->view->recaptcha = $recaptcha = $this->_recaptcha;

		if ( $this->_request->isPost() )
		{
			$this->view->login = $login = $this->_request->getPost('login');
			$this->view->email = $email = $this->_request->getPost('email');

			//проверяем капчу
			if( !$this->_helper->checkCaptcha($recaptcha) )
			{
				$this->view->messType = 'error';
				$this->view->messText = 'Текст с изображения введён неверно';
				return;
			}

			$user = $this->_helper->modelLoad('Users')->findForRemember( $login, $email );

			//юзер не найден
			if( is_null($user) )
			{
				$this->view->messType = 'error';
				$this->view->messText = 'Пользователь с указанным сочетанием пары логин/email не существует';

				$dbUser = $this->_helper->modelLoad('Users')->findByLogin( $login );
				if( !is_null($dbUser) )
					$this->_helper->modelLoad('UsersHistory')->add( $dbUser->id, 'Запрос восстановления пароля (неверный email адрес)', $this->_request);
				return;
			}

			$this->_helper->modelLoad('UsersHistory')->add($user->id, 'Запрос восстановления пароля (письмо отправлено)', $this->_request);
			$rememberProp = $this->getFrontController()->getParam('bootstrap')->getOption('remember');

			$token = $this->_helper->modelLoad('UsersRemember')->add( $user->id );

			//шлём письмо
			$mail = new Mylib_Mail('utf-8');
			$mail->setHeaderEncoding(Zend_Mime::ENCODING_BASE64);
			$mail->setSubject('Восстановление пароля DSeye.ru');
			$mail->setBodyHtml(
					$this->view->partial(
							'Partials/mail/password-remember.phtml',
							array(
								'login' => $user->login,
								'token' => $token,
								'scavProp' => $rememberProp['hours'],
								'ip' => $ip
								)) );
			$mail->addTo($user->email);
			$mail->send();

			$this->_helper->flashMessenger->addMessage(  array( 'success' => "Заявка на восстановление пароля принята.<br>В ближайшее время на указанный вами при регистрации email-адрес придёт письмо с инструкцией по восстановлению." ) );
			$this->_helper->redirector->gotoRouteAndExit(array(), 'staticLogin', true);
		}
	}

	/*
	 * Попытка восстановления пароля
	 * (заменяем пароль по токену и шлём новый на почту)
	 */
	public function passwordRememberActivateAction()
	{
		$this->view->keywords = "Забыл пароль, Восстановление пароля, Подтверждение, Remember password, Recovery password";
		$this->view->description = "Подтверждение восстановления пароля в личный кабинет";
		$this->view->title = "Подтверждение восстановления пароля";

		$this->view->helpLink = $this->view->url( array('id'=>'password_remember'), 'helpView', true );

		$this->view->token = $token = $this->_getParam('token');
		$rememberProp = $this->getFrontController()->getParam('bootstrap')->getOption('remember');

		if ( !is_null($token) )
		{
			$idUser = $this->_helper->modelLoad('UsersRemember')->findAndUpdToken( $token, $rememberProp['hours'] );

			//юзер с таким токеном найден
			if( is_null($idUser) )
			{
				$this->view->messType = 'error';
				$this->view->messText = 'Код активации не верен либо устарел';
				return;
			}

			//устанавливаем новый пароль
			$newPass = $this->_helper->modelLoad('Users')->updPass( $idUser );
			$user = $this->_helper->modelLoad('Users')->getInfo( $idUser );

			//шлём письмо
			$mail = new Mylib_Mail('utf-8');
			$mail->setHeaderEncoding(Zend_Mime::ENCODING_BASE64);
			$mail->setSubject('Восстановление пароля DSeye.ru');
			$mail->setBodyHtml(
					$this->view->partial(
							'Partials/mail/password-remember-success.phtml',
							array(
								'login' => $user['login'],
								'pass' => $newPass)) );
			$mail->addTo($user['email']);
			$mail->send();

			$this->_helper->modelLoad('UsersHistory')->add( $idUser, 'Смена пароля через восстановление', $this->_request);

			$this->_helper->flashMessenger->addMessage( array( 'success' => "Новый пароль успешно установлен и отправлен вам на почту" ) );
			$this->_helper->redirector->gotoRouteAndExit(array(), 'staticLogin', true);
		}
	}
}