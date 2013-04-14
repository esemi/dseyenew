<?php

/*
 * возвращает форму с ранж слайдером
 * @TODO форматер больших чисел
 * @TODO логирафмическая шкала
 */

class Zend_View_Helper_FormSlider extends Zend_View_Helper_Abstract
{
	public function FormSlider($name, $tagname, $maxParams, $searchParams)
	{
		if($maxParams[$tagname] == 0)
			return false;

		$min_name = "{$tagname}Min";
		$max_name = "{$tagname}Max";
		echo "<span class='js-slider-validate'>{$name}: "
			. $this->view->formText("{$tagname}_min", isset($searchParams->$min_name) ? $searchParams->$min_name : 0, array())
			. ' &mdash; '
			. $this->view->formText("{$tagname}_max", isset($searchParams->$max_name) ? $searchParams->$max_name : $maxParams[$tagname], array())
			. "</span><div class='js-ui-slider' name='{$tagname}' max='{$maxParams[$tagname]}'></div>";
	}


}
