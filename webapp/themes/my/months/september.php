<?php
/****************[ September 17: Constitution day ]*******************/
if($month==9 && $day==17 && $year == 2010)
{
	$stylesheet = ($_GET['style']) ? preg_replace('/[^a-zA-Z0-9\-\_]/','',$_GET['style']).'.css' : 'style.css';
	$theme->add('constitution',$stylesheet,true);
	$theme->event = true;
}//end elseif

/****************[ September 19: Talk like a pirate day ]*******************/
if($month==9 && $day==19 && $year == 2010)
{
	//$theme->add('pirate','style.css',true);
	//$theme->event = true;
}//end elseif

/****************[ Homecoming 2010 ]********************/
if($month==9 && ($day >= 27 && $day <=30) && $year == 2010)
{
	$stylesheet = ($_GET['style']) ? preg_replace('/[^a-zA-Z0-9\-\_]/','',$_GET['style']).'.css' : 'style.css';
	$theme->add('homecoming10', $stylesheet, true);
	$theme->event = true;
}//end if

/****************[ Homecoming 2011 ]********************/
if($month==9 && ($day >= 23 && $day <=25) && $year == '2011') {
	$theme->add('homecoming11', 'my.css', true);
	$theme->event = true;
}//end if

/*---------- TODO: -------------*/
/****************[ September ??: Startup Week ]*******************/
/****************[ September ??: Labor Day ]*******************/
