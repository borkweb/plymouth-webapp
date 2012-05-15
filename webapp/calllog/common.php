<?php

require dirname( dirname( __DIR__ ) ) . '/legacy/git-bootstrap.php';

require_once 'autoload.php';
PSU::session_start( PSU::FORCE_SSL );

$GLOBALS['TITLE'] = 'Call Log';
define ('PSU_CDN', false);

$config = \PSU\Config\Factory::get_config();
define( 'PSU_API_APPID', $config->get( 'calllog', 'api_appid' ) );
define( 'PSU_API_APPKEY', $config->get( 'calllog', 'api_key' ) );

require_once 'PSUWordPress.php';

ini_set('memory_limit', -1);

$start_time = time();
ignore_user_abort(true);

// Call Log Web Home
$HOST = 'https://'.$_SERVER['SERVER_NAME'];
$GLOBALS['BASE_URL'] = $HOST.'/webapp/calllog';
$GLOBALS['BASE_DIR'] = __DIR__;

define('CALL_LOG_WEB_HOME', $GLOBALS['BASE_URL']);

$GLOBALS['DEVELOPMENT'] = PSU::isdev();
$GLOBALS['uploads'] = '/web/uploads/webapp/calllog/attachments';

// Call Log Administrative Web Home
define('TEMPLATE_DIR', __DIR__ . '/templates');
define('TEMPLATE_ADMIN_DIR', TEMPLATE_DIR . '/admin');

$GLOBALS['TEMPLATES'] = TEMPLATE_DIR;

// Absolute Path To Cascading Style Sheet (CSS) Files
define('CSS_DIR', __DIR__ . '/css');

// Web Path To CSS Files
define( 'CSS_WEB_DIR', PSU::cdn( $GLOBALS['BASE_URL'] . '/css' ) );
$GLOBALS['CSS_WEB_DIR'] = CSS_WEB_DIR;

// Web Path To JS Files
define('JS_WEB_DIR', $GLOBALS['BASE_URL'] . '/js');
$GLOBALS['JS_WEB_DIR'] = JS_WEB_DIR;

define('INCLUDES_DIR', __DIR__ . '/includes');
define('FUNCTIONS_DIR', INCLUDES_DIR . '/functions');

// Callog functions
$INCLUDES = __DIR__ . "/includes"; // use the include variable to reference all app specific includes

$IMAGES = $GLOBALS['BASE_URL'] . '/images';
// End variable definitions

define('MAGPIE_CACHE_ON', false);
require_once 'magpierss/rss_fetch.inc';

require_once 'functions.php';

if( isset($_GET['go']) ) {
	PSUHTML::redirect( $GLOBALS['BASE_URL'] . '/ticket/' . $_GET['go'] );
}

require_once (INCLUDES_DIR."/functions.php");
require_once (INCLUDES_DIR."/CallLog.class.php");

include(FUNCTIONS_DIR . "/add_update.class.php");
include(FUNCTIONS_DIR . "/admin_functions.php");
include(FUNCTIONS_DIR . "/call_log_graph_functions.php");
include(FUNCTIONS_DIR . "/call_log_keyword_admin_functions.php");
include(FUNCTIONS_DIR . "/call_log_search.php");
include(FUNCTIONS_DIR . "/call_log_stats_and_reports_functions.php");
include(FUNCTIONS_DIR . "/employee_calls_function.php");
include(FUNCTIONS_DIR . "/my_options_functions.php");
include(FUNCTIONS_DIR . "/news_functions.php");
include(FUNCTIONS_DIR . "/open_call_functions.php");
include(FUNCTIONS_DIR . "/recovered_data.php");
include(FUNCTIONS_DIR . "/restore.class.php");
include(FUNCTIONS_DIR . "/tlc_users_functions.php");
include(FUNCTIONS_DIR . "/user.class.php");

require_once 'portal.class.php';

/*******************[Database Connections]*****************/
$db = PSU::db('calllog');
$GLOBALS['BANNER'] = PSU::db('banner');
$GLOBALS['PHONEBOOK'] = PSU::db('phonebook');
/*******************[End Database Connections]*****************/
$idm = new IDMObject($GLOBALS['BANNER']);
$GLOBALS['BannerIDM'] =& $idm;

$GLOBALS['BannerGeneral'] =  new BannerGeneral($GLOBALS['BANNER']);
$portal = new Portal('prod'); // Portal object
$GLOBALS['portal'] =& $portal;

