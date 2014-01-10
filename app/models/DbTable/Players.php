<?php

/*
 * игроки
 * players и все остальные таблицы связанные с игроками
 */

class App_Model_DbTable_Players extends Mylib_DbTable_Cached
{

	protected
		$_name = 'players',
		$_cacheName = 'up';

	protected $_tagsMap = array(
		'listWorldPlayers' => array('up','dshelpra','ranks', 'nra'),
		'listAlliancePlayers' => array('up','dshelpra','ranks', 'nra'),
		'listAllianceColony' => array('up','dshelpra','ranks', 'nra'),
		'getInfo' => array('up','dshelpra','ranks', 'nra', 'gate'),
		'getRingMap' => array('up','dshelpra','ranks', 'nra', 'gate'),
		'getMaxRanks' => array('up','dshelpra','ranks', 'nra'),
		'sotsWithoutWorld' => array('up'),
		'getNeighborsDom' => array('up'),
		'getNeighborsMels' => array('up'),
		'findByNik' => array('up'),
		'findByDomName' => array('up'),
		'findByAddress' => array('up'),
		'fastSearch' => array('up'),
		'getUsedCompls' => array('up'),
		'getCountNotavaliableGateByWorld' => array('up','gate'),
		'getCountPremiumByWorld' => array('up','gate'),
	);

	/*
	 * обновить РА игроку мира
	 * dshelpra cli
	 */
	public function updateRA( $data, $idW )
	{
		return $this->update(
				array('ra' => $data['ra']),
				array(
					$this->_db->quoteInto( 'id_world = ?', $idW, Zend_Db::INT_TYPE ),
					$this->_db->quoteInto( 'nik = ?', $data['nik'] ))
				);
	}

	/*
	 * обновить НРА игроку мира
	 * nra cli
	 */
	public function updateNRA( $nra, $idP )
	{
		return $this->update(
				array('nra' => $nra),
				array($this->_db->quoteInto('id = ?', $idP, Zend_Db::INT_TYPE)));
	}

	/**
	 * обновить расширенные статусы ворот/према игроку
	 * gate cli
	 */
	public function updateGateStatuses($idW, $nik, $gate, $shield, $newbee, $ban, $prem)
	{
		return $this->update(
				array(
					'gate' => ($gate) ? 1 : 0,
					'gate_shield' => ($shield) ? 1 : 0,
					'gate_newbee' => ($newbee) ? 1 : 0,
					'gate_ban' => ($ban) ? 1 : 0,
					'premium' => ($prem) ? 1 : 0,
					),
				array(
					$this->_db->quoteInto( 'id_world = ?', $idW, Zend_Db::INT_TYPE ),
					$this->_db->quoteInto( 'nik = ?', $nik ))
				);
	}

	/*
	 * обновить старые рейтинги игроку мира
	 * oldranks cli
	 */
	public function updateOldRanks($data, $idW )
	{
		return $this->update(
				array( 'rank_old' => $data['rank_old'], 'bo' => $data['bo']),
				array(
					$this->_db->quoteInto( 'id_world = ?', $idW, Zend_Db::INT_TYPE ),
					$this->_db->quoteInto( 'nik = ?', $data['nik'] ))
				);
	}

	/*
	 * обновить новые рейтинги игроку мира
	 * newranks cli
	 */
	public function updateNewRanks($data, $idW )
	{
		return $this->update(
				array(
					'rank_new' => $data['rank_new'],
					'level' => $data['level'],
					'liga' => $data['liga'],
					'archeology' => $data['arch'],
					'building' => $data['build'],
					'science' => $data['scien']),
				array(
					$this->_db->quoteInto( 'id_world = ?', $idW, Zend_Db::INT_TYPE ),
					$this->_db->quoteInto( 'nik = ?', $data['nik'] ))
				);
	}

	/*
	 * обновить кешируемые дельты рейтинга и БО
	 * oldranks cli
	 */
	public function updateDelts($idW, $hours)
	{
		$sql = "UPDATE `{$this->_name}`
			SET delta_rank_old = IFNULL( (
				SELECT delta
				FROM stat_players_rank_old
				WHERE id_player = players.id
				AND `date` >= DATE_SUB( NOW( ) , INTERVAL ? HOUR )
				ORDER BY `date` DESC
				LIMIT 1 ) , 0),
				delta_bo = IFNULL( (
				SELECT delta
				FROM stat_players_bo
				WHERE id_player = players.id
				AND `date` >= DATE_SUB( NOW( ) , INTERVAL ? HOUR )
				ORDER BY `date` DESC
				LIMIT 1 ) , 0)
			WHERE id_world = ?";
		return $this->_db->query($sql, array($hours, $hours, $idW));
	}

