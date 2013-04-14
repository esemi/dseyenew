<?php

class App_Model_DbTable_WorldsProperty extends Mylib_DbTable_Cached
{

	protected $_name = 'worlds_property';
	protected $_primary = 'id_world';
	protected $_cacheName = 'up';
	protected $_tagsMap = array(
		'getProp' => array('up','dshelpra','ranks'),
		'getAllPlayersCount' => array('up'),
	);

	private $_updateCols = null;


	/*
	 * добавление/обновление параметров одного мира
	 * для up cli
	 * http://dev.mysql.com/doc/refman/5.5/en/insert-on-duplicate.html
	 */
	public function insertOrUpdate($idW, $data)
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

		$sql = sprintf("INSERT INTO %s (id_world, %s) VALUES (%d, %s) ON DUPLICATE KEY UPDATE %s",
				$this->_name, $keyStr, $idW, $valStr, $keyValStr);

		return $this->_db->query($sql, array_values($data));
	}


	/*
	 * up cli
	 */
	private function _getColumsForUpdate()
	{
		if(is_null($this->_updateCols))
			$this->_updateCols = array_diff($this->_getCols(), array('id_world'));
		return $this->_updateCols;
	}


	/*
	 * данные по количеству игроков во всех мирах
	 */
	protected function notcached_getAllPlayersCount()
	{
		$select = $this->select()
						->setIntegrityCheck(false)
						->from($this, array('id_world', 'count' => '(`count_voran` + `count_liens` + `count_psol`)'))
						->join('worlds','id = id_world',array('name'))
						->order('count DESC');
		return $this->fetchAll($select)->toArray();
	}


	/*
	 * получить текущие свойства конкретного мира
	 */
	protected function notcached_getProp( $idW )
	{
		$select = $this->select()
						->from($this, array(
							'compls_voran', 'compls_liens', 'compls_psol', 'compls_mels',
							'great_compl' => 'GREATEST( `compls_voran` , `compls_liens` , `compls_psol` , `compls_mels` )',

							'count' => '(count_voran + count_liens + count_psol)',
							'count_voran', 'count_liens', 'count_psol',

							'count_alliance',

							'count_colony_voran', 'count_colony_liens', 'count_colony_psol',
							'count_colony' => '(count_colony_voran + count_colony_liens + count_colony_psol)',

							'avg_level' => 'ROUND((level_voran + level_liens + level_psol) / (count_voran + count_liens + count_psol))',
							'avg_level_voran' => 'ROUND(level_voran / count_voran)',
							'avg_level_liens' => 'ROUND(level_liens / count_liens)',
							'avg_level_psol' => 'ROUND(level_psol / count_psol)',

							'avg_rank_old' => 'ROUND((rank_old_voran + rank_old_liens + rank_old_psol) / (count_voran + count_liens + count_psol))',
							'avg_rank_old_voran' => 'ROUND(rank_old_voran / count_voran)',
							'avg_rank_old_liens' => 'ROUND(rank_old_liens / count_liens)',
							'avg_rank_old_psol' => 'ROUND(rank_old_psol / count_psol)',

							'avg_rank_new' => 'ROUND((rank_new_voran + rank_new_liens + rank_new_psol) / (count_voran + count_liens + count_psol))',
							'avg_rank_new_voran' => 'ROUND(rank_new_voran / count_voran)',
							'avg_rank_new_liens' => 'ROUND(rank_new_liens / count_liens)',
							'avg_rank_new_psol' => 'ROUND(rank_new_psol / count_psol)',

							'avg_bo' => 'ROUND((bo_voran + bo_liens + bo_psol) / (count_voran + count_liens + count_psol))',
							'avg_bo_voran' => 'ROUND(bo_voran / count_voran)',
							'avg_bo_liens' => 'ROUND(bo_liens / count_liens)',
							'avg_bo_psol' => 'ROUND(bo_psol / count_psol)',

							'avg_nra' => 'ROUND((nra_voran + nra_liens + nra_psol) / (count_voran + count_liens + count_psol), 1)',
							'avg_nra_voran' => 'ROUND(nra_voran / count_voran, 1)',
							'avg_nra_liens' => 'ROUND(nra_liens / count_liens, 1)',
							'avg_nra_psol' => 'ROUND(nra_psol / count_psol, 1)',

							'avg_ra' => 'ROUND((ra_voran + ra_liens + ra_psol) / (count_voran + count_liens + count_psol), 1)',
							'avg_ra_voran' => 'ROUND(ra_voran / count_voran, 1)',
							'avg_ra_liens' => 'ROUND(ra_liens / count_liens, 1)',
							'avg_ra_psol' => 'ROUND(ra_psol / count_psol, 1)',

							'avg_arch' => 'ROUND((archeology_voran + archeology_liens + archeology_psol) / (count_voran + count_liens + count_psol))',
							'avg_arch_voran' => 'ROUND(archeology_voran / count_voran)',
							'avg_arch_liens' => 'ROUND(archeology_liens / count_liens)',
							'avg_arch_psol' => 'ROUND(archeology_psol / count_psol)',

							'avg_build' => 'ROUND((building_voran + building_liens + building_psol) / (count_voran + count_liens + count_psol))',
							'avg_build_voran' => 'ROUND(building_voran / count_voran)',
							'avg_build_liens' => 'ROUND(building_liens / count_liens)',
							'avg_build_psol' => 'ROUND(building_psol / count_psol)',

							'avg_scien' => 'ROUND((science_voran + science_liens + science_psol) / (count_voran + count_liens + count_psol))',
							'avg_scien_voran' => 'ROUND(science_voran / count_voran)',
							'avg_scien_liens' => 'ROUND(science_liens / count_liens)',
							'avg_scien_psol' => 'ROUND(science_psol / count_psol)',

							'people_voran' => 'ROUND( count_voran / ( compls_voran * 6) *100 , 1 )',
							'people_liens' => 'ROUND( count_liens / ( compls_liens * 6) *100 , 1 )',
							'people_psol'  => 'ROUND( count_psol / ( compls_psol * 6) *100 , 1 )',
							'people_mels'  => 'ROUND( (count_colony_voran + count_colony_liens + count_colony_psol) / ( compls_mels * 6) *100 , 1 )'

							))
						->where("id_world = ?", $idW, Zend_Db::INT_TYPE)
						->limit(1);
		$result = $this->fetchRow($select);
		return (!is_null($result) ) ? $result->toArray() : null;
	}

	/*
	 * получить общее количество живых игроков в мире ()
	 */
	public function getPlayersCount( $idW )
	{
		$data = $this->getProp($idW);

		return (isset($data['count'])) ? (int)$data['count'] : 0;
	}


	/*
	 * получить количество активных альянсов в мире
	 */
	public function getAllianceCount( $idW )
	{
		$data = $this->getProp($idW);

		return (isset($data['count_alliance'])) ? (int)$data['count_alliance'] : 0;
	}


	/*
	 * получить максимальный комплекс на кольце ()
	 */
	public function getMaxComple( $idW, $idRing )
	{
		$colums = array('compls_voran', 'compls_liens', 'compls_psol', 'compls_mels');
		$data = $this->getProp($idW);
		$index = $colums[$idRing-1];

		return (isset($data[$index])) ? (int)$data[$index] : 0;
	}


	/*
	 * выбираем максимальный компл в мире
	 */
	public function getGreatCompl( $idW )
	{
		$data = $this->getProp($idW);

		return (isset($data['great_compl'])) ? (int)$data['great_compl'] : 0;
	}

}

