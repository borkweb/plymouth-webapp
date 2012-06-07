<?php
session_start();
header('Content-type: image/png');

$hour = date('H');

$image = 'header_bg_squirrel.png';

if( $hour >= 5 && $hour < 9 ) {
	$image = 'header_bg_morning.png';
} elseif( $hour > 11 && $hour < 14 ) {
	$image = 'header_bg_leeroy.png';
} elseif( $hour < 5 || $hour >= 21 ) {
	$image = 'header_bg_bedtime.png';
} else {
	if( rand(0, 2500) == 0 ) {
		$image = 'header_bg_albino.png';
	}//end if

	if( $_SESSION['username'] == 'jfoote' ) {
		$image = 'header_bg_albino.png';
	}//end if
}//end if

echo file_get_contents('images/'.$image);
