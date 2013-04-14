<?php
/*
 * выводит сообщение "обратите внимание" с иконкой
 *
 */
class Zend_View_Helper_PrintWarning extends Zend_View_Helper_Abstract
{
	public function printWarning($mess=null)
	{
		print '<span class="warning-icon"></span> <span class="color-logo bold">Обратите внимание</span>';
	}
}
