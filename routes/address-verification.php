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

	if (($_POST['fd_from_date'] == null && $_POST['fd_to_date'] == null) || ((strtotime($_POST['fd_to_date']) > strtotime($_POST['fd_from_date'])) && strtotime($_POST['fd_from_date']) != null)) {
		$_SESSION['errors'] = array();
		$errorFlag=false;
	} else {
		$_POST['fd_from_date'] = null;
		$_POST['fd_to_date'] = null;
		$_SESSION['errors'][] = 'Error: Either you typed in one date, and not the other, or your to date was a date before your from date. Please correct and try again.';
		$errorFlag=true;
	}
	unset($_POST['fv_address_type_val']);
	unset($_POST['fn_days_back_val']);
	foreach( $_POST as $key => $val ) {
		$parms .= "--{$key}={$val} ";
	}
	$cmd = PSU_BASE_DIR.'/scripts/runner-verify-spraddr.php '.$parms.' &> /dev/null &';
	echo $cmd;
	if (!$errorFlag) {
		exec( $cmd );
	}
	$response->redirect( $GLOBALS['BASE_URL'].'/spraddr' );
});

