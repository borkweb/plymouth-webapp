<?php

/**
 * Plymouth webapp bootstrap.
 */

require dirname( __DIR__ ) . '/legacy/git-bootstrap.php';

require 'autoload.php';
require PSU_BASE_DIR . '/routes/routes.php';

$config = new PSU\Config;
$config->load();

$uri = substr( $_SERVER['REQUEST_URI'], strlen( parse_url( $config->get( 'app_url' ), PHP_URL_PATH ) ) );

dispatch( $uri );
