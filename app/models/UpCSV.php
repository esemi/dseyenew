<?php

/*
 *  модель работы с csv файлом старых рейтингов игры
 */

class App_Model_UpCSV
{

	protected
			$_currentFilePath,
			$_gziped,
			$_data = array(),
			$_info = null;

	/**
	 * загружает удалённый csv файл во временный дескриптор
	 * @TODO gzdecode in PHP 6.0?! (Oo)
	 */
	public function load($rep, $filename, $tmpPath)
	{
		$this->_gziped = (mb_substr($filename, -2) === 'gz');
		$this->_currentFilePath = "{$tmpPath}{$filename}";
		$fid = fopen($this->_currentFilePath, 'w+');

		$prop = Zend_Controller_Front::getInstance()->getParam('bootstrap')->getOption('curl');
		$ch = curl_init("{$rep}{$filename}");
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 0);
		curl_setopt($ch, CURLOPT_FAILONERROR, 1);
		curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $prop['up']['conn']);
		curl_setopt($ch, CURLOPT_TIMEOUT, $prop['up']['wait']);
		curl_setopt($ch, CURLOPT_FILE, $fid);
		curl_setopt($ch, CURLOPT_USERAGENT, Zend_Controller_Front::getInstance()->getParam('bootstrap')->getOption('botname'));
		curl_setopt($ch, CURLOPT_REFERER, $rep);
		curl_setopt($ch, CURLOPT_BINARYTRANSFER, true);
		curl_setopt($ch, CURLINFO_HEADER_OUT, true);
		curl_setopt($ch, CURLOPT_FRESH_CONNECT, true);
		curl_setopt($ch, CURLOPT_FILETIME, true);
		curl_setopt($ch, CURLOPT_DNS_USE_GLOBAL_CACHE, false);
		curl_setopt($ch, CURLOPT_DNS_CACHE_TIMEOUT, 2);

		curl_exec($ch);
		$err = curl_error($ch);
		$this->_info = curl_getinfo($ch);
		curl_close($ch);

		fclose($fid);

		if ($err !== '')
			return $err;

		return true;
	}

	/*
	 * декодит скачанный gz файл
	 */

	public function decode()
	{
		$this->_data = ($this->_gziped) ? gzfile($this->_currentFilePath) : file($this->_currentFilePath);

		if ($this->_data === false)
			return 'Ошибка при открытии файла';

		if (!is_array($this->_data))
			return 'Не массив?Оо';

		if (count($this->_data) === 0)
			return 'Файл пуст';

		return true;
	}

	/*
	 * возвращает md5 hash
	 */

	public function getMD5()
	{
		return md5_file($this->_currentFilePath);
	}

	/*
	 * вернуть массив данных
	 */
	public function getData()
	{
		return $this->_data;
	}

	public function getInfo()
	{
		return $this->_info;
	}

	public function moveCsvToArchive($worldName, $archivePath)
	{
		$newFileName = sprintf('%s_%s.%s',
				mb_strtolower( str_replace(' ', '_', $worldName ), 'utf8'),
				date('Y-m-d_H:i:s'),
				($this->_gziped) ? 'csv.gz' : 'csv');
		return copy($this->_currentFilePath, "{$archivePath}{$newFileName}");
	}
}
