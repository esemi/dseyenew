<?php
/*
 * возвращает имя логотипа
 * используется для праздничных логотипов
 */
class Zend_View_Helper_CustomLogo extends Zend_View_Helper_Abstract
{
	public function customLogo()
	{
		$date = getdate();

		//логотип дня рожденья
		if($date['mon'] == 2 && $date['mday'] >= 11 && $date['mday'] < 18)
			return 'logo_birthday';

		if($date['mon'] == 2 && $date['mday'] == 23)
			return 'logo_man';

		if($date['mon'] == 3 && $date['mday'] == 8)
			return 'logo_woman';

		return 'logo';
	}
}
