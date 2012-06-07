<?php
/*---------- TODO: -------------*/
/****************[ April 1: April Fool's Day ]*******************/
if($day == 1 && $year == 2012) {
	$theme->add('topsy-turvy','my.css',false);
}//end elseif
/****************[ April ?: Spring Fling ]*******************/
/****************[ April 15: Tax Day ]*******************/
/****************[ April 20-21: Medieval Forum ]*******************/
if($day >= 20 && $day <= 21 && $year == 2012) {
	$theme->add('medieval-forum-12','my.css',true);
}//end elseif
/****************[ April 22: Earth Day ]*******************/
/****************[ April 26-28: Chili Cook-Off ]*******************/
if($day >= 26 && $day <= 28 && $year == 2012) {
	$theme->add('chili-cookoff','my.css',true);
}//end elseif
