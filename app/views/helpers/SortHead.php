<?php

/*
 * возвращает сортировочный заголовок таблицы
 */

class Zend_View_Helper_SortHead extends Zend_View_Helper_Abstract
{
	public function sortHead($currentVal, $val, $str, $save = null)
	{
		$urlOpt = array( 'sort' => ($currentVal == $val) ? "{$val}_r" : $val);

		if( !is_null($save) )
			$urlOpt['save'] = $save;
		else
			$urlOpt['page'] = 1;



		$href = $this->view->url( $urlOpt );

		if( $currentVal == $val || $currentVal == "{$val}_r" )
		{
			$class =  ($currentVal == $val) ? 'sort-arrow-down' : 'sort-arrow-up';
		}else{
			$class = 'may-be-sorted';
		}

		return sprintf('<a class="sort-head %s" href="%s">%s <span class="arrow-block"></span></a>', $class, $href, $str);
	}

}

