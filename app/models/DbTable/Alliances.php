<?php

class App_Model_DbTable_Alliances extends Mylib_DbTable_Cached
{

    protected $_name = 'alliances';
    protected $_cacheName = 'up';

    protected $_tagsMap = array(
        'listWorldAlliances' => array('up','dshelpra','ranks','nra'),
        'getFilterAlliance' => array('up'),
        'getData' => array('up'),
    );

	/*
	 * листинг альянсов мира
	 */
	public function notcached_listWorldAlliances( $idW, $page, $countItem, $sort, $count = 20 )
	{
		$select = $this->select()
				->setIntegrityCheck(false)
				->from($this, array( 'id', 'name',))
				->joinRight('alliances_property', "{$this->_name}.id = alliances_property.id_alliance",
						array(
					'count' => '(count_voran + count_liens + count_psol)',
					'count_colony' => '(count_colony_voran + count_colony_liens + count_colony_psol)',
					'rank_old' => '(rank_old_voran + rank_old_liens + rank_old_psol)',
					'bo' => '(bo_voran + bo_liens + bo_psol)',
					'avg_rank_old' => '(rank_old_voran + rank_old_liens + rank_old_psol) / (count_voran + count_liens + count_psol)',
					'avg_bo' => '(bo_voran + bo_liens + bo_psol) / (count_voran + count_liens + count_psol)',
					'avg_ra' => '(ra_voran + ra_liens + ra_psol) / (count_voran + count_liens + count_psol)',
					'avg_nra' => '(nra_voran + nra_liens + nra_psol) / (count_voran + count_liens + count_psol)',
					'rank_new' => '(rank_new_voran + rank_new_liens + rank_new_psol)',
					'arch' => '(archeology_voran + archeology_liens + archeology_psol)',
					'build' => '(building_voran + building_liens + building_psol)',
					'scien' => '(science_voran + science_liens + science_psol)' ))
				->where("status = 'active'")
				->where('id_world = ?', $idW, Zend_Db::INT_TYPE);

		$this->_sortDecode($select, $sort);

		$paginator = Zend_Paginator::factory($select);
		$paginator->getAdapter()->setRowCount($countItem);
		$paginator->setCurrentPageNumber($page)
				->setItemCountPerPage($count)
				->setPageRange(5);

		return $paginator;
	}

    /*
     * id-name альянсов для фильтра в поиске
     */
    public function notcached_getFilterAlliance( $idW, $count = 20 )
    {
        $select = $this->select()
                ->setIntegrityCheck(false)
                ->from($this, array( 'id', 'name' ))
                ->joinRight('alliances_property', "{$this->_name}.id = alliances_property.id_alliance",
                        array('sorter' => '(rank_old_voran + rank_old_liens + rank_old_psol)'))
                ->where("status = 'active'")
                ->where('id_world = ?', $idW, Zend_Db::INT_TYPE)
                ->order('sorter DESC')
                ->limit($count);
        $result = $this->fetchAll($select);

        $out = array();
        foreach ($result as $val)
            $out[$val['id']] = $val['name'];

        return $out;
    }

    /*
     * получить имя альянса по id
     */
    public function getName( $idA )
    {
        $data = $this->getData($idA);
        return $data['name'];
    }

    /*
     * существует ли альянс с таким id в этом мире
     */
    public function validate( $idA, $idW )
    {
        $select = $this->select()
                        ->from($this, array( 'id' ))
                        ->where('id_world = ?', $idW, Zend_Db::INT_TYPE)
                        ->where('id = ?', $idA, Zend_Db::INT_TYPE)
                        ->limit(1);
        $result = $this->fetchRow($select);
        return !is_null($result);
    }

