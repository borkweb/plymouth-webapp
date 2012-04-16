<?php

require_once 'StatsD.class.php';

function includes_psu_register( $prefix = null, $basedir = null ) {
	static $directories = array();

	if( $prefix && $basedir ) {
		$directories[$prefix] = $basedir;
	}

	return $directories;
}

function includes_psu_autoload( $class ) {
	static $base_dir = null;

	if( null === $base_dir ) {
		$base_dir = dirname( __FILE__ );
	}

	// translate namespaces: PSU\Foo becomes PSU_Foo
	$class = str_replace( '\\', '_', $class );

	// whitelisting for now, maybe later we just check for the file
	$prefix_whitelist = includes_psu_register();

	$prefix = null;

	if( ( $pos = strpos( $class, '_' ) ) !== false ) {
		$prefix = substr( $class, 0, $pos );
	} else {
		$prefix = $class;
	}

	if( isset( $prefix_whitelist[$prefix] ) ) {
		$file = $prefix_whitelist[$prefix] . '/' . str_replace( '_', '/', $class ) . '.php';

		if( file_exists( $file ) ) {
			require_once $file;
			return;
		}
	}

	if( file_exists( $file = $base_dir . '/' . $class . '.class.php' ) ) {
		require_once $file;
		return;
	}
}
spl_autoload_register( 'includes_psu_autoload' );

// new apps will
define( 'PSU_LEGACY_DIR', __DIR__ );
define( 'PSU_BASE_DIR', dirname( __DIR__ ) );
define( 'PSU_LIB_DIR', PSU_BASE_DIR . '/lib' );
define( 'PSU_EXTERNAL_DIR', PSU_BASE_DIR . '/external' );

includes_psu_register( 'PSU', PSU_LIB_DIR );
includes_psu_register( 'Rave', PSU_LIB_DIR );
includes_psu_register( 'Zend', PSU_EXTERNAL_DIR );

if( 'apache2handler' == php_sapi_name() ) {
	$logger = new PSU\Error\Logger( '/var/log/php/deprecated.log' );
	$handler = PSU\Error\DeprecationMonitor::soft_handler();
	$handler->logger = $logger;
}
