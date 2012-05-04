<?php

function returnNewUserForm(){
	global $db;

	$template_name = TEMPLATE_ADMIN_DIR.'/new_user_form.tpl';
	$tpl = new XTemplate($template_name);
	$tpl->assign('form_action', 'tlc_users_admin.html?action=addtlcuser');
	$tpl->parse('main.new_privileges_list');

	// ITS Groups Options Array
	$its_group_options = Array();
	$its_group_options = getITSGroupOptions();   
	$tpl->assign('select_group', $its_group_options);
	$its_select_group_list = PSUHTML::getSelectOptions($its_group_options,$getUserInfo['group_id']);
	$tpl->assign('its_select_group_list', $its_select_group_list);
	$getTotalGroupsQuery = $db->Execute("SELECT * from itsgroups WHERE itsgroups.deleted = 0");
	$k=0;
	while($getTotalGroups = $getTotalGroupsQuery->FetchRow()){
		$tpl->assign("totalGroups", $k);
		$tpl->parse("main.countTotalGroups");
		$k++;
	}
	$tpl->assign('i',0);
	// loop over the results, parsing main.group for each
	$tpl->assign('my_group', strtolower($getUserInfo['subgroup']));
	$tpl->assign('my_group_name', $getUserInfo['subgroupName']);
	$tpl->parse('main.ManageITSGroups');

	$js_its_select_group_list = str_replace(array("\n",'selected="selected'),'',$its_select_group_list);
	$tpl->assign('js_its_select_group_list', $js_its_select_group_list);
	$tpl->assign('tlc_employee_positions', PSUHTML::getSelectOptions($GLOBALS['tlc_employee_positions'],$key['user_privileges']));
	$tpl->assign('user_status', PSUHTML::getSelectOptions($GLOBALS['user_status'],$key['status']));
	$tpl->assign('class_options', PSUHTML::getSelectOptions($GLOBALS['class_options'],$key['student_class']));
	$tpl->assign('signed_ferpa', PSUHTML::getSelectOptions($GLOBALS['ferpa_options'],$key['ferpa']));
	$tpl->parse('main.new_status_list');
	$tpl->parse('main.add_tlc_user');
	$tpl->parse('main');

	return $tpl->text('main');
}// end function returnUserForm


function displayTLCUsers($display_option, $display_type=''){
	global $db;
	$query = "SELECT * FROM call_log_employee WHERE user_name != 'helpdesk' ";
	switch($display_option){
		case 'active':
			$query.= "AND status = 'active' ORDER BY last_name"; 
			$active = "yes";
			break;
		case 'disabled':
			$query.= "AND status = 'disabled' ORDER BY last_name"; 
			$active = "yes";
			break;
		case 'inactive':
			$query.= "AND status = 'inactive' ORDER BY last_name";
			$inactive = "yes";
			break;
		case 'all':
			$query.= "ORDER BY last_name";
			break;
		default:
			$query.= "ORDER BY last_name";
			break;
	
	}// end switch

	if($display_type == 'admin'){
		$template_name = TEMPLATE_ADMIN_DIR.'/list_tlc_users_admin.tpl';
		$tpl = new XTemplate($template_name);		
		$tpl->assign('call_log_web_home', CALL_LOG_WEB_HOME);

		$result = $db->Execute($query);
		while($info = $result->FetchRow()){
			$getGroupResult = $db->Execute("SELECT * FROM call_log_employee, its_employee_groups, itsgroups WHERE itsgroups.deleted = 0 and call_log_employee.call_log_user_id = '$info[call_log_user_id]' AND its_employee_groups.employee_id = '$info[call_log_user_id]' AND its_employee_groups.group_id = itsgroups.itsgroupid ORDER BY subgroup ASC");
			$getGroupInfo = $getGroupResult->FetchRow();
			$tpl->assign('row2', $getGroupInfo);

			$tpl->assign('row', $info);
			$tpl->parse('main.group');
			$tpl->parse('main.tlc_user_list.group');
			$tpl->parse('main.tlc_user_list');
		}// end while
	
	}else if($display_type != 'admin' || $display_type == ''){
		$template_name = TEMPLATE_ADMIN_DIR.'list_tlc_users.tpl';
		$tpl = new XTemplate($template_name);		
		$tpl->assign('call_log_web_home', CALL_LOG_WEB_HOME);

		$result = $db->Execute($query);
		while($info = $result->FetchRow()){
			$getGroupResult = $db->Execute("SELECT * FROM call_log_employee, its_employee_groups, itsgroups WHERE itsgroups.deleted = 0 and call_log_employee.call_log_user_id = '$info[call_log_user_id]' AND its_employee_groups.employee_id = '$info[call_log_user_id]' AND its_employee_groups.group_id = itsgroups.itsgroupid ORDER BY subgroup ASC");
			$getGroupInfo = $getGroupResult->FetchRow();
			$tpl->assign('row2', $getGroupInfo);

			$tpl->assign('row', $info);
			$tpl->parse('main.tlc_user_list');
		}// end while
	}// end if

	$tpl->parse('main');
return $tpl->text('main');
}// end function displayTLCUsers


