<?php

/*
 * хелпер для проверки прав пользователя
 *
 * инстанцирует наш ACL
 * учитывает незалогиненых юзеров как гостей
 *
 */

class Action_Helper_CheckAccess extends Zend_Controller_Action_Helper_Abstract
{

	private
			$_hasIdentity,
			$_role,
			$_acl;

	public function __construct()
	{
		$auth = Zend_Auth::getInstance();

		$this->_hasIdentity = $auth->hasIdentity();
		$this->_role = ( $this->_hasIdentity ) ? $auth->getStorage()->read()->role : 'guest';
		$this->_acl = new Mylib_Acl();
	}

	/*
	 * @param string $mode (redirect | return | excep )
	 */

	public function check($resourse, $privileges, $mode)
	{
		$cntr = $this->getActionController();
		$result = $this->_acl->isAllowed($this->_role, $resourse, $privileges);

		switch ($mode) {
			case 'redirect':
				if (!$result) {
					$url = $cntr->getHelper('url')->url(array(), 'staticLogin', true);

					if ($this->_hasIdentity) {
						$message = 'Попробуйте перезайти под другим пользователем';
					} else {
						$message = 'Войдите в систему';
						$returnUrl = $this->getRequest()->getServer('REQUEST_URI', $cntr->getHelper('url')->url(array(), 'userProfile', true));
						$url .= "?return={$returnUrl}";
					}

					$cntr->getHelper('Messenger')->addMessage('error', "У вас недостаточно прав для доступа к данной странице. {$message}.");
					$cntr->getHelper('redirector')->gotoUrlAndExit($url);
				}
				break;

			case 'excep':
				if (!$result)
					throw new Exception("Access denied");
				break;

			case 'return':
				return $result;
				break;

			default:
				throw new Exception('Undefined mode (helper access)');
				break;
		}
	}

	public function direct($resourse = null, $privileges = null, $mode = 'return')
	{
		return $this->check($resourse, $privileges, $mode);
	}

}