	/*
	 * листинг всех игроков мира
	 */
	protected function notcached_listWorldPlayers( $idW, $page, $countItem, $sort, $count = 20 )
	{
		$select = $this->select()
				->setIntegrityCheck(false)
				->from($this, array(
					'id', 'id_rase', 'id_alliance',
					'dom' => 'CONCAT_WS(".", ring, players.compl, players.sota )',
					'nik', 'rank_old', 'rank_new', 'bo', 'ra', 'nra', 'gate', 'level', 'liga', 'archeology', 'building', 'science','delta_rank_old','delta_bo' ))
				->join('alliances',
						'alliances.id = players.id_alliance',
						array( 'alliance' => 'name' ))
				->where("players.status = 'active'")
				->where('players.id_world = ?', $idW, Zend_Db::INT_TYPE);

		$this->_sortDecode($select, $sort);

		$paginator = Zend_Paginator::factory($select);
		$paginator->getAdapter()->setRowCount($countItem);
		$paginator->setCurrentPageNumber($page)
				->setItemCountPerPage($count)
				->setPageRange(5);

		return $paginator;
	}

	/*
	 * листинг игроков альянса
	 */
	protected function notcached_listAlliancePlayers( $idA, $page, $countItem, $sort, $count = 20 )
	{
		$select = $this->select()
				->setIntegrityCheck(false)
				->from($this, array(
					'id', 'id_rase','dom_name',
					'dom' => 'CONCAT_WS(".", ring, players.compl, players.sota )',
					'nik', 'rank_old', 'rank_new', 'bo', 'ra', 'nra', 'gate', 'level', 'liga', 'archeology', 'building', 'science','delta_rank_old','delta_bo' ))
				->where("status = 'active'")
				->where('id_alliance = ?', $idA, Zend_Db::INT_TYPE);

		$this->_sortDecode($select, $sort);

		$paginator = Zend_Paginator::factory($select);
		$paginator->getAdapter()->setRowCount($countItem);
		$paginator->setCurrentPageNumber($page)
				->setItemCountPerPage($count)
				->setPageRange(5);

		return $paginator;
	}

	/*
	 * листинг колоний альянса
	 */
	protected function notcached_listAllianceColony($idA, $page, $countItem, $sort, $count = 20)
	{
		$select = $this->select()
				->setIntegrityCheck(false)
				->from('players_colony',
						array('col_name', 'colony' => 'CONCAT( "4.", players_colony.compl, ".", players_colony.sota)'))
				->joinLeft($this->_name, "{$this->_name}.id = players_colony.id_player",
						array('id', 'id_rase', 'nik', 'rank_old', 'rank_new', 'bo', 'ra', 'nra', 'level', 'liga', 'archeology', 'building', 'science','delta_rank_old','delta_bo'))
				->where("status = 'active'")
				->where('id_alliance = ?', $idA, Zend_Db::INT_TYPE);

		$this->_sortDecode($select, $sort);

		$paginator = Zend_Paginator::factory($select);
		$paginator->getAdapter()->setRowCount($countItem);
		$paginator->setCurrentPageNumber($page)
				->setItemCountPerPage($count)
				->setPageRange(5);

		return $paginator;
	}

	/*
	 * валидация игрока
	 */
	public function validate( $idP, $idW = null )
	{
		$select = $this->select()
						->from($this, array( 'id', 'id_world' ))
						->where('id = ?', $idP, Zend_Db::INT_TYPE)
						->limit(1);
		if( !is_null($idW) )
			$select->where('id_world = ?', $idW, Zend_Db::INT_TYPE);

		$result = $this->fetchRow($select);
		return !is_null($result);
	}

	/**
	 * поиск ид игрока по нику ( быстрый переход и аддон )
	 * @return mixed Int or false
	 */
	protected function notcached_findByNik( $nik, $limit, $idW = null, $strong=true )
	{
		$select = $this->select()
				->from($this, array('id'))
				->limit((int) $limit);

		if($strong){
			$select->where('nik = ?', $nik);
		}else{
			$select->where($this->_db->quoteInto( "nik LIKE ?", "{$nik}%" ));
		}

		if( !is_null($idW) )
			$select->where('id_world = ?', $idW, Zend_Db::INT_TYPE);

		return $this->fetchAll($select)->toArray();
	}


	/**
	 * поиск ид игроков по имени домашней соты ( аддон )
	 * @TODO check db perfomance
	 * @return array Array of int user_ids
	 */
	protected function notcached_findByDomName( $term, $limit, $strong=true )
	{
		$select = $this->select()
				->from($this, array('id'))
				->limit((int) $limit);

		if($strong){
			$select->where('dom_name = ?', $term);
		}else{
			$select->where($this->_db->quoteInto( "dom_name LIKE ?", "{$term}%" ));
		}

		return $this->fetchAll($select)->toArray();
	}

	/**
	 * Поиск id игроков по адресу дом соты
	 * @TODO check db perfomance
	 * @return array Array of int user_ids
	 */
	protected function notcached_findByAddress( $term )
	{
		$select = $this->select()
				->from($this, array('id'))
				->where('CONCAT_WS(".", ring, compl, sota ) = ?', $term);

		return $this->fetchAll($select)->toArray();
	}


