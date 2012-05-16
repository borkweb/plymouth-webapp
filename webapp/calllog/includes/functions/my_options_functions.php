<?php
function display_all_groups(){
	global $db;

	$display_groups = $db->GetAll("SELECT * FROM itsgroups WHERE deleted = 0 ORDER BY subgroupName ASC");	
	$group_listing = array();
	foreach($display_groups as $group){
		$group_listing[$group['itsgroupid']] = $group;
	}

	return $group_listing;
}

function display_my_groups(){
	global $db;

	$employee_id = $GLOBALS['EMPLOYEE_INFO']['call_log_user_id'];
	$display_my_groups = $db->GetAll("SELECT * FROM its_employee_groups WHERE employee_id = '$employee_id'");		
	
	$my_groups = array();
	foreach($display_my_groups as $my_group){
		$my_groups[$my_group['group_id']] = $my_group;
	}
	return $my_groups;
}

function delete_previous_entries($employee_id){
	global $db;

	$delete_previous_entries = $db->Execute("DELETE FROM its_employee_groups WHERE employee_id = '$employee_id'");

	return $delete_previous_entries;
}

function insert_new_entries($employee_id){
	global $db;

	foreach($_POST['group'] as $its_group_id=>$group_option){
		$insert_new_entries = $db->Execute("INSERT INTO its_employee_groups (employee_id, group_id, option_id) VALUES ($employee_id, $its_group_id, $group_option)");
	}

	return $insert_new_entries;
}