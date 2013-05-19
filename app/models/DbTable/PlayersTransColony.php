<?php

/*
 * переезды колоний игроков
 */
class App_Model_DbTable_PlayersTransColony extends App_Model_Abstract_Trans
	implements App_Model_Interface_Clearable
{

	protected $_name = 'players_trans_colony';
	protected $_cacheName = 'up';

	protected function notcached_getTransByPlayer( $idP, $limit )
	{
		return false;
	}

	protected function notcached_getTransByAlliance( $idA, $limit)
	{
		return false;
	}

	protected function notcached_getTransByWorld($idW, $date = null, $returnCount = false, $limit = 10)
	{
		return false;
	}

	public function addTransColony($idP, $oldC=null, $oldS=null, $newC=null, $newS=null)
	{
		$data = array(
			'id_player' => $idP,
			'date' => new Zend_Db_Expr('NOW()') );

		if( !is_null($oldC) && !is_null($oldS) )
		{
			$data['old_compl'] = $oldC;
			$data['old_sota'] = $oldS;
		}

		if( !is_null($newC) && !is_null($newS) ){
			$data['new_compl'] =  $newC;
			$data['new_sota'] = $newS;
		}

		return $this->insert( $data );
	}


	/**
	 * время последнего приобритения колонии
	 * НРА
	 */
	public function getLastNewColonyDate( $idP )
	{
		$select = $this->select()
				->from($this,array('date'))
				->where("id_player = ?", $idP, Zend_Db::INT_TYPE)
				->where("old_compl IS NULL")
				->order("date DESC")
				->limit(1);
		$data = $this->fetchRow($select);
		return (is_null($data)) ? null : $data->date;
	}

}
