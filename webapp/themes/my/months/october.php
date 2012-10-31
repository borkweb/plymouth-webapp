<?php

/****************[ LGBTQ Month 2011 ]********************/
if($month==10 && ($day >= 1 && $day <= 7) && $year == 2011) {
	$theme->add('lgbtq', 'my.css', true);
	$theme->add('lgbtq/', 'queer-history-month.css', false);
	$theme->event = true;
}//end if

/****************[ Homecoming 2010 ]********************/
if($month==10 && ($day >= 1 && $day <=3) && $year == 2010) {
	$stylesheet = ($_GET['style']) ? preg_replace('/[^a-zA-Z0-9\-\_]/','',$_GET['style']).'.css' : 'style.css';
	$theme->add('homecoming10', $stylesheet, true);
	$theme->event = true;
}//end if

/****************[ October 31: Halloween ]*******************/
if($year == 2012 && $month==10 && $day>=28 && $day <= 31) {
	$theme->add('halloween11','my.css',true);
	$theme->event = true;
}//end elseif

/****************[ LGBTQ Month 2012 ]********************/
if($month==10 && ($day >= 1 && $day <= 7) && $year == 2012) {
	$theme->add('lgbtq', 'my.css', true);
	$theme->add('lgbtq/', 'queer-history-month.css', false);
	$theme->event = true;
}//end if

/*---------- TODO: -------------*/
/****************[ October: Pumpkins on Rounds ]*******************/
/****************[ October: Columbus Day ]*******************/
