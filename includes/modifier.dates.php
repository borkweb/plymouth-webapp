<?php
/*
 * Smarty Plugin
 * -------------------------------------------
 * File:	modifier.dates.php
 * Type:	modifier
 * Name: dates
 * Purpose:	convert dates from YYYY-DD-MM to MM-DD-YYYY
 * -------------------------------------------
 */
function smarty_modifier_dates($string){
	return(date("n-j-Y",strtotime($string));

}
