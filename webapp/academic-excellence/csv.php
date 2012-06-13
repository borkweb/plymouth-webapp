<?php

if( 'admin' !== $_SESSION['user_type'] )
{
	$_SESSION['errors'][] = 'You are not allowed to access the Academic Excellence CSV.';
	PSU::redirect( $GLOBALS['BASE_URL'] . '/' );
}

$tpl = new AETemplate();

header("Content-type: text/csv");

$term = (int)$_GET['term'];

$sql = 'SELECT *, TRIM(CONCAT(name_first, " ", name_middle)) AS first_middle FROM academic_excellence WHERE term = ' . $term;
$results = PSU::db('myplymouth')->GetAll($sql);

$tpl->assign('students', $results);
$tpl->display('csv.tpl', false);
