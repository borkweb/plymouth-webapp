<?php
class Restore{
	var $db;

	function Restore(&$db){
		$this->db=$db;
	}

	function getDateTimeOptions($WhichDateTime){
		global $db;

		if ($WhichDateTime == 'Month'){
			for($m=1;$m<=12;$m++){
				if ($m < '10'){
					$getMonth['0'.$m] = '0'.$m;
				}else{
					$getMonth[$m] = $m;
				}
			}
			return $getMonth;
		}else if ($WhichDateTime == 'Date'){
			for($d=1;$d<=31;$d++){
				if ($d < '10'){
					$getDate['0'.$d] = '0'.$d;
				}else{
					$getDate[$d] = $d;
				}
			}
			return $getDate;
		}else if ($WhichDateTime == 'Year'){
			for($y=date("Y")-1;$y<=date("Y");$y++){
				$getYear[$y] = $y;
			}
			return $getYear;
		}else if ($WhichDateTime == 'Hour'){
			for($h=1;$h<=24;$h++){
				if ($h < '10'){
					$getHour['0'.$h] = '0'.$h;
				}else{
					$getHour[$h] = $h;
				}
			}
			return $getHour;
		}else if ($WhichDateTime == 'Minute'){
			for($i=0;$i<=60;$i++){
				if ($i < '10'){
					$getMinute['0'.$i] = '0'.$i;
				}else{
					$getMinute[$i] = $i;
				}
			}
			return $getMinute;
		}
		$getDateTimeArray = array(1=>$getMonth, 2=>$getDate, 3=>$getYear, 4=>$getHour, 5=>$getMinute);
		return $getDateTimeArray;
	}

	function restoreRequestFunc($getData=false, $_GET=''){
		global $db;
		$tpl = new XTemplate(TEMPLATE_DIR.'/restore_request.tpl');

		$restore_system_options = PSUHTML::getSelectOptions($this->getRestoreSystemOptions(), $_GET['restore_system']);
		$date_time_month_options = PSUHTML::getSelectOptions($this->getDateTimeOptions('Month'), date("m"));
		$date_time_date_options = PSUHTML::getSelectOptions($this->getDateTimeOptions('Date'), date("d"));
		$date_time_year_options = PSUHTML::getSelectOptions($this->getDateTimeOptions('Year'), date("Y"));
		$date_time_hour_options = PSUHTML::getSelectOptions($this->getDateTimeOptions('Hour'), date("G"));
		$date_time_minute_options = PSUHTML::getSelectOptions($this->getDateTimeOptions('Minute'), date("i"));
		
		$tpl->assign('restore_system_options', $restore_system_options);
		$tpl->assign('date_time_month_options', $date_time_month_options);
		$tpl->assign('date_time_date_options', $date_time_date_options);
		$tpl->assign('date_time_year_options', $date_time_year_options);
		$tpl->assign('date_time_hour_options', $date_time_hour_options);
		$tpl->assign('date_time_minute_options', $date_time_minute_options);
		$tpl->assign('restore_details', $_GET[restore_details]);
		$tpl->assign('restore_filenames', $_GET[restore_filenames]);
		$tpl->assign('restore_path', $_GET[restore_path]);

		$tpl->parse('main');
		return $tpl->text('main');
	}


	function getRestoreSystemOptions(){
		global $db;
		$restore_system_options = Array();
		$RestoreSystemArray = array("oz"=>"M Drive", "space"=>"Shared Drives", "www"=>"Webfiles", "other"=>"Other");

		foreach($RestoreSystemArray as $RestoreSystemKey => $RestoreSystemValue){
			$restore_system_options[$RestoreSystemKey] = $RestoreSystemValue;
		}

		return $restore_system_options;
	}

}
?>
