<?php

header('Content-type: text/javascript');
//$GLOBALS['BannerIDM']->db->debug = true;

$attribute = $_GET['attribute'];
list($type_id, $type) = $GLOBALS['BannerIDM']->any2type($_GET['type']);

$args = array(
	'pa.type_id' => $type_id,
	'pa.attribute' => $attribute
);

$results = $GLOBALS['BannerIDM']->getUsersByAttribute($args, null, 'i.first_name,i.last_name,i.username,l.source');

if($results === false)
{
	die( json_encode(array()) );
}

$tmp = array();
foreach($results as $result)
{
	$username = $result['username'];

	if( !isset($tmp[$username]) )
	{
		$tmp[$username] = $result;
	}
	elseif($tmp[$username]['source'] != $GLOBALS['IDM_SOURCE'])
	{
		$tmp[$username]['source'] = $GLOBALS['IDM_SOURCE'];
	}
}
$results = $tmp;

uasort($results, create_function('$a,$b', '$cmp = strnatcasecmp($a["last_name"], $b["last_name"]); return $cmp == 0 ? strnatcasecmp($a["first_name"], $b["first_name"]) : $cmp;'));

echo json_encode(array_values($results));
