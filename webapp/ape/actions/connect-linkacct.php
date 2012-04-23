<?php

if(!IDMObject::authZ('permission', 'ape_wp_email_reset'))
{
	throw new Exception('You are not authorized to reset profiles.');
}

$wp_id = $_GET['wp_id'];
$type = $_GET['type'] == 'email_alt' ? 'email_alt' : 'user_email';
$email = strtolower($_GET['email']);

$redirect_to = $GLOBALS['BASE_URL'];

if( !$wp_id ) {
	$_SESSION['errors'][] = 'wp_id was missing from request. How\'d that happen?';
	PSU::redirect( $redirect_to );
}

$person = new PSUPerson($wp_id);
$user = get_userdatabylogin( $person->wp_id );
update_usermeta( $user->ID, 'psuname', $person->login_name );

$redirect_to .= '/user/' . $wp_id;

if( $person->wp_id != $wp_id ) {
	$_SESSION['errors'][] = 'Problem fetching user with wp_id ' . htmlentities($wp_id) . '.';
	PSU::redirect( $redirect_to );
}

if( !$email ) {
	$_SESSION['messages'][] = 'Email address was blank; updated username only.';
	PSU::redirect( $redirect_to );
}

if( !filter_var($email, FILTER_VALIDATE_EMAIL) ) {
	$_SESSION['errors'][] = 'Invalid email address provided: ' . htmlentities($email);
	PSU::redirect( $redirect_to );
}

//
// validation done; do link
//

$ticket = sl_initiate_link( $person->wp_id, $email, $type );

$_SESSION['messages'][] = 'Ticket created: ' . $ticket;

PSU::redirect( $redirect_to );