	/*
	 * поиск игроков по части ника/названию домашней соты
	 */
	protected function notcached_fastSearch(  $term, $limit, $idW = 0 )
	{
		$select = $this->select();
		$select->setIntegrityCheck(false)
				->from($this, array(
					'id', 'id_rase', 'id_world', 'id_alliance',
					'dom' => 'CONCAT_WS(".", ring, compl, sota )',
					'dom_name', 'nik', 'gate', 'status' ))
				->join('alliances', "alliances.id = {$this->_name}.id_alliance",
						array( 'alliance' => 'name' ))
				->where(
						 $this->_db->quoteInto( "nik LIKE ?", "{$term}%" )
						. ' OR '
						.$this->_db->quoteInto( "dom_name LIKE ?", "{$term}%" ) )
				->limit($limit);

		if( $idW == 0 )
			$select->join('worlds', 'worlds.id = players.id_world', array( 'world' => 'name' ));
		else
			$select->where('players.id_world = ?', $idW, Zend_Db::INT_TYPE);

		//var_dump($select->__toString());
		return $this->fetchAll($select)->toArray();
	}


	/*
	 * полный поиск игроков
	 */
	public function fullSearch( $idW, stdClass $searchOpt, $sort, $limit )
	{
		$select = $this->select();

		//кольца и комлекс (зависим от таблицы)
		$complCol = null;
		if( $searchOpt->ring == 4 )
		{
			$select->from('players_colony', array(
						'sort_compl' => 'compl',
						'sort_sota' => 'sota',
						'main_sota' => 'col_name',
						'main_addr' => 'CONCAT( "4.", players_colony.compl, ".", players_colony.sota )') )
					->join('players', 'players_colony.id_player = players.id', array(
						'id', 'id_rase', 'id_alliance', 'nik', 'rank_old','rank_new', 'bo', 'ra', 'nra',
						'liga','level', 'arch' => 'archeology', 'build' => 'building', 'scien' => 'science',
						'gate_shield', 'gate_newbee', 'gate_ban', 'premium'));
			$complCol = 'players_colony.compl';
		}else{
			$select->from($this, array(
						'sort_compl' => 'compl',
						'sort_sota' => 'sota',
						'ring' => 'players.ring',
						'main_sota' => 'dom_name',
						'main_addr' => 'CONCAT_WS(".", players.ring, players.compl, players.sota )',
						'id', 'id_rase', 'id_alliance', 'nik', 'rank_old','rank_new', 'bo', 'ra', 'nra', 'gate',
						'liga', 'level', 'arch' => 'archeology', 'build' => 'building', 'scien' => 'science',
						'gate_shield', 'gate_newbee', 'gate_ban', 'premium'));
			$complCol = 'players.compl';

			if( mb_strlen($searchOpt->ring) == 1 )
			{
				$select->where('players.ring = ?', $searchOpt->ring, Zend_Db::INT_TYPE);
			}else{
				$tmp = str_split($searchOpt->ring);
				$rings = array();
				foreach( $tmp as $val )
					$rings[] = $this->_db->quoteInto( 'players.ring = ?', (int)$val );
				$select->where( implode(' OR ', $rings) );
			}
		}

		//ворота домашней соты
		if( !empty($searchOpt->gate) )
		{
			switch ($searchOpt->gate)
			{
				case 'close':
					$select->where("players.gate = ?", 0, Zend_Db::INT_TYPE);
				break;
				case 'open':
					$select->where("players.gate = ?", 1, Zend_Db::INT_TYPE);
				break;
			}
		}

		//скрывать недоступных по расширенным статусам ворот
		if( !empty($searchOpt->onlyGateAvaliable) )
		{
			$select->where('players.gate_shield = 0');
			$select->where('players.gate_newbee = 0');
			$select->where('players.gate_ban = 0');
		}

		//фильтровать премиум/нет
		if( !empty($searchOpt->premium) )
		{
			switch ($searchOpt->premium)
			{
				case 'yes':
					$select->where('players.premium = 1');
				break;
				case 'none':
					$select->where('players.premium = 0');
				break;
			}
		}

		//поиск по нейтралам
		if( !empty($searchOpt->alliance) && $searchOpt->alliance !== 'all' )
		{
			$nameNeutral = Zend_Controller_Front::getInstance()->getParam('bootstrap')->getOption('nameNeutral');
			switch($searchOpt->alliance)
			{
				case 'yes':
					$select->where('alliances.name != ?', $nameNeutral);
				break;

				case 'none':
					$select->where('alliances.name = ?', $nameNeutral);
				break;
			}
		}

		//фильтр альянсов
		if( !empty($searchOpt->filterAllianceMod) && !empty($searchOpt->filterAlliance) )
		{
			$alls = array();
			foreach( $searchOpt->filterAlliance as $idAll )
				$alls[] = (int) $idAll;

			if( count($alls) > 0)
			{
				switch ($searchOpt->filterAllianceMod)
				{
					case 'only':
						$select->where( 'alliances.id IN (?)', $alls);
					break;

					case 'not':
						$select->where( 'alliances.id NOT IN (?)', $alls);
					break;
				}
			}
		}

		//лига
		if( !empty($searchOpt->liga) )
		{
			$ligs = array();
			foreach( $searchOpt->liga as $val )
				$ligs[] = $this->_db->quoteInto( 'players.liga = ?', $val );
			$select->where( implode(' OR ', $ligs) );
		}


		//расса
		if( !empty($searchOpt->rase) )
		{
			$rases = array();
			foreach( $searchOpt->rase as $val )
				$rases[] = $this->_db->quoteInto( 'players.id_rase = ?', $val, Zend_Db::INT_TYPE );
			$select->where( implode(' OR ', $rases) );
		}

		//сладеры
		$sliders = array(
			'compl' => $complCol,
			'rankold' => 'players.rank_old',
			'bo' => 'players.bo',
			'nra' => 'players.nra',
			'ra' => 'players.ra',
			'ranknew' => 'players.rank_new',
			'level' => 'players.level',
			'arch' => 'players.archeology',
			'build' => 'players.building',
			'scien' => 'players.science',
		);
		foreach( $sliders as $prop => $col )
		{
			if( isset($searchOpt->{"{$prop}Min"}) && isset($searchOpt->{"{$prop}Max"}) )
				$this->_setWhereSlider( $select, $col, $searchOpt->{"{$prop}Min"}, $searchOpt->{"{$prop}Max"} );
		}

		//общие параметры
		$select->setIntegrityCheck(false)
				->limit($limit)
				->join('alliances', 'alliances.id = players.id_alliance', array( 'alliance' => 'name' ))
				->where('players.id_world = ?', $idW, Zend_Db::INT_TYPE)
				->where("players.status = 'active'");
		$this->_sortDecode($select, $sort);

		//var_dump($select->__toString());die;

		return $this->fetchAll($select)->toArray();
	}

