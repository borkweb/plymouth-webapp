<?php
/**
 * weather.class.php
 *
 * === Modification History ===
 * 0.1.0  21-nov-07 [djb] original
 *
 * @package 		Tools
 */

/**
 * weather.class.php
 *
 * Weather function library
 *
 * @version		0.1.0
 * @module		wearther.class.php
 * @author		Dan Bramer <djbramer@plymouth.edu>
 * @copyright 2007, Plymouth State University, ITS
 */ 

class Weather 
{

	function getCampusData()
	{
		$infile = file("/web/temp/mapwall.txt");
		$campus_data['location'] = "Plymouth State University"; 
		$extra_data = $this->getCampusAirportConditions();
		$campus_data['wximg'] = $extra_data['wximg'];
		$campus_data['sky'] = $extra_data['sky'];
		$campus_data['precip'] = $extra_data['precip'];
		$i=0;

		foreach($infile as $tmp_line) {
			$line[$i] = trim($tmp_line);
			if (preg_match('/^Current Temperature/',$line[$i]))
			{
				$campus_data['temp_f'] = preg_replace('/^Current Temperature (.*)°F/','$1',$line[$i]);
			}
			else if ( preg_match('/^Current Humidity/',$line[$i]))
			{
				$campus_data['relative_humidity'] = preg_replace('/^Current Humidity (.*)%/','$1',$line[$i]);
			}
			else if ( preg_match('/^Current Dewpoint/',$line[$i]))
			{
				$campus_data['dewpoint_f'] = preg_replace('/^Current Dewpoint (.*)°F/','$1',$line[$i]);
			}
			else if ( preg_match('/^Wind Direction in Degrees/',$line[$i]))
			{
				$campus_data['wind_dir_deg'] = preg_replace('/^Wind Direction in Degrees (.*)/','$1',$line[$i]);
			}
			else if ( preg_match('/^Current Speed Wind/',$line[$i]))
			{
				$campus_data['wind_dir_abbrev'] = preg_replace('/^Current Speed Wind (.*) at(.*)/','$1',$line[$i]);
				$campus_data['wind_mph'] = preg_replace('/^Current Speed Wind (.*) at (.*)mph/','$2',$line[$i]);
			}
			else if ( preg_match('/^Sunrise/',$line[$i]))
			{
				$campus_data['sunrise'] = preg_replace('/^Sunrise at (.*)/','$1',$line[$i]);
			}
			else if ( preg_match('/^Sunset/',$line[$i]))
			{
				$campus_data['sunset'] = preg_replace('/^Sunset at (.*)/','$1',$line[$i]);
			}
			else if ( preg_match('/^Current Wind Chill/',$line[$i]))
			{
				$campus_data['new_wind_chill'] = preg_replace('/^Current Wind Chill (.*)°F/','$1',$line[$i]);
			}
			else if ( preg_match('/^Current Heat Index/',$line[$i]))
			{
				$campus_data['new_heat_index'] = preg_replace('/^Current Heat Index (.*)°F/','$1',$line[$i]);
			}
			else if ( preg_match('/^Current Conditions at/',$line[$i]))
			{
				$campus_data['observation_time'] = preg_replace('/^Current Conditions at (.*)/','$1',$line[$i]);
			}
			$i++;
		}
		return $campus_data;
	}

