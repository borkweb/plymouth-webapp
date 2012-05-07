<?php

use \Guzzle\Service\Client;
use \PSU\TeacherCert;

require dirname( dirname( __DIR__ ) ) . '/legacy/git-bootstrap.php';

require_once 'autoload.php';
PSU::session_start(); // force ssl + start a session

$GLOBALS['BASE_URL'] = '/webapp/teacher-cert';
$GLOBALS['BASE_DIR'] = __DIR__;

$GLOBALS['TITLE'] = 'Teacher Certification';
$GLOBALS['TEMPLATES'] = $GLOBALS['BASE_DIR'] . '/templates';

$domain = PSU::isdev() ? 'dev.plymouth.edu' : 'plymouth.edu';
$GLOBALS['PSUAPI'] = "https://api.{$domain}/0.1/?appid={{appid}}&appkey={{appkey}}";
unset( $domain );

require_once $GLOBALS['BASE_DIR'] . '/includes/TeacherCertAPI.class.php';
require_once $GLOBALS['BASE_DIR'] . '/includes/TeacherCertTemplate.class.php';

if( file_exists( $GLOBALS['BASE_DIR'] . '/debug.php' ) ) {
	include $GLOBALS['BASE_DIR'] . '/debug.php';
}

IDMObject::authN();

require_once 'klein/klein.php';
require_once 'guzzle.phar';

/**
 * Make some objects available elsewhere.
 */
respond( function( $request, $response, $app ) {
	$config = PSU\Config\Factory::get_config();

	$app->client = new Client( $GLOBALS['PSUAPI'] );
	$app->client->setConfig( array(
		'appid' => $config->get( 'teacher-cert', 'api_appid' ),
		'appkey' => $config->get( 'teacher-cert', 'api_key' ),
	));

	$app->readonly = false;

	$app->parse_model_results = function( $results ) use ( $app ) {
		foreach( (array) $results->messages as $category => $messages) {
			foreach( $messages as $message ) {
				$_SESSION[ $category ][] = $message;
			}//end foreach
		}//end foreach
	};

	$response->readonly = function( $readonly = null ) use ( $app ) {
		if( null === $readonly ) {
			return $app->readonly;
		}

		$readonly = (bool)$readonly;

		$app->readonly = $readonly;
		$app->tpl->assign('readonly', true);
		$app->tpl->body_style_classes[] = 'tcert-readonly';
	};

	$response->deny_to_readonly = function() use ( $response ) {
		if( $response->readonly() ) {
			$response->denied();
		}
	};

	$response->denied = function() use ( $app ) {
		$app->tpl->display( 'access-denied.tpl' );
		// Is it ok to die here, or do we need a way to skip future routes?
		die();
	};

	$app->permissions = \PSU\TeacherCert\PermissionsFactory::from_idmobject();

	/**
	 * Shortcut function to load an object into $app and $app->tpl. Has two
	 * possible execution paths: pass two parameters to explicitly set one
	 * parameter, or pass a Student\Gate or Student\GateSystem as the first
	 * (and only) argument to populate those objects plus the GateSystem and
	 * Student objects.
	 */
	$app->populate = function( $key, $object = null ) use ( $app ) {
		if( null !== $object ) {
			$app->$key = $object;
			$app->tpl->assign( $key, $object );
			return;
		} else {
			// allow just passing the $object as the first param; if so,
			// do the magic below this conditional
			$object = $key;
			$key = null;
		}

		//
		// Attempt to populate some $app properties. Usually happens when
		// there was a single argument.
		//

		if( $object instanceof TeacherCert\Student ) {
			$app->student = $object;
		} elseif( $object instanceof TeacherCert\Student\Gate ) {
			$app->student_gate = $object;
			$app->student_gate_system = $object->student_gate_system();
		} elseif( $object instanceof TeacherCert\Student\GateSystem ) {
			$app->student_gate_system = $object;
		} elseif( $object instanceof TeacherCert\GateSystem ) {
			$app->gate_system = $object;
		} elseif( $object instanceof Gate ) {
			$app->gate = $object;
		}

		if( isset( $app->student_gate_system ) ) {
			if( ! isset( $app->gate_system ) ) {
				$app->gate_system = $app->student_gate_system->gate_system();
			}

			if( ! isset( $app->student ) ) {
				$app->student = $app->student_gate_system->student();
			}
		}

		if( isset( $app->gate ) ) {
			if( ! isset( $app->gate_system ) ) {
				$app->gate_system = $app->gate->gate_system();
			}
		}

		//
		// Load available properties into the template
		//

		$props = array(
			'student_gate_system',
			'student_gate',
			'student',
			'gate_system',
		);

		foreach( $props as $key ) {
			if( isset( $app->$key ) ) {
				$app->tpl->assign( $key, $app->$key );
			}
		}
	};

	$app->tpl = new TeacherCertTemplate;
	$app->user = PSUPerson::get( $_SESSION['wp_id'] ); 

	$app->populate( 'config', new \PSU\Config );
	$app->config->load();

	$app->populate( 'resolver', new TeacherCert\Template\Resolver( $app->config ) );
	$app->populate( 'breadcrumbs', new \PSU\Template\Breadcrumbs );

	$app->breadcrumbs->push( new \PSU\Template\Breadcrumb( 'Home', $app->config->get( 'teacher-cert', 'base_url' ) ) );

	$app->tpl->assign( 'user', $app->user );
	$app->tpl->assign( 'back_url', $_SERVER['HTTP_REFERER'] );
});

