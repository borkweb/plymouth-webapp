<?php

function returnNewUserForm(){

	$template_name = TEMPLATE_ADMIN_DIR.'/new_user_form.tpl';
	$tpl = new XTemplate($template_name);
	$tpl->assign('form_action', 'manage_users.html?action=addtlcuser');
	$tpl->parse('main.new_privileges_list');

	// ITS Groups Options Array
	$its_group_options = Array();
	$its_group_options = getITSGroupOptions();   
	$tpl->assign('select_group', $its_group_options);
	$its_select_group_list = PSUHTML::getSelectOptions($its_group_options,$getUserInfo['group_id']);
	$tpl->assign('its_select_group_list', $its_select_group_list);
	$getTotalGroupsQuery = PSU::db('calllog')->Execute("SELECT * from itsgroups WHERE itsgroups.deleted = 0");
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


function getTLCUsers( $which ) {

	$query = "SELECT * FROM call_log_employee WHERE user_name != 'helpdesk' ";
	switch( $which ) {
		case 'active':
			$query.= "AND status = 'active' ORDER BY last_name"; 
			break;
		case 'disabled':
			$query.= "AND status = 'disabled' ORDER BY last_name"; 
			break;
		case 'inactive':
			$query.= "AND status = 'inactive' ORDER BY last_name";
			break;
		case 'all':
			$query.= "ORDER BY last_name";
			break;
		default:
			$query.= "ORDER BY last_name";
			break;
	}// end switch
	
	return PSU::db('calllog')->GetAll( $query );
} // end function

function displayTLCUsers($which, $display_type=''){

	$users = getTLCUsers( $which );

	if( $display_type == 'admin' ) {
		$template_name = 'admin/list_tlc_users_admin.tpl';
	}
	else {
		$template_name = 'admin/list_tlc_users.tpl';
	}
	$tpl = new PSU\Template;
	
	$tpl->assign( 'users', $users );
	
	return $tpl->fetch( $template_name );
}// end function


function addTLCUser($tlc_user){
	$template_name = TEMPLATE_ADMIN_DIR.'/status_messages.tpl';
	$tpl = new XTemplate($template_name);

	$fields = "`".implode("`,`",array_keys($tlc_user))."`";
	$values = "'".implode("','",$tlc_user)."'";

	$tpl->parse('main');
	return $tpl->text('main');
}// end function addTLCUser

function editTLCUser($user_name){
	$template_name = TEMPLATE_ADMIN_DIR.'/new_user_form.tpl';
	$tpl = new XTemplate($template_name);
	$tpl->assign('form_action', 'manage_users.html?action=updatetlcuser');
	
	$getTotalGroupsQuery = PSU::db('calllog')->Execute("SELECT * from itsgroups WHERE itsgroups.deleted = 0");
	$k=0;
	while($getTotalGroups = $getTotalGroupsQuery->FetchRow()){
		$tpl->assign("totalGroups", $k);
		$tpl->parse("main.countTotalGroups");
		$k++;
	}

	$getUserInfoSQL = "SELECT * FROM call_log_employee, its_employee_groups, itsgroups WHERE itsgroups.deleted = 0 and call_log_employee.user_name='$user_name' AND call_log_employee.call_log_user_id = its_employee_groups.employee_id AND itsgroups.itsgroupid = its_employee_groups.group_id ORDER BY subgroupName ASC";
	$getUserInfoRes = PSU::db('calllog')->Execute($getUserInfoSQL);
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

	$query = PSU::db('calllog')->Execute("SELECT * FROM call_log_employee WHERE user_name = '$user_name'");
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
	$getUserRes = PSU::db('calllog')->Execute("SELECT * FROM its_employee_groups WHERE employee_id = '$call_log_user_id'");
	if ($getUserRes->RecordCount() == '0'){
		$groupListing = $_POST['addUser_groupListing'];
		$getUserRow = $getUserRes->FetchRow();
		$test = PSU::db('calllog')->Execute("INSERT INTO its_employee_groups (employee_id, group_id) VALUES ('$call_log_user_id', '$groupListing[0]')");
	}else{
		$groupListing = $_POST['addUser_groupListing'];
		for($i=1;$i<=$getUserRes->RecordCount();$i++){
			$getUserRow = $getUserRes->FetchRow();
			$group_id = $getUserRow['group_id'];
			$groupUpdate = PSU::db('calllog')->Execute("UPDATE its_employee_groups SET group_id = '$groupListing[$i]' WHERE employee_id = '$call_log_user_id' AND group_id = '$group_id'");
		}
	}

	$profile_query = "UPDATE call_log_employee SET last_name = '$_GET[last_name]', first_name = '$_GET[first_name]', user_name = '$_GET[calllog_username]', student_class = '$_GET[class_options]', position = '$_GET[position]', comments = '$_GET[comments]', user_privileges = '$_GET[user_privileges]', status = '$_GET[status]', work_phone = '$_GET[work_phone]', cell_phone = '$_GET[cell_phone]', home_phone = '$_GET[home_phone]', ferpa = '$_GET[ferpa]' WHERE call_log_user_id = '$_GET[user_id]'";
	if(PSU::db('calllog')->Execute($profile_query)){
		echo "<div class='update_message'>User Updated Successfully</div>";
	}else{
		echo "<div class='update_message'>User Update Failed</div>";
	}
	$tpl->parse('main');
	return $tpl->text('main');
}// end function updateTLCUserDetails

function setTLCUserStatus($user_name, $status){
	$template_name = TEMPLATE_ADMIN_DIR.'/status_messages.tpl';
	$tpl = new XTemplate($template_name);
	
	$query = "UPDATE call_log_employee SET status = '$status' WHERE user_name = '$user_name'";
	if(PSU::db('calllog')->Execute($query)){
	   $tpl->parse('main.user_updated_successfully');
	}// end if
	else{
	   $_SESSION['user_message'] = 'Error Changing User\'s Status.';
	}

}// end function setTLCUserStatus
