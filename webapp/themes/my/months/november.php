<?php
$thanksgiving = get_holiday($year, 11, 4, 4);

/****************[ November: Election Day ]*******************/
if($day == 3 || $day == 4)
{
//	$theme->add('election_day','style.css',true);
//	$theme->add('election_day/phase'.(($day==3) ? '1' : '4'));
}//end if

/****************[ November: Guy Fawkes Day ]*******************/
if($day == 5)
{
//	$stylesheet = ($_GET['style']) ? preg_replace('/[^a-zA-Z0-9\-\_]/','',$_GET['style']).'.css' : 'style.css';

//	$theme->add('fawkes', $stylesheet, true);
}//end if
/****************[ November: International Week ]*******************/
if($year == 2011 && $day >= 14 && $day <= 18)
{
	$theme->add('international-week-11', 'my.css', true);
	$theme->event = true;
}//end if

/****************[ November: Thanksgiving Phase Base ]*******************/
if($year == 2011 && $day >= $thanksgiving['day']-3 && $day <= $thanksgiving['day'])
{
	$theme->add('thanksgiving-11','my.css',true);
	$theme->event = true;
}//end if

/*---------- TODO: -------------*/
/****************[ November 4: Election Day ]*******************/
/****************[ November 11: Veteren's Day ]*******************/
