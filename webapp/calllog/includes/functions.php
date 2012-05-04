<?php

function tableFields( $table ) {
	$fields = array();

	$sql = "DESCRIBE " . $table;
	if( $results = PSU::db('calllog')->GetAll( $sql ) ) {
		foreach( $results as $row ) {
			$fields[] = $row['Field'];
		}//end foreach
	}//end if

	return $fields;
}//end tableFields

function generateKeywordList($call_id, $details)
{
	global $db;
	$keywords = array();
	$getKeywordList = $db->GetAll("SELECT * FROM call_log_keywords WHERE active = '1'");
	$checkKeywords = $db->GetRow("SELECT call_id, keywords FROM call_log WHERE call_id = ?", array($call_id));

	if ($checkKeywords['keywords'] != "") 
	{
		$keywords = $checkKeywords['keywords'] ? explode(', ', $checkKeywords['keywords']) : array();
	}

	for($i=0;$i<count($getKeywordList);$i++)
	{
		$keyword_name = str_replace('/','\/',$getKeywordList[$i]['name']);
		if(preg_match("/($keyword_name)/i", $details))
		{
			$keywords[] = $keyword_name;
		}
	}

	$keywords = array_unique($keywords);
	
	return implode(', ', $keywords);
}//end generateKeywordList

function uploadFile($call_id)
{
	global $db;
	
	/************[ Begin File Upload Logic ]****************/
	if($call_id && $_FILES['attachment']['name'] && preg_match('/\.(gif|jpg|png|txt|doc|xls|docx|xlsx|pdf)$/i',$_FILES['attachment']['name']) && $db->GetOne("SELECT count(*) FROM call_log WHERE call_id=".$call_id))
	{
		if(!file_exists($GLOBALS['uploads']))
		{
			mkdir($GLOBALS['uploads']);
		}//end if
		
		$upload_dir = $GLOBALS['uploads'].'/'.$call_id;
		if(!file_exists($upload_dir))
		{
			mkdir($upload_dir);
		}//end if
		
		$files = @scandir($upload_dir);
		if(!array($files))
		{
			$files = array();
		}//end if
	
		preg_match('/\.(.*)$/',basename($_FILES['attachment']['name']),$matches);
		$extension = $matches[1];
		$file_name = str_replace($extension,'',basename($_FILES['attachment']['name']));
	
		$file_name = str_pad(((count($files)-2)+1),2,'0',STR_PAD_LEFT).'_'.PSUTools::createSlug($file_name).'.'.$extension;
		$upload_file = $upload_dir.'/'.$file_name;
		if(move_uploaded_file($_FILES['attachment']['tmp_name'], $upload_file))
		{
			$comments.="\n\n".'File ('.$file_name.') Uploaded!';
		}//end if
	}//end if	
	/************[ End File Upload Logic ]****************/
}//end uploadFile

function phonebookSearch($search_string, $search_type = 'all')
{
	if($search_type == 'all')
	{
		$input = array(
			'search_phrase' => $search_string,
			'everybody' => true,
			'count' => 50
		);
		return PSU::searchPhonebook($input);
	}//end if
	else
	{
		$search_string = substr($GLOBALS['PHONEBOOK']->qstr($search_string),1,strlen($search_string));
		if($search_type == 'phone')
		{
			$where = "(REPLACE(REPLACE(REPLACE(REPLACE(phone_of,' ',''),'-',''),')',''),'(','') like '%".$search_string."%' OR REPLACE(REPLACE(REPLACE(REPLACE(phone_vm,' ',''),'-',''),')',''),'(','') like '%".$search_string."%')";
		}//end if
		else
		{
			$where = $search_type." like '".$search_string."%'";
		}//end else
		
		$sql = "SELECT * FROM phonebook WHERE $where ORDER BY name_last, name_first, email LIMIT 500";
		
		return $GLOBALS['PHONEBOOK']->GetAll($sql);
	}//end else
}//end phonebookSearch
	
function prePrint($printIt){
	echo "<pre>"; print_r($printIt); echo "</pre>";
}

function pp($printIt){
	prePrint($printIt);
}