$user = new User($db); // User object
$GLOBALS['user'] =& $user;
$restore = new Restore($db); // Restore object
$new_call = new NewCall($db); // New Call object

IDMObject::authN();
$GLOBALS['is_employee'] = checkEmployee();

if(!checkEmployee() && $_SERVER['SCRIPT_NAME'] != '/webapp/calllog/add_new_call.html' && $_SERVER['SCRIPT_NAME'] != '/webapp/calllog/update_call_details.html'){
	exit('Not a Valid Employee');
} elseif( $_SERVER['SCRIPT_NAME'] == '/webapp/calllog/add_new_call.html' && $_POST && ($_GET['call_source'] == 'support' || $_GET['call_source'] == 'feedback')){
	$_POST['problem_details'] = filter_var( $_POST['problem_details'], FILTER_SANITIZE_STRING );

	$prevent_file_upload = true;
	$GLOBALS['end_user_email'] = true;
	$person = new PSUPerson( $_SESSION['username'] );
	$_POST['caller_user_name'] = $_POST['call_log_username'] = $person->username ? $person->username : $person->wp_id;
	$_POST['caller_first_name'] = $person->formatName('f');
	$_POST['caller_last_name'] = $person->formatName('l');
	if( $person->phones['OF'][0] ) {
		$_POST['caller_phone_number'] = '('.$person->phones['OF'][0]->phone_area.')'.$person->phones['OF'][0]->phone_number;
	} else {
		$_POST['caller_phone_number'] = '';
	}//end else

	if( $_GET['call_source'] == 'feedback' ) {
		$_POST['feelings_face'] = filter_var( $_POST['feelings_face'], FILTER_SANITIZE_STRING );
		$_POST['feelings'] = filter_var( $_POST['feelings'], FILTER_SANITIZE_STRING );
		$_POST['title'] = filter_var( $_POST['excerpt'], FILTER_SANITIZE_STRING );
		$_POST['problem_details'] = filter_var( $_POST['feedback'], FILTER_SANITIZE_STRING );
		$_POST['problem_details'] .= "\n\n-----------\n";
		if( $_POST['url'] ) {
			$_POST['problem_details'] .= "URL: ".filter_var($_POST['url'], FILTER_SANITIZE_STRING)."\n";
		}//end if
		$_POST['problem_details'] .= 'User Agent: '.$_SERVER['HTTP_USER_AGENT'];
	}//end if

	$_POST['resnet_check'] = 'no';
	$_POST['its_assigned_group'] = $_GET['call_source'] == 'support' ? 7 : 27;
	$_POST['tlc_assigned_to'] = 'unassigned';
	$_POST['call_status'] = 'open';
	$_POST['call_priority'] = 'normal';
	$_POST['location_building_id'] = 0;
	$_POST['location_building_room_number'] = '';
	$_POST['location_call_logged_from'] = $_GET['call_source'] == 'support' ? 'support' : 'feedback';
} elseif( $_SERVER['SCRIPT_NAME'] == '/webapp/calllog/update_call_details.html' && $_POST && $_GET['ticket'] && $_GET['call_source'] == 'support'){
	$_POST['problem_details'] = filter_var( $_POST['problem_details'], FILTER_SANITIZE_STRING );

	$prevent_file_upload = true;
	$GLOBALS['end_user_email'] = true;
	$person = new PSUPerson( $_SESSION['username'] );

	$_POST['call_id'] = $_GET['call_id'] = (int) $_GET['ticket'];

	$call = $db->GetRow("SELECT * FROM call_log WHERE call_id = ?", array($_GET['call_id']));
	$_POST['title'] = $call['title'];
	$_POST['feelings'] = $call['feelings'];
	$_POST['feelings_face'] = $call['feelings_face'];

	$max_history = $db->GetRow("SELECT * FROM call_history WHERE call_id = ? AND current = 1", array($_GET['call_id']));
	if( $max_history['call_status'] == 'closed' ) {
		PSU::redirect( $_GET['redirect'] );
	}//end if

	$_POST['call_log_username'] = $_POST['caller_user_name'] = $person->username ? $person->username : $person->wp_id;
	$_POST['caller_first_name'] = $person->formatName('f');
	$_POST['caller_last_name'] = $person->formatName('l');
	if( $person->phones['OF'][0] ) {
		$_POST['caller_phone_number'] = '('.$person->phones['OF'][0]->phone_area.')'.$person->phones['OF'][0]->phone_number;
	} else {
		$_POST['caller_phone_number'] = '';
	}//end else

	$_POST['its_assigned_group'] = $max_history['its_assigned_group'];
	if( $max_history['tlc_assigned_to'] != $person->username ) {
		$_POST['tlc_assigned_to'] = $max_history['tlc_assigned_to'];
	} else {
		$_POST['tlc_assigned_to'] = 'unassigned';
	}//end else
	$_POST['call_status'] = $_POST['call_status'] == 'closed' ? 'closed' : $max_history['call_status'];
	$_POST['call_priority'] = $max_history['call_priority'];
	$_POST['location_building_id'] = 0;
	$_POST['location_building_room_number'] = '';
	$_POST['location_call_logged_from'] = 'support';

}//end else
require_once 'xtemplate.php';

