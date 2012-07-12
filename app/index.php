<?php

//
// uranus directory override. if $_COOKIE['psudevhost'] is
// set, nothing else in this repository's /app/ will be executed.
//
// this override functionality is limited by the conditional
// logic capabilities of an .htaccess file, so we'll perform
// this check on the Host: header.
//
// See also: /app/.htaccess
//

if( 'www.dev.plymouth.edu' === $_SERVER['HTTP_HOST'] ) {
	require dirname( __DIR__ ) . '/legacy/git-uranus.php';
}

// override include_path
require dirname( __DIR__ ) . '/legacy/git-bootstrap.php';

require 'autoload.php';

// setup the outer webapp object and its internal host config 
$config = PSU\Config\Factory::get_config();
$webapp = new PSU\Webapp( $config );
$webapp->set_host_by_domain( $_SERVER['HTTP_HOST'] );

require_once PSU_BASE_DIR . '/routes/routes.php';

$uri = $webapp->host()->uri_for_dispatch( $_SERVER['REQUEST_URI'] );
dispatch( $uri );
