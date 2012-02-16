<?php

/**
 * Plymouth webapp bootstrap.
 */

define( 'PSU_WEBAPP_BASE', dirname(__DIR__) );
define( 'PSU_LIB_DIR', PSU_WEBAPP_BASE . '/lib' );

ini_set( 'include_path', str_replace( 'BASE', PSU_WEBAPP_BASE, '.:BASE/legacy:BASE/external:/etc/httpd/conf/admin:/web/pscpages/includes' ) );

require PSU_WEBAPP_BASE . '/legacy/autoload.php';
require PSU_WEBAPP_BASE . '/routes/routes.php';

dispatch( $_SERVER['PATH_INFO'] );
