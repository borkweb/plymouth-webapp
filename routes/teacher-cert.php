<?php

use \Guzzle\Service\Client;
use \PSU\TeacherCert;

require_once 'guzzle.phar';

/**
 * Old /me/ path redirects to /student/
 */
respond( '/me/?[*]?', function( $request, $response, $app ) {
	$base_url = $app->config->get( 'teacher-cert', 'base_url' );
	$response->redirect( $base_url . '/student/' );
});

respond( '/student/?[*]?', function( $request, $response, $app ) {
	PSU::add_filter( 'student_view', 'PSU\TeacherCert::__return_true' );
});

respond( function( $request, $response, $app ) {
	PSU::session_start();

	$GLOBALS['BASE_URL'] = $app->config->get( 'teacher-cert', 'base_url' );

	$GLOBALS['TITLE'] = 'Teacher Certification';
	$GLOBALS['TEMPLATES'] = PSU_BASE_DIR . '/app/teacher-cert/templates';

	$domain = PSU::isdev() ? 'dev.plymouth.edu' : 'plymouth.edu';
	$GLOBALS['PSUAPI'] = "https://api.{$domain}/0.1/?appid={{appid}}&appkey={{appkey}}";
	unset( $domain );

	if( file_exists( PSU_BASE_DIR . '/debug/teacher-cert-debug.php' ) ) {
		include PSU_BASE_DIR . '/debug/teacher-cert-debug.php';
	}

	IDMObject::authN();

	$app->client = new Client( $GLOBALS['PSUAPI'] );
	$app->client->setConfig( array(
		'appid' => $app->config->get( 'teacher-cert', 'api_appid' ),
		'appkey' => $app->config->get( 'teacher-cert', 'api_key' ),
	));

	$app->readonly = false;
	$app->student_view = false;

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

		// Is it ok to die here, or do we need a way to skip
		// future routes? (For example, if there is a final cleanup
		// routine.)
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

	$app->tpl = new TeacherCert\Template;
	$app->user = PSUPerson::get( $_SESSION['wp_id'] ); 

	$app->populate( 'config', new \PSU\Config );
	$app->config->load();

	$app->populate( 'resolver', new TeacherCert\Template\Resolver( $app->config ) );
	$app->populate( 'breadcrumbs', new \PSU\Template\Breadcrumbs );

	$app->breadcrumbs->push( new \PSU\Template\Breadcrumb( 'Home', $app->config->get( 'teacher-cert', 'base_url' ) . '/' ) );

	$app->tpl->assign( 'user', $app->user );
	$app->tpl->assign( 'back_url', $_SERVER['HTTP_REFERER'] );

	// mocks go after the first responder (above) and below normal routes (below)
	if( defined('TCERT_MOCK') && TCERT_MOCK ) {
		include PSU_BASE_DIR . '/debug/teacher-cert-mock.php';
	}

	// Assign this after mock.php has run
	$app->tpl->assign( 'permissions', $app->permissions );

	$app->student_view = PSU::apply_filters( 'student_view', $app->student_view );

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

with( "/admin", __DIR__ . "/teacher-cert/admin.php" );
with( "/api", __DIR__ . "/teacher-cert/api.php" );
with( "/student-gate", __DIR__ . "/teacher-cert/student-gate.php" );
with( "/student-clinical-faculty", __DIR__ . "/teacher-cert/student-clinical-faculty.php" );
with( "/student-school", __DIR__ . "/teacher-cert/student-school.php" );
with( "/gate-system", __DIR__ . "/teacher-cert/gate-system.php" );
with( "/gate-systems", __DIR__ . "/teacher-cert/gate-systems.php" );
with( "/student", __DIR__ . "/teacher-cert/student.php" );
