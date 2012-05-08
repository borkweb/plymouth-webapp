<?php

/**
 * Plymouth webapp bootstrap.
 */

require dirname( __DIR__ ) . '/legacy/git-bootstrap.php';

require 'autoload.php';
require PSU_BASE_DIR . '/routes/routes.php';

$uri = substr( $_SERVER['REQUEST_URI'], strlen( parse_url( PSU\Config\Factory::get_config()->get( 'app_url' ), PHP_URL_PATH ) ) );

dispatch( $uri );
