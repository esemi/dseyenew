<?php

/**
 * Клиент для общения с игрой
 */
class App_Model_GameClient
{

	protected $_RINGS = array(1 => 'RING_TECH', 2 => 'RING_BIO', 3 => 'RING_PSI');

	protected
			$_log,
			$_timeoutLogin,
			$_timeoutOther,
			$_userAgent,
			$_curl = null,
			$_url = '',
			$_cookieFilename,
			$_sidix = '',
			$_ck = '',
			$_lastComplData = '';


	public function __construct($url, $log)
	{
		$this->_userAgent = Zend_Controller_Front::getInstance()->getParam('bootstrap')->getOption('botname');

		$timeouts = Zend_Controller_Front::getInstance()->getParam('bootstrap')->getOption('curl');
		$this->_timeoutLogin = $timeouts['gameClient']['login'];
		$this->_timeoutOther = $timeouts['gameClient']['other'];

		$this->_curlInit();

		$this->setGameUrl($url);
		$this->setLogger($log);
	}

	public function __destruct()
	{
		$this->_curlClose();
	}

	public function setGameUrl($url)
	{
		$this->_url = $url;
	}
	public function setLogger($log)
	{
		$this->_log = $log;
	}

	public function doEnter($login, $pass, $uiid, $reset=false)
	{
		if( $reset === true )
			$this->_curlReset();

		//логинимся
		$this->_log->add('логинимся');
		$res = $this->login($login, $pass);
		if( $res !== true )
		{
			$this->_log->add('не смогли залогиниться');
			return false;
		}

		//чекинимся в мире
		$this->_log->add('чекинимся');
		$res = $this->checkin($uiid);
		if( $res !== true )
		{
			$this->_log->add('не смогли зачекиниться в мире');
			return false;
		}
		return true;
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
			$this->_log->add(curl_error($this->_curl));
			return false;
		}

		$res = $this->_parseLoginResponse($result);
		if( $res !== true ){
			$this->_log->add('Not parsed login response');
			$this->_log->add($result);
			return false;
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
			$this->_log->add(curl_error($this->_curl));
			return false;
		}

		$res = $this->_parseCheckinResponse($result);
		if( $res !== true ){
			$this->_log->add('Not parsed checkin response');
			$this->_log->add($result);
			return false;
		}

		return true;
	}

	public function viewCompl($ringNum, $complNum)
	{
		curl_setopt($this->_curl, CURLOPT_URL, $this->_getViewComplUrl());
		curl_setopt($this->_curl, CURLOPT_CONNECTTIMEOUT, $this->_timeoutOther);
		curl_setopt($this->_curl, CURLOPT_TIMEOUT, $this->_timeoutOther);
		curl_setopt($this->_curl, CURLOPT_POSTFIELDS, array(
			'ck' => $this->_ck,
			'onLoad' => '[type Function]',
			'xmldata' => sprintf('<complexview direction="" ring="%s" complexnum="%d"/>',
					$this->_RINGS[$ringNum],
					$complNum)
		));
		$result = curl_exec($this->_curl);
		if( $result === false ){
			$this->_log->add(curl_error($this->_curl));
			return false;
		}

		$res = $this->_parseViewCompl($result);
		if( $res !== true ){
			$this->_log->add('Not parsed view compl response');
			$this->_log->add($result);
			return false;
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
			$this->_log->add('login matches');
			$this->_log->add($matches);
			return true;
		}

		return false;
	}

	protected function _parseCheckinResponse($headers)
	{
		$matches = array();
		if( preg_match('/Location:\s\.\.\/ds\/index.php\?ck=([\d\w]{10})&/iu', $headers, $matches) )
		{
			$this->_log->add('checkin matches');
			$this->_log->add($matches);
			$this->_ck = $matches[1];
			return true;
		}
		return false;
	}

	protected function _parseViewCompl($content)
	{
		$matches = array();
		$complData = array();
		if(
				mb_strpos($content, '<key valid="1"') !== false &&
				preg_match('/&ck=([\d\w]{10})&loadkey=/iu', $content, $matches) &&
				preg_match('/<combcomplex.*<\/combcomplex>/iu', $content, $complData)
		){
			$this->_log->add('compl matches');
			$this->_log->add(array($matches, $complData));
			$this->_ck = $matches[1];
			$this->_lastComplData = $complData[0];

			return true;
		}
		return false;
	}

	/**
	 * Определяет следующие статусы:  защита новичка, силовой щит, защита богов, премиум.
	 * @return array
	 */
	public function parseComplData()
	{
		$data = array();
		$dom = new Zend_Dom_Query('<?xml version="1.0" encoding="utf-8"?>' . $this->_lastComplData);
		$res = $dom->queryXpath('/combcomplex/comb');
		if( count($res) !== 6 ){
			return false;
		}

		foreach( $res as $compl )
		{
			$nik = $compl->getAttribute('uname');
			if( empty($nik) ){
				continue;
			}

			$player = new stdClass();
			$player->nik = $nik;
			$player->shield = false;
			$player->newbee = false;
			$player->ban = false;
			$player->premium = false;

			//щит/дом отпуска
			if( $compl->hasAttribute('freez') &&  $compl->getAttribute('freez') == 1 )
				$player->shield = true;

			//защита новичка
			if( $compl->hasAttribute('newbee') &&  $compl->getAttribute('newbee') == 1 )
				$player->newbee = true;

			//защита богов/бан
			if( $compl->hasAttribute('cban') &&  $compl->getAttribute('cban') == 1 )
				$player->ban = true;

			//премиум
			if( $compl->hasAttribute('premic') &&  $compl->getAttribute('premic') == 1 )
				$player->premium = true;

			$data[] = $player;
		}
		return $data;
	}

	protected function _getLoginUrl()
	{
		return "{$this->_url}ds/index_login.php";
	}
	protected function _getCheckinUrl()
	{
		return "{$this->_url}ds/index_start.php";
	}
	protected function _getViewComplUrl()
	{
		return "{$this->_url}ds/useraction.php?SIDIX={$this->_sidix}";
	}

	protected function _curlInit()
	{
		$this->_cookieFilename = Zend_Controller_Front::getInstance()->getParam('bootstrap')->getOption('tmp_folder') . uniqid('cookie') . '.txt';

		$this->_curl = curl_init();
		curl_setopt($this->_curl, CURLOPT_FAILONERROR, true);
		curl_setopt($this->_curl, CURLOPT_HEADER, true);
		curl_setopt($this->_curl, CURLOPT_RETURNTRANSFER, true);
		//curl_setopt($this->_curl, CURLOPT_FOLLOWLOCATION, true);
		//curl_setopt($this->_curl, CURLOPT_VERBOSE, true);
		curl_setopt($this->_curl, CURLOPT_POST, true);
		curl_setopt($this->_curl, CURLOPT_USERAGENT, $this->_userAgent);
		curl_setopt($this->_curl, CURLOPT_COOKIEFILE, $this->_cookieFilename);
		curl_setopt($this->_curl, CURLOPT_COOKIEJAR, $this->_cookieFilename);
	}
	protected function _curlClose()
	{
		if( !is_null($this->_curl) )
			curl_close($this->_curl);

		if( file_exists($this->_cookieFilename) )
			unlink($this->_cookieFilename);
	}
	protected function _curlReset()
	{
		$this->_curlClose();
		$this->_curlInit();
	}
}
?>