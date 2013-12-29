<?php

/*
 * абстрактный класс для всех моделей аналитики (переходы/переезды)
 */
abstract class App_Model_Abstract_Trans extends Mylib_DbTable_Cached
    implements App_Model_Interface_Clearable
{

    public function clearOld( $days )
    {
        return $this->delete( $this->_db->quoteInto( 'date < NOW() - INTERVAL ? DAY', $days, Zend_Db::INT_TYPE ) );
    }


    protected abstract function notcached_getTransByPlayer( $idP, $limit );
    protected abstract function notcached_getTransByAlliance( $idA, $limit);
    protected abstract function notcached_getTransByWorld($idW, $limit = null, $date = null, $returnCount = true);

	/**
	 * время последнего изменения
	 * НРА
	 */
	public function getLastChangeDate( $idP )
	{
		$select = $this->select()
				->from($this,array('date'))
				->where("id_player = ?", $idP, Zend_Db::INT_TYPE)
				->order("date DESC")
				->limit(1);

		$data = $this->fetchRow($select);
		return (is_null($data)) ? null : $data->date;
	}

}
