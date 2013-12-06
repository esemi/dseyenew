<?php

/*
 * модель рас
 */
class App_Model_DbTable_Rases extends Mylib_DbTable_Cached
{

	protected $_name = 'rases';
	protected $_cacheName = 'long';

	/*
	 * получить все рассы
	 */
	public function notcached_getAll()
	{
		return $this->fetchAll()->toArray();
	}

	/*
	 * рассы доступные для поиска в формате id-name
	 */
	public function getRasesForSearch()
	{
		$data = $this->getAll();
		$out = array();
		foreach($data as $rase)
			$out[$rase['id']] = $rase['name'];
		return $out;
	}

	/*
	 * кольца доступные для карты
	 */
	public function getRings()
	{
		return array(4 => 'Мельсион', 1 => 'Ворания', 2 => 'Лиенсорд', 3 => 'Псолеон');
	}

	/*
	 * up cli
	 * @todo replace into DB
	 */
	public function getRasePrefix($idR)
	{
		$rases = array(1 => 'voran', 2 => 'liens', 3 => 'psol');
		return isset($rases[$idR]) ? $rases[$idR] : null;
	}
}