    /*
     * получить текущие свойства конкретного альянса
     */
    public function notcached_getData( $idA )
    {
        $select = $this->select()
                        ->from($this, array(
                            'id_world', 'name', 'status',
                            'DATE_FORMAT(`date_create` , \'%d.%m.%Y\') as date_create',
                            'DATE_FORMAT(`date_delete` , \'%d.%m.%Y\') as date_delete'))
                        ->where('id = ?', $idA, Zend_Db::INT_TYPE)
                        ->limit(1);
        return $this->fetchRow($select)->toArray();
    }

    /*
     * расшифровка столбца сортировки
     */
    private function _sortDecode($select, $sort)
    {
        switch ($sort)
        {
            case 'count':
                $select->order('count DESC');
                break;
            case 'count_r':
                $select->order('count ASC');
                break;
            case 'count_colony':
                $select->order('count_colony DESC');
                break;
            case 'count_colony_r':
                $select->order('count_colony ASC');
                break;
            case 'rank_old':
                $select->order('rank_old DESC');
                break;
            case 'rank_old_r':
                $select->order('rank_old ASC');
                break;
            case 'rank_new':
                $select->order('rank_new DESC');
                break;
            case 'rank_new_r':
                $select->order('rank_new ASC');
                break;
            case 'bo':
                $select->order('bo DESC');
                break;
            case 'bo_r':
                $select->order('bo ASC');
                break;
            case 'avg_rank_old':
                $select->order('avg_rank_old DESC');
                break;
            case 'avg_rank_old_r':
                $select->order('avg_rank_old ASC');
                break;
            case 'avg_bo':
                $select->order('avg_bo DESC');
                break;
            case 'avg_bo_r':
                $select->order('avg_bo ASC');
                break;
            case 'avg_ra':
                $select->order('avg_ra DESC');
                break;
            case 'avg_ra_r':
                $select->order('avg_ra ASC');
                break;
			case 'avg_nra':
                $select->order('avg_nra DESC');
                break;
            case 'avg_nra_r':
                $select->order('avg_nra ASC');
                break;
            case 'arch':
                $select->order('arch DESC');
                break;
            case 'arch_r':
                $select->order('arch ASC');
                break;
            case 'build':
                $select->order('build DESC');
                break;
            case 'build_r':
                $select->order('build ASC');
                break;
            case 'scien':
                $select->order('scien DESC');
                break;
            case 'scien_r':
                $select->order('scien ASC');
                break;
        }

    }

    /*
     * для up cli
     */
    public function getAllByWorld($idW)
    {
        $select = $this->select()
                ->from($this, array( 'id', 'name' ))
                ->where('id_world = ?', $idW, Zend_Db::INT_TYPE);
        return $this->fetchAll($select)->toArray();
    }

    /*
     * для up cli
     */
    public function add($idW, $name)
    {
        return $this->insert( array(
            'id_world' => $idW,
            'name' =>  $name,
            'date_create' => new Zend_Db_Expr('NOW()') ));
    }

    /*
     * множественное удаление альянсов (отметка об удалённом статусе)
     * для up cli
     */
    public function del(Array $ids)
    {
        return $this->update(
                array(
                    'status' => 'delete',
                    'date_delete' => new Zend_Db_Expr('NOW()') ),
                array(
                    'id IN (?)' => $ids,
                    'status = ?' => 'active',)
                );
    }

    /*
     * отметка альянса как живого
     * для up cli
     */
    public function setActive(Array $ids)
    {
        return $this->update(
                array(
                    'status' => 'active',
                    'date_delete' => new Zend_Db_Expr('NULL')),
                array(
                    'id IN (?)' => $ids)
                );
    }

    /*
     * количество живых альянсов в мире
     * up cli
     */
    public function getCountByWorld($idW)
    {
        $select = $this->select()
                ->from($this, array( 'count' => 'COUNT(*)' ))
                ->where('status = "active"')
                ->where('id_world = ?', $idW, Zend_Db::INT_TYPE)
                ->limit(1);

        return (int)$this->fetchRow($select)->count;
    }


}
