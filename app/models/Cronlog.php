<?php

/*
 * работает с логами кроновских скриптов
 * пишет в БД или файл
 *  Zend_Registry::get('log')
 */
class App_Model_Cronlog
{
	protected $_type,
			  $_log,
			  $_result, // success|warning|fail|none
			  $_table,
			  $_mctimeStart;

	public function __construct()
	{
		$opt =  Zend_Controller_Front::getInstance()->getParam('bootstrap')->getOption('cronlog');

		$this->_mctimeStart = microtime(1);
		$this->_table = new App_Model_DbTable_CronLogs();
		$this->_type = $opt['type'];
		$this->_log = fopen("php://temp", 'w+');
		$this->setResultError();
	}

	public function add($mes, $addStat = false)
	{
		if(is_array($mes) || is_object($mes))
			$mes = print_r($mes, true);

		fwrite( $this->_log,  date('H:i:s') . " - {$mes}<br>");
		if($addStat)
			$this->_addStat();
	}

	public function get()
	{
		rewind($this->_log);
		return stream_get_contents($this->_log);
	}

	public function setResultSuccess()
	{
		$this->_result = 'success';
	}

	public function setResultError()
	{
		$this->_result = 'FAIL';
	}

	public function setResultNone()
	{
		$this->_result = 'none';
	}
	public function setResultWarn()
	{
		$this->_result = 'warn';
	}

	/*
	 * сохранить лог
	 */
	public function save($type)
	{
		$action = Zend_Controller_Front::getInstance()->getRequest()->getActionName();

		$this->_addStat();

		$txt = $this->get();

		switch ($this->_type)
		{
			case 'db':
				$this->_table->insert(array( 'type' => $type, 'text' => $txt, 'result' => strtolower($this->_result) ));
				return date("d_F_Y__H:i")." {$action} log to database {$this->_result}\n";
			break;

			case 'file':
				$handle = fopen( APPLICATION_PATH . "/logs/{$action}_" . date("d_F_Y__H-i") . '.html' , 'w' );
				fwrite( $handle,  nl2br($txt));
				fclose( $handle );
				return date("d_F_Y__H:i")." {$action} log to file {$this->_result}\n";
			break;

			case 'text':
			default :
				return str_replace(array('<br/>', '<br>'), "\n", $txt);
			break;
		}
	}


	protected function _addStat()
	{
		$this->add($this->_getStat());
	}


	protected function _getStat()
	{
		$profiler = Zend_Db_Table_Abstract::getDefaultAdapter()->getProfiler();
		return sprintf("time= %.4f; memory= %.4f; memory peak= %.4f; query_count= %d; query_time= %.4f",
			microtime(1) - $this->_mctimeStart,
			memory_get_usage()/1024/1024,
			memory_get_peak_usage()/1024/1024,
			$profiler->getTotalNumQueries(),
			$profiler->getTotalElapsedSecs());
	}
}
?>