function addTLCUser($tlc_user){
	global $db;

	$template_name = TEMPLATE_ADMIN_DIR.'/status_messages.tpl';
	$tpl = new XTemplate($template_name);

	$fields = "`".implode("`,`",array_keys($tlc_user))."`";
	$values = "'".implode("','",$tlc_user)."'";

	$tpl->parse('main');
	return $tpl->text('main');
}// end function addTLCUser

function editTLCUser($user_name){
	global $db;

	$template_name = TEMPLATE_ADMIN_DIR.'/new_user_form.tpl';
	$tpl = new XTemplate($template_name);
	$tpl->assign('form_action', 'tlc_users_admin.html?action=updatetlcuser');
	
	$getTotalGroupsQuery = $db->Execute("SELECT * from itsgroups WHERE itsgroups.deleted = 0");
	$k=0;
	while($getTotalGroups = $getTotalGroupsQuery->FetchRow()){
		$tpl->assign("totalGroups", $k);
		$tpl->parse("main.countTotalGroups");
		$k++;
	}

	$getUserInfoSQL = "SELECT * FROM call_log_employee, its_employee_groups, itsgroups WHERE itsgroups.deleted = 0 and call_log_employee.user_name='$user_name' AND call_log_employee.call_log_user_id = its_employee_groups.employee_id AND itsgroups.itsgroupid = its_employee_groups.group_id ORDER BY subgroupName ASC";
	$getUserInfoRes = $db->Execute($getUserInfoSQL);
	$i=0;
	if ($getUserInfoRes->_numOfRows == '0'){
		// ITS Groups Options Array
		$its_group_options = Array();
		$its_group_options = getITSGroupOptions();   
		$tpl->assign('select_group', $its_group_options);
		$its_select_group_list = PSUHTML::getSelectOptions($its_group_options,$getUserInfo['group_id']);
		$tpl->assign('its_select_group_list', $its_select_group_list);
		$tpl->assign('i',0);
		// loop over the results, parsing main.group for each
		$tpl->assign('my_group', strtolower($getUserInfo[subgroup]));
		$tpl->assign('my_group_name', $getUserInfo[subgroupName]);
		$tpl->parse('main.ManageITSGroups');
	}else{
		while($getUserInfo = $getUserInfoRes->FetchRow()){
			// ITS Groups Options Array
			$its_group_options = Array();
			$its_group_options = getITSGroupOptions();
			$its_select_group_list = PSUHTML::getSelectOptions($its_group_options,$getUserInfo['group_id']);
			$tpl->assign('its_select_group_list', $its_select_group_list);
			$tpl->assign('i',$i);
			$i++;
			// loop over the results, parsing main.group for each
			$tpl->assign('my_group', strtolower($getUserInfo[subgroup]));
			$tpl->assign('my_group_name', $getUserInfo[subgroupName]);
			$tpl->parse('main.ManageITSGroups');

		}

	}

	$js_its_select_group_list = str_replace(array("\n",'selected="selected'),'',$its_select_group_list);
	$tpl->assign('js_its_select_group_list', $js_its_select_group_list);

	$query = $db->Execute("SELECT * FROM call_log_employee WHERE user_name = '$user_name'");
	$key = $query->FetchRow();
	$tpl->assign('tlc_employee_positions', PSUHTML::getSelectOptions($GLOBALS['tlc_employee_positions'],$key['user_privileges']));
	$tpl->assign('user_status', PSUHTML::getSelectOptions($GLOBALS['user_status'],$key['status']));
	$tpl->assign('class_options', PSUHTML::getSelectOptions($GLOBALS['class_options'],$key['student_class']));
	$tpl->assign('signed_ferpa', PSUHTML::getSelectOptions($GLOBALS['ferpa_options'],$key['ferpa']));
	$tpl->assign('key', $key);
	$tpl->parse('main.update_tlc_user');
	$tpl->parse('main');
	return $tpl->text('main');
}// end function editTLCUser

