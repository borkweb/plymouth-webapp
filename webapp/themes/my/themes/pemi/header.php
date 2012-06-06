<?php
session_start();
header('Content-type: image/png');

$hour = date('H');
$month = date('n');
$weekday = date('N');

$image = 'pemi.png';

if( $month >= 1 && $month <= 4 ) {
	switch( $weekday ) {
		case 1:
			$image = 'pemi_ski.png';
			break;
		case 2:
			$image = 'pemi_wrestling.png';
			break;
		case 4:
			$image = 'pemi_hockey.png';
			break;
		case 5:
			$image = 'pemi_basketball.png';
			break;
	}//end switch
}//end if

echo file_get_contents('images/'.$image);
