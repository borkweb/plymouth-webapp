<?php
/* US Holiday Calculations in PHP
 * Version 1.02
 * by Dan Kaplan <design@abledesign.com>
 * Last Modified: April 15, 2001
 * ------------------------------------------------------------------------
 * The holiday calculations on this page were assembled for
 * use in MyCalendar:  http://abledesign.com/programs/MyCalendar/
 * 
 * USE THIS LIBRARY AT YOUR OWN RISK; no warranties are expressed or
 * implied. You may modify the file however you see fit, so long as
 * you retain this header information and any credits to other sources
 * throughout the file.  If you make any modifications or improvements,
 * please send them via email to Dan Kaplan <design@abledesign.com>.
 * ------------------------------------------------------------------------
*/

// Gregorian Calendar = 1583 or later
if (!$_GET["y"] || ($_GET["y"] < 1583) || ($_GET["y"] > 4099)) {
    $_GET["y"] = date("Y",time());    // use the current year if nothing is specified
}

function format_date($year, $month, $day) {
    // pad single digit months/days with a leading zero for consistency (aesthetics)
    // and format the date as desired: YYYY-MM-DD by default

    if (strlen($month) == 1) {
        $month = "0". $month;
    }
    if (strlen($day) == 1) {
        $day = "0". $day;
    }
    $date = $year ."-". $month ."-". $day;
    return $date;
}

// the following function get_holiday() is based on the work done by
// Marcos J. Montes: http://www.smart.net/~mmontes/ushols.html
//
// if $week is not passed in, then we are checking for the last week of the month
function get_holiday($year, $month, $day_of_week, $week="") {
    if ( (($week != "") && (($week > 5) || ($week < 1))) || ($day_of_week > 6) || ($day_of_week < 0) ) {
        // $day_of_week must be between 0 and 6 (Sun=0, ... Sat=6); $week must be between 1 and 5
        return FALSE;
    } else {
        if (!$week || ($week == "")) {
            $lastday = date("t", mktime(0,0,0,$month,1,$year));
            $temp = (date("w",mktime(0,0,0,$month,$lastday,$year)) - $day_of_week) % 7;
        } else {
            $temp = ($day_of_week - date("w",mktime(0,0,0,$month,1,$year))) % 7;
        }
        
        if ($temp < 0) {
            $temp += 7;
        }

        if (!$week || ($week == "")) {
            $day = $lastday - $temp;
        } else {
            $day = (7 * $week) - 6 + $temp;
        }

        return array('year'=>$year, 'month'=>$month, 'day'=>$day);
    }
}

function observed_day($year, $month, $day) {
    // sat -> fri & sun -> mon, any exceptions?
    //
    // should check $lastday for bumping forward and $firstday for bumping back,
    // although New Year's & Easter look to be the only holidays that potentially
    // move to a different month, and both are accounted for.

    $dow = date("w", mktime(0, 0, 0, $month, $day, $year));
    
    if ($dow == 0) {
        $dow = $day + 1;
    } elseif ($dow == 6) {
        if (($month == 1) && ($day == 1)) {    // New Year's on a Saturday
            $year--;
            $month = 12;
            $dow = 31;
        } else {
            $dow = $day - 1;
        }
    } else {
        $dow = $day;
    }

    return format_date($year, $month, $dow);
}

function calculate_easter($y) {
    // In the text below, 'intval($var1/$var2)' represents an integer division neglecting
    // the remainder, while % is division keeping only the remainder. So 30/7=4, and 30%7=2
    //
    // This algorithm is from Practical Astronomy With Your Calculator, 2nd Edition by Peter
    // Duffett-Smith. It was originally from Butcher's Ecclesiastical Calendar, published in
    // 1876. This algorithm has also been published in the 1922 book General Astronomy by
    // Spencer Jones; in The Journal of the British Astronomical Association (Vol.88, page
    // 91, December 1977); and in Astronomical Algorithms (1991) by Jean Meeus. 

    $a = $y%19;
    $b = intval($y/100);
    $c = $y%100;
    $d = intval($b/4);
    $e = $b%4;
    $f = intval(($b+8)/25);
    $g = intval(($b-$f+1)/3);
    $h = (19*$a+$b-$d-$g+15)%30;
    $i = intval($c/4);
    $k = $c%4;
    $l = (32+2*$e+2*$i-$h-$k)%7;
    $m = intval(($a+11*$h+22*$l)/451);
    $p = ($h+$l-7*$m+114)%31;
    $EasterMonth = intval(($h+$l-7*$m+114)/31);    // [3 = March, 4 = April]
    $EasterDay = $p+1;    // (day in Easter Month)
    
    return format_date($y, $EasterMonth, $EasterDay);
}

/////////////////////////////////////////////////////////////////////////////
// end of calculation functions; place the dates you wish to calculate below
/////////////////////////////////////////////////////////////////////////////
?>