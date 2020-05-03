<?php

/*
 * модель для парсинга данных о старых/новых рейтингах с сайта игры (постранично по миру)
 * единый объект для всех страниц одного парсинга (ради уникальности игровых ников)
 */
abstract class App_Model_Abstract_RemoteRanks
{
	const MAX_PAGES = 200;

	protected
		$_url = '',
		$_errorData = array(),
		$_userAgent = null,
		$_parser = null,
		$_prop = null,
		$_uniqueNiks = array();


	public function __construct( $url, $prop )
	{
		$this->_url = $url;
		$this->_userAgent = Zend_Controller_Front::getInstance()->getParam('bootstrap')->getOption('botname');
		$this->_parser = new Zend_Dom_Query();
		$this->_prop = $prop;
	}

	public function getErrors()
	{
		return $this->_errorData;
	}

	/*
	 * получить страницу со списком игроков
	 * @return false | html-source
	 */
	public function getPageSource( $num )
	{
		$url = $this->_prepareLink( $num );

		$ch = curl_init($url);
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 0);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_FAILONERROR, 1);
		curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $this->_prop['conn']);
		curl_setopt($ch, CURLOPT_TIMEOUT, $this->_prop['wait']);
		curl_setopt($ch, CURLOPT_USERAGENT, $this->_userAgent);
		$response = iconv("Windows-1251", "UTF-8",curl_exec($ch));
		$err = curl_error($ch);
		curl_close($ch);

		if( $err != '' )
		{
			$this->_errorData[] = "{$url} - {$err}";
			return false;
		}else{
			return $response;
		}

	}


	/*
	 * парсинг страницы игроков на строки
	 */
	public function parsePlayers( $source )
	{
		$pos = strpos( $source, '<table width="100%" border="0" cellpadding="0" cellspacing="1" class=ranking_table>');
		$offset = strpos( $source, '</table>', $pos+8) - $pos;
		$table = substr($source, $pos, $offset);
		$this->_parser->setDocumentHtml("<meta http-equiv=\"content-type\" content=\"text/html; charset=utf-8\">{$table}");
		return $this->_parser->query('tr.ranking_table_01, tr.ranking_table_02');
	}

	/**
	 * парсинг строки на ник и новые рейтинги
	 * @return stdClass contains data, success and errors properties
	 */
	abstract public function parsePlayerStr( $domElement );


	/*
	 * составляем ссылку для запроса страницы с игроками
	 */
	protected function _prepareLink( $num )
	{
		$offset=( $num - 1 ) * 25;
		return "{$this->_url}&offset={$offset}";
	}

	protected function _addUniqNik($nik)
	{
		$this->_uniqueNiks[] = $nik;
	}

	protected function _checkUniqNik($str)
	{
		return !in_array($str, $this->_uniqueNiks);
	}

	protected function _checkNik($str)
	{
		return preg_match('/^[\wА-Яа-яёЁ\s.\-]{3,50}$/ui',$str);
	}
	protected function _checkRankOld($str)
	{
		return preg_match('/^[\d]{1,10}$/',$str);
	}
	protected function _checkRankNew($str)
	{
		return preg_match('/^[\d]{1,10}$/',$str);
	}
	protected function _checkBo($str)
	{
		$tmp = floatval($str);
		return ( $tmp>=0 && $tmp <= 999999999.99 );
	}
	protected function _checkLevel($str)
	{
		return preg_match('/^[\d]{1,5}$/',$str);
	}
	protected function _checkLiga($str)
	{
		return in_array($str, array('I','II','III'));
	}
	protected function _checkArch($str)
	{
		return preg_match('/^[\d]{1,9}$/',$str);
	}
	protected function _checkBuild($str)
	{
		return preg_match('/^[\d]{1,9}$/',$str);
	}
	protected function _checkScien($str)
	{
		return preg_match('/^[\d]{1,9}$/',$str);
	}
}
