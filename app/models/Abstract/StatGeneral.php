<?php

/*
 * абстрактный класс для моделей статистики миров и альянсов
 */
abstract class App_Model_Abstract_StatGeneral extends Mylib_DbTable_Cached
    implements App_Model_Interface_Clearable
{
    protected $_cacheName = 'up';

    final public function clearOld( $days )
    {
        return $this->delete( $this->_db->quoteInto( 'date_create < CURDATE() - INTERVAL ? DAY', $days, Zend_Db::INT_TYPE ) );
    }

    abstract protected function _addItemWhere($idI,$select);

	/*
	 * количество игроков
	 */
	protected function notcached_getCountPlayers( $idI )
	{
		$select = $this->select()
						->from($this, array(
							'all' => '(count_voran + count_liens + count_psol)',
							'voran' => 'count_voran',
							'liens' => 'count_liens',
							'psol' => 'count_psol',
							'date' => "DATE_FORMAT( `date_create` , '%d.%m.%Y' )" ))
						->order('date_create ASC');
		$this->_addItemWhere($idI, $select);
		return $this->fetchAll($select)->toArray();
}

	/*
	 * количество колоний
	 */
	protected function notcached_getCountColonies( $idI )
	{
		$select = $this->select()
						->from($this, array(
							'all' => '(count_colony_voran + count_colony_liens + count_colony_psol)',
							'voran' => 'count_colony_voran',
							'liens' => 'count_colony_liens',
							'psol' => 'count_colony_psol',
							'date' => "DATE_FORMAT( `date_create` , '%d.%m.%Y' )" ))
						->order('date_create ASC');
		$this->_addItemWhere($idI, $select);
		return $this->fetchAll($select)->toArray();
	}

	/*
	 * общий старый рейтинг
	 */
	protected function notcached_getSumRankOld($idI)
	{
		$select = $this->select()
						->from($this, array(
							'all' => '(rank_old_voran + rank_old_liens + rank_old_psol)',
							'voran' => 'rank_old_voran',
							'liens' => 'rank_old_liens',
							'psol' => 'rank_old_psol',
							'date' => "DATE_FORMAT( `date_create` , '%d.%m.%Y' )" ))
						->order('date_create ASC');
		$this->_addItemWhere($idI, $select);
		return $this->fetchAll($select)->toArray();
	}

	/*
	 * средний старый рейтинг
	 */
	protected function notcached_getAvgRankOld($idI)
	{
		$select = $this->select()
						->from($this, array(
							'all' => 'IFNULL( ROUND((rank_old_voran + rank_old_liens + rank_old_psol) / (count_voran + count_liens + count_psol)), 0)',
							'voran' => 'IFNULL( ROUND(rank_old_voran / count_voran), 0)',
							'liens' => 'IFNULL( ROUND(rank_old_liens / count_liens), 0)',
							'psol' => 'IFNULL( ROUND(rank_old_psol / count_psol), 0)',
							'date' => "DATE_FORMAT( `date_create` , '%d.%m.%Y' )" ))
						->order('date_create ASC');
		$this->_addItemWhere($idI, $select);
		return $this->fetchAll($select)->toArray();
	}

	/*
	 * общий новый рейтинг
	 */
	protected function notcached_getSumRankNew($idI)
	{
		$select = $this->select()
						->from($this, array(
							'all' => '(rank_new_voran + rank_new_liens + rank_new_psol)',
							'voran' => 'rank_new_voran',
							'liens' => 'rank_new_liens',
							'psol' => 'rank_new_psol',
							'date' => "DATE_FORMAT( `date_create` , '%d.%m.%Y' )" ))
						->order('date_create ASC');
		$this->_addItemWhere($idI, $select);
		return $this->fetchAll($select)->toArray();
	}

	/*
	 * средний новый рейтинг
	 */
	protected function notcached_getAvgRankNew($idI)
	{
		$select = $this->select()
						->from($this, array(
							'all' => 'IFNULL( ROUND((rank_new_voran + rank_new_liens + rank_new_psol) / (count_voran + count_liens + count_psol)), 0)',
							'voran' => 'IFNULL( ROUND(rank_new_voran / count_voran), 0)',
							'liens' => 'IFNULL( ROUND(rank_new_liens / count_liens), 0)',
							'psol' => 'IFNULL( ROUND(rank_new_psol / count_psol), 0)',
							'date' => "DATE_FORMAT( `date_create` , '%d.%m.%Y' )" ))
						->order('date_create ASC');
		$this->_addItemWhere($idI, $select);
		return $this->fetchAll($select)->toArray();
	}

	/*
	 * общий БО
	 */
	protected function notcached_getSumBO($idI)
	{
		$select = $this->select()
						->from($this, array(
							'all' => '(bo_voran + bo_liens + bo_psol)',
							'voran' => 'bo_voran',
							'liens' => 'bo_liens',
							'psol' => 'bo_psol',
							'date' => "DATE_FORMAT( `date_create` , '%d.%m.%Y' )" ))
						->order('date_create ASC');
		$this->_addItemWhere($idI, $select);
		return $this->fetchAll($select)->toArray();
	}

	/*
	 * средний БО
	 */
	protected function notcached_getAvgBO($idI)
	{
		$select = $this->select()
						->from($this, array(
							'all' => 'IFNULL( ROUND((bo_voran + bo_liens + bo_psol) / (count_voran + count_liens + count_psol)), 0)',
							'voran' => 'IFNULL( ROUND(bo_voran / count_voran), 0)',
							'liens' => 'IFNULL( ROUND(bo_liens / count_liens), 0)',
							'psol' => 'IFNULL( ROUND(bo_psol / count_psol), 0)',
							'date' => "DATE_FORMAT( `date_create` , '%d.%m.%Y' )" ))
						->order('date_create ASC');
		$this->_addItemWhere($idI, $select);
		return $this->fetchAll($select)->toArray();
	}


	/*
	 * общий РА
	 */
	protected function notcached_getSumRA($idI)
	{
		$select = $this->select()
						->from($this, array(
							'all' => '(ra_voran + ra_liens + ra_psol)',
							'voran' => 'ra_voran',
							'liens' => 'ra_liens',
							'psol' => 'ra_psol',
							'date' => "DATE_FORMAT( `date_create` , '%d.%m.%Y' )" ))
						->order('date_create ASC');
		$this->_addItemWhere($idI, $select);
		return $this->fetchAll($select)->toArray();
	}

	/*
	 * общий НРА
	 */
	protected function notcached_getSumNRA($idI)
	{
		$select = $this->select()
						->from($this, array(
							'all' => '(nra_voran + nra_liens + nra_psol)',
							'voran' => 'nra_voran',
							'liens' => 'nra_liens',
							'psol' => 'nra_psol',
							'date' => "DATE_FORMAT( `date_create` , '%d.%m.%Y' )" ))
						->order('date_create ASC');
		$this->_addItemWhere($idI, $select);
		return $this->fetchAll($select)->toArray();
	}

	/*
	 * средний РА
	 */
	protected function notcached_getAvgRA($idI)
	{
		$select = $this->select()
						->from($this, array(
							'all' => 'IFNULL( ROUND((ra_voran + ra_liens + ra_psol) / (count_voran + count_liens + count_psol), 1), 0)',
							'voran' => 'IFNULL( ROUND(ra_voran / count_voran, 1), 0)',
							'liens' => 'IFNULL( ROUND(ra_liens / count_liens, 1), 0)',
							'psol' => 'IFNULL( ROUND(ra_psol / count_psol, 1), 0)',
							'date' => "DATE_FORMAT( `date_create` , '%d.%m.%Y' )" ))
						->order('date_create ASC');
		$this->_addItemWhere($idI, $select);
		return $this->fetchAll($select)->toArray();
	}

	/*
	 * средний НРА
	 */
	protected function notcached_getAvgNRA($idI)
	{
		$select = $this->select()
						->from($this, array(
							'all' => 'IFNULL( ROUND((nra_voran + nra_liens + nra_psol) / (count_voran + count_liens + count_psol), 1), 0)',
							'voran' => 'IFNULL( ROUND(nra_voran / count_voran, 1), 0)',
							'liens' => 'IFNULL( ROUND(nra_liens / count_liens, 1), 0)',
							'psol' => 'IFNULL( ROUND(nra_psol / count_psol, 1), 0)',
							'date' => "DATE_FORMAT( `date_create` , '%d.%m.%Y' )" ))
						->order('date_create ASC');
		$this->_addItemWhere($idI, $select);
		return $this->fetchAll($select)->toArray();
	}

	/*
	 * средний уровень
	 */
	protected function notcached_getAvgLevel($idI)
	{
		$select = $this->select()
						->from($this, array(
							'all' => 'IFNULL( ROUND((level_voran + level_liens + level_psol) / (count_voran + count_liens + count_psol), 1), 0)',
							'voran' => 'IFNULL( ROUND(level_voran / count_voran, 1), 0)',
							'liens' => 'IFNULL( ROUND(level_liens / count_liens, 1), 0)',
							'psol' => 'IFNULL( ROUND(level_psol / count_psol, 1), 0)',
							'date' => "DATE_FORMAT( `date_create` , '%d.%m.%Y' )" ))
						->order('date_create ASC');
		$this->_addItemWhere($idI, $select);
		return $this->fetchAll($select)->toArray();
	}


	/*
	 * общая археология
	 */
	protected function notcached_getSumArch($idI)
	{
		$select = $this->select()
						->from($this, array(
							'all' => '(archeology_voran + archeology_liens + archeology_psol)',
							'voran' => 'archeology_voran',
							'liens' => 'archeology_liens',
							'psol' => 'archeology_psol',
							'date' => "DATE_FORMAT( `date_create` , '%d.%m.%Y' )" ))
						->order('date_create ASC');
		$this->_addItemWhere($idI, $select);
		return $this->fetchAll($select)->toArray();
	}

	/*
	 * средняя археология
	 */
	protected function notcached_getAvgArch($idI)
	{
		$select = $this->select()
						->from($this, array(
							'all' => 'IFNULL( ROUND((archeology_voran + archeology_liens + archeology_psol) / (count_voran + count_liens + count_psol)), 0)',
							'voran' => 'IFNULL( ROUND(archeology_voran / count_voran), 0)',
							'liens' => 'IFNULL( ROUND(archeology_liens / count_liens), 0)',
							'psol' => 'IFNULL( ROUND(archeology_psol / count_psol), 0)',
							'date' => "DATE_FORMAT( `date_create` , '%d.%m.%Y' )" ))
						->order('date_create DESC');
		$this->_addItemWhere($idI, $select);
		return $this->fetchAll($select)->toArray();
	}

	/*
	 * общее строительство
	 */
	protected function notcached_getSumBuild($idI)
	{
		$select = $this->select()
						->from($this, array(
							'all' => '(building_voran + building_liens + building_psol)',
							'voran' => 'building_voran',
							'liens' => 'building_liens',
							'psol' => 'building_psol',
							'date' => "DATE_FORMAT( `date_create` , '%d.%m.%Y' )" ))
						->order('date_create ASC');
		$this->_addItemWhere($idI, $select);
		return $this->fetchAll($select)->toArray();
	}

	/*
	 * среднее строительство
	*/
	protected function notcached_getAvgBuild($idI)
	{
		$select = $this->select()
						->from($this, array(
							'all' => 'IFNULL( ROUND((building_voran + building_liens + building_psol) / (count_voran + count_liens + count_psol)), 0)',
							'voran' => 'IFNULL( ROUND(building_voran / count_voran), 0)',
							'liens' => 'IFNULL( ROUND(building_liens / count_liens), 0)',
							'psol' => 'IFNULL( ROUND(building_psol / count_psol), 0)',
							'date' => "DATE_FORMAT( `date_create` , '%d.%m.%Y' )" ))
						->order('date_create ASC');
		$this->_addItemWhere($idI, $select);
		return $this->fetchAll($select)->toArray();
	}

	/*
	 * общая наука
	 */
	protected function notcached_getSumScien($idI)
	{
		$select = $this->select()
						->from($this, array(
							'all' => '(science_voran + science_liens + science_psol)',
							'voran' => 'science_voran',
							'liens' => 'science_liens',
							'psol' => 'science_psol',
							'date' => "DATE_FORMAT( `date_create` , '%d.%m.%Y' )" ))
						->order('date_create ASC');
		$this->_addItemWhere($idI, $select);
		return $this->fetchAll($select)->toArray();
	}

	/*
	 * средняя наука
	 */
	protected function notcached_getAvgScien($idI)
	{
		$select = $this->select()
						->from($this, array(
							'all' => 'IFNULL( ROUND((science_voran + science_liens + science_psol) / (count_voran + count_liens + count_psol)), 0)',
							'voran' => 'IFNULL( ROUND(science_voran / count_voran), 0)',
							'liens' => 'IFNULL( ROUND(science_liens / count_liens), 0)',
							'psol' => 'IFNULL( ROUND(science_psol / count_psol), 0)',
							'date' => "DATE_FORMAT( `date_create` , '%d.%m.%Y' )" ))
						->order('date_create ASC');
		$this->_addItemWhere($idI, $select);
		return $this->fetchAll($select)->toArray();
	}

}
