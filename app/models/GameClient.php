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
			$_sessid = '',
			$_ck = '',
			$_uiid = '',
			$_lastComplData = '',
			$_uniqNiks = array();


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

	public function doEnter($login, $pass, $reset=false)
	{
		if( $reset === true ){
			$this->_curlReset();
		}

		//логинимся
		$this->_log->add('логинимся');
		$res = $this->login($login, $pass);
		if( $res !== true )
		{
			$this->_log->add('не смогли залогиниться');
			return false;
		}

		//получаем uiid
		$this->_log->add('получаем uiid');
		$res = $this->getUIID();
		if( $res !== true )
		{
			$this->_log->add('не смогли получить UIID мира');
			return false;
		}

		//чекинимся в мире
		$this->_log->add('чекинимся');
		$res = $this->checkin();
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
			'x' => rand(1, 18),
			'y' => rand(1, 53)
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

	public function getUIID()
	{
		curl_setopt($this->_curl, CURLOPT_URL, $this->_getUiidUrl());
		curl_setopt($this->_curl, CURLOPT_CONNECTTIMEOUT, $this->_timeoutLogin);
		curl_setopt($this->_curl, CURLOPT_TIMEOUT, $this->_timeoutLogin);
		curl_setopt($this->_curl, CURLOPT_REFERER, $this->_url);
		$result = curl_exec($this->_curl);
		if( $result === false ){
			$this->_log->add(curl_error($this->_curl));
			return false;
		}

		$result = iconv("Windows-1251", "UTF-8//IGNORE", $result);
		$res = $this->_parseUiidResponse($result);
		if( $res !== true ){
			$this->_log->add('Not parsed UIID response');
			$this->_log->add($result);
			return false;
		}

		return true;
	}

	public function checkin()
	{
		curl_setopt($this->_curl, CURLOPT_URL, $this->_getCheckinUrl());
		curl_setopt($this->_curl, CURLOPT_CONNECTTIMEOUT, $this->_timeoutLogin);
		curl_setopt($this->_curl, CURLOPT_TIMEOUT, $this->_timeoutLogin);
		curl_setopt($this->_curl, CURLOPT_REFERER, $this->_getUiidUrl());
		curl_setopt($this->_curl, CURLOPT_POSTFIELDS, array(
			'ck' => $this->_ck,
			'start' => 1,
			'uiid' => $this->_uiid
		));

		$result = curl_exec($this->_curl);
		if( $result === false ){
			$this->_log->add(curl_error($this->_curl));
			return false;
		}

		$params = $this->_parseQueueResponse($result);
		if( ! is_array($params) ){
			$checkinSource = $result;
		}else{
			$checkinSource = $this->_queueRequest($params);
		}

		$res = $this->_parseCheckinResponse($checkinSource);
		if( $res !== true ){
			$this->_log->add('Not parsed checkin response');
			$this->_log->add($checkinSource);
			return false;
		}

		return true;
	}

	protected function _queueRequest($params){
		$this->_log->add('process queue redirect');
		$url = $this->_getQueueUrl() . '?' . http_build_query(array(
			'ck' => $this->_ck,
			'SIDIX' => $this->_sessid,
			'VERS' => $params[2],
			'sport' => $params[3]
		));
		$this->_log->add($url);
		$this->_log->add(array(
			'ck' => $this->_ck,
			'SIDIX' => $this->_sessid,
			'VERS' => $params[2],
			'sport' => $params[3]
		));
		$this->_log->add(http_build_query(array(
			'ck' => $this->_ck,
			'SIDIX' => $this->_sessid,
			'VERS' => $params[2],
			'sport' => $params[3]
		)));

		curl_setopt($this->_curl, CURLOPT_URL, $url);
		curl_setopt($this->_curl, CURLOPT_CONNECTTIMEOUT, $this->_timeoutLogin);
		curl_setopt($this->_curl, CURLOPT_TIMEOUT, $this->_timeoutLogin);
		curl_setopt($this->_curl, CURLOPT_REFERER, $this->_getUiidUrl());

		$result = curl_exec($this->_curl);
		if( $result === false ){
			$this->_log->add(curl_error($this->_curl));
			return false;
		}else{
			return $result;
		}
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

		$result = iconv("Windows-1251", "UTF-8//IGNORE", $result);
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
			$this->_sessid = $matches[2];
			$this->_log->add('login matches');
			$this->_log->add($matches);
			return true;
		}

		return false;
	}

	protected function _parseUiidResponse($content)
	{
		$matchesUiid = array();
		$matchesCk = array();
		if(
				mb_strpos($content, 'name="uiid"') !== false &&
				preg_match('/value="(\d{1,7}\_\d{1,4}\_\d{1,4})"/iu', $content, $matchesUiid) &&
				preg_match('/name="ck"\svalue="([\d\w]{10})"/iu', $content, $matchesCk)
		){
			$this->_log->add('uiid and ck matches');
			$this->_log->add($matchesUiid);
			$this->_log->add($matchesCk);
			$this->_uiid = $matchesUiid[1];
			$this->_ck = $matchesCk[1];

			return true;
		}
		return false;
	}

	protected function _parseQueueResponse($headers)
	{
		$matches = array();
		if( preg_match('/Location:\s\.\.\/ds\/index_queue.php\?ck=([\d\w]{10})&VERS=(\w+)&sport=(\d+)/iu', $headers, $matches) )
		{
			$this->_log->add('found queue matches');
			$this->_log->add($matches);
			$this->_ck = $matches[1];
			return $matches;
		}
		return false;
	}

	protected function _parseCheckinResponse($headers)
	{
		$matches = array();
		if( preg_match('/Location:\s(\.\.\/ds\/)?index.php\?ck=([\d\w]{10})&/iu', $headers, $matches) )
		{
			$this->_log->add('checkin matches');
			$this->_log->add($matches);
			$this->_ck = $matches[2];
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
			if( empty($nik) || in_array($nik, $this->_uniqNiks) ){
				continue;
			}

			$this->_uniqNiks[] = $nik;

			$player = new stdClass();
			$player->nik = $nik;
			$player->gate = false;
			$player->shield = false;
			$player->newbee = false;
			$player->ban = false;
			$player->premium = false;

			//ворота
			if( $compl->hasAttribute('gate') &&  $compl->getAttribute('gate') == 1 )
				$player->gate = true;

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
	protected function _getUiidUrl()
	{
		return "{$this->_url}ds/index_start.php?" . http_build_query(array(
			'ck' => $this->_ck,
			'SIDIX' => $this->_sessid));
	}
	protected function _getCheckinUrl()
	{
		return "{$this->_url}ds/index_start.php";
	}

	protected function _getQueueUrl(){
		return "{$this->_url}ds/index_queue.php";
	}

	protected function _getViewComplUrl()
	{
		return "{$this->_url}ds/useraction.php?SIDIX={$this->_sessid}";
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