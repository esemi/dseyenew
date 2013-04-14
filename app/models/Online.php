<?php
/*
 * простенький мапер для доступа к текущим данным онлайна
 * берёт данные либо из кеша, либо парсит и сохраняет в кеш
 */
class App_Model_Online
{
	protected $_userAgent = null;
	protected $_errorData = array();

	public function __construct()
	{
		$this->_userAgent = Zend_Controller_Front::getInstance()->getParam('bootstrap')->getOption('botname');
	}


	public function getErrors()
	{
		return $this->_errorData;
	}

	/*
	 * получить текущий онлайн конкретной версии игры (из кеша или с сайта)
	 * @return int
	 */
	public function getCurrentOnline($href)
	{
		$count = $this->_getFromHttp($href);

		return $count;
	}

	/*
	 * получаем данные с сайта
	 * @return int | false
	 */
	protected function _getFromHttp( $href )
	{
		$prop = Zend_Controller_Front::getInstance()->getParam('bootstrap')->getOption('curl');
		$ch = curl_init($href);
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 0);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_FAILONERROR, 1);
		curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $prop['online']['conn']);
		curl_setopt($ch, CURLOPT_TIMEOUT, $prop['online']['wait']);
		curl_setopt($ch, CURLOPT_USERAGENT, $this->_userAgent);
		$response = curl_exec($ch);
		$err = curl_error($ch);
		curl_close($ch);

		if( $err != '' )
		{
			$this->_errorData[$href] = array('curl_error' => $err);
			return false;
		}

		$matches = array();
		$res = preg_match('/\("(\d+)( |").*\)/', $response, $matches);

		if( $res == 0 )
		{
			$this->_errorData[$href] = array(
				'parce_error' => 'not found',
				'response' => $response,
				'matches' => $matches);
			return false;
		}

		return (int) $matches[1];
	}

}
?>
