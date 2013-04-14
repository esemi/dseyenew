<?php

class Zend_View_Helper_DbProfiler extends Zend_View_Helper_Abstract
{
	public function DbProfiler()
	{
		$profiler = Zend_Db_Table_Abstract::getDefaultAdapter()->getProfiler();

		return sprintf('Выполнено %d запросов к БД за %.4f сек.',
				$profiler->getTotalNumQueries(),
				$profiler->getTotalElapsedSecs() );
	}
}
