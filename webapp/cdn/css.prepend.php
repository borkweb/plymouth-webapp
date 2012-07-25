<?php

header('Content-type: text/css');
define('PSU_CDN', true);

if( !defined('CDN_OVERRIDE') ) {
	define('CDN_OVERRIDE', false);
}

if( ! CDN_OVERRIDE ) {
	ob_start( 'cdn_postprocess' );
}

function cdn_relative_path( $css_dir, $resource_path ) {
	// skip absolute paths
	if( "/" === substr($resource_path, 0, 1) || 'http://' === substr($resource_path, 0, 7) || 'https://' === substr($resource_path, 0, 8) ) {
		return $resource_path;
	}

	// trim parents off the resource path and the css_dir until they match up
	while( "../" === substr($resource_path, 0, 3) ) {
		$css_dir = dirname($css_dir);
		$resource_path = substr($resource_path, 3);
	}

	$full_path = $css_dir . '/' . $resource_path;

	// remove /web/pscpages
	return substr($full_path, strlen($_SERVER['DOCUMENT_ROOT']));
}

function cdn_postprocess( $css ) {
	$file = $_SERVER['SCRIPT_FILENAME'];
	$css_dir = dirname($file);

	if( ! preg_match_all( '/url\s*\(\s*[\'"]?(.+?)[\'"]?\s*\)/', $css, $matches, PREG_PATTERN_ORDER ) ) {
		return $css;
	}

	$paths = $matches[1];

	$css_footer = "\n/*\ncss.prepend.php translations:\n\n";

	$search = array();
	$replace = array();

	foreach( $paths as $index => $path ) {
		$orig_path = $path;
		$orig_match = $matches[0][$index];

		if( isset($search[$orig_match]) ) {
			continue;
		}

		$path = cdn_relative_path( $css_dir, $path );
		$path = PSU::cdn( $path, $file );

		if( $path != $orig_path ) {
			$search[$orig_match] = $orig_match; // the complete original match
			$replace[$orig_match] = 'url(' . $path . ')';
		}
	}

	foreach( $search as $key => $path ) {
		$css_footer .= "$path -> " . $replace[$key] . "\n";
	}

	$css_footer .= "*/";

	$css = str_replace( $search, $replace, $css );

	if( false ) {
		$css .= $css_footer;
	}

	return $css;
}

/* vim: noet
*/
