<?php

/**
 * Utility script to dump email addresses for every portal user who
 * has done some customization. Currently, this includes adding a
 * relationship and modifying the layout.
 *
 * Append ?simple=1 to URL to hide names.
 */

require_once 'IDMObject.class.php';
require_once 'PSUWordPress.php';
require_once 'PSUPerson.class.php';

IDMObject::authN();

if( !IDMObject::authZ('role', 'myplymouth') ) {
	die( 'no access' );
}

echo '<pre>';
PSU::get()->portal = PSU::db('portal_dev');

$sql = "SELECT DISTINCT wpid1 FROM relsearch WHERE substr(wpid1, 1, 1) <> 't'";
$wpids = PSU::db('portal')->GetCol($sql);

$sql = "SELECT DISTINCT wp_id FROM usertabs WHERE wp_id != 0 AND substr(wp_id, 1, 1) <> 't'";
$wpids2 = PSU::db('portal')->GetCol($sql);

$wpids = array_merge($wpids, $wpids2);
$wpids = array_unique($wpids);
sort($wpids);

if( !$_GET['simple'] ) {
	echo "// append ?simple=1 to url to hide names\n\n";
}

foreach($wpids as $wpid) {
	$p = new PSUPerson($wpid);

	if( $_GET['simple'] == 1 ) {
		if( $p->wp_email ) {
			echo $p->wp_email . ", ";
		}
	} else {
		if( PSU::is_wpid( $wpid, PSU::MATCH_TEMPID ) ) {
			printf( "%30s [%s] %s\n", "", $wpid, $p->wp_email );
		} else {
			printf( "%30s [%s] %s\n", $p->first_name . " " . $p->last_name, $wpid, $p->wp_email );
		}
	}

	$p->destroy();
}
