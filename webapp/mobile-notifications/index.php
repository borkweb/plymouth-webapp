<?php
require_once 'autoload.php';
PSU::session_start(); // force ssl + start a session

$GLOBALS['BASE_URL'] = '/webapp/mobile-notifications';
$GLOBALS['BASE_DIR'] = __DIR__;

$GLOBALS['TITLE'] = 'PSU Mobile Notifications';
$GLOBALS['TEMPLATES'] = $GLOBALS['BASE_DIR'] . '/templates';
$GLOBALS['EMERGENCY_GROUP'] = 8306124;

require_once 'klein/klein.php';

if( file_exists( $GLOBALS['BASE_DIR'] . '/debug.php' ) ) {
	include $GLOBALS['BASE_DIR'] . '/debug.php';
}

IDMObject::authN();

/**
 * Routing provided by klein.php (https://github.com/chriso/klein.php)
 * Make some objects available elsewhere.
 */
respond( function( $request, $response, $app ) {
	// initialize the template
	$app->tpl = new PSUTemplate;

	// get the logged in user
	$app->user = PSUPerson::get( $_SESSION['wp_id'] ); 
	$app->groups = array();

	// assign user to template
	$app->tpl->assign( 'user', $app->user );

	$app->config = new PSU\Config;
	$app->config->load();

	if( $app->rave_user = \PSU\Rave\User::get( $app->user->wpid ) ) {

		// get the rave users groups for the app
		$app->user_groups = array();
		foreach( $app->rave_user->groups() as $group ) {
			$app->user_groups[] = $group->attributes()->__toString();
		}//end foreach

		// assign all of the groups to the template
		foreach( \Rave\REST\SiteAdmin::getGroups() as $group ) {
			$id = $group->attributes()->__toString();
			$app->groups[ $id ][ 'id' ] = $id; 
			$app->groups[ $id ][ 'name' ] = $group->name->__toString();
			$app->groups[ $id ][ 'description' ] = $group->description->__toString();
			$app->groups[ $id ][ 'subscribed' ] = ( in_array( $id, $app->user_groups ) ) ? 'checked="checked"' : ''; 
		}//end foreach

		unset( $app->groups[ $GLOBALS[ 'EMERGENCY_GROUP' ] ] );
	}//end if

});

/**
 * Catch the form's submission
 */
respond( 'POST', '/', function( $request, $response, $app ) {
	foreach( (array)$_POST[ 'group' ] as $group ) {

		if( ! in_array( $group, $app->user_groups ) ) {

			$app->rave_user->subscribeToGroup( $group );

			// get the rave users groups for the app
			$user_groups = array();
			foreach( $app->rave_user->groups() as $grp ) {
				$user_groups[] = $grp->attributes()->__toString();
			}//end foreach

			if( in_array( $group, $user_groups ) ) {
				$_SESSION[ 'successes' ][] = 'Successfully subscribed to group: ' . $app->groups[ $group ][ 'name' ];
				$app->user_groups[] = $group;
			} else {
				$_SESSION[ 'errors' ][] = 'Failed to subscribe to group: ' . $app->groups[ $group ][ 'name' ];
			}//end else
		}//end if
	}//end foreach

	foreach( $app->user_groups as $key => $ugroup ) {
		if( ! in_array( $ugroup, (array)$_POST[ 'group' ] ) && $ugroup != $GLOBALS[ 'EMERGENCY_GROUP' ] ) {
			if( $app->rave_user->unsubscribeFromGroup( $ugroup ) ) {
				$_SESSION[ 'successes' ][] = 'Successfully unsubscribed from group: ' . $app->groups[ $ugroup ][ 'name' ];
				unset( $app->user_groups[ $key ] );
			} else {
				$_SESSION[ 'errors' ][] = 'Failed to unsubscribe from group: ' . $app->groups[ $ugroup ][ 'name' ];
			}//end else
		}//end if
	}//end foreach
});

// klein catch-all
respond( '/', function( $request, $response, $app ) {
	if( $app->rave_user ) {
		foreach( $app->groups as &$group ) {
			$group[ 'subscribed' ] = ( in_array( $group[ 'id' ], $app->user_groups ) ) ? 'checked="checked"' : '';
		}//end foreach

		$app->tpl->assign( 'groups', $app->groups );
		$app->tpl->assign( 'rave_user', $app->rave_user );
	}//end if

	$app->tpl->display('index.tpl');
});

$app_routes = array();

foreach( $app_routes as $base ) {
	with( "/{$base}", $GLOBALS['BASE_DIR'] . "/routes/{$base}.php" );
}//end foreach

dispatch( $_SERVER['PATH_INFO'] );