	function getNHStateData($stn='PLYMOUTH ARPRT')
	{
		$apline=-1;
		$infile = file("/web/temp/obs.txt");
		if($infile) {
			foreach($infile as $ob) {
				if (!stristr($ob,'NEW HAMPSHIRE OBSERVATIONS')===FALSE) {
					$timeline = $ob;
					preg_match('/OBSERVATIONS for (.*)/', $ob, $matches);
					$airport_data['observation_time'] = $matches[1];
				}
				if (!stristr($ob,$stn)===FALSE) {
					$apline = $ob;
					break;
				}
			}
		}
		if (($stn='PLYMOUTH ARPRT') && ($apline==-1))
		{
			$stn='PLYMOUTH STATE';
			foreach($infile as $ob) {
				if (!stristr($ob,'KPLY')===FALSE) { // Can't use Plymouth State as it is in the header
					$apline = $ob;
					break;
				}
			}
		}
		$airport_data['location'] = $stn =='PLYMOUTH ARPRT' ?  "Plymouth Airport" : ucwords($stn); 	
		$airport_data['location'] = $stn =='PLYMOUTH STATE' ?  "Plymouth State" : ucwords($stn); 	
		$airport_data['temp_f'] = trim(substr($apline,17,3));
		$airport_data['relative_humidity'] = trim(substr($apline,25,3));
		$airport_data['dewpoint_f'] = trim(substr($apline,21,3));
		$airport_data['wind_degrees'] = trim(substr($apline,29,3));
		$airport_data['wind_dir_abbrev'] = $this->get_wind_dir_abbrev($airport_data['wind_degrees']);
		$airport_data['latitude'] = 43.77;
		$airport_data['longitude'] = -71.75;
		$airport_data['tzoffset'] = date_offset_get(new DateTime())/3600*100;
		$airport_data['sunrise'] = $this->get_sundata("sunrise",$airport_data['latitude'],$airport_data['longitude'],$airport_data['tzoffset']);
		$airport_data['sunset'] = $this->get_sundata("sunset",$airport_data['latitude'],$airport_data['longitude'],$airport_data['tzoffset']);
	    $airport_data['wind_mph'] = trim(substr($apline,37,2));
		$airport_data['new_wind_chill'] = $this->get_new_wind_chill($airport_data['temp_f'],$airport_data['wind_mph']);
		$airport_data['new_heat_index'] = $this->get_new_heat_index($airport_data['temp_f'],$airport_data['relative_humidity']);
		$airport_data['sky'] = trim(substr($apline,66,3));
	    $airport_data['precip'] = trim(substr($apline,69,4));

		if(strlen($airport_data['precip']) > 0) {
			if (preg_match('/R/',$airport_data['precip'])) {$airport_data['weather'] = "rain";}
			else if (preg_match('/S/',$airport_data['precip'])) {$airport_data['weather'] = "snow";}
			else if (preg_match('/H/',$airport_data['precip'])) {$airport_data['weather'] = "haze";}
			else if (preg_match('/F/',$airport_data['precip'])) {$airport_data['weather'] = "fog";}
			else if (preg_match('/L/',$airport_data['precip'])) {$airport_data['weather'] = "drizzle";}
			if (preg_match('/-/',$airport_data['precip'])) {
				$airport_data['weather'] = "Lt. ".$airport_data['weather'];
			} else if (preg_match('/\+/',$data['precip'])) {
				$airport_data['weather'] = "Hvy. ".$airport_data['weather'];
			}
			$airport_data['wximg'] = $this->get_wximage('ob',$airport_data['weather'],$airport_data['latitude'],$airport_data['longitude'],true);
		} else {
			if (preg_match('/CLR/',$data['sky'])) {$data['weather'] = "clear";}
			else if (preg_match('/FEW/',$airport_data['sky'])) {$airport_data['weather'] = "fair";}
			else if (preg_match('/SCT/',$airport_data['sky'])) {$airport_data['weather'] = "partly cloudy";}
			else if (preg_match('/BKN/',$airport_data['sky'])) {$airport_data['weather'] = "mostly cloudy";}
			else if (preg_match('/OVC/',$airport_data['sky'])) {$airport_data['weather'] = "overcast";}
			else if (preg_match('/X/',$airport_data['sky'])) {$airport_data['weather'] = "obscur";}
			$airport_data['wximg'] = $this->get_wximage('ob',$airport_data['weather'],$airport_data['latitude'],$airport_data['longitude'],false);
		}
		return $airport_data;
	}

	function getCampusAirportConditions()
	{
		$apdata=$this->getNHStateData($stn='PLYMOUTH ARPRT');
		return $apdata;
	}

