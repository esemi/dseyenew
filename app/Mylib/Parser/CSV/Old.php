<?php
/*
 * Парсер старых рейтингов
 */
class Mylib_Parser_CSV_Old extends Mylib_Parser_CSV_Abstract
{
	public function parse($str)
	{
		$this->_clearError();
		$fake_rase = array( "Voraner", "Liensu", "Psolao" );

		$arr = explode(";", $str);

		if(count($arr) < 8)
			$this->_addErr('count cols < 8');
		elseif( strlen($arr[1]) < 3 )
			$this->_addErr('short nik');
		elseif( !preg_match('/^[\wА-Яа-яёЁ\s.\-]{3,50}$/ui',$arr[1]) )
			$this->_addErr('invalid nik');
		elseif( count(explode('.', $arr[3])) < 3 )
			$this->_addErr('invalid adresses');
		elseif( count(explode(',', $arr[2])) !== count(explode(',', $arr[3])) )
			$this->_addErr('invalid relative colony (adresses - name)');
		elseif( !in_array(substr($arr[3], 0, 1), $this->_rasesIds) )
			$this->_addErr('invalid ring');
		elseif( !in_array($arr[5], array_merge( $fake_rase, $this->_rasesNames ) ) )
			$this->_addErr('invalid rase');
		elseif( in_array($arr[1], $this->_uniqNiks) )
			$this->_addErr('not unique nik');

		if( !is_null($this->getErr()) )
			return false;

		$this->_uniqNiks[] = $tmp = trim($arr[1]);
		$this->_data->setParam('nik', $tmp);
		if( !is_null($this->findPlayer($tmp)) )
		{
			$this->_data->setParam('id', $this->findPlayer($tmp));
			$this->_data->setParam('isNew', false);
			$this->delPlayer($tmp);
		}else{
			$this->_data->setParam('isNew', true);
		}

		$this->_data->setParam('allianceName', mb_strtoupper(trim($arr[4])));
		if($this->_data->getParam('allianceName') == '')
			$this->_data->setParam('allianceName', Zend_Controller_Front::getInstance()->getParam('bootstrap')->getOption('nameNeutral'));

		if( !is_null($this->findAlliance($this->_data->getParam('allianceName'))) )
		{
			$this->_data->setParam('allianceId', $this->findAlliance($this->_data->getParam('allianceName')) );
			$this->checkAlliance($this->_data->getParam('allianceName'));
		}

		$this->_data->setParam('raseName', trim(str_replace($fake_rase, $this->_rasesNames, $arr[5])));
		$this->_data->setParam('raseId', $this->_rasesIds[$this->_data->getParam('raseName')]);

		//$this->_data->setParam('rankOld', intval($arr[6]));
		//$this->_data->setParam('bo', floatval($arr[7]));
		//$this->_data->setParam('gate', (substr(trim($arr[8]), -1) == '1') ? 1 : 0);

		//адреса и имена сот
		$names = explode(',', trim($arr[2]));
		$adresses = explode(',', trim($arr[3]));

		//дом
		$this->_data->setParam('domName', array_shift($names));
		$tmp = explode('.', array_shift($adresses));
		$this->_data->setParam('ring', $tmp[0]);
		$this->_data->setParam('compl', $tmp[1]);
		$this->_data->setParam('sota', $tmp[2]);

		//колонии
		$this->_data->setParam('colName', $names);
		$tmp = array();
		foreach($adresses as $adr)
		{
			$t = explode('.', $adr);
			$tmp[] = array('compl' => $t[1], 'sota' => $t[2]);
		}
		$this->_data->setParam('colAdr', $tmp);

		return true;
	}
}