	/*
	 * партиал условия для слайдера по свойствам игрока
	 */
	private function _setWhereSlider( $select, $name, $min, $max)
	{
		if( isset($min) && isset($max) )
		{
			if( $max > $min )
			{
				$select->where("{$name} >= ?", $min, Zend_Db::INT_TYPE )
						->where("{$name} <= ?", $max, Zend_Db::INT_TYPE );
			}else{
				$select->where(
						$this->_db->quoteInto( "{$name} <= ?", $max, Zend_Db::INT_TYPE )
						. ' OR '
						. $this->_db->quoteInto( "{$name} >= ?", $min, Zend_Db::INT_TYPE ) );
			}
		}
	}



	/*
	 * соты игрока в других мирах
	 */
	protected function notcached_sotsWithoutWorld( $nik, $idW )
	{
		$select = $this->select()
				->setIntegrityCheck(false)
				->from($this, array(
					'id', 'id_world', 'id_rase', 'id_alliance', 'dom' => 'CONCAT_WS(".", ring, compl, sota )', 'status' ))
				->join('alliances', 'alliances.id = players.id_alliance', array( 'alliance' => 'name' ))
				->join('worlds', 'worlds.id = players.id_world', array( 'world' => 'name' ))
				->where('players.id_world != ?', $idW, Zend_Db::INT_TYPE)
				->where("players.status = 'active'")
				->where('nik = ?', $nik );

		return $this->fetchAll($select)->toArray();
	}

	/*
	 * получение инфы по заселённым комплексам (все кольца кроме мельса)
	 */
	protected function notcached_getUsedCompls( $idW )
	{
		$select = $this->select()
				->setIntegrityCheck(false)
				->from($this, array( 'ring', 'compl' ))
				->where('id_world = ?', $idW, Zend_Db::INT_TYPE)
				->where("status = 'active'")
				->group('ring')
				->group('compl');
		$out = $this->fetchAll($select)->toArray();

		return $out;
	}

	/*
	 * получение инфы по одному игроку
	 */
	protected function notcached_getInfo( $idP )
	{
		//данные основные
		$select = $this->select()
				->setIntegrityCheck(false)
				->from($this, array(
					new Zend_Db_Expr('players.*'),
					'dom' => "CONCAT_WS('.', ring, compl, sota )",
					'date_create' => 'DATE_FORMAT(players.`date_create` , \'%d.%m.%Y\')',
					'date_delete' => 'DATE_FORMAT(players.`date_delete` , \'%d.%m.%Y\')' ))
				->joinLeft('alliances', 'players.id_alliance = alliances.id', array( 'all' => 'name', 'idA' => 'id' ))
				->where('players.id = ?', $idP, Zend_Db::INT_TYPE)
				->limit(1);
		$out = $this->fetchRow($select)->toArray();

		//данные о колониях
		$select = $this->select()
				->setIntegrityCheck(false)
				->from('players_colony', array( 'col' => "CONCAT( '4.', compl, '.', sota )", 'col_name', 'compl' ))
				->where('players_colony.id_player = ?', $idP, Zend_Db::INT_TYPE);
		$out['colony'] = $this->fetchAll($select)->toArray();

		return $out;
	}


