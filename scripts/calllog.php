#!/usr/local/bin/php
<?php

require 'autoload.php';

require dirname( __DIR__ ) . '/webapp/calllog/includes/functions.php';

require dirname( __DIR__ ) . '/webapp/calllog/includes/functions/my_options_functions.php'; // needed to fetch config options for users
require dirname( __DIR__ ) . '/webapp/calllog/includes/functions/tlc_users_functions.php'; // needed to fetch all the active users
require dirname( __DIR__ ) . '/webapp/calllog/includes/functions/user.class.php'; // needed to fetch high priority user groups
require dirname( __DIR__ ) . '/webapp/calllog/includes/functions/open_call_functions.php'; // needed to fetch open calls

$GLOBALS['db'] = PSU::db('calllog');

$GLOBALS['BANNER'] = PSU::db('banner');
$GLOBALS['BannerGeneral'] =  new BannerGeneral( $GLOBALS['BANNER'] );

$GLOBALS['user'] = new User( PSU::db('calllog') );

$HOST = 'https://www.plymouth.edu';
$GLOBALS['BASE_URL'] = $HOST . '/webapp/calllog';
$GLOBALS['BASE_DIR'] = dirname(__FILE__);
$GLOBALS['TEMPLATES'] = dirname( __DIR__ ) . '/webapp/calllog/templates';
$GLOBALS['CALLLOG_WEB'] = $GLOBALS['BASE_URL'] . '/calls/my/';

$tpl = new PSU\Template;

$users = getTLCUsers( 'active' );	

foreach( $users as $person ) {

	$reminder = $GLOBALS['user']->getReminderSetting( $person['user_name'] );
	
	if( $reminder == 'yes' ) {

		$options =  array( 
						'which' => 'my', 
						'who' => $person['user_name'],
						);
		
		$calls = getOpenCalls( $options );
		
		if( count( $calls ) >0 ) {
		
			$tpl->assign( 'calls', $calls );
			$tpl->assign( 'user', $person );
			
			$html = $tpl->fetch( 'email.calls.tpl' );
			$text = 'This email is optimized for HTML.  View your open calls here: ' . $GLOBALS['CALLLOG_WEB'];
	
			$p = new PSUPerson($person['user_name']);
			
			PSU::mail( $p->email['CA'][0], '[CallLog] Daily Open Call Report', array( $text, $html ) );
		} // end if 
	} // end if
} // end foreach


