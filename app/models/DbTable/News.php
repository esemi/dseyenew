<?php

/*
 * модель новостей
 */
class App_Model_DbTable_News extends Mylib_DbTable_Cached
{

	protected
			$_name = 'news',
			$_cacheName = 'default';
	/**
	 * получить список новостей
	 */
	public function notcached_listAll()
	{
		$select = $this->select()
				->from($this, array(
					'id', 'cat', 'title', 'text',
					'date' => 'DATE_FORMAT(`date` , \'%d.%m.%Y\')',
					'date_unix' => 'UNIX_TIMESTAMP(`date`)' ))
				->order('news.date DESC')
				->limit(50);

		return $this->fetchAll($select);
	}

}
