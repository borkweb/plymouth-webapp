<?php

require dirname( dirname( __DIR__ ) ) . '/legacy/git-bootstrap.php';
require_once 'autoload.php';

PSU::session_start(); // force ssl + start a session

$GLOBALS['BASE_URL'] = '/webapp/training-tracker';
$GLOBALS['BASE_DIR'] = __DIR__;
$GLOBALS['TITLE'] = 'Training Tracker';
$GLOBALS['TEMPLATES'] = $GLOBALS['BASE_DIR'] . '/templates';

if( file_exists( $GLOBALS['BASE_DIR'] . '/debug.php' ) ) {
	include $GLOBALS['BASE_DIR'] . '/debug.php';
}

includes_psu_register( 'TrainingTracker', $GLOBALS['BASE_DIR'] . '/includes' );

require_once 'klein/klein.php';

require_once $GLOBALS['BASE_DIR'] . '/includes/TrainingTrackerAPI.class.php';

IDMObject::authN();

/**
 * Routing provided by klein.php (https://github.com/chriso/klein.php)
 * Make some objects available elsewhere.
 */

//Catch all
respond( function( $request, $response, $app ) {
	// get the logged in user
	$app->user = PSUPerson::get( $_SESSION['wp_id'] ); 

	$memcache = new \PSUMemcache('training-tracker_teams');
	if ( ! ($cached_results = $memcache->get('is_admin'))){

		$staff_collection = new TrainingTracker\StaffCollection();
		$staff_collection->load(); 
		$valid_users = $staff_collection->valid_users();

		$is_valid = false;
		$is_mentor = false;
		$is_admin = false;
		foreach ($valid_users as $user){
			if ($app->user->wpid == $user->wpid){
				$is_valid = true;
			}
		}	

		if (!$is_valid){
			die('You do not have access to this app.');
		}

		$teams_data = TrainingTracker::get_teams();

		$has_team = false;
		$wpid = $app->user->wpid;
		if (isset($teams_data["$wpid"])){
			$has_team = true;
		}

		$admins = $staff_collection->admins();
		$mentors = $staff_collection->mentors();

		foreach ($admins as $admin){
			if ($app->user->wpid == $admin->wpid){
				$is_admin = true;
				$is_mentor = true;
			}
		}
		if (!$is_mentor){
			foreach ($mentors as $mentor){
				if ($app->user->wpid == $mentor->wpid){
					$is_mentor = true;
				}
			}
		}

		$active_user_parameters['wpid'] = $wpid;
		$active_user = new TrainingTracker\Staff($active_user_parameters);

		$memcache->set( 'active_user', $active_user, MEMCACHE_COMPRESSED, 60 * 5 );
		$memcache->set( 'has_team', $has_team, MEMCACHE_COMPRESSED, 60 * 5 );
		$memcache->set( 'is_admin', $is_admin, MEMCACHE_COMPRESSED, 60 * 5 );
		$memcache->set( 'is_mentor', $is_mentor, MEMCACHE_COMPRESSED, 60 * 5 );
		$memcache->set( 'is_valid', $is_valid, MEMCACHE_COMPRESSED, 60 * 5 );

	}
	else{

		$active_user = $memcache->get('active_user');
		$has_team = $memcache->get('has_team');
		$is_admin = $memcache->get('is_admin');
		$is_mentor = $memcache->get('is_mentor');
		$is_valid = $memcache->get('is_mentor');
	}

	if (!$is_valid){
		die('You do not have access to this app.');
	}

	$app->active_user = $active_user;
	$app->is_admin = $is_admin;
	$app->is_mentor = $is_mentor;
	// initialize the template
	$app->tpl = new PSUTemplate;

	// assign user to template
	$app->tpl->assign('active_user', $active_user);
	$app->tpl->assign('user', $app->user);
	$app->tpl->assign('base_url', $GLOBALS['BASE_URL']);
	$app->tpl->assign('has_team', $has_team);
	$app->tpl->assign('wpid', $wpid);
	$app->tpl->assign('is_admin', $is_admin);
	$app->tpl->assign('is_mentor', $is_mentor);
});

// the person select page
respond( '/?', function( $request, $response, $app ) {

	if ($app->is_mentor){
		$staff_collection = new TrainingTracker\StaffCollection();
		$staff_collection->load();

		$staff = $staff_collection->staff();
	}
	else{
		$current_user_parameter["wpid"] = $app->user->wpid;
		$person = new TrainingTracker\Staff($current_user_parameter);
		$person->privileges = TrainingTracker::get_user_level($person->wpid);
		$staff[0] = $person;
	}

	foreach ($staff as $person){
		$pidm = $person->person()->pidm;
		
		$person->merit = TrainingTracker::merit_get($pidm);
		$person->demerit = TrainingTracker::demerit_get($pidm);
		$type = TrainingTracker::checklist_type($person->privileges);
		if (!TrainingTracker::checklist_exists($pidm, $type, 0)){
			//get tybe based off of a persons privileges
			$type = TrainingTracker::checklist_type($person->privileges);
			//insert new checklist (pidm, type)
			TrainingTracker::checklist_insert($pidm, $type);
		}
	}
	$app->tpl->assign('staff', $staff);
	$app->tpl->display('index.tpl');
});

$app_routes = array(
	 'staff', 
	 'team' 
);

foreach( $app_routes as $base ) {
	with( "/{$base}", $GLOBALS['BASE_DIR'] . "/routes/{$base}.php" );
}//end foreach

dispatch( $_SERVER['PATH_INFO'] );
