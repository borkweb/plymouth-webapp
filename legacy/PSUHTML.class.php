<?php

/**
 * PSUHTML.class.php
 *
 * === Modification History ===
 * 1.1.0  02-feb-2004  [zbt] earliest history
 * 1.2.0  18-jan-2008  [zbt] merged with layout_functions.php
 * 2.0.0  25-jan-2008  [mtb] class-ified this puppy
 *
 * @package 		Tools
 */

/**
 * PSUHTML.class.php
 *
 * functions for simplifying certain HTML interface tasks
 *
 * @version		2.0.0
 * @module		PSUHTML.class.php
 * @author		Zachary Tirrell <zbtirrell@plymouth.edu>, Matthew Batchelder <mtbatchelder@plymouth.edu>
 * @copyright 2007, Plymouth State University, ITS
 */ 

class PSUHTML
{
	function buildTable($assoc_array)
	{
		$html = "";
		$html = '<table border="0" cellspacing="1" cellpadding="1" bgcolor="#cccccc"><tr bgcolor="#eeeeee">';
		$keys = array_keys($assoc_array[0]);
		foreach($keys AS $key)
		{
			$html .= '<th>'.$key.'</th>';
		}
		$html .= '</tr>';
	
		foreach($assoc_array AS $row)
		{
			$html .= '<tr bgcolor="#ffffff">';
			if(is_array($row))
			{
				foreach($row AS $value)
				{
					$html .= '<td>'.$value.'&nbsp;</td>';
				}
			}
			$html .= "</tr>\n";
		}
		
		$html .= '</table>';
		return $html;
	}//end buildTable

	function csvExplode($str, $delim = ',', $qual = "\"")
	{
		$len = strlen($str);
		$inside = false;
		$word = '';
		for ($i = 0; $i < $len; ++$i) {
			if ($str[$i]==$delim && !$inside) {
				$out[] = $word;
				$word = '';
			} else if ($inside && $str[$i]==$qual && ($i<$len && $str[$i+1]==$qual)) {
				$word .= $qual;
				++$i;
			} else if ($str[$i] == $qual) {
				$inside = !$inside;
			} else {
				$word .= $str[$i];
			} 
		} 
		$out[] = $word;
		return $out;
	}//end csvExplode
	
	function csvToArray($result)
	{
		$result_array = array();
		$keys = array();
	
		$result = explode("\n",$result);
		$header_row = true;
		$j=0;
		foreach($result AS $row)
		{
			if(trim($row))
			{
				$temp = csvExplode($row,',');
				if($header_row)
				{
					$keys = $temp;
					$header_row = false;
				}
				else
				{
					foreach($temp AS $i=>$value)
					{
						$result_array[$j][$keys[$i]] = $value;
					}
					$j++;
				}
			}
		}
	
		return $result_array;
	}//end csvToArray

	function getDateSelect($default="",$format="%s",$type=1,$leading_blank=false,$min_year=false,$max_year=false,$extra="")
	{
		// types are:
		// 1 - m/d/Y
		// 2 - m/Y
		// 3 - m/d/Y H:m
		// 4 - H:m
	
		$html = "";
		$min_year = ($min_year) ? $min_year : (date("Y") - 5);
		$max_year = ($max_year) ? $max_year : (date("Y") + 10);
	
	
		if(is_numeric($default))
		{
			$d_month = date("m", $default);
			$d_day = date("d", $default);
			$d_year = date("Y", $default);
			$d_hour = date("H", $default);
			$d_minute = date("i", $default);
		}
	
		if($type <= 3)
		{
			$months = PSUHTML::_copyValues(range(1,12));
			$days = PSUHTML::_copyValues(range(1,31));
			$years = PSUHTML::_copyValues(range($min_year,$max_year));
	
			$html .= "<select $extra name=\"".str_replace("%s","month",$format)."\">\n";
			$html .= PSUHTML::getSelectOptions($months,$d_month,$leading_blank);
			$html .= "</select>";
			if($type != 2)
			{
				$html .= "/";
				$html .= "<select $extra name=\"".str_replace("%s","day",$format)."\">\n";
				$html .= PSUHTML::getSelectOptions($days,$d_day,$leading_blank);
				$html .= "</select>";
			}
			$html .= "/";
			$html .= "<select $extra name=\"".str_replace("%s","year",$format)."\">\n";
			$html .= PSUHTML::getSelectOptions($years,$d_year,$leading_blank);
			$html .= "</select>";
	
			if($type == 3)
			{
				$html .= "&nbsp;";
			}
		}
	
		if($type >= 3)
		{
			$hours = PSUHTML::_copyValues(range(0,23));
			$minutes = PSUHTML::_copyValues(range(0,60));
	
			$html .= "<select $extra name=\"".str_replace("%s","hour",$format)."\">\n";
			$html .= PSUHTML::getSelectOptions($hours,$d_hour,$leading_blank);
			$html .= "</select>";
			$html .= ":";
			$html .= "<select $extra name=\"".str_replace("%s","minute",$format)."\">\n";
			$html .= PSUHTML::getSelectOptions($minutes,$d_minute,$leading_blank);
			$html .= "</select>";
		}
	
		return $html;
	}//end getDateSelect

