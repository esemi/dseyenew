<?php

/*
 * модель статистики миров
 * @TODO единая выборка статистики с последующим распарсингом на графики
 */
class App_Model_DbTable_StatWorlds extends App_Model_Abstract_StatGeneral
{
	protected $_name = 'stat_worlds';
	protected $_primary = array('id_world','date_create');
	protected $_tagsMap = array(
		'getMinStatDate' => array('day'),
		'getIOByMonth' => array('day'),
		'getIOByAllTime' => array('day'),
		'getCountPlayers' => array('day'),
		'getCountColonies' => array('day'),
		'getCountAlliances' => array('day'),
		'getCountPremium' => array('day'),
		'getCountNotAvaliableGate' => array('day'),
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

	public function addStat($idW, Array $data)
	{
		return $this->insert(
						array_merge(
							$data,
							array('id_world'=>$idW, 'date_create' => new Zend_Db_expr('CURRENT_DATE') )
							));
	}

	protected function _addItemWhere($idI, $select)
	{
		$select->where('id_world = ?', $idI, Zend_Db::INT_TYPE);
	}

	/*
	 * максимальный отпуск для истории изменений
	 */
	protected function notcached_getMinStatDate($idW)
	{
		$select = $this->select()
						->from($this, array('date' => "DATE_FORMAT(MIN(date_create), '%d-%m-%Y')"));
		$this->_addItemWhere($idW, $select);
		$res = $this->fetchRow($select);
		return $res['date'];
	}

	/*
	 * пришли/ушли за последний месяц (по дням)
	 */
	protected function notcached_getIOByMonth( $idW )
	{
		$select = $this->select();
		$this->_addItemWhere($idW, $select);

		$select->from($this, array(
			'input','output',
			'date' => "DATE_FORMAT( `date_create` , '%d.%m.%Y' )" ))
				->where('`date_create` >= CURRENT_DATE - INTERVAL 1 MONTH')
				->order('date_create ASC');

		return $this->fetchAll($select)->toArray();
	}

	/*
	 * пришли/ушли за всё время (по месяцам)
	 */
	protected function notcached_getIOByAllTime( $idW )
	{
		$select = $this->select();
		$this->_addItemWhere($idW, $select);

		$select->from($this, array(
			'input' => 'SUM(input)', 'output' => 'SUM(output)',
			'date' => "DATE_FORMAT( `date_create` , '%m.%Y' )" ))
				->group("DATE_FORMAT( `date_create` , '%m.%Y' )")
				->order('date_create ASC');

		return $this->fetchAll($select)->toArray();
	}

	/*
	 * количество альянсов
	 */
	protected function notcached_getCountAlliances($idW)
	{
		$select = $this->select()
						->from($this, array('value' => 'count_alliance', 'date' => "DATE_FORMAT( `date_create` , '%d.%m.%Y' )" ))
						->order('date_create ASC');
		$this->_addItemWhere($idW, $select);
		return $this->fetchAll($select)->toArray();
	}


}