function setUpHeader($page_title='', $parse_option='', $suppressem=''){
	global $db;

	$tpl = new XTemplate(TEMPLATE_DIR.'/header.tpl');

	$tlc_user = $_SESSION['username'];
	$tpl->assign('tlc_user', $tlc_user);

	// If new login, insert new entry in employee_login_history table.
	if(empty($_SESSION['user_session_logged'])){
		$user_name = $_SESSION['username'];
		$ip_address = $_SERVER['REMOTE_ADDR'];
		$session_status = logUserSession($user_name, $ip_address);
		if($session_status == true){
			$_SESSION['user_session_logged'] = 'okay';	
		}// end if
		else{
			$_SESSION['user_session_logged'] = 'An Error Occured While Logging This Session.';
		}
	}// end if

	$tpl->parse('main.body');

	// Generic user message, use for errors, or other notifications.
	if(isset($_SESSION['user_message'])){
		$tpl->assign('user_message', $_SESSION['user_message']);	
		$tpl->parse('main.header.user_message');	
		unset($_SESSION['user_message']);
	}// end if

	// Set application information
	$tpl->assign('application_name', APPLICATION_NAME);

	$tlc_positions = $GLOBALS['tlc_employee_positions'];

	// Get TLC position and full name, assign into template
	$user_privileges = $GLOBALS['EMPLOYEE_INFO']['user_privileges'];
	$_SESSION['tlc_full_name'] = $GLOBALS['EMPLOYEE_INFO']['first_name'].' '.$GLOBALS['EMPLOYEE_INFO']['last_name'];
	$_SESSION['tlc_position_name'] = $tlc_positions[$user_privileges]; 
	$_SESSION['tlc_position'] = $user_privileges;

	$tpl->assign('tlc_username', $_SESSION['username']);
	$tpl->assign('tlc_position', ucwords($_SESSION['tlc_position_name']));
	$tpl->assign('tlc_full_name', $_SESSION['tlc_full_name']);

	$tpl->assign('page_title', $page_title);

	// Set up page header, return the top navigation links
	$tpl->assign('top_nav', returnTopNav($page_title, CALL_LOG_WEB_HOME));

	// Parse the standard menu, available to all users.
	if ($suppressem != 'yes'){
		$tpl->parse('main.header');
	}

	$tpl->parse('main');

	return $tpl->text('main');	
}// end funtion setUpHeader

function setUpFooter($unused=''){
	$tpl = new XTemplate(TEMPLATE_DIR.'/footer.tpl');
	$tpl->parse('main');
	return $tpl->text('main');	
}// end function setUpFooter

function returnTopNav($page_name, $call_log_web_home){
	$topNav = '<li><a href="'.$call_log_web_home.'/index.html">Home</a></li>';
	$topNav .= '<li><a href="'.$call_log_web_home.'/search/">Detail Search</a></li>';
	$topNav .= '<li><a href="http://www.plymouth.edu/webapp/cts/" target="_blank">Media Loans</a></li>';
	$topNav .= '<li><a href="'.$call_log_web_home.'/tools.html">Tools</a></li>';
	$topNav .= '<li><a href="'.$call_log_web_home.'/graphs/statistics.html">Statistics</a></li>';
	$topNav .= '<li><a href="'.$call_log_web_home.'/my_options.html">Options</a></li>';
	if(in_array($_SESSION['tlc_position'], $_SESSION['priv_users'])){
		$topNav .= '<li><a href="'.$call_log_web_home.'/admin/call_log_admin.html">Admin</a></li>';
		if($_SESSION['tlc_position'] == 'webguru'){
			$topNav .= '<li><a href="'.$call_log_web_home.'/call_log_tools.html">Tools</a></li>';
		}
	}
	return $topNav;
}//end returnTabNav

function searchBuildingRoom($extension=''){
	global $db;
	$result = $db->Execute("SELECT * FROM extension_to_room WHERE extension = '$extension'");
	$num_rows = $result->RecordCount();
	if ($num_rows == '0'){
		$building_info = array('', 'N/A','N/A');
	}else{
		while($row = $result->FetchRow()){
			$building_info = array($row['build_id'], $row['extension'], $row['room_number']);
		}// end while
	}
	return $building_info;
}

function getBuildingOptions($building_id='', $extention='', $room=''){
	global $db;

	$building_options = Array();

	// Get building list
	$query = "SELECT building_id, building_name FROM building_info ORDER BY building_name";
	$result = $db->Execute($query);
	if(!empty($result)){
		while($row = $result->FetchRow()){
			$building_id = $row['building_id'];
			$building_options[$building_id] = $row['building_name'];
		}// end while
		return $building_options;
	}// end if
	else{
		$_SESSION['user_message'] = 'Error getting building options in getBuildingOptions()';
	}

}// end function getBuildingOptions


