<?php

/*
 * модель версий игры
 */
class App_Model_DbTable_GameVersions extends Mylib_DbTable_Cached
{
    protected $_name = 'game_versions';        
    protected $_cacheName = 'default';
    
    /*
     * урлы для статистики онлайна всех доступных версий игры
     */
    public function notcached_getAllForStat()
    {
        $select = $this->select()
                    ->from($this, array('id','name', 'url' => 'onlinestat_url'))
                    ->where('onlinestat_url IS NOT NULL');

        return $this->fetchAll($select)->toArray();
    }
    
    /**
     * урлы для графика на главной
	 * @param array|string $name Array of names or one name for search versions
	 * @return array
     */
    public function notcached_getByName($name)
    {
        $select = $this->select()
                    ->from($this, array('id', 'name', 'url' => 'onlinestat_url'));
		if( is_array($name) )
		{
            $select->where('name IN (?)', $name);			
		}else{
			$select->where('name = ?', $name);			
		}
		
        return $this->fetchAll($select)->toArray();
    }
    
    
    /*
     * аналог find
     */
    public function notcached_getData( $idV )
    {
        $select = $this->select()
                        ->where('id = ?', $idV, Zend_Db::INT_TYPE)
                        ->limit(1);
        return $this->fetchRow($select)->toArray();
    }
    
    public function availableForumSearch($idV)
    {
        $data = $this->getData($idV);
        return !is_null($data['forum_search_pattern']);
    }
    
    public function getForumSearch($idV)
    {
        $data = $this->getData($idV);
        return $data['forum_search_pattern'];
    }
}
