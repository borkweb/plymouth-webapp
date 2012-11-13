<?php
/****************[ DO NOT WANT ]*******************/
require_once 'IDMObject.class.php';
if($_SESSION['username'] == 'nrporter')
{
	//$theme->add('chili-cookoff', 'my.css', true);
}//end if

if( in_array( $_SESSION[ 'username' ], $prank_crew ) )
{
	//$theme->add('miss_al', 'my.css', true);
}//end if