	function getRadioOptions($name, $options, $default, $delimiter='<br />', $extra_html='')
	{
		$html = "";
	
		if(is_array($options))
		{
			foreach($options as $value => $text)
			{		
				if(strcmp($value,$default) == 0)
				{
					$selected = "checked";
				}
				else
				{
					$selected = "";
				}
				$html .= "<input type=\"radio\" name=\"$name\" value=\"$value\" $selected $extra_html />$text$delimiter\n";
			}
		}
		return $html;
	}//end getRadioOptions

	function getSelectOptions($options,$default="",$leading_blank=false,$lb_text="")//($keyed_array,$default)
	{
		$html = "";
	
		if($leading_blank !== false)
		{
			$html .= "<option value=\"$leading_blank\">$lb_text</option>\n";
		}
	
		if(is_array($options))
		{
			foreach($options as $value => $text)
			{
				if(is_array($default))
				{
					$selected = "";
					foreach($default as $dvalue)
					{
						if(strcmp($value,$dvalue) == 0)
						{
							$selected = " selected";
						}
					}
				}
				else
				{
					if(strcmp($value,$default) == 0)
					{
						$selected = " selected";
					}
					else
					{
						$selected = "";
					}
				}
				$html .= "<option value=\"$value\"$selected>$text</option>\n";
			}
		}
		return $html;
	}//end getSelectOptions

	/**
	 * Display the 'go' bar in place.
	 */
	public static function gobar($username=false)
	{
		$username = $username === false ? $_SESSION['username'] : $username;

		require_once('PSUSmarty.class.php');
		$tpl = new PSUSmarty('gobar');
		$tpl->assign('psu_gobar_42199_includes', true);
		$tpl->assign('psu_gobar_42199_user', $username);
		$tpl->display('/web/pscpages/webapp/gobar/templates/gobar.tpl');
	}

