<?php
ini_set('memory_limit', -1);
require_once 'autoload.php';
require_once 'PSUWordPress.php';

if( substr(php_sapi_name(), 0, 3) == 'cli' ) {
	$config = new PSU\Config;
	$config->load();

	$GLOBALS['BASE_DIR'] = dirname(__FILE__).'/..';
	$GLOBALS['BASE_URL'] = $config->get('ape', 'base_url');
	$GLOBALS['TEMPLATES'] = $GLOBALS['BASE_DIR'].'/templates';

	$command_line = true;
	require_once $GLOBALS['BASE_DIR'].'/includes/HRChecklist.class.php';

	$cron = new cron('checklist-trigger','page_threshold=20');
	if($cron->checkLock())
	{
		$cron->log('Employee Exit Checklist Trigger is already running');
		exit;
	}//end if

	$cron->lock();
}//end if

$time = time();

$one_month_ago = strtotime('-1 month');

$sql = "SELECT * FROM v_emp_clearance_candidates";
if( $results = PSU::db('banner')->Execute( $sql ) ) {
	foreach( $results as $row ) {
		if( $checklist = HRChecklist::get( $row['pidm'], 'employee-exit' ) ) {
			if( $checklist['position_code'] == $row['position_code'] && strtotime($checklist['activity_date']) >= $one_month_ago ) {
				continue;
			}//end if
		}//end if

		$person = PSUPerson::get( $row['pidm'] );

		$response = HRChecklist::start( $person->pidm, strtotime( $row['end_date'] ), 'employee-exit', $row['position_code'], 0 );
		$checklist_id = $response->id;

		if( $checklist_id ) {
			HRChecklist::email( $person, strtotime($row['end_date']), 'ape_checklist_employee_exit', 2, 'employee-exit', $checklist_id, $response);
		}//end if
	}//end foreach
}//end if

if( $command_line ) {
	$cron->stopTimer();
	$cron->log('Employee Exit Checklist Trigger completed (Run Time: '.$cron->getRunTime().')');
	$cron->unlock();
}//end if
