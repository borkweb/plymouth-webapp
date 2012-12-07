<?php

class ECommerceSmarty extends PSUSmarty
{
	function __construct()
	{
		parent::__construct();
		$this->template_dir = $GLOBALS['TEMPLATES'];
	}//end constructor
}//end class ECommerceSmarty
?>