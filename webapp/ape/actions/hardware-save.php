<?php
/**
 * Save changes to hardware from hardware.html.
 */

if( !IDMObject::authZ('permission', 'ape_hardware') ) {
	die('You do not have hardware privileges.');
}

require_once('PSUHardware.class.php');

if( isset($_POST['ajax']) ) {
	$id = isset($_POST['id']) ? $_POST['id'] : null;
	$value = isset($_POST['value']) ? $_POST['value'] : null;

	list($kind, $id) = explode('-', $id);

	try {
		$person = PSUHardware::userForID($id);
		$person = new PSUPerson($person);

		if( $kind === 'mac' ) {
			$person->hardware->changeMAC( $id, $value );
			die( strtolower($person->hardware[$id]['mac_address']) );
		} elseif( $kind === 'name' ) {
			$person->hardware->changeName( $id, $value );
			die( strtolower($person->hardware[$id]['computer_name']) );
		} elseif( $kind === 'comments' ) {
			$person->hardware->changeComments( $id, $value );
			die( $person->hardware[$id]['comments'] );
		}
	} catch(Exception $e) {
		$_SESSION['errors'][] = 'Error: ' . $e->getMessage();
		PSUHTML::redirect( $BASE_URL . '/hardware/u/' . $person->username );
	}
}

//
// non-ajax stuff
//

$pidm = (int)$_POST['pidm'];
$mac = $_POST['mac'];
$name = $_POST['name'];
$comments = $_POST['comments'];

try{
	$person = new PSUPerson($pidm);
	$person->hardware->addHardware( $name, $mac, $comments );
} catch(Exception $e) {
	$_SESSION['errors'][] = 'Error: ' . $e->getMessage();
	PSUHTML::redirect( $BASE_URL . '/hardware/u/' . $person->username );
}//end try/catch

PSUHTML::redirect( $BASE_URL . '/hardware/u/' . $person->username );
