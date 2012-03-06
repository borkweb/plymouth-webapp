<?php

// TODO: bar should have username, login/out, 'go' searchbox, news ticker?

class userbar
{

/**
  *
  *
  * TODO ?
  *
  *
  *
  */
	function __construct()
	{
	
	}//end constructor
/**
  *out
  *
  *echos the function text 
  *
  */	
	function out()
	{
		echo $this->text();
	}//end out
/**
  *text
  *
  *constructs the user bar
  *
  *@return string
  */	
	function text()
	{
		if($_SESSION['username'] && !$_SERVER['REQUEST_METHOD']!='POST' && !$_GET['ajax'])
		{
			$self_url = sprintf('http%s://%s%s',
				(isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == TRUE ? 's': ''),
				$_SERVER['HTTP_HOST'],
				$_SERVER['REQUEST_URI']
			);
			
			$out ='
			<html>
			<head>
			<style>
				body{margin:0;}
				#psu-userbar {
					background: #f3f1e6;
					border-bottom: 1px solid #bfbea3;
					font-family: arial, helvetica sans-serif !important;
					font-size: 0.9em !important;
					padding: 3px !important;			
				}
				
				#psu-userbar .user{
					text-align:right;
				}
			</style>
			</head>
			<body>
			';
			$out .= '<div id="psu-userbar">
				<div class="user">
					logged in as: <strong>'.$_SESSION['username'].'</strong> | <a href="http://www.plymouth.edu/webapp/userbar.html?logout='.urlencode($self_url).'">logout</a>
				</div>
			</div></body></html>';
		}//end if
		else
		{
			$out ='';
		}//end else	
		return $out;
	}//end text
}//end class userbar

?>
