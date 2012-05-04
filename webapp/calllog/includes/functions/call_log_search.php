<?php

function searchUser($search_string){

}


function searchTicketNumber($search_string){
	global $db;

	return $db->GetRow("SELECT * FROM call_log WHERE call_id = '$search_string'");
}

function searchHardwareInformation($search_string){
	global $db;

	$hardware_info = $db->GetAll("SELECT * FROM hardware_inventory WHERE computer_name = '$search_string'");

	return $hardware_info;
}
