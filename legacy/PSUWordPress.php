<?php

/**
 * Include the connect.plymouth.edu WordPress files.
 */

if( isset($GLOBALS['wpdb']) )
{
	// wordpress was included some other way, accept it as it is.
	return true;
}

if( defined('WP_INSTALLING') && WP_INSTALLING !== true )
{
	// we're about to include wordpress for the first time, but wp_installing is set to an incompatible value
	throw new Exception('WordPress has not been included, but WP_INSTALLING is not true: cannot proceed');
}

define('DISABLE_WP_CRON', true);
define('WP_INSTALLING', true);
define('IS_PSUWORDPRESS', true);
define('WP_CACHE', false);
define('WP_DEBUG', false);
define('SAVEQUERIES', false);

if( ! defined('WP_MEMORY_LIMIT') ) {
	define( 'WP_MEMORY_LIMIT', ini_get('memory_limit') );
}

define('DOMAIN_CURRENT_SITE', PSU::isdev() ? 'www.dev.plymouth.edu' : 'www.plymouth.edu');
define('PATH_CURRENT_SITE', '/connect/');
define('SITE_ID_CURRENT_SITE', 1);
define('BLOGID_CURRENT_SITE', 1);

require_once 'autoload.php';

require_once '/web/connect.plymouth.edu/wp-load.php';
 
// ask php to set the correct time zone (by default, wp changes the tz to utc)
wp_timezone_override_offset();

// in PHP 5, WordPress core is explicitly setting the timezone to UTC. (wp-settings.php line 37)
date_default_timezone_set('America/New_York');

if( !isset($GLOBALS['wpdb']) )
{
	throw new Exception('Cannot find $wpdb after including WordPress. Please include from the global scope.');
}