	function getStationData($stn='KPLY') 
	{
		if ($stn=='KPLY') 
		{
			$station_data = $this->getCampusData();
			return $station_data;
		}
		if ($stn=='K1P1') 
		{
			$station_data = $this->getNHStateData('PLYMOUTH ARPRT');
			return $station_data;
		}
		$infile = file("http://www.nws.noaa.gov/data/current_obs/".$stn.".xml");
		if($infile)	{
			foreach($infile as $tmp_line) {
				$line[$i] = trim($tmp_line);
				if (!preg_match('/^<\//',$line[$i])) {
					preg_match_all('/<(.*?)>/s', $line[$i], $tags);
					$tag = $tags[1][0];
					preg_match_all('/<'.$tag.'>(.*?)<\/'.$tag.'>/s', $line[$i], $value);
					$station_data[$tag] = $value[1][0];
				}
				$i++;
			}
		}
		else 
		{
			$station_data = getCampusData();
			return $station_data;
		}
		return $station_data;
	}


	function getForecastData($indata)
	{
		if (!is_array($indata))
		{
			parse_str($indata,$data);
		} 
		else
		{
			$data = $indata;
		}
		if ($data['type']=='campus')
		{
			$weather = @file_get_contents('/web/temp/weather-forecast.txt');
			if ($weather)
			{
				return $weather;
			}
		}
		else if ($data['type']=='zip')
		{ // no way to do this currently
		}
		else if ($data['type']=='station')
		{ // no way to do this currently
		}
		if(!$weather)
		{
			$sFile = file_get_contents("http://forecast.weather.gov/MapClick.php?lat=43.75888268069382&lon=-71.68969631195068&site=gyx&smap=1&unit=0&lg=en&FcstType=text");
			$tmp = explode("partial-width-borderbottom point-forecast-icons",$sFile);
			$tmp2 = explode("one-ninth-first",$tmp[1]);
			
			foreach ($tmp2 as $timeint) {
				$timetmp = array();
				preg_match_all('/<p class="txt-ctr-caps">(.*?)<\/p>/',$timeint,$timetmp);
				$timetext[] = str_replace("<br>"," ",$timetmp[1][0]);
				preg_match_all('/<p class="point-forecast-icons-(high|low)">(High|Low):(.*?) &deg/',$timeint,$temptmp);
				$hiloind[] = $temptmp[1][0];
				$temptext[] = $temptmp[3][0];
				preg_match_all('/<p>(.*?)<\/p>/',$timeint,$alttmp);
				$alttext[] = str_replace("<br>"," ",$alttmp[0][1]);
			}
			array_shift($timetext);
			array_shift($temptext);
			array_shift($hiloind);
			array_shift($alttext);

			$highfirst = $hiloind[0] == "high" ? 1:0;

			$ind=0;
			for ($i=0;$i<=count($temptext);$i+=2) {
				if ($highfirst == 0) {
					$fcstout[$ind]['lotemp'] = $temptext[$i];
					$fcstout[$ind]['hitemp'] = $temptext[($i+1)];
					$fcstout[$ind]['date'] = $timetext[($i+1)];
					$fcstout[$ind]['wxtext'] = $alttext[($i+1)];
				} else {
					$fcstout[$ind]['hitemp'] = $temptext[$i];
					$fcstout[$ind]['lotemp'] = $temptext[($i+1)];
					$fcstout[$ind]['date'] = $timetext[$i];
					$fcstout[$ind]['wxtext'] = $alttext[$i];
				}
				print_r($fcstout[$ind]['date']);
				if ($fcstout[$ind]['date'] == "THIS AFTERNOON") $fcstout[$ind]['date'] = "Today";
				if ($fcstout[$ind]['date'] == "LATE AFTERNOON") $fcstout[$ind]['date'] = "Today";
				$fcstout[$ind]['date'] = substr($fcstout[$ind]['date'],0,3);
				if ($fcstout[$ind]['date'] == "Tod") $fcstout[$i]['date'] = "Today";
				if ($fcstout[$ind]['date'] == "Tom") $fcstout[$i]['date'] = "Tmrrw";
				$fcstout[$ind]['wxtext'] = preg_replace('/ Chance for Measurable Precipitation [0-9]{1,3}%/','',$fcstout[$ind]['wxtext']);
				$fcstout[$ind]['wxtext'] = preg_replace('/Thunderstorm|Thunderstorms/','T-Storm',$fcstout[$ind]['wxtext']);
				$fcstout[$ind]['imgname'] = $this->get_wximage('fcst',$fcstout[$ind]['wxtext'],0,0,false);
				$ind++;
			}
			if ($highfirst == 1) $fcstout[0]['lotemp'] = '&nbsp;';
			while ((count($fcstout) > $data['days']) && (count($fcstout) > 0))
			{
				$dummy = array_pop($fcstout);
			}
		}
		return $fcstout;
	}