	/**
	 * Force the client to use HTTPS.
	 */
	public static function forceSSL()
	{
		if($_SERVER['HTTPS'] !== "on")
		{
			header("Location: https://".$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI']);
			exit;
		}
	}
	
	function formatDateSelect($month, $day, $year, $hour=0, $minute=0, $ampm=0) 
	{
		$select_date .= $year . "-";
		if (strlen($month) == 1) 
		{
			$select_date .= "0";
		}
		$select_date .= $month . "-";
		if (strlen($day) == 1) 
		{
			$select_date .= "0";
		}
		$select_date .= $day;
	
		if ( $hour ) 
		{
			if ( !$minute )
			{
				$minute=0;
			}
			if ( $ampm == "PM" )
			{
				$hour += 12;
			}
			$select_date .= " $hour:$minute:00";
		}
		return($select_date);
	}//end formatDateSelect
	
	function formatErrors($errors)
	{
		$error_string = "";
		if(is_array($errors) && count($errors)>0)
		{
			$error_string.="<ul><font color=\"red\">";
			foreach($errors AS $fieldname=>$error)
			{
				$error_string.="<li>$error</li>";
			}
			$error_string.="</font></ul>";
		}
	
		return $error_string;
	}//end formatErrors
	
	function highlightErrorFields($errors, $formname)
	{
		$error_string = "";
		if(is_array($errors) && count($errors)>0)
		{
			$error_string.="<script language=\"JavaScript\">\n";
			foreach($errors AS $fieldname=>$error)
			{
				if($fieldname)
				{
					$error_string.="document.$formname.elements('".$formname."[$fieldname]').style.background = \"#CCFFCC\";\n";
				}
			}
			$error_string.="</script>\n";
		}
		return $error_string;
	}//end highlightErrorFields

	function paging($template_var_name,$record_count,$current_page,$records_per_page=25,$template_prefix='main')
	{
		$paging=array();
		$paging['num_pages']=ceil($record_count/$records_per_page);
		$paging['first_row']=(($current_page-1)*$records_per_page)+1;
		if(($paging['first_row']+$records_per_page-1)>=$record_count)
			$paging['last_row']=$record_count;
		else
			$paging['last_row']=$paging['first_row']+$records_per_page-1;
	
		$GLOBALS[$template_var_name]->assign('max_page',$paging['num_pages']);
	
		if($current_page>1)
		{
			$GLOBALS[$template_var_name]->assign('back_page',$current_page-1);
			$GLOBALS[$template_var_name]->parse($template_prefix.'.paging.pages_beginning');
			$GLOBALS[$template_var_name]->parse($template_prefix.'.paging.back_a_page');
		}//end if
	
		if($current_page!=$paging['num_pages'])
		{
			$GLOBALS[$template_var_name]->assign('up_page',$current_page+1);
			$GLOBALS[$template_var_name]->parse($template_prefix.'.paging.pages_end');
			$GLOBALS[$template_var_name]->parse($template_prefix.'.paging.up_a_page');
		}//end if
	
		if($paging['num_pages']>1)
		{
			if(strlen($current_page)>1)
			{
				$start_page_display=(substr($current_page,0,strlen($current_page)-1)*10)-2;
			}//end if
			else
			{
				$start_page_display=1;
			}//end else
	
			for($i=$start_page_display;$i<=$paging['num_pages'] && $i<=$start_page_display+12;$i++)
			{
				$GLOBALS[$template_var_name]->assign('page',$i);
				if($i==$current_page)
					$GLOBALS[$template_var_name]->parse($template_prefix.'.paging.pages.page.selected');
				$GLOBALS[$template_var_name]->parse($template_prefix.'.paging.pages.page');
			}//end for
			$GLOBALS[$template_var_name]->parse($template_prefix.'.paging.pages');
		}//end if
		if($start_page_display+12<$paging['num_pages'])
		{
			$GLOBALS[$template_var_name]->parse($template_prefix.'.paging.etc_page');
		}//end if
		$GLOBALS[$template_var_name]->assign('first_row',number_format($paging['first_row']));
		$GLOBALS[$template_var_name]->assign('last_row',number_format($paging['last_row']));
		$GLOBALS[$template_var_name]->assign('record_count',number_format($record_count));
		$GLOBALS[$template_var_name]->parse($template_prefix.'.paging');
	
		return $paging;
	}//end paging
	
	/**
	 * redirect the browser via HTTP headers or JavaScript, whichever is more
	 * appropriate.
	 *
	 * @params			string $url the destination url
	 * @params			int $code the http response code. should be either 301 (permanent) or 302 (temporary). will be ignored if JavaScript is used for the redirect.
	 * @deprecated use PSU::redirect() instead
	 */
	function redirect($url,$code=302)
	{
		if(headers_sent())
		{
			?>
			<SCRIPT LANGUAGE="JavaScript">
			<!--
			window.location = '<?=$url?>';
			//-->
			</SCRIPT>
			<?
		}
		else
		{
			header('Location: ' . $url, true, $code);
		}
		exit;
	}//end redirect
	
	function __construct()
	{
	
	}//end constructor

	// internal function for building arrays for selects
	function _copyValues($array)
	{
		$new_array = array();
		foreach($array as $value)
		{
			$value = str_pad($value,2,"0",STR_PAD_LEFT);
			$new_array[$value] = $value;
		}
		return $new_array;
	}//end _copyValues
	
}//end class PSUHTML
?>
