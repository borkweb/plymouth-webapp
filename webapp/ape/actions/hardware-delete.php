<?php

/**
 * Save changes to hardware from hardware.html.
 */

if( !IDMObject::authZ('permission', 'ape_hardware') ) {
	die('You do not have hardware privileges.');
}

$pidm = (int)$_GET['pidm'];
$id = (int)$_GET['id'];

$person = new PSUPerson($pidm);
$person->hardware->deleteHardware( $id );

PSUHTML::redirect( $BASE_URL . '/hardware/u/' . $person->username );
