<?php
/**
 * Routing provided by klein.php (https://github.com/chriso/klein.php)
 * Make some objects available elsewhere.
 */
respond( function( $request, $response, $app ) {
	PSU::session_start(); // force ssl + start a session

	$GLOBALS['BASE_URL'] = '/app/address-verification';

	$GLOBALS['TITLE'] = 'Address Verification';
	$GLOBALS['TEMPLATES'] = PSU_BASE_DIR . '/app/address-verification/templates';

	if( file_exists( PSU_BASE_DIR . '/debug/address-verification-debug.php' ) ) {
		include PSU_BASE_DIR . '/debug/address-verification-debug.php';
	}
	IDMObject::authN();

	if( ! IDMObject::authZ('permission', 'mis') && ! IDMObject::authZ('role','address_verification') ) {
		die('You do not have access to this application.');
	}

	// get the logged in user
	$app->user = PSUPerson::get( $_SESSION['wp_id'] ); 

	// create template object
	$app->tpl = new PSU\Template;

	// assign user to template
	$app->tpl->assign( 'user', $app->user );
});

respond('/', function( $request, $response, $app ) {
	$app->tpl->display("index.tpl");
});

respond('GET', '/[:table]', function( $request, $response, $app ) {
	$table = strtolower($request->param('table'));
	if( 'spraddr' != $table ) {
		  $response->abort( 400, 'UNEXPECTED_VALUE' );
	}//end if
	$is_running = shell_exec('ps -ef | grep -i runner-verify-'.$request->param('table').'.php | grep -v grep');
	if( $is_running ) {
		$_SESSION['messages'][] = 'The '.$table.' script is running. Please click reload to check status again. If this message no longer appears, the script has completed you can view the analytics report.';
	}//end if
	$app->tpl->assign( 'is_running', $is_running );
	$app->tpl->display("{$table}.tpl");
});

respond('POST', '/spraddr', function( $request, $response, $app ) {
	foreach ($_POST as $key => $val) {
		if ($val == 'null') $_POST[$key] = null;
		if ($val == '') $_POST[$key] = null;
	}
	if ($_POST['fv_address_type_val'] != null) $_POST['fv_address_type'] = $_POST['fv_address_type_val'];
	if ($_POST['fn_days_back_val'] != null) $_POST['fn_days_back'] = $_POST['fn_days_back_val'];

	$errorFlag=false;
	if (($_POST['fd_from_date'] == null && $_POST['fd_to_date'] == null) || ((strtotime($_POST['fd_to_date']) > strtotime($_POST['fd_from_date'])) && strtotime($_POST['fd_from_date']) != null)) {
		$_SESSION['errors'] = array();
	} else {
		$_POST['fd_from_date'] = null;
		$_POST['fd_to_date'] = null;
		$_SESSION['errors'][] = 'Error: Either you typed in one date, and not the other, or your to date was a date before your from date. Please correct and try again.';
		$errorFlag=true;
	}
	if (!$errorFlag) {
		unset($_POST['fv_address_type_val']);
		unset($_POST['fn_days_back_val']);

		$keys = array(
			'fn_max_verify'
			,'fb_update'
			,'fb_only_unverified'
			,'fv_address_type'
			,'fn_days_back'
			,'fb_skip_international'
			,'fb_verify_inactive'
			,'fd_from_date'
			,'fd_to_date'
			,'fb_set_activity_date_user'
			,'fv_set_source_code'
		);

		foreach ($keys as $key) {
			$val = $_POST[$key];
			if (is_null($val)) {
				continue;	
			}
			if ($key == 'fv_address_type') {
				if( ! preg_match( '/[a-zA-Z0-9]{2}/', $val ) ) {
					$_SESSION['errors'][] = 'Error: Invalid Address Type';
					$errorFlag=true;
				}
			} elseif ($key == 'fn_days_back') {
				if(!is_numeric($val)) {
					$_SESSION['errors'][] = 'Error: Non-numeric number of days specified';
						$errorFlag=true;
				}
			} elseif ($key == 'fd_from_date' || $key == 'fd_to_date') {
				if (!strtotime($val)) {
					$_SESSION['errors'][] = 'Error: One of the date entries can not be validated as a date';
					$errorFlag=true;
				}
			} elseif ($key == 'fn_max_verify' || $key == 'fb_set_activity_user' || $key == 'fv_set_source_code') { // these one can not be set here ... just continue on by
				continue;
			} else { // the rest are booleans...just make sure they are
				if (!is_bool($val)) {
					$_SESSION['errors'][] = 'Error: A true/false variable has been incorrectly set';
					$errorFlag=true;
				}
			}
			$parms .= "--{$key}={$val} ";
		}
		$cmd = PSU_BASE_DIR.'/scripts/runner-verify-spraddr.php '.$parms.' &> /dev/null &';
	}
	if (!$errorFlag) {
		exec( $cmd );
	}
	$response->redirect( $GLOBALS['BASE_URL'].'/spraddr' );
});