	function get_wximage($type='ob',$chk,$lat,$lon)
	{
		$chk = strtolower($chk);
		if (preg_match('/partly cloudy/',$chk)){$fname .= "day/pc";}
		else if (preg_match('/mostly cloudy/',$chk)){$fname .= "day/mc";}
		else if (preg_match('/mostly sunny/',$chk)){$fname .= "day/fair";}
		else if (preg_match('/overcast/',$chk)){$fname .= "day/overcast";}
		else if (preg_match('/few clouds/',$chk)){$fname .= "day/fair";}
		else if (preg_match('/fair/',$chk)){$fname .= "day/fair";}
		else if (preg_match('/clear/',$chk)){$fname .= "day/clear";}
		else if (preg_match('/sunny/',$chk)){$fname .= "day/clear";}
		else if (preg_match('/fog/',$chk)){$fname .= "precip/fog";}
		else if (preg_match('/haze/',$chk)){$fname .= "precip/haze";}
		else if ((preg_match('/rain/',$chk))&&(preg_match('/snow/',$chk))){$fname .= "precip/mix";}
		else if (preg_match('/rain/',$chk)){$fname .= "precip/rain";}
		else if (preg_match('/snow/',$chk)){$fname .= "precip/snow";}
		else if (preg_match('/thunderstorm/',$chk)){$fname .= "precip/thunder";}
		else if (preg_match('/t-storm/',$chk)){$fname .= "precip/thunder";}
		else if (preg_match('/thunder/',$chk)){$fname .= "precip/thunder";}		
		else if (preg_match('/obscured/',$chk)){$fname .= "day/obscur";}
		else if (preg_match('/blowing snow/',$chk)){$fname .= "precip/bsnow";}
		else if (preg_match('/drizzle/',$chk)){$fname .= "precip/drizzle";}
		else if (preg_match('/mix/',$chk)){$fname .= "precip/mix";}
		else if (preg_match('/crystals/',$chk)){$fname .= "precip/icecrystals";}
		else if (preg_match('/dust/',$chk)){$fname .= "precip/dust";}
		else if (preg_match('/smoke/',$chk)){$fname .= "precip/smoke";}
		else {$fname = '../spacer';}
		if ($type == 'ob')
		{
			$sun1 = date_sunrise(gmmktime(), SUNFUNCS_RET_TIMESTAMP, $lat, $lon);
			$sun2 = date_sunset(gmmktime(), SUNFUNCS_RET_TIMESTAMP, $lat, $lon);
			if ((gmmktime() < $sun1) || (gmmktime() > $sun2))
			{
				$fname = preg_replace('/day/','night',$fname);
			}
		}
		return $fname.".gif";
	}

/* This is like not being used anymore ... please delete upon next revision */
	function get_tzoffset($typ='num',$str) {
		if ($typ='text') 
		{
			if ($str = 'EST')
			{
				return -500;
			} 
			else 
			{
				return -400;
			}
		}
		$arr = explode(" ",$str);
		return $arr[(sizeof($arr)-2)];
	}


	function convertTemp($from, $to, $val)
	{
		if (($from == 'C') && ($to == 'F'))
		{
			return ($val / 5 * 9) + 32.0;
		} 
		else if (($from == 'F') && ($to == 'C')) 
		{	
			return ($val - 32.0) / 9 * 5;
		} 
		else
		{
			return $val;
		}	
	}


	function get_new_wind_chill($tf,$ws)
	{
		$wc = 35.74 + (0.6215*$tf) - (35.75*pow($ws,0.16)) + (0.4275*$tf*pow($ws,0.16));
		if ($ws == 0) {$wc = $tf;}
		return $wc;
	}


