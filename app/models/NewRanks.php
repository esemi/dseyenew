<?php

class App_Model_NewRanks extends App_Model_Abstract_RemoteRanks
{
	public function parsePlayerStr( $domElement )
	{
		$result = new stdClass();
		$result->success = false;
		$result->error = array();
		$result->data = array();

		$items = $domElement->getElementsByTagName('td');

		$tmpData=array();
		foreach ($items as $item)
			$tmpData[] = trim($item->nodeValue);

		//не битая ли строка
		if( count($tmpData) != 15 )
		{
			$result->error[] = "строка битая";
		}else{

			$result->data['nik'] = $tmpData[1];
			if( !$this->_checkNik($result->data['nik']) )
				$result->error[] = "стрёмный ник {$result->data['nik']}";

			if( !$this->_checkUniqNik($result->data['nik']) ){
				$result->error[] = "не уникальный ник {$result->data['nik']}";
			}else{
				$this->_addUniqNik($result->data['nik']);
			}

			//выдираем рейтинг
			$result->data['rank_new'] = $tmpData[6];
			if( !$this->_checkRankNew($result->data['rank_new']) )
				$result->error[] = "стрёмный рейтинг {$result->data['rank_new']}";

			//выдираем уровень
			$result->data['level'] = ($tmpData[7] != '') ? $tmpData[7] : 0;
			if( !$this->_checkLevel($result->data['level']) )
				$result->error[] = "стрёмный уровень {$result->data['level']}";

			//выдираем лигу
			$result->data['liga'] = $tmpData[8];
			if( !$this->_checkLiga($result->data['liga']) )
				$result->error[] = "стрёмная лига {$result->data['liga']}";

			//археология
			$result->data['arch'] = $tmpData[9];
			if( !$this->_checkArch($result->data['arch']) )
				$result->error[] = "стрёмная археология {$result->data['arch']}";

			//стройка
			$result->data['build'] = $tmpData[10];
			if( !$this->_checkBuild($result->data['build']) )
				$result->error[] = "стрёмная стройка {$result->data['build']}";

			//наука
			$result->data['scien'] = $tmpData[11];
			if( !$this->_checkScien($result->data['scien']) )
				$result->error[] = "стрёмная наука {$result->data['scien']}";
		}

		if( count($result->error) === 0 )
			$result->success = true;

		return $result;
	}
}
?>
