<?php

/*
 * модель статистики альянсов
 */
class App_Model_DbTable_StatAlliances extends App_Model_Abstract_StatGeneral
{
	protected $_name = 'stat_alliances';
	protected $_primary = array('id_alliance','date_create');
	protected $_tagsMap = array(
		'getCountPlayers' => array('day'),
		'getCountColonies' => array('day'),
		'getSumRankOld' => array('day'),
		'getAvgRankOld' => array('day'),
		'getSumRankNew' => array('day'),
		'getAvgRankNew' => array('day'),
		'getSumBO' => array('day'),
		'getAvgBO' => array('day'),
		'getSumRA' => array('day'),
		'getAvgRA' => array('day'),
		'getSumNRA' => array('day'),
		'getAvgNRA' => array('day'),
		'getAvgLevel' => array('day'),
		'getSumArch' => array('day'),
		'getAvgArch' => array('day'),
		'getSumBuild' => array('day'),
		'getAvgBuild' => array('day'),
		'getSumScien' => array('day'),
		'getAvgScien' => array('day'),
	);


	protected function _addItemWhere($idI, $select)
	{
		$select->where('id_alliance = ?', $idI, Zend_Db::INT_TYPE);
	}

	public function addStat(Array $data)
	{
		foreach($data as $idA => $row)
			$this->insert(
						array_merge(
							$row,
							array('id_alliance'=>$idA, 'date_create' => new Zend_Db_expr('CURRENT_DATE') )
							));
	}

}