// Zach's LDAP Function
require_once 'search.class.php';
$search_PSU = new searchPSU();

$_SESSION['tlc_error_message'] = '<h2>You are not authorized to access this page.</h2>';

// Application Name And Version Number
define('APPLICATION_NAME', 'Call Log');

$GLOBALS['search_setting'] = $GLOBALS['user']->getSearchSetting($_SESSION['username']);

// Select Options Arrays, To Use For Zach's getSelectOptions function
// ------------------------------------------------------------------ 
$call_status_options = Array('closed'=>'Closed', 'open'=>'Open');
$call_priority_options = Array('normal'=>'Normal', 'medium'=>'Medium', 'high'=>'High', 'inprogress'=>'In Progress', 'pending'=>'Pending', 'delayed'=>'Delayed', 'install'=>'Install', 'upgrade' => 'Upgrade', 'purchase' => 'Purchase', 'collision' => 'Account Collision');
$user_status = Array('active'=>'Active', 'disabled'=>'Disabled', 'inactive'=>'Inactive');
$tlc_employee_positions = Array('trainee'=>'Information Desk Trainee', 'sta'=>'Information Desk Consultant', 'shift_leader'=>'Senior Information Desk Consultant', 'cts'=>'Classroom Technology Assistant', 'cts_manager'=>'CTS Manager', 'supervisor'=>'Information Desk Shift Supervisor',  'manager'=>'LLC Manager', 'staff'=>'Staff Member', 'webguru'=>'Call Log Overlord');
$class_options = Array('first_year'=>'First Year', 'second_year'=>'Second Year', 'third_year'=>'Third Year', 'fourth_year'=>'Fourth Year', 'fifth_year'=>'Fifth Year', 'grad'=>'Grad School', 'alum'=>'Alumni');
$yes_no_options = Array('yes'=>'Yes', 'no'=>'No');
$ferpa_options = Array('1'=>'Yes', '0'=>'No');
$group_option_id = Array('0'=>'not_subscribed','1'=>'show', '2'=>'show_email');

$phonebook_search = array('all','name_first','name_last','email','phone');
if($GLOBALS['search_setting'] == 'full')
{
	//$search_options = Array('1'=>'Everybody', '9'=>'Ticket Number', '6'=>'Computer Name', '7'=>'MAC Address', '8'=>'IP Address', '11'=>'Closed Calls', '10'=>'Call Log User');
	$search_options = Array(
		'all'=>'Everybody', 
		'wp_id' => 'WordPress/Family Portal',
		'ticket'=>'Ticket Number', 
		'computer'=>'Computer Name', 
		'mac'=>'MAC Address', 
		'ip'=>'IP Address', 
		'closed'=>'Closed Calls', 
		'user'=>'Call Log User'
	);
}//end if
else
{
	$search_options = Array(
		'name_last' => 'Last Name', 
		'email' => 'Username',
		'name_first'=>'First Name', 
		'wp_id' => 'WordPress/Family Portal',
		'ticket'=>'Ticket Number', 
		'phone' => 'Phone', 
		'computer'=>'Computer Name', 
		'mac'=>'MAC Address', 
		'ip'=>'IP Address', 
		'closed'=>'Closed Calls', 
		'user'=>'Call Log User'
	);
}//end else
// ------------------------------------------------------------------

$today = date("Y-m-d");
$time = date("H:i:s");

if(empty($_SESSION['priv_users'])){
  $_SESSION['priv_users'] = Array('manager', 'webguru', 'shift_leader','supervisor');
}// end if

if(in_array($_SESSION['tlc_position'], $_SESSION['priv_users'])){
	$GLOBALS['calllog_admin'] = true;
}
