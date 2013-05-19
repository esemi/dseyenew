<?php

/*
 * хранилище автопоиска юзеров
 */
class App_Model_DbTable_UsersSearch extends Mylib_DbTable_Cached
{
	protected $_name = 'users_search';
	protected $_cacheName = 'default';

	/*
	 * добавление нового автопоиска юзера
	 */
	public function add( $idU, $idW, $name, $prop )
	{
		return $this->insert( array(
			'id_user' => $idU,
			'id_world' =>  $idW,
			'name' => $name,
			'prop' => serialize($prop) ));
	}

	/*
	 * удаление автопоиска юзера
	 */
	public function del( $idA )
	{
		return $this->delete( array('id = ?' => $idA) );
	}

	/*
	 * обновление даты последнего просмотра автопоиска (для списка последних используемых)
	 */
	public function touch( $idA )
	{
		return $this->update(
				array('date_touch' => new Zend_Db_Expr('NOW()') ),
				array( $this->_db->quoteInto( 'id = ?', $idA, Zend_Db::INT_TYPE ) ) );
	}

	/*
	 * обновление настроек автопоиска
	 */
	public function upd( $idA, $prop )
	{
		return $this->update(
				array(
					'prop' => serialize($prop),
					'date_touch' => new Zend_Db_Expr('NOW()') ),
				array( $this->_db->quoteInto( 'id = ?', $idA, Zend_Db::INT_TYPE ) ) );
	}

	/*
	 * проверка наличия данного автопоиска у юзера
	 */
	public function validateAccess( $idA, $idU )
	{
		$select = $this->select()
					->from($this, array( 'id' ))
					->where('id_user = ?', $idU, Zend_Db::INT_TYPE)
					->where('id = ?', $idA, Zend_Db::INT_TYPE);
		return !is_null($this->fetchRow($select));
	}

	public function getOne( $idA, $idU )
	{
		$select = $this->select()
				->from($this, array('id_world', 'name', 'prop'))
				->where('id = ?', $idA, Zend_Db::INT_TYPE)
				->where('id_user = ?', $idU, Zend_Db::INT_TYPE)
				->limit(1);

		$result = $this->fetchRow($select);
		return (is_null($result)) ? null : $result->toArray();
	}


	/*
	 * получить имена-ид поисков конкретного юзера по конкретному миру
	 */
	public function getUserList( $idU, $idW )
	{
		$select = $this->select()
				->from($this, array( 'id', 'name' ))
				->where('id_user = ?', $idU, Zend_Db::INT_TYPE)
				->where('id_world = ?', $idW, Zend_Db::INT_TYPE);
		return $this->fetchAll($select)->toArray();
	}

	/*
	 * вернуть несколько последних записей автопоиска
	 */
	public function lastUsed( $idU, $count )
	{
		$select = $this->select()
					->setIntegrityCheck(false)
					->from($this, array( 'id', 'name','id_world'))
					->join('worlds', 'worlds.id = id_world', array( 'world' => 'name' ))
					->where('id_user = ?', $idU, Zend_Db::INT_TYPE)
					->order('users_search.date_touch DESC')
					->limit($count);
		return $this->fetchAll($select)->toArray();
	}

	/*
	 * вернуть все записи автопоиска игрока
	 */
	public function listAll( $idU, $prepare = true )
	{
		$select = $this->select()
				->setIntegrityCheck(false)
				->from($this, array( 'id', 'id_world', 'name' ))
				->join('worlds', 'worlds.id = id_world', array( 'world' => 'name' ))
				->where('id_user = ?', $idU, Zend_Db::INT_TYPE)
				->order('world')
				->order('users_search.date_touch DESC');
		$res = $this->fetchAll($select)->toArray();

		if( $prepare === true )
			$res = $this->_prepare($res);

		return $res;
	}


	/*
	 * комплектует данные по поискам игрока по массивам миров
	 */
	private function _prepare( $data )
	{
		if( count($data) == 0 )
			return $data;

		$out = array();
		foreach( $data as $item )
		{
			if( !isset($out[$item['world']]) )
			{
				$tmp = new stdClass();
				$tmp->id = $item['id_world'];
				$tmp->name = $item['world'];
				$tmp->data = array();

				$out[$item['world']] = $tmp;
			}

			array_push($out[$item['world']]->data, $item);
		}

		return $out;
	}
}