function getTLCUsersOptions(){
	global $db;
	$tlc_users_options = Array();

	$query = "SELECT call_log_user_id, last_name, first_name, user_name FROM call_log_employee WHERE status = 'active' ORDER BY last_name";
	$result = $db->Execute($query);
	if(!empty($result)){
		while($row = $result->FetchRow()){
			$tlc_users_options[$row['user_name']] = $row['last_name'].', '.$row['first_name'];
		}
		return $tlc_users_options;
	}else{
		$_SESSION['user_message'] = 'Error getting tlc users options in getTLCUsersOptions().';
	}

}

function getITSGroupOptions(){
	global $db;
	$its_group_options = Array();

	// Get ITS Group List
	$groups_query = "SELECT * FROM itsgroups WHERE deleted = 0 ORDER BY subgroupName";
	$groups_result = $db->Execute($groups_query);
	$user_query = "SELECT * FROM its_employee_groups, itsgroups WHERE itsgroups.deleted = 0 AND its_employee_groups.group_id = itsgroups.itsgroupid AND its_employee_groups.employee_id = {$GLOBALS['EMPLOYEE_INFO']['call_log_user_id']}   AND its_employee_groups.option_id != '0'";
	$user_row = $db->GetRow($user_query);
	
	if(!empty($groups_result)){
		while($groups_row = $groups_result->FetchRow()){
			if ($groups_row['subgroup'] == $groups_row['group']){
				$its_group_options[$groups_row['itsgroupid']] = $groups_row['groupName'];
			}else{
				$its_group_options[$groups_row['itsgroupid']] = $groups_row['subgroupName']." - ". $groups_row['groupName'];
			}
		}// end while
		return $its_group_options;
	}// end if
	else{
		$_SESSION['user_message'] = 'Error getting ITS Group options in getITSGroupOptions().';
	}
}

function loadingContent($loadingDiv){
	echo "<div id='$loadingDiv' style='display: none;'><img src='{call_log_web_home}/images/loading-anim.gif' alt='Loading...'/>Loading Content Please Wait...</div>";
}

function getGroupInfo($ITSGroupNumber, $loop=''){
	global $db;
	$groups_query = "SELECT * FROM itsgroups WHERE itsgroupid = '$ITSGroupNumber' and itsgroups.deleted = 0";
	$groups_result = $db->CacheExecute($groups_query);
	if ($loop == 1){
		while ($groups_row = $groups_result->FetchRow()){
			$groups_array = array($groups_row[subgroupName], $groups_row[subgroup], $groups_row[itsgroupid]);
		}
	}else{
		$groups_row = $groups_result->FetchRow();
		$groups_array = array($groups_row[subgroupName], $groups_row[subgroup]);
	}
	return $groups_array;
}

