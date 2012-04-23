<?php

header('Content-type: application/json');

$idm = new IDMObject($GLOBALS['BANNER']);

if( isset($_GET['attribute']) )
{
	$attribute = $_GET['attribute'];
	$children = $idm->getChildAttributes($attribute);

	$attributes = array();
	foreach($children as $type)
	{
		$attributes = array_merge($attributes, array_keys($type));
	}
}
else
{
	$roles = $idm->getRoles();

	$attributes = array();
	foreach($roles as &$role)
	{
		$attributes[] = $role['attribute'];
	}
}

echo json_encode($attributes);