// mocks go after the first responder (above) and below normal routes (below)
if( defined('TCERT_MOCK') && TCERT_MOCK ) {
	include $GLOBALS['BASE_DIR'] . '/mock.php';
}

// TODO: move this into routes/me.php, when the syntax gets cleaner
respond( '/me/?[*]', function( $request, $response, $app ){
	if( ! $app->permissions->pidm ) {
		die( 'Could not find your user identifier.' );
	}

	$app->populate( new TeacherCert\Student( $app->permissions->pidm ) );
	$app->populate( 'student_view', true );
});

respond( function( $request, $response, $app ) {
	// Assign this after mock.php has run
	$app->tpl->assign( 'permissions', $app->permissions );

	// User does not have tcert permission; is it a student
	// trying to access his student gates?
	if( $app->permissions->has('tcert') ) {
		// no special overrides;
	} else {
		// non-tcert folks can only read
		$response->readonly( true );

		// non-faculty can only view the student page
		if( ! $app->permissions->has('faculty') ) {
			if( ! $app->student_view ) {
				$response->redirect( $GLOBALS['BASE_URL'] . '/me/' );
			}
		}
	}

	// instantiate gate systems collection
	$app->populate( 'gatesystems', new TeacherCert\GateSystems );

	// setup search default pref
	$wpid = $_SESSION['wp_id'];
	$meta = PSUMeta::get( 'teacher-cert', "search:$wpid:gs" );

	if( $meta ) {
		$app->populate( 'search_default_gs', $meta->value );
	}
});

//
// Nothing specific requested; show list of gatesystems
//
respond( 'GET', '/', function( $request, $response, $app ) {
	$app->tpl->display( 'index.tpl' );
});

respond( '/verify/?', function( $request, $response, $app ){
	include __DIR__ . '/check/verification.php';
	$app->tpl->assign('tables', $tables);
	$app->tpl->display('verification.tpl');
});

respond( 'POST', '/search', function( $request, $response, $app ) {
	$gs_id = $request->param( 'gatesystem_id' );
	$q = $request->param( 'q' );

	$gate_system = new TeacherCert\GateSystem( $gs_id );

	$wpid = $_SESSION['wp_id'];
	PSUMeta::set( 'teacher-cert', "search:$wpid:gs", $gate_system->id );

	$url = sprintf( "%s/gate-system/%s/search?q=%s", $GLOBALS['BASE_URL'],
		$gate_system->slug, urlencode($q) );
	$response->redirect( $url );
});

$app_routes = array(
	'admin',
	'api',
	'student-gate',
	'student-clinical-faculty',
	'student-school',
	'gate-system',
	'gate-systems',
	'me',
);

foreach( $app_routes as $base ) {
	with( "/{$base}", $GLOBALS['BASE_DIR'] . "/routes/{$base}.php" );
}

dispatch( $_SERVER['PATH_INFO'] );
