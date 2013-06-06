<?php

/*
 * модель настроек парсинга комплексов на предмет расширенных статусов ворот
 */
class App_Model_DbTable_WorldsGameParse extends Mylib_DbTable_Cached
{
	protected
			$_name = 'worlds_game_parse',
			$_primary = 'id_world',
			$_cacheName = 'default';

	/*
	 * мир для обновления
	 * @return array | null
	 */
	public function getWorldForParse( $minutes )
	{
		$select = $this->select()
						->where("date_check < NOW() - INTERVAL ? MINUTE", $minutes, Zend_Db::INT_TYPE)
						->order('date_check ASC')
						->limit(1);
		$res = $this->fetchRow($select);
		return (is_null($res)) ? null : $res->toArray();
	}

	public function updCheck($idW)
	{
		return $this->update(
					array('date_check' => new Zend_Db_Expr('NOW()')),
					$this->_db->quoteInto('id_world = ?', $idW, Zend_Db::INT_TYPE)
					);
	}

	public function notcached_statusAvaliable($idW)
	{
		$select = $this->select()
						->where('id_world = ?', $idW, Zend_Db::INT_TYPE)
						->limit(1);
		return !is_null($this->fetchRow($select));
	}

}
