<?php

require_once 'PSUTools.class.php';

ini_set('memory_limit', '512M');
set_time_limit(-1);

//PSU::db('portal')->debug = true;

function purge_temp_table()
{
	$sql = 'TRUNCATE TABLE banner_roles_temp'; 
	PSU::db('portal')->execute($sql);
}

function swap_tables()
{

	$sql =	'RENAME TABLE banner_roles_temp TO roless';
	PSU::db('portal')->execute($sql);

	$sql =	'RENAME TABLE banner_roles TO banner_roles_temp';
	PSU::db('portal')->execute($sql);

	$sql =	'RENAME TABLE roless TO banner_roles';
	PSU::db('portal')->execute($sql);

}

function update_temp_table()
{
	$sql = 'SELECT gorirol_pidm, LOWER(gorirol_role) gorirol_role FROM gorirol';
	$results = PSU::db('psc1')->Execute($sql);
	
	$populate = 'INSERT INTO `banner_roles_temp` (`pidm`, `role`) VALUES ';
	$count = 0;
	$params = array();
	
	foreach($results as $result)
	{
		$populate .= ($params ? "," : "") . "(?,?)";

		$count++;
		
		$params[] = $result['gorirol_pidm']; 
		$params[] = $result['gorirol_role']; 

		if($count%1000==0)
		{
			PSU::db('portal')->execute($populate, $params);
			$populate = 'INSERT INTO `banner_roles_temp` (`pidm`, `role`) VALUES ';
			
			unset($params);
			$params = array();
		}
	}

	if( $params ) {
		PSU::db('portal')->execute($populate, $params);
	}
}

purge_temp_table();
update_temp_table();
swap_tables();
