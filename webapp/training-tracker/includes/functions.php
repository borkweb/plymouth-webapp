<?php


//get item categories
function get_checklist_item_categories($current_user_level){
	if ($current_user_level == 'trainee'){
		$type = "training-tracker-trainee";
	}
	else if ($current_user_level == 'sta'){
		$type = "training-tracker-consultant";
	}
	else if ($current_user_level == 'shift_leader'){
		$type = "training-tracker-senior-consultant";
	}
	$result = PSU::db('hr')->GetAll("SELECT * FROM checklist WHERE type=?", array($type));
	return $result;
}


function get_checklist_items($current_user_level){

	if ($current_user_level == 'trainee'){
		$type = "training-tracker-trainee";
	}
	else if ($current_user_level == 'sta'){
		$type = "training-tracker-consultant";
	}
	else if ($current_user_level == 'shift_leader'){
		$type = "training-tracker-senior-consultant";
	}
	$result = PSU::db('hr')->GetAll("SELECT items.* FROM checklist_items items 
																						JOIN checklist_item_categories categories 
																						ON items.category_id = categories.id 
																						WHERE categories.type=?", array($type));
	return $result;
}


function get_checklist_sub_cat($current_user_level){
//	PSU::db('hr')->debug=true;
	if ($current_user_level == 'trainee'){
		$type = "training-tracker-trainee";
	}
	else if ($current_user_level == 'sta'){
		$type = "training-tracker-consultant";
	}
	else if ($current_user_level == 'shift_leader'){
		$type = "training-tracker-senior-consultant";
	}
	$result = PSU::db('hr')->GetAll("SELECT * FROM checklist_item_categories WHERE type=?", array($type));
	return $result;
}


