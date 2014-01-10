<?php

/*
 * модель статистики по онлайну
 */
class App_Model_DbTable_StatOnline extends Mylib_DbTable_Cached
    implements App_Model_Interface_Clearable
{

    protected $_name = 'stat_online';
    protected $_primary = array('id_version', 'date');
    protected $_cacheName = 'up';
    protected $_tagsMap = array(
        'getLastVal' => array('onlinestat'),
    );

	/*
	 * чистим старые данные
	 */
	public function clearOld($years){
		return $this->delete( $this->_db->quoteInto( 'date < CURDATE() - INTERVAL ? YEAR', $years, Zend_Db::INT_TYPE ) );
	}


    public function addStat($idV, $count)
    {
        return $this->insert( array(
            'id_version' => $idV,
            'count' =>  $count ) );
    }

    /*
     * количество игроков online
     * по часам за месяц
     * одна серия
     */
    public function notcached_getAllOnline( $idV )
    {
        $select = $this->select()
                        ->from($this, array(
                            'ser' => 'count',
                            'date' => "DATE_FORMAT( `date` , '%H.%d.%m.%Y' )" ))
                        ->where('id_version = ?', $idV, Zend_Db::INT_TYPE)
                        ->where('date > NOW()- INTERVAL 1 MONTH')
                        ->order("{$this->_name}.date ASC");

        return $this->fetchAll($select)->toArray();
    }


    /*
     * количество игроков online
     * апроксимация по дням
     * среднее - минимум - максимум
     */
    public function notcached_getDayOnline( $idV )
    {
        $select = $this->select()
                        ->from($this, array(
                            'round' => "ROUND(AVG(count))",
                            'min' => "MIN(count)",
                            'max' => "MAX(count)",
                            'date' => "DATE_FORMAT( `date` , '%d.%m.%Y' )" ))
                        ->where('id_version = ?', $idV, Zend_Db::INT_TYPE)
                        ->group("DATE(`date`)")
                        ->order("{$this->_name}.date ASC");
        $result = $this->fetchAll($select);

        return (!is_null($result) ) ? $result->toArray() : array();
    }

    /*
     * возвращает последний замер количества онлайна
     * return int
     */
    public function notcached_getLastVal($idV)
    {
        $select = $this->select()
                        ->from($this, array('count'))
                        ->where('id_version = ?', $idV, Zend_Db::INT_TYPE)
                        ->order('date DESC')
                        ->limit(1);
        $result = $this->fetchRow($select);
        return (!is_null($result) ) ? (int)$result->count : 0;
    }


    public function prepareForHourGraph($data)
    {
        $out = new stdClass();
        $out->name = 'Всего';
        $out->realname = 'count';
        $out->visible = true;
        $out->data = array();

        foreach( $data as $val )
            $out->data[] = array($val['date'], floatval($val['ser']));

        return array($out);
    }

	public function prepareForDayGraph($data)
	{
		$items = array(
			'round' => 'В среднем',
			'min' => 'Минимум',
			'max' => 'Максимум');

		$result = array();

		foreach( $items as $realname => $name )
		{
			$$realname = new stdClass();
			$$realname->name = $name;
			$$realname->realname = $realname;
			$$realname->data = array();
			$$realname->visible = true;
			$result[] = $$realname;
		}

		foreach( $data as $val )
			foreach( $items as $realname => $name )
				$$realname->data[] = array($val['date'], floatval($val[$realname]));

		return $result;
	}
}