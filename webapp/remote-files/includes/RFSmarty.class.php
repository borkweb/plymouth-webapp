<?php

require_once('PSUSmarty.class.php');

class RFSmarty extends PSUSmarty
{
	function __construct()
	{
		parent::__construct();
		
		$this->template_dir = $GLOBALS['BASE_DIR'] . '/templates';

		// custom template functions
		$this->assign('RFP', $GLOBALS['RFP']);

		// TODO: PSUSmarty.class.php should be updated to assign $PHP on fetch
		$this->assign('PHP', $GLOBALS);
		$this->assign('SESSION', $_SESSION);
	}
	
	function exception2error($e)
	{
		$error_message = sprintf('%s (%d)', $e->GetMessage(), $e->GetCode());
		array_push($_SESSION['errors'], $error_message);
	}
}

// vim:ts=2:sw=2:noet:
?>
