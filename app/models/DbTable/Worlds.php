<?php

/*
 * модель миров
 */

class App_Model_DbTable_Worlds extends Mylib_DbTable_Cached
{

	protected $_name = 'worlds';
	protected $_cacheName = 'up';

	/*
	 * валидация мира по id
	 */
	public function validate( $idW )
	{
		$select = $this->select()
						->from($this, array( 'id' ))
						->where('id = ?', $idW, Zend_Db::INT_TYPE)
						->limit(1);
		$result = $this->fetchRow($select);
		return !is_null($result);
	}

	/*
	 * аналог find
	 * cli
	 */
	public function notcached_getData( $idW )
	{
		$select = $this->select()
						->from($this, array(
				'id_version','name','type','intro',
				'date_create' => 'DATE_FORMAT(`date_create` , \'%d.%m.%Y\')',
				'date_birth' => 'DATE_FORMAT(`date_birth` , \'%d.%m.%Y\')'))
						->where('id = ?', $idW, Zend_Db::INT_TYPE)
						->limit(1);
		return $this->fetchRow($select)->toArray();
	}

	/*
	 * имя мира
	 */
	public function getName( $idW )
	{
		$data = $this->getData($idW);
		return $data['name'];
	}

	/*
	 * версия игры мира
	 */
	public function getVersion( $idW )
	{
		$data = $this->getData($idW);
		return $data['id_version'];
	}

	/*
	 * получить список миров
	 */
	public function notcached_listing()
	{
		$select = $this->select()
						->from($this, array( 'id', 'name' ));
		$this->_sortWorld($select);
		return $this->fetchAll($select);
	}

	protected function _sortWorld($select)
	{
		$select->order('Field(id, "11", "20", "27", "7", "6", "28", "16", "8" ,"10" ,"18", "30") DESC')
				->order('id_version')
				->order('id');
	}
}
