<?php
/*
 * Lazy load db table classes models
 */
class Action_Helper_ModelLoad extends Zend_Controller_Action_Helper_Abstract
{
	private $_instances = array();

	/*
	 * @param string $name Name of model class
	 * @param bool $new Create new instance
	 */
	public function load($name, $new = false)
	{
		$classTable = sprintf('App_Model_DbTable_%s', $name);

		if( $new === true || !isset($this->_instances[$name]) )
			$this->_instances[$name] = new $classTable;

		return $this->_instances[$name];
	}



	public function direct($name, $new=false)
	{
		return $this->load($name, $new);
	}
}