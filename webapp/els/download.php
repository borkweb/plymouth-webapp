<?php

$downloader = new PSU\Downloader;

try {
	$downloader->download( $GLOBALS['RELATIVE_URL'], $_GET['file'] );
} catch( PSU\Downloader\FileNotFoundException $e ) {
	$downloader->do404();
}