function updateTLCUser($tlc_user){
	global $db;
	$template_name = TEMPLATE_ADMIN_DIR.'/status_messages.tpl';
	$tpl = new XTemplate($template_name);
	
	$call_log_user_id = $tlc_user['call_log_user_id'];
	$last_name = $tlc_user['last_name'];
	$first_name = $tlc_user['first_name'];
	$user_name = $tlc_user['user_name'];
	$phone = $tlc_user['phone'];
	$student_class = $tlc_user['student_class'];
	$comments = $tlc_user['comments'];
	$user_privileges = $tlc_user['user_privileges'];
	$status = $tlc_user['status'];
	$getUserRes = $db->Execute("SELECT * FROM its_employee_groups WHERE employee_id = '$call_log_user_id'");
	if ($getUserRes->RecordCount() == '0'){
		$groupListing = $_POST['addUser_groupListing'];
		$getUserRow = $getUserRes->FetchRow();
		$test = $db->Execute("INSERT INTO its_employee_groups (employee_id, group_id) VALUES ('$call_log_user_id', '$groupListing[0]')");
	}else{
		$groupListing = $_POST['addUser_groupListing'];
		for($i=1;$i<=$getUserRes->RecordCount();$i++){
			$getUserRow = $getUserRes->FetchRow();
			$group_id = $getUserRow['group_id'];
			$groupUpdate = $db->Execute("UPDATE its_employee_groups SET group_id = '$groupListing[$i]' WHERE employee_id = '$call_log_user_id' AND group_id = '$group_id'");
		}
	}

	$profile_query = "UPDATE call_log_employee SET last_name = '$_GET[last_name]', first_name = '$_GET[first_name]', user_name = '$_GET[calllog_username]', student_class = '$_GET[class_options]', position = '$_GET[position]', comments = '$_GET[comments]', user_privileges = '$_GET[user_privileges]', status = '$_GET[status]', work_phone = '$_GET[work_phone]', cell_phone = '$_GET[cell_phone]', home_phone = '$_GET[home_phone]', ferpa = '$_GET[ferpa]' WHERE call_log_user_id = '$_GET[user_id]'";
	if($db->Execute($profile_query)){
		echo "<div class='update_message'>User Updated Successfully</div>";
	}else{
		echo "<div class='update_message'>User Update Failed</div>";
	}
	$tpl->parse('main');
	//header("Location: tlc_users_admin.html?message=update_success");
	return $tpl->text('main');
}// end function updateTLCUserDetails

function setTLCUserStatus($user_name, $status){
	global $db;

	$template_name = TEMPLATE_ADMIN_DIR.'/status_messages.tpl';
	$tpl = new XTemplate($template_name);
	
	$query = "UPDATE call_log_employee SET status = '$status' WHERE user_name = '$user_name'";
	if($db->Execute($query)){
	   $tpl->parse('main.user_updated_successfully');
	}// end if
	else{
	   $_SESSION['user_message'] = 'Error Changing User\'s Status.';
	}

}// end function setTLCUserStatus
?>
