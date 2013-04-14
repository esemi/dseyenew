<?php

/*
 * Наследник парсеров строчек CSV
 */
abstract class Mylib_Parser_CSV_Abstract
{
    protected 
        $_err = null,
        $_rasesIds = array(),
        $_rasesNames = array(),
        $_alliances = array(),
        $_alliancesDelete = array(),
        $_players = array(),
        $_uniqNiks = array(),
        $_source = array(),
        $_data = null; //структура данных одной строки


    final public static function factory($type)
    {
        switch($type)
        {
            case 'old_rank':
                return new Mylib_Parser_CSV_Old();
                break;
            case 'new_rank':
                return new Mylib_Parser_CSV_Old();
                break;

            default:
                throw new Exception('Undefined parser type', 100);
                break;
        }

    } 
    
    final public function setDataContainer($obj)
    {
        $this->_data = $obj;
        return $this;
    }
    final public function setPlayers(Array $players)
    {        
        foreach($players as $item)            
            $this->addPlayer( $item['id'], $item['nik']);
        return $this;
    }
    final public function setRases(Array $rases)
    {
        foreach($rases as $item)
        {
            $this->_rasesIds[$item['name']] = $item['id'];
            $this->_rasesNames[$item['id']] = $item['name'];
        }
        return $this;
    }
    final public function setAlliances(Array $alliances)
    {        
        foreach($alliances as $item)
            $this->addAlliance( $item['id'], $item['name']);
        
        $this->_alliancesDelete = $this->_alliances;
        
        return $this;
    }
        
    public function addAlliance($id, $name)
    {
        $this->_alliances[$name] = $id;
    }
    protected function findAlliance($name)
    {
        return (isset($this->_alliances[$name])) ? $this->_alliances[$name] : null;
    }
    
    protected function checkAlliance($name)
    {
        unset($this->_alliancesDelete[$name]);
    }


    protected function addPlayer($id, $name)
    {        
        $this->_players[$name] = $id;
    }

    protected function delPlayer($name)
    {
        unset($this->_players[$name]);
    }
    
    protected function findPlayer($name)
    {
        return (isset($this->_players[$name])) ? $this->_players[$name] : null;
    }

    /*
     * возвращает оставшихся из изначально заданных игроков
     * в процессе обработки каждый обновлённый игрок удаляется из данного массива
     * оставшиеся - удалённые игроки
     */
    public function getOldPlayersId()
    {        
        return array_values($this->_players);
    }
    /*
     * возвращает оставшихся из изначально заданных альянсов
     * в процессе обработки игроков каждый непустой альянс удаляется из данного массива
     * оставшиеся - пустые альянсы и должны быть удалены
     */
    public function getOldAlliancesId()
    {        
        return array_values($this->_alliancesDelete);
    }

    final protected function _clearError()
    {
        $this->_err = null;
    }
    final protected function _addErr($err)
    {        
        $this->_err = $err;
    }
    final public function getErr()
    {
        return $this->_err;
    }
    

    /*
     * validate and parse one row of csv
     * @return bool
     */
    abstract public function parse($str);
    
}
?>