<?php

/*
 * Структура данных одной строки CSV
 * @TODO сеттеры и геттеры
 */
class Mylib_Parser_CSV_Row
{
    private $_data = array(); //распарсенная строка

    public function setParam($param, $value)
    {
        $this->_data[$param] = $value;
    }

    public function getParam($param)
    {
        return (isset($this->_data[$param])) ? $this->_data[$param] : null;
    }

    public function isNewAlliance()
    {
        return is_null($this->getParam('allianceId'));
    }
    public function isNewPlayer()
    {
        return $this->getParam('isNew');
    }

	public function exportData()
	{
		return array(
			//'id' => $this->getParam('id'),
			'id_rase' => $this->getParam('raseId'),
			'id_alliance' => $this->getParam('allianceId'),
			'nik' => $this->getParam('nik'),
			'ring' => $this->getParam('ring'),
			'compl' => $this->getParam('compl'),
			'sota' => $this->getParam('sota'),
			'dom_name' => $this->getParam('domName'),
			//'rank_old' => $this->getParam('rankOld'),
			//'bo' => $this->getParam('bo'),
			//'gate' => $this->getParam('gate'),
		);
	}

    public function exportColony()
    {
        return array();
    }
}
?>