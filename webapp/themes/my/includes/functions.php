<?php
class myPlymouthTheme
{
	var $base_dir;
	var $theme;
	
	function out()
	{
		header('Content-Type: text/css');
		echo $this->text();
	}//end out
	
	function text()
	{
		/****************************************
		 * If HTTPS, alter css urls to use https
		 ****************************************/
		if($_SERVER['HTTPS'] == "on")
		{
			$this->theme = str_replace('http:','https:',$this->theme);
		}
		
		return $this->theme;
	}//end text
	
	function add($theme, $css = 'style.css', $clear_previous = false)
	{
		$theme_css = $this->base_dir.'/themes/'.$theme.'/'.$css;
		
		if($clear_previous)
		{
			$this->theme = file_get_contents($theme_css);
		}//end if
		else
		{
			$this->theme .= file_get_contents($theme_css);
		}//end else
	}//end use
	
	function __construct($base_dir, $theme ='', $css = 'style.css')
	{
		$this->base_dir = $base_dir;
		
		if($theme)
		{
			$this->useTheme($theme, $css);
		}//end if
	}//end constructor
}//end class myTheme

?>