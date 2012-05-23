<?php
/**
 * db.php
 *
 * PHP Logon Stats: Database connection
 *
 * @version		1.0
 * @author		Alan Baker <a_bake@plymouth.edu>
 * @copyright 2007, Plymouth State University, ITS
 */ 
$GLOBALS['SYSTEMS_DB']= ADONewConnection('mysql');
$GLOBALS['SYSTEMS_DB']->debug = false;
$GLOBALS['SYSTEMS_DB']->SetFetchMode(ADODB_FETCH_ASSOC);
$GLOBALS['SYSTEMS_DB']->Connect($GLOBALS['SYSTEMS']['HOSTNAME'], $GLOBALS['SYSTEMS']['USERNAME'],$GLOBALS['SYSTEMS']['PASSWORD'], $GLOBALS['SYSTEMS']['SCHEMA']);

?>
