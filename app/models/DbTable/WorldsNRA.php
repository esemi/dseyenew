<?php

/*
 * модель настроек обновления НРА
 */
class App_Model_DbTable_WorldsNRA extends Mylib_DbTable_Cached
{
	protected
			$_name = 'worlds_nra_update',
			$_primary = 'id_world',
			$_cacheName = 'default';

	/*
	 * миры для обновления НРА
	 * @return array | null
	 */
	public function getWorldsForUpdate( $minutes )
	{
		$select = $this->select()
						->where("date_upd < NOW() - INTERVAL ? MINUTE", $minutes, Zend_Db::INT_TYPE)
						->order('date_upd ASC')
						->limit(1);
		return $this->fetchRow($select);
	}

	public function updCheck($idW)
	{
		return $this->update(
					array('date_upd' => new Zend_Db_Expr('NOW()')),
					$this->_db->quoteInto('id_world = ?', $idW, Zend_Db::INT_TYPE)
					);
	}
}
