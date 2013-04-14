<?php

class App_Model_DbTable_AlliancesProperty extends Mylib_DbTable_Cached
{

	protected $_name = 'alliances_property';
	protected $_primary = 'id_alliance';
	protected $_cacheName = 'up';
	protected $_tagsMap = array(
		'getProp' => array('up','dshelpra','ranks', 'nra'),
	);

	private $_updateCols = null;



	/*
	 * получить текущие свойства конкретного ала
	 */
	protected function notcached_getProp( $idA )
	{
		$cols = array(
			'count' => '(count_voran + count_liens + count_psol)',
			'count_colony' => '(count_colony_voran + count_colony_liens + count_colony_psol)',
			'avg_level' => 'ROUND((level_voran + level_liens + level_psol) / (count_voran + count_liens + count_psol))',
			'avg_rank_old' => 'ROUND((rank_old_voran + rank_old_liens + rank_old_psol) / (count_voran + count_liens + count_psol))',
			'avg_bo' => 'ROUND((bo_voran + bo_liens + bo_psol) / (count_voran + count_liens + count_psol))',
			'avg_nra' => 'ROUND((nra_voran + nra_liens + nra_psol) / (count_voran + count_liens + count_psol), 1)',
			'avg_ra' => 'ROUND((ra_voran + ra_liens + ra_psol) / (count_voran + count_liens + count_psol), 1)',
			'avg_rank_new' => 'ROUND((rank_new_voran + rank_new_liens + rank_new_psol) / (count_voran + count_liens + count_psol))',
			'avg_arch' => 'ROUND((archeology_voran + archeology_liens + archeology_psol) / (count_voran + count_liens + count_psol))',
			'avg_build' => 'ROUND((building_voran + building_liens + building_psol) / (count_voran + count_liens + count_psol))',
			'avg_scien' => 'ROUND((science_voran + science_liens + science_psol) / (count_voran + count_liens + count_psol))',

			'count_voran', 'count_liens', 'count_psol',
			'count_colony_voran', 'count_colony_liens', 'count_colony_psol',

			'avg_level_voran' => 'ROUND(level_voran / count_voran)',
			'avg_level_liens' => 'ROUND(level_liens / count_liens)',
			'avg_level_psol' => 'ROUND(level_psol / count_psol)',

			'avg_rank_old_voran' => 'ROUND(rank_old_voran / count_voran)',
			'avg_rank_old_liens' => 'ROUND(rank_old_liens / count_liens)',
			'avg_rank_old_psol' => 'ROUND(rank_old_psol / count_psol)',

			'avg_bo_voran' => 'ROUND(bo_voran / count_voran)',
			'avg_bo_liens' => 'ROUND(bo_liens / count_liens)',
			'avg_bo_psol' => 'ROUND(bo_psol / count_psol)',

			'avg_nra_voran' => 'ROUND(nra_voran / count_voran, 1)',
			'avg_nra_liens' => 'ROUND(nra_liens / count_liens, 1)',
			'avg_nra_psol' => 'ROUND(nra_psol / count_psol, 1)',

			'avg_ra_voran' => 'ROUND(ra_voran / count_voran, 1)',
			'avg_ra_liens' => 'ROUND(ra_liens / count_liens, 1)',
			'avg_ra_psol' => 'ROUND(ra_psol / count_psol, 1)');


		$select = $this->select()
						->from($this, $cols)
						->where("id_alliance = ?", $idA, Zend_Db::INT_TYPE)
						->limit(1);
		$result = $this->fetchRow($select);
		return (!is_null($result) ) ? $result->toArray() : null;
	}


	/*
	 * количество игроков в альянсе
	 */
	public function getPlayersCount( $idA )
	{
		$data = $this->getProp($idA);
		return (isset($data['count'])) ? (int)$data['count'] : 0;
	}

	/*
	 * количество колоний в альянсе
	 */
	public function getColonyCount( $idA )
	{
		$data = $this->getProp($idA);
		return (isset($data['count_colony'])) ? (int)$data['count_colony'] : 0;
	}

	/*
	 * множественное удаление параметров альянсов
	 * для up cli
	 */
	public function del(Array $ids)
	{
		return $this->delete( $this->_db->quoteInto('id_alliance IN (?)', $ids) );
	}


	/*
	 * добавление/обновление параметров одного альянса
	 * для up cli
	 * http://dev.mysql.com/doc/refman/5.5/en/insert-on-duplicate.html
	 */
	public function insertOrUpdate($idA, $data)
	{
		$cols = $this->_getColumsForUpdate();
		$emptyArr = array_combine($cols, array_pad(array(), count($cols), 0));
		$data = array_merge($emptyArr,$data);

		$keyStr = implode(', ', $cols);
		$valStr = implode(', ', array_pad(array(), count($cols), '?'));
		$keyValArr = array();
		foreach($cols as $col)
			$keyValArr[] = sprintf('`%s` = VALUES(%s)', $col, $col);
		$keyValStr = implode(', ', $keyValArr);

		$sql = sprintf("INSERT INTO %s (id_alliance, %s) VALUES (%d, %s) ON DUPLICATE KEY UPDATE %s",
				$this->_name, $keyStr, $idA, $valStr, $keyValStr);

		return $this->_db->query($sql, array_values($data));
	}


	/*
	 * up cli
	 */
	private function _getColumsForUpdate()
	{
		if(is_null($this->_updateCols))
			$this->_updateCols = array_diff($this->_getCols(), array('id_alliance'));
		return $this->_updateCols;
	}
}