	/*
	 * получить соседей игрока по дому
	 */
	protected function notcached_getNeighborsDom( $idW, $idP, $compl, $ring)
	{
		//данные о соседях по дому
		$select = $this->select()
				->setIntegrityCheck(false)
				->from($this, array(
					'id','nik','id_rase', 'id_alliance', 'gate',
					'adr' => "CONCAT_WS('.', ring, compl, sota )" ))
				->joinLeft('alliances', "{$this->_name}.id_alliance = alliances.id", array( 'alliance' => 'name' ))
				->where('ring = ?', $ring, Zend_Db::INT_TYPE)
				->where('compl = ?', $compl, Zend_Db::INT_TYPE)
				->where("{$this->_name}.id_world = ?", $idW, Zend_Db::INT_TYPE)
				->where("{$this->_name}.id != ?", $idP, Zend_Db::INT_TYPE)
				->where("{$this->_name}.status = 'active'")
				->order('sota ASC');
		return $this->fetchAll($select)->toArray();
	}

	/*
	 * получить соседей игрока по мельсу
	 */
	protected function notcached_getNeighborsMels( $idW, $idP, Array $cols)
	{
		if(count($cols) === 0)
			return array();

		$compls = array();
		foreach( $cols as $col )
			$compls[] = (int) $col['compl'];

		$select = $this->select()
				->setIntegrityCheck(false)
				->from('players_colony', array( 'adr' => "CONCAT( '4.', players_colony.compl, '.', players_colony.sota )" ) )
				->joinLeft($this->_name, "{$this->_name}.id = players_colony.id_player", array( 'id', 'nik', 'id_rase', 'id_alliance'  ))
				->joinLeft('alliances', "{$this->_name}.id_alliance = alliances.id", array( 'alliance' => 'name' ))
				->where('players_colony.compl IN (?)', $compls)
				->where('players_colony.id_player != ?', $idP, Zend_Db::INT_TYPE)
				->where("{$this->_name}.id_world = ?", $idW, Zend_Db::INT_TYPE)
				->where("{$this->_name}.status = 'active'")
				->order('players_colony.compl ASC')
				->order('players_colony.sota ASC');
		return $this->fetchAll($select)->toArray();

	}


	/*
	 * максимальные значения параметров по миру
	 */
	protected function notcached_getMaxRanks( $idW )
	{
		$select = $this->select()
				->from($this, array(
					'rankold'  => 'MAX(`rank_old`)',
					'ranknew'  => 'MAX(`rank_new`)',
					'bo'    => 'CEIL(MAX(`bo`))',
					'ra'    => 'CEIL(MAX(`ra`))',
					'nra'    => 'CEIL(MAX(`nra`))',
					'level' => 'MAX(`level`)',
					'arch'  => 'MAX(`archeology`)',
					'build' => 'MAX(`building`)',
					'scien' => 'MAX(`science`)'))
				->where('id_world = ?', $idW, Zend_Db::INT_TYPE)
				->where("status = 'active'")
				->limit(1);

		return $this->fetchRow($select)->toArray();
	}


	/*
	 * получаем карту отрезка кольца
	 */
	protected function notcached_getRingMap($idW, $first, $last, $ring)
	{
		$select = $this->select();

		//кольцо и комлекс (зависим от таблицы)
		if( $ring == 4 )
		{
			$select->from('players_colony', array(
						'main_compl' => 'compl',
						'main_sota' => 'sota',
						'sota_name' => 'col_name',
						'main_addr' => 'CONCAT( "4.", players_colony.compl, ".", players_colony.sota )') )
					->join('players', 'players_colony.id_player = players.id', array(new Zend_Db_Expr('players.*')));
			$this->_setWhereSlider( $select, 'players_colony.compl', $first, $last );
		}else{
			$select->from($this, array(
						new Zend_Db_Expr('players.*'),
						'main_compl' => 'compl',
						'main_sota' => 'sota',
						'sota_name' => 'dom_name',
						'main_addr' => 'CONCAT_WS(".", players.ring, players.compl, players.sota )'))
				->where('players.ring = ?', $ring, Zend_Db::INT_TYPE);
			$this->_setWhereSlider( $select, 'players.compl', $first, $last );
		}

		//общие параметры
		$select->setIntegrityCheck(false)
				->join('alliances', 'alliances.id = players.id_alliance', array( 'alliance' => 'name' ))
				->where('players.id_world = ?', $idW, Zend_Db::INT_TYPE)
				->where("players.status = 'active'")
				->order('main_compl ASC')
				->order('main_sota ASC');

		//var_dump($select->__toString());die;
		$result = $this->fetchAll($select);

		return $this->_prepareMapArray($result, $first, $last);
	}


	/*
	 * подготовка массива с картой
	 */
	private function _prepareMapArray($result)
	{
		$map = array();
		if ( !is_null($result) )
		{
			$data =  $result->toArray();
			foreach($data as $player)
			{
				$map[$player['main_compl']][$player['main_sota']] = $player;
			}
		}
		return $map;

	}

