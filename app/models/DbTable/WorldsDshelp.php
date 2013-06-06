<?php

/*
 * модель настроек обновления РА с хелпера
 */
class App_Model_DbTable_WorldsDshelp extends Mylib_DbTable_Cached
{
	protected $_name = 'worlds_dshelp';
	protected $_primary = 'id_world';
	protected $_cacheName = 'up';
	protected $_tagsMap = array(
		'getUpdDate' => array('dshelpra'),
	);

	/*
	 * мир для обновления РА
	 * @return array | null
	 */
	public function getOldRaWorld( $minutes )
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

	public function notcached_getName($idW)
	{
		$select = $this->select()
						->from($this, array( 'name' ))
						->where('id_world = ?', $idW, Zend_Db::INT_TYPE)
						->limit(1);
		$res = $this->fetchRow($select);
		return $res['name'];
	}


	/*
	 * доступность графика по игроку
	 * если имя есть - график доступен
	 */
	protected function notcached_graphAvailable($idW)
	{
		$name = $this->getName($idW);
		return !is_null($name);
	}

	protected function notcached_getUpdDate($idW)
	{
		$select = $this->select()
						->from($this, array('date' => 'DATE_FORMAT(`date_check`,"%H:%i %d.%m.%Y")'))
						->where("id_world = ?", $idW, Zend_Db::INT_TYPE)
						->limit(1);
		$res = $this->fetchRow($select);
		return $res['date'];
	}
}
