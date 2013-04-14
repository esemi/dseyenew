<?php

class App_Model_OldRanks extends App_Model_Abstract_RemoteRanks
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
		if( count($tmpData) != 9 )
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
			$result->data['rank_old'] = $tmpData[6];
			if( !$this->_checkRankOld($result->data['rank_old']) )
				$result->error[] = "стрёмный рейтинг {$result->data['rank_old']}";

			//выдираем БР (не БО, а БР)
			$result->data['bo'] = floatval($tmpData[8]);
			if( !$this->_checkBo($tmpData[8]) )
				$result->error[] = "стрёмный боевой рейтинг {$tmpData[8]}";
		}

		if( count($result->error) === 0 )
			$result->success = true;

		return $result;
	}
}
?>
