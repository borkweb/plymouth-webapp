<?php
function smarty_modifier_link_urls($text)
{

	$text = preg_replace_callback('/(^|([^\'"]\s*))([hf][tps]{2,4}:\/\/[^\s<>"\'()]{4,})/mi','_smarty_shorten_url', $text);
	$text = preg_replace('/<a href="([^"]+)[\.:,\]]">/','<a href="$1">', $text);
	$text = preg_replace('/([\.:,\]])<\/a>/', '</a>$1',$text);
	return $text;
}

function _smarty_shorten_url($matches)
{
		return $matches[2].'<a href="'.$matches[3].'" target="_blank">'.((strlen($matches[3])>80)?substr($matches[3],0,77).'...':$matches[3]).'</a>';
}//end shorten_url
