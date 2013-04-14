<?php

/*
 * хелпер для проверки рекапчи
 */
class Action_Helper_CheckCaptcha extends Zend_Controller_Action_Helper_Abstract
{
	public function check($recaptcha)
	{
		$req = $this->getActionController()->getRequest();
		$tmp1 = $req->getPost('recaptcha_challenge_field','');
		$tmp2 = $req->getPost('recaptcha_response_field','');
		$captchaRes = $recaptcha->verify(
				(!empty($tmp1)) ? $tmp1 : 'fail',
				(!empty($tmp2)) ? $tmp2 : 'fail' );
		return $captchaRes->isValid();
	}

	public function direct($recaptcha)
	{
		return $this->check($recaptcha);
	}
}
