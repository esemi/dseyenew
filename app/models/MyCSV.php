<?php
/*
 * Модель работы с сосбтвенными csv файлами.
 */
class App_Model_MyCSV
{
	protected
			$_path = null,
			$_archivePath = null,
			$_limit = null,
			$_gzLevel = null;

	public function __construct($limit, $gzLevel, $path, $archivePath)
	{
		$this->_path = $path;
		$this->_limit = $limit;
		$this->_gzLevel = $gzLevel;
		$this->_archivePath = $archivePath;
	}

	public function createMain( $resource, $idW, $worldName )
	{
		$filename = mb_strtolower( str_replace(' ', '_', $worldName ), 'utf8');

		$handle = fopen('php://temp', 'w+');
		$params = array(
			'id','nik','dom_adr','dom_name','gate','colony_adr',
			'colony_name','alliance','rase','level','liga', 'mesto', 'rank_old',
			'bo', 'rank_new', 'archeology','building','science','nra','ra', 'delta_rank_old', 'delta_bo',
			'gate_shield', 'gate_newbee', 'gate_ban', 'premium');
		fputcsv($handle, $params, ';', '"');

		$players = $resource->getDataForCsv($idW);
		foreach($players as $player)
		{
			//билиать, надо было писать на питоне (:
			$data = array();
			foreach($params as $key)
				$data[] = $player[$key];
			fputcsv($handle, $data, ';', '"');
		}
		rewind($handle);
		$content = stream_get_contents($handle);
		fclose($handle);

		file_put_contents("{$this->_path}{$filename}.csv", $content);
		file_put_contents("{$this->_path}{$filename}.csv.gz", gzencode($content, $this->_gzLevel));

		//добавляем архивный файл
		return copy( "{$this->_path}{$filename}.csv.gz", sprintf("{$this->_archivePath}{$filename}_%s.csv.gz", date('Y-m-d_H:i:s')) );
	}
}

?>
