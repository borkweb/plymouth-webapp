<?php

require_once dirname( dirname( dirname( __DIR__ ) ) ) . '/legacy/git-bootstrap.php';

if( php_sapi_name() !== 'cli' ) {
	die( 'This script must be run from the command line.' );
}
