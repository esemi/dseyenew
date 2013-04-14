<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Exception
 *
 * @author esemi
 */
class Mylib_Exception_NotFound extends Exception
{
	public function __construct($message, $code=404, $previous=null)
	{
		parent::__construct($message, $code, $previous);
	}
}

?>