	/*
	 * расшифровка столбца сортировки
	 */
	private function _sortDecode($select, $sort)
	{
		switch ($sort)
		{
			case 'adr':
				$select->order('sort_compl DESC')
					   ->order('sort_sota DESC');
				break;
			case 'adr_r':
				$select->order('sort_compl ASC')
					   ->order('sort_sota ASC');
				break;
			case 'nik':
				$select->order('players.nik DESC');
				break;
			case 'nik_r':
				$select->order('players.nik ASC');
				break;
			case 'dom':
				$select->order('players.compl DESC')
					   ->order('players.sota DESC');
				break;
			case 'dom_r':
				$select->order('players.compl ASC')
					   ->order('players.sota ASC');
				break;
			case 'colony':
				$select->order('players_colony.compl DESC')
					   ->order('players_colony.sota DESC');
				break;
			case 'colony_r':
				$select->order('players_colony.compl ASC')
					   ->order('players_colony.sota ASC');
				break;
			case 'rank_old':
				$select->order('players.rank_old DESC');
				break;
			case 'rank_old_r':
				$select->order('players.rank_old ASC');
				break;
			case 'rank_new':
				$select->order('players.rank_new DESC');
				break;
			case 'rank_new_r':
				$select->order('players.rank_new ASC');
				break;
			case 'delta_rank':
				$select->order('delta_rank_old DESC');
				break;
			case 'delta_rank_r':
				$select->order('delta_rank_old ASC');
				break;
			case 'bo':
				$select->order('players.bo DESC');
				break;
			case 'bo_r':
				$select->order('players.bo ASC');
				break;
			case 'delta_bo':
				$select->order('delta_bo DESC');
				break;
			case 'delta_bo_r':
				$select->order('delta_bo ASC');
				break;
			case 'ra':
				$select->order('players.ra DESC');
				break;
			case 'ra_r':
				$select->order('players.ra ASC');
				break;
			case 'nra':
				$select->order('players.nra DESC');
				break;
			case 'nra_r':
				$select->order('players.nra ASC');
				break;
			case 'level':
				$select->order('players.level DESC');
				break;
			case 'level_r':
				$select->order('players.level ASC');
				break;
			case 'liga':
				$select->order('players.liga DESC');
				break;
			case 'liga_r':
				$select->order('players.liga ASC');
				break;
			case 'arch':
				$select->order('players.archeology DESC');
				break;
			case 'arch_r':
				$select->order('players.archeology ASC');
				break;
			case 'build':
				$select->order('players.building DESC');
				break;
			case 'build_r':
				$select->order('players.building ASC');
				break;
			case 'scien':
				$select->order('players.science DESC');
				break;
			case 'scien_r':
				$select->order('players.science ASC');
				break;
		}

	}

	/**
	 * Количество недоступных ворот в мире
	 */
	public function notcached_getCountNotavaliableGateByWorld($idW)
	{
		$select = $this->select()
				->from($this, array( 'count' => 'COUNT(*)' ))
				->where('id_world = ?', $idW, Zend_Db::INT_TYPE)
				->where("status = 'active'")
				->where('(gate_shield + gate_newbee + gate_ban) > 0');
		$row = $this->fetchRow($select);
		return (is_null($row)) ? 0 : intval($row['count']);
	}

	/**
	 * Количество премиум игроков в мире
	 */
	public function notcached_getCountPremiumByWorld($idW)
	{
		$select = $this->select()
				->from($this, array( 'count' => 'COUNT(*)' ))
				->where('id_world = ?', $idW, Zend_Db::INT_TYPE)
				->where("status = 'active'")
				->where("premium = 1");
		$row = $this->fetchRow($select);
		return (is_null($row)) ? 0 : intval($row['count']);
	}


	/*
	 * все активные игроки мира с колониями
	 * csv cli
	 */
	public function getDataForCsv($idW)
	{
		$select = $this->select()
				->setIntegrityCheck(false)
				->from($this, array(
					'id', 'nik', 'dom_adr' => "CONCAT_WS('.', ring, {$this->_name}.compl, {$this->_name}.sota )",
					'dom_name', 'rank_old', 'rank_new', 'bo', 'nra', 'ra', 'gate', 'level', 'liga', 'archeology', 'building', 'science',
					'delta_rank_old','delta_bo', 'gate_shield', 'gate_newbee', 'gate_ban', 'premium'))
				->joinLeft('players_colony', "{$this->_name}.id = players_colony.id_player",
						array(
							'colony_adr' => new Zend_Db_Expr('CAST( GROUP_CONCAT( CONCAT( "4.", players_colony.compl, ".", players_colony.sota) SEPARATOR ",")  AS char)'),
							'colony_name' =>new Zend_Db_Expr('CAST( GROUP_CONCAT(players_colony.col_name SEPARATOR ",") AS char)') ))
				->joinLeft('alliances', "alliances.id = {$this->_name}.id_alliance",
						array( 'alliance' => 'name' ))
				->joinLeft('rases', 'rases.id = players.id_rase',
						array( 'rase' => 'name' ))
				->where("{$this->_name}.status = 'active'")
				->where("{$this->_name}.id_world = ?", $idW, Zend_Db::INT_TYPE)
				->group("{$this->_name}.id")
				->order("{$this->_name}.rank_old DESC");

		return $this->fetchAll($select)->toArray();
	}

