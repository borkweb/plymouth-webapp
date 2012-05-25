<?php

$template_dir = $_GET['dir'] == 'js' ? 'js' : 'css';
$file = $_GET['file'] . '.tpl';

$content_type = $template_dir == 'js' ? 'text/javascript' : 'text/css';
header("Content-Type: $content_type");

if(preg_match('/^[a-zA-Z0-9_\.]+$/', $file) == 0)
{
	$_SESSION['errors'][] = "Error in media request $template_dir/$file.";
	exit;
}

$tpl = new PSUSmarty();
$tpl->template_dir = $GLOBALS['BASE_DIR'] . '/' . $template_dir;

$tpl->display($file);
