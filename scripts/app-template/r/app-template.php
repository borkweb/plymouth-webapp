<?php

use \Guzzle\Service\Client;
/**
 * Uncomment once object structure has been written
 * use \PSU\%CUSTOM%;
 */

require_once 'guzzle.phar';

respond( function( $request, $response, $app ) {
	PSU::session_start();

	$GLOBALS['BASE_URL'] = $app->config->get( '%CUSTDIR%', 'base_url' );

	$GLOBALS['TITLE'] = 'Generic Application Template';
	$GLOBALS['TEMPLATES'] = PSU_BASE_DIR . '/app/%CUSTDIR%/templates';

	$domain = PSU::isdev() ? 'dev.plymouth.edu' : 'plymouth.edu';
	$GLOBALS['PSUAPI'] = "https://api.{$domain}/0.1/?appid={{appid}}&appkey={{appkey}}";
	unset( $domain );

	if( file_exists( PSU_BASE_DIR . '/debug/%CUSTDIR%-debug.php' ) ) {
		include PSU_BASE_DIR . '/debug/%CUSTDIR%-debug.php';
	}

	IDMObject::authN();

	$app->client = new Client( $GLOBALS['PSUAPI'] );
	$app->client->setConfig( array(
		'appid' => $app->config->get( '%CUSTDIR%', 'api_appid' ),
		'appkey' => $app->config->get( '%CUSTDIR%', 'api_key' ),
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
		$app->tpl->body_style_classes[] = '%CUSTDIR%-readonly';
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

	/**
	 * Once you have created a way to handle permissions 
	 * for your application, uncomment this and delete
	 * the line setting the permissions as an empty array.
	 *
	 * $app->permissions = \PSU\%CUSTOM%\PermissionsFactory::from_idmobject();
	 */
	$app->permissions = array();

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

		/**
		 * Attempt to populate some $app properties. Usually happens when
		 * there was a single argument.
		 * 
		 * if( $object instanceof %CUSTOM%\Object ) {
		 *	   $app->object = $object;
		 * } elseif( $object instanceof %CUSTOM%\Object\Child ) {
		 *     $app->object_child = $object;
		 * }
		 *
 		 */

		//
		// Load available properties into the template
		//

		$props = array(
		);

		foreach( $props as $key ) {
			if( isset( $app->$key ) ) {
				$app->tpl->assign( $key, $app->$key );
			}
		}
	};

	$app->tpl = new PSUTemplate;
	$app->user = PSUPerson::get( $_SESSION['wp_id'] ); 

	$app->populate( 'config', new \PSU\Config );
	$app->config->load();

	/**
	 * Uncomment once resolver has been created for your application.
	 * $app->populate( 'resolver', new %CUSTOM%\Template\Resolver( $app->config ) );
	 * $app->populate( 'breadcrumbs', new \PSU\Template\Breadcrumbs );
	 *
	 * $app->breadcrumbs->push( new \PSU\Template\Breadcrumb( 'Home', $app->config->get( '%CUSTDIR', 'base_url' ) . '/' ) );
	 */

	$app->tpl->assign( 'user', $app->user );
	$app->tpl->assign( 'back_url', $_SERVER['HTTP_REFERER'] );

	// Assign this after mock.php has run
	$app->tpl->assign( 'permissions', $app->permissions );

	/**
	 *  User does not have %CUSTDIR% permission.
	 *
	 *  Uncomment the line below once there is a class
	 *  in your application to handle permissions.
	 *
	 * if( $app->permissions->has('%CUSTDIR%') ) {
	 */
	if( true ) {
		// no special overrides;
	} else {
		// non-%CUSTDIR% folks can only read
		$response->readonly( true );
	}

	// setup search default pref
	$wpid = $_SESSION['wp_id'];
	$meta = PSUMeta::get( '%CUSTDIR%', "search:$wpid:gs" );

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
/**
 * Customize and uncomment lines below to populate the 
 * sub-routes within your application.
 *
 * with( "/admin", __DIR__ . "/%CUSTOM%/admin.php" );
 * with( "/api", __DIR__ . "/%CUSTOM%/api.php" );
 */
