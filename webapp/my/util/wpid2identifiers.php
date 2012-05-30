<?php

/**
 * Take wpids from connect and populate them into the idm person_identifiers database.
 */

ignore_user_abort(true);

if( ob_get_level() ) {
	ob_end_flush();
}

require 'PSUTools.class.php';

$sid = 'idm';

$sql = "SELECT u.user_login wp_id, m.meta_value pid FROM wp_users u LEFT JOIN wp_usermeta m ON u.ID = m.user_id WHERE m.meta_key = 'pidm'";

$rset = PSU::db('connect')->Execute($sql);

PSU::db( $sid )->StartTrans();

$sql = "
	MERGE INTO psu_identity.person_ext_cache target
	USING ( SELECT :pid pid, :wp_id wp_id FROM dual ) source
	ON (target.pid = source.pid)
	WHEN MATCHED THEN
		UPDATE SET target.wp_id = source.wp_id
	WHEN NOT MATCHED THEN
		INSERT ( target.pid, target.wp_id )
		VALUES ( source.pid, source.wp_id )
";

foreach($rset as $row) {
	PSU::db($sid)->Execute($sql, $row);
	flush();
}

PSU::db( $sid )->CompleteTrans();
