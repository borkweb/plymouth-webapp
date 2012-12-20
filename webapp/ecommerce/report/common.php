<?php
include_once('../common.php');

//setlocale(LC_MONETARY, 'en_US');

$GLOBALS['BASE_URL'] .= '/report';

/*******************[End Site Constants]*****************/

require_once( 'includes/report.lib.php' );	

/*******************[Authentication Stuff]*****************/
IDMObject::authN();

if(!IDMObject::authZ('permission','ecommerce_report') && !IDMObject::authZ('permission', 'mis'))
{
	echo 'You do not have access to use this application';
	exit;
}//end if
/*******************[End Authentication Stuff]*****************/

$GLOBALS['ECommerceTransaction'] = new PSUECommerceTransaction(PSU::get('banner'));