	function get_new_heat_index($tf,$rh)
	{
		$hi = -42.379 + (2.04901523*$tf) + (10.14333127*$rh) - (0.22475541*$tf*$rh) - (.00683783*$tf*$tf) - (.05481717*$rh*$rh) + (.00122874*$tf*$tf*$rh) + (.00085282*$tf*$rh*$rh) - (.00000199*$tf*$tf*$rh*$rh);
		return $hi;
	}


	function get_sundata($which,$lat,$lon,$off)
	{
		$off += 500;
		if ($which == "sunrise") 
		{
			$sun = date_sunrise(gmmktime(), SUNFUNCS_RET_DOUBLE, $lat, $lon);
		} 
		else 
		{
			$sun = date_sunset(gmmktime(), SUNFUNCS_RET_DOUBLE, $lat, $lon);
		}
		$sun += ($off/100);
		if ($sun < 0) {$sun += 24;}
		if ($sun >= 24) {$sun -= 24;}
		$ampm = "AM";
		if ($sun >= 12.0)
		{
			$ampm = "PM";
		}
		if ($sun >= 13.0)
		{
			$sun -= 12.0;
		}
		if ($sun <= 1.0) 
		{
			$sun += 12.0;
		}
		$minutes = $sun - intval($sun);
		$minutes *=60;
		return intval($sun).":".sprintf("%02d",intval($minutes))." ".$ampm;
		//return $sun;
	}

	function get_wind_dir_abbrev($degrees)
	{
		  if ($degrees >= 349 || $degrees <= 11) return("N");
		  if ($degrees >= 12 &&  $degrees <= 33) return("NNE");
		  if ($degrees >= 34 &&  $degrees <= 56) return("NE");
		  if ($degrees >= 57 &&  $degrees <= 78) return("ENE");
		  if ($degrees >= 79 &&  $degrees <= 101) return("E");
		  if ($degrees >= 102 &&  $degrees <= 123) return("ESE");
		  if ($degrees >= 124 &&  $degrees <= 146) return("SE");
		  if ($degrees >= 147 &&  $degrees <= 168) return("SSE");
		  if ($degrees >= 169 &&  $degrees <= 191) return("S");
		  if ($degrees >= 192 &&  $degrees <= 213) return("SSW");
		  if ($degrees >= 214 &&  $degrees <= 236) return("SW");
		  if ($degrees >= 237 &&  $degrees <= 258) return("WSW");
		  if ($degrees >= 259 &&  $degrees <= 281) return("W");
		  if ($degrees >= 282 &&  $degrees <= 303) return("WNW");
		  if ($degrees >= 304 &&  $degrees <= 326) return("NW");
		  if ($degrees >= 327 &&  $degrees <= 348) return("NNW");
		  return("ERR");
	}

	function get_stn_from_zip($zip)
	{
		$zipfile = file("zip2stn2latlon");
		if($zipfile) 
		{
			foreach($zipfile as $ob) 
			{
				if (eregi($zip,$ob)) 
				{
					$tmp = explode(',',$ob);
					return chop($tmp[1]);
				}
			}
		}
	}

	function convertStationInfo($val, $intype, $outtype) 
	{
		// valid values: zip codes, station ids (4 letter)
		// valid intypes: "ZIP", "STN"
		// valid outtypes: "ZIP", "STN", "LATLON"
		$zipfile = file("zip2stn2latlon");
		if($zipfile) 
		{
			foreach($zipfile as $tmp_line) 
			{
				$line = chop($tmp_line);
				$tmp = explode(',',$line);
				$key = $tmp[0];
				if ($intype == "STN") 
				{
					$key = $tmp[1];
				}
				if (eregi($val,$key)) 
				{
					if ($outtype == "ZIP") 
					{
						return $tmp[0];
					} 
					else if ($outtype == "STN") 
					{
						return $tmp[1];
					} 
					else if ($outtype == "LATLON") 
					{
						$linearr[0] = $tmp[2];
						$linearr[1] = $tmp[3];
						return $linearr;
					} 
					else 
					{
						return $tmp[0];
					}
				}
			}
		}
	}
}
?>
