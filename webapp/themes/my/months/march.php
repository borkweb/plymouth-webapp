<?php
/*---------- TODO: -------------*/
/****************[ March: Women's History Month ]*******************/
if($year == 2010 && $day >= 1 && $day <= 8) {
	if( $_GET['style'] == 'my' ) {
		$theme->add('womens-history', 'my.css', true);
	} else {
		$theme->add('womens-history', 'style.css', true);
	}//end else
	$theme->event = true;
}//end if
/****************[ March 14: SABJD ]*******************/
/****************[ March ?: Spring Break ]*******************/
/****************[ March 17: St. Patrick's Day - NIXED FOR NOW]*******************/