function sendOpenCallMail($call_info, $action){
	global $db;
	$person_cache = array();
			
	$call = $db->GetRow("SELECT * FROM call_log WHERE call_id = ?", array($call_info['call_id']));
	$call_info['call_date'] = $call['call_date'] . ' ' . $call['call_time'];

	$headers = array();
	$headers['content-type'] = 'text/html';

	$caller = PSU::nvl($call_info['caller_wp_id'], $call_info['caller_pidm'], $call_info['call_log_username']);
	
	$caller_data = $GLOBALS['user']->getCallerData($caller);

	$logger = $person_cache[ $call_info['call_log_username'] ] = PSUPerson::get( $call_info['call_log_username'] );

	$groupInfo = getGroupInfo($call_info['its_group_assigned_to']);

	$call_log_employee = checkEmployee( $call_info['call_log_username'] );
	$assigned_employee = checkEmployee( $call_info['tlc_assigned_to'] );

	$to = array();

	// always send to the submitter if the submitter is an employee
	if( $call_log_employee ){	
		if( $logger->system_account_exists ) {
			$to[] = $logger->wp_email;
		}//end 
	} else {
		$end_user_to = $logger->wp_email;
	}//end else

	if($action == "its_staff"){
		$call_info['call_id'] = $call_info['new_call_id'].$call_info['call_id'];
		$call_info['comments'] = $call_info['problem_details'].$call_info['comments'];

		if($call_info['tlc_assigned_to'] != "unassigned"){
			if( $assigned_employee ){
				$to[] = PSUPerson::get($call_info['tlc_assigned_to'])->wp_email;
			} elseif( $call_info['tlc_assigned_to'] == 'caller' && $call_info['call_id']) {
				$sql = "SELECT caller_username 
									FROM call_log 
								 WHERE call_log.call_id = ?";
				
				$end_user_to = $db->GetOne($sql, array( $call_info['call_id'] ));
				if( $end_user_to ) {
					$end_user = PSUPerson::get($end_user_to);

					if( $end_user ) {
						$end_user_to = $end_user->wp_email;
					}
				}//end if
			} else {
				$end_user = PSUPerson::get($call_info['tlc_assigned_to']);
				$end_user_to = $end_user->wp_email;
			}//end if
		}//end if

		if($call_info['its_assigned_group'] != 0){
			$sql = "SELECT * 
								FROM   itsgroups
								     , call_log 
										 , call_history 
							 WHERE itsgroups.deleted = 0 
								 AND call_log.call_id = call_history.call_id 
								 AND itsgroups.itsgroupid = ?
			           AND call_log.call_id = ?";

			$getEmailTo = $db->GetRow($sql, array( $call_info['its_assigned_group'], $call_info['call_id'] ));

			if($getEmailTo['email_to'] == 'all'){
				$sql = "SELECT user_name
									FROM   itsgroups
									     , its_employee_groups
									     , call_log_employee
									WHERE itsgroups.deleted = 0 
										AND call_log_employee.call_log_user_id = its_employee_groups.employee_id 
										AND its_employee_groups.group_id = ?
										AND itsgroups.itsgroupid = its_employee_groups.group_id 
										AND call_log_employee.status = 'active' 
				            AND its_employee_groups.option_id = '2'";

				$email_list = $db->GetCol($sql, array( $call_info['its_assigned_group'] ));
			} else {
				$email_list = explode(',', $getEmailTo['email_to']);
			}//end else

			foreach( (array) $email_list as $email) {
				$user = PSUPerson::get($email);
				$to[] = $user->wp_email;
			}//end foreach
		}//end if

		if($call_info['its_group_assigned_to'] != 0){
			$subject = '[Call Log] ['.$groupInfo[1].'] '.$caller_data['name_full'];
		}else{
			$subject = '[Call Log] '.$caller_data['name_full'];
		}

		$subject .= ' (#' . $call_info['call_id'] . ')';

		if($call_info['call_status'] == 'closed') {
			$subject .= " [CLOSED]";

			// always send close to the owner, if they are allowed to see the
			// full history
			$caller_to = $db->GetOne("SELECT calllog_username FROM call_log WHERE call_log.call_id = '{$call_info['call_id']}'");
			$caller_email = $caller_to . '@plymouth.edu';

			if( $GLOBALS['end_user_email'] ){
				$closing_user = $_SESSION['username'] . '@plymouth.edu';
				if( trim( $caller_email ) == $closing_user ) {
					$end_user_to = $closing_user;
				}//end if
			} elseif( checkEmployee($caller_to) ) {
				$to[] = $caller_email;
			}//end else
		}

		require_once 'PSUTemplate.class.php';

		$sql = "SELECT * 
							FROM   call_log
							     , call_history 
						 WHERE call_log.call_id = ?
							 AND call_log.call_id = call_history.call_id
						 ORDER BY date_assigned DESC
		               , time_assigned DESC";

		$call_info_query = $db->Execute($sql, array( $call_info['call_id'] ));

		foreach($call_info_query as $call_info2){
			$group_name = getGroupInfo($call_info2['its_assigned_group']);
			if($group_name[0] == ""){
				$group_name = "Unassigned";
			}else{
				$group_name = $group_name[0];
			}

			$call_info2['group_name'] = $group_name;
			$call_info2['update_date'] = $call_info2['date_assigned'].' '.$call_info2['time_assigned'];

			if( $call_info2['tlc_assigned_to'] && $call_info2['tlc_assigned_to'] != 'unassigned' ) {
				if( !$person_cache[ $call_info2['tlc_assigned_to'] ] ) {
					$person_cache[ $call_info2['tlc_assigned_to'] ] = PSUPerson::get( $call_info2['tlc_assigned_to'] );
				}//end else
				$call_info2['assigned_to'] = $call_info2['tlc_assigned_to'];
			}//end if

			if( $call_info2['updated_by'] ) {
				if( !$person_cache[ $call_info2['updated_by'] ] ) {
					$person_cache[ $call_info2['updated_by'] ] = PSUPerson::get( $call_info2['updated_by'] );
				}//end else
				$call_info2['logger'] = $call_info2['updated_by'];
			}//end if
			$history[] = $call_info2;
		}

		$caller_id = $caller_data['identifier'];
		if( !$person_cache[ $caller_id ] ) {
			$person_cache[ $caller_id ] = PSUPerson::get( $caller_id );
		}//end else

		$current = array_slice( $history, 0, 1 );
		$current = $current[0];
		/*
		psu::dbug($call_info);
		psu::dbug($current);
		die;
		 */

		// email ITS
		$tpl = new PSUTemplate();
		$tpl->assign('caller', $caller_data);
		$tpl->assign('caller_id', $caller_id);
		$tpl->assign('pcache', $person_cache);
		$tpl->assign('call', $call_info);
		$tpl->assign('current', $current);
		$tpl->assign('history', array_slice( $history, 1 ));
		$text_message = $tpl->fetch('email.ticket.text.tpl');
		$html_message = $tpl->fetch('email.ticket.html.tpl');

		$to = implode(',', array_unique( $to ));

		if($to){
			$headers['from'] = $logger->formatName('f m l').' <'.$logger->wp_email.'>';
			PSU::mail($to, $subject, array( stripslashes($text_message), stripslashes($html_message) ), $headers);
		}//end if

		// email user
		if( $end_user_to ) {
			$headers['from'] = 'Support Tickets <do-not-reply@plymouth.edu>';

			$tpl->assign('is_caller', true);
			$tpl->assign('history', array());
			$text_message = $tpl->fetch('email.ticket.text.tpl');
			$html_message = $tpl->fetch('email.ticket.html.tpl');
			PSU::mail($end_user_to, $subject, array( stripslashes($text_message), stripslashes($html_message) ), $headers);
		}//end if
	}
}