	/*
	 * все игроки мира (включая удалённых по дефолту)
	 * cli
	 */
	public function getAllByWorld($idW, $active=false)
	{
		$select = $this->select()
				->from($this, array( 'id', 'nik' ))
				->where('id_world = ?', $idW, Zend_Db::INT_TYPE);
		if( $active === true )
			$select->where("status = 'active'");

		return $this->fetchAll($select)->toArray();
	}

	/*
	 * добавление нового игрока
	 * up cli
	 */
	public function add($idW, $data)
	{
		$data = array_merge($data, array(
			'id_world' => $idW,
			'date_create' => new Zend_Db_Expr('NOW()'),
			'status' => 'active') );

		return $this->insert( $data );
	}

	/*
	 * обновление основных параметров игрока
	 * up cli
	 */
	public function upd($idP, $data)
	{
		$data = array_merge($data, array('status' => 'active'));
		return $this->update( $data, array( $this->_db->quoteInto( 'id = ?', $idP, Zend_Db::INT_TYPE ) ) );
	}

	/*
	 * множественное удаление игроков только активных (отметка об удалённом статусе)
	 * up cli
	 */
	public function del(Array $ids)
	{
		return $this->update(
				array('status' => 'delete', 'date_delete' => new Zend_Db_Expr('NOW()')),
				array('id IN (?)' => $ids, 'status = ?' => 'active') );
	}

	/*
	 * основные параметры альянсов
	 * cli
	 */
	public function getAllianceParams($idW)
	{
		$select = $this->select()
				->setIntegrityCheck(false)
				->from($this, array(
					'id_alliance', 'id_rase',
					'count' => 'COUNT(*)',
					'rank_old' => 'SUM(`rank_old`)',
					'rank_new' => 'SUM(`rank_new`)',
					'bo' => 'SUM(`bo`)',
					'archeology' => 'SUM(`archeology`)',
					'building' => 'SUM(`building`)',
					'science' => 'SUM(`science`)',
					'nra' => 'SUM(`nra`)',
					'ra' => 'SUM(`ra`)',
					'level' => 'SUM(`level`)'))
				->where('id_world = ?', $idW, Zend_Db::INT_TYPE)
				->where('status = ?', 'active')
				->group('id_alliance')
				->group('id_rase')
				->order( new Zend_Db_Expr('NULL') );
		return $this->fetchAll($select)->toArray();
	}


	/*
	 * основные параметры мира
	 * cli
	 */
	public function getMainWorldParams($idW, $forStat = false)
	{
		$cols = array(
					'id_rase',
					'count' => 'COUNT(*)',
					'rank_old' => 'SUM(`rank_old`)',
					'rank_new' => 'SUM(`rank_new`)',
					'bo' => 'SUM(`bo`)',
					'archeology' => 'SUM(`archeology`)',
					'building' => 'SUM(`building`)',
					'science' => 'SUM(`science`)',
					'nra' => 'SUM(`nra`)',
					'ra' => 'SUM(`ra`)',
					'level' => 'SUM(`level`)', );
		if($forStat === false)
			$cols['compls'] = "MAX({$this->_name}.compl)";

		$select = $this->select()
				->setIntegrityCheck(false)
				->from($this, $cols)
				->where('id_world = ?', $idW, Zend_Db::INT_TYPE)
				->where('status = ?', 'active')
				->group('id_rase')
				->order( new Zend_Db_Expr('NULL') );
		return $this->fetchAll($select)->toArray();
	}

