<?php

/**
 * Кеширующая прослойка для моделей zend_db_table
 */
abstract class Mylib_DbTable_Cached extends Zend_Db_Table_Abstract
{
	protected $_cache = null, //slow cache with tags
			  $_tagsMap = array(),//'method' => array([tag1, tag2, ...])
			  $_cacheName = null;

	public function init()
	{
		parent::init();

		if(is_null($this->_cacheName))
			throw new Exception("Cache name for {$this->_name} Table not found");

		$this->_cache = Zend_Controller_Front::getInstance()
							->getParam('bootstrap')
							->getResource('cachemanager')
							->getCache($this->_cacheName);
	}

	protected function _getFromCache($method, $args)
	{
		$signature = "{$this->_name}_{$method}_" . md5(serialize($args)); //сигнатура метода по опциям
		$methodDB = "notcached_{$method}"; //имя метода обращения к ДБ

		//if( APPLICATION_ENV === 'cli' ) printf("Cached method by CLI %s->%s\n", $this->_name, $methodDB);

		if( !method_exists($this, $methodDB) )
			throw new Exception("Method {$methodDB} for {$this->_name} table not found. Cache layer fault.");

		//теги
		$tags = array($this->_name);
		if(isset($this->_tagsMap[$method]))
			$tags = array_merge( $tags, $this->_tagsMap[$method] );

		if( !( $data = $this->_cache->load($signature) ) )
		{
			$data = call_user_func_array(array($this, $methodDB), $args);
			$this->_cache->save($data, $signature, $tags);
		}

		return $data;
	}

	public function __call($method, $args)
	{
		return $this->_getFromCache($method, $args);
	}
}
