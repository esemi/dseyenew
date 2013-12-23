<?php

/*
 * хелпер для проверки токена
 */
class Action_Helper_TokenCheck extends Zend_Controller_Action_Helper_Abstract
{
	public function direct($token)
	{
		$user = Zend_Auth::getInstance()->getStorage()->read();
		$result = ( !is_null($user) && $user->csrf === $token );

		if($result === false){
			$this->getActionController()->getHelper('Logger')->csrfError();
		}

		return $result;
	}
}
