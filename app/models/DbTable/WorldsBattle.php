<?php

/*
 * модель настроек боя миров (калькулятор времени раунда)
 */
class App_Model_DbTable_WorldsBattle extends Mylib_DbTable_Cached
{
	protected $_name = 'worlds_battle';
	protected $_primary = 'id_world';
	protected $_cacheName = 'default';

	/*
	 * Данные по мирам, имеющим настройки
	 * @return array | null
	 */
	public function getPropsById( $idW )
	{
		$select = $this->select()
						->where('id_world = ?', $idW, Zend_Db::INT_TYPE)
						->limit(1);
		return $this->fetchRow($select);
	}

	public function getAllIds()
	{
		$select = $this->select()
						->from($this, array('id_world'));
		$res = $this->fetchAll($select)->toArray();
		return array_map(function($x){ return $x['id_world']; }, $res);
	}

}