	/**
	 * очистка настроек поиска от лишних полей (если поле подразумевает, что такое значение не требовательно к наличию в where)
	 *
	 * @var searchProp - stdClass of search property
	 * @var max - array of max ranks for sliders
	 * @var rases - array of avaliable rases
	 * @return stdClass prepared search prop
	 */
	public function _prepareSearchProp(stdClass $searchProp, $max, $rases)
	{
		if( empty($searchProp->gate) || $searchProp->gate === 'all' ){
			unset($searchProp->gate);
		}

		if( empty($searchProp->alliance) || $searchProp->alliance === 'all' ){
			unset($searchProp->alliance);
		}

		if(
				empty($searchProp->liga)
				||
				(
					is_array($searchProp->liga)
					&&
					count(array_diff( $searchProp->liga, array('I','II','III') ) ) === 0
					&&
					count($searchProp->liga) === 3
				)
		){
			unset($searchProp->liga);
		}

		if( empty($searchProp->premium) || $searchProp->premium === 'all' ){
			unset($searchProp->premium);
		}

		if( empty($searchProp->onlyGateAvaliable) || $searchProp->onlyGateAvaliable === '0' ){
			unset($searchProp->onlyGateAvaliable);
		}

		if(
				empty($searchProp->rase)
				||
				(
					is_array($searchProp->rase)
					&&
					count(array_diff( $searchProp->rase, array_keys($rases) ) ) === 0
					&&
					count($searchProp->rase) === count($rases)
				)
		){
			unset($searchProp->rase);
		}

		if( empty($searchProp->filterAlliance) ){
			unset($searchProp->filterAlliance);
		}


		if( empty($searchProp->filterAllianceMod) || empty($searchProp->filterAlliance) ){
			unset($searchProp->filterAllianceMod);
		}

		$sliders = array('compl', 'rankold', 'ranknew', 'bo', 'nra', 'ra', 'level', 'arch' ,'build', 'scien');
		foreach($sliders as $name)
		{
			if(
				( empty($searchProp->{"{$name}Min"}) && empty($searchProp->{"{$name}Max"}) )
				||
				( $searchProp->{"{$name}Min"} == 0 && isset($max[$name]) && $searchProp->{"{$name}Max"} == $max[$name] )
			){
				unset($searchProp->{"{$name}Min"});
				unset($searchProp->{"{$name}Max"});
			}
		}

		return $searchProp;
	}

	/**
	 * валидация объекта настроек поиска
	 * @var searchProp - stdClass of search property
	 * @var rases - array of avaliable rases
	 * @return bool
	 */
	public function _validateFullSearchProp( stdClass $searchProp, $rases )
	{
		return (
				(empty($searchProp->gate)
					||
				in_array($searchProp->gate, array('all','open','close')) ) &&    //ворота

				(isset($searchProp->ring)
					&&
				in_array($searchProp->ring, array(1,2,3,4,12,13,23,123) ) ) &&       //кольцо

				(empty($searchProp->alliance)
					||
				in_array($searchProp->alliance, array('all','yes','none') ) ) && //альянс

				(empty($searchProp->liga)
					||
				(is_array($searchProp->liga) && count(array_diff( $searchProp->liga, array('I','II','III') ) ) == 0 ) ) && //лига

				(empty($searchProp->premium)
					||
				in_array($searchProp->premium, array('all','yes','none') ) ) && //премиум

				(empty($searchProp->onlyGateAvaliable)
					||
				in_array($searchProp->onlyGateAvaliable, array('1','0') ) ) && //скрывание недоступных по расширенным статусам ворот

				(empty($searchProp->rase)
					||
				(is_array($searchProp->rase) && count(array_diff( $searchProp->rase, array_keys($rases) ) ) == 0 ) ) && //расса

				(empty($searchProp->filterAlliance)
					||
				(is_array($searchProp->filterAlliance) && count( $searchProp->filterAlliance ) <= 20 ) ) && //фильтр альянсов (ids)

				(empty($searchProp->filterAllianceMod)
					||
				in_array($searchProp->filterAllianceMod, array('only','not') ) ) && //фильтр альянсов (модификатор)


				((empty($searchProp->complMin) && empty($searchProp->complMax))
					||
				Mylib_Utils::validateSlide($searchProp->complMin, $searchProp->complMax) ) &&

				((empty($searchProp->rankoldMin) && empty($searchProp->rankoldMax))
					||
				Mylib_Utils::validateSlide($searchProp->rankoldMin, $searchProp->rankoldMax) ) &&

				((empty($searchProp->ranknewMin) && empty($searchProp->ranknewMax))
					||
				Mylib_Utils::validateSlide($searchProp->ranknewMin, $searchProp->ranknewMax) ) &&

				((empty($searchProp->boMin) && empty($searchProp->boMax))
					||
				Mylib_Utils::validateSlide($searchProp->boMin, $searchProp->boMax) ) &&

				((empty($searchProp->nraMin) && empty($searchProp->nraMax))
					||
				Mylib_Utils::validateSlide($searchProp->nraMin, $searchProp->nraMax) ) &&

				((empty($searchProp->raMin) && empty($searchProp->raMax))
					||
				Mylib_Utils::validateSlide($searchProp->raMin, $searchProp->raMax) ) &&

				((empty($searchProp->levelMin) && empty($searchProp->levelMax))
					||
				Mylib_Utils::validateSlide($searchProp->levelMin, $searchProp->levelMax) ) &&

				((empty($searchProp->archMin) && empty($searchProp->archMax))
					||
				Mylib_Utils::validateSlide($searchProp->archMin, $searchProp->archMax) ) &&

				((empty($searchProp->buildMin) && empty($searchProp->buildMax))
					||
				Mylib_Utils::validateSlide($searchProp->buildMin, $searchProp->buildMax) ) &&

				((empty($searchProp->scienMin) && empty($searchProp->scienMax))
					||
				Mylib_Utils::validateSlide($searchProp->scienMin, $searchProp->scienMax) )

			) ? true : false;
	}
}