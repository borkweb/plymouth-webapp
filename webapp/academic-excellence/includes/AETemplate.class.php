<?php

/**
 * AETemplate provides a custom PSUTemplate object for the Academic Excellence
 * application.
 * @ingroup acadexcel
 */
class AETemplate extends PSU\Template
{
	function __construct($title = null)
	{
		parent::__construct($title);

		$this->assign('user_type', $_SESSION['user_type']);
	}
}//end AETemplate
