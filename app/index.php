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

require dirname( __DIR__ ) . '/legacy/git-bootstrap.php';

require 'autoload.php';
require PSU_BASE_DIR . '/routes/routes.php';

$uri = substr( $_SERVER['REQUEST_URI'], strlen( parse_url( PSU\Config\Factory::get_config()->get( 'app_url' ), PHP_URL_PATH ) ) );

dispatch( $uri );