function logUserSession($user_name, $ip_address){
	global $db;

	$host_name = gethostbyaddr($ip_address);
	$query = "INSERT INTO employee_login_history (user_name, ip_address, host_name, login_date, login_time) VALUES ('$user_name', '$ip_address', '$host_name', NOW(), NOW())";

	if($db->Execute($query)){
	   return true;	
	}// end if
	else{
	   return false;
	}

}// end function logUserSession

function getBuildingName($building_id){
	global $db;

	if(!$building_id){
		$building_name = 'N/A';
	}else{
		$building_name = $db->GetOne("SELECT building_name FROM building_info WHERE building_id = '$building_id'");
		if(!$building_name){
			$building_name = 'N/A';
		}
	}
	return $building_name;
}// end function getBuildingName

function getBuildingID($building_name){
	global $db;

	if(!$building_name){
		$building_id = 'N/A';
	}else{
		$building_id = $db->GetOne("SELECT building_id FROM building_info WHERE building_name = '$building_name'");
		if(!$building_name){
			$building_id = 'N/A';
		}
	}
	return $building_id;
}// end function getBuildingName

function getFiles($dir, $ext){
 
	$dir_contents = Array();
	$temp = Array();
	if($read_dir = opendir($dir)){
		while(($file=@readdir($read_dir)) !== false){
			if($file!='.' && $file!='..' && preg_match('/.'.$ext.'$/', $file)){
				$temp[] = $file;
			}// end if
		}// end while
		closedir($read_dir);
		return $temp;
	}// end if
}// end function getFiles



function fixMemorySize($SizeToFix){
	if ($SizeToFix != ""){
		$TotalSize = ($SizeToFix/(1024*1024))+1.1;
		$MBSize = intval($TotalSize).' MB';
	}
	return $MBSize;
}

function fixQuotaSize($SizeToFix){
	if ($SizeToFix != ""){
		$TotalSize = ($SizeToFix/(1000));
		$MBSize = intval($TotalSize).' MB';
	}
	return $MBSize;
}

function fixHDSize($SizeToFix){
	if ($SizeToFix != ""){
		$TotalSize = ($SizeToFix/(1024))+1+.05;
		$GBSize = intval($TotalSize).' GB';
	}
	return $GBSize;
}

/**
 * Returns the number 
 */
function checkEmployee($user = null){
	global $db, $EMPLOYEE_INFO;

	if( $user === null ) {
		$user = $_SESSION['username'];
	}//end if

	$EMPLOYEE_INFO = $db->GetRow("SELECT * FROM call_log_employee WHERE (user_name=? AND status='active')", array($user));
	
	return count($EMPLOYEE_INFO);
}

function undo_magic_quotes( &$array ) {
	if( !get_magic_quotes_gpc() ) {
		return;
	}

	foreach( $array as &$v ) {
		if( is_string($v) ) {
			$v = stripslashes($v);
		}
	}
}

?>
