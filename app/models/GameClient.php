<?php

/**
 * Клиент для общения с игрой
 */
class App_Model_GameClient
{

	protected
			$_timeoutLogin,
			$_timeoutOther,
			$_userAgent,
			$_curl,
			$_url = '',
			$_cookieFilename,
			$_sidix = '',
			$_ck = '';


	public function __construct()
	{
		$this->_userAgent = Zend_Controller_Front::getInstance()->getParam('bootstrap')->getOption('botname');

		$timeouts = Zend_Controller_Front::getInstance()->getParam('bootstrap')->getOption('curl');
		$this->_timeoutLogin = $timeouts['gameClient']['login'];
		$this->_timeoutOther = $timeouts['gameClient']['other'];

		$this->_curlInit();
	}

	public function __destruct()
	{
		$this->_curlClose();
	}

	public function setGameUrl($url)
	{
		$this->_url = $url;
	}

	public function login($login, $pass)
	{
		curl_setopt($this->_curl, CURLOPT_URL, $this->_getLoginUrl());
		curl_setopt($this->_curl, CURLOPT_CONNECTTIMEOUT, $this->_timeoutLogin);
		curl_setopt($this->_curl, CURLOPT_TIMEOUT, $this->_timeoutLogin);
		curl_setopt($this->_curl, CURLOPT_REFERER, $this->_url);
		curl_setopt($this->_curl, CURLOPT_POSTFIELDS, array(
			'enter' => 'Login',
			'login' => $login,
			'pass' => $pass,
			'x' => 6,
			'y' => 28
		));
		$result = curl_exec($this->_curl);
		if( $result === false ){
			return curl_error($this->_curl);
		}

		$res = $this->_parseLoginResponse($result);
		if( $res !== true ){
			return 'Not parsed login response';
		}

		return true;
	}

	public function checkin($uiid)
	{
		curl_setopt($this->_curl, CURLOPT_URL, $this->_getCheckinUrl());
		curl_setopt($this->_curl, CURLOPT_CONNECTTIMEOUT, $this->_timeoutLogin);
		curl_setopt($this->_curl, CURLOPT_TIMEOUT, $this->_timeoutLogin);
		curl_setopt($this->_curl, CURLOPT_REFERER, $this->_getCheckinUrl() . '?ck=' . $this->_ck);
		curl_setopt($this->_curl, CURLOPT_POSTFIELDS, array(
			'ck' => $this->_ck,
			'start' => 1,
			'uiid' => $uiid
		));
		$result = curl_exec($this->_curl);
		if( $result === false ){
			return curl_error($this->_curl);
		}

		$res = $this->_parseCheckinResponse($result);
		if( $res !== true ){
			return 'Not parsed checkin response';
		}

		return true;
	}

	protected function _parseLoginResponse($headers)
	{
		$matches = array();
		if( preg_match('/Location:\sindex_start.php\?ck=([\d\w]{10})&SIDIX=([\w\d]{26})/iu', $headers, $matches) )
		{
			$this->_ck = $matches[1];
			$this->_sidix = $matches[2];
			return true;
		}
		return false;
	}

	protected function _parseCheckinResponse($headers)
	{
		$matches = array();
		var_dump($headers);
		if( preg_match('/Location:\s\.\.\/ds\/index.php\?ck=([\d\w]{10})&/iu', $headers, $matches) )
		{
			var_dump($matches);
			$this->_ck = $matches[1];
			return true;
		}
		return false;
	}

	protected function _getLoginUrl()
	{
		return "{$this->_url}ds/index_login.php";
	}

	protected function _getCheckinUrl()
	{
		return "{$this->_url}ds/index_start.php";
	}

	protected function _curlInit()
	{
		$this->_cookieFilename = Zend_Controller_Front::getInstance()->getParam('bootstrap')->getOption('tmp_folder') . uniqid('cookie') . '.txt';

		$this->_curl = curl_init();
		curl_setopt($this->_curl, CURLOPT_FAILONERROR, true);
		curl_setopt($this->_curl, CURLOPT_HEADER, true);
		curl_setopt($this->_curl, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($this->_curl, CURLOPT_POST, true);
		curl_setopt($this->_curl, CURLOPT_USERAGENT, $this->_userAgent);
		curl_setopt($this->_curl, CURLOPT_COOKIEFILE, $this->_cookieFilename);
		curl_setopt($this->_curl, CURLOPT_COOKIEJAR, $this->_cookieFilename);
	}

	protected function _curlClose()
	{
		curl_close($this->_curl);
		unlink($this->_cookieFilename);
	}



}
?>
