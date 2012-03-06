<?php

require_once 'PSUTools.class.php';

/**
 * @deprecated Please use \PSU\Downloader
 */
class PSUDownloader {
	public static function download( $subpath, $filename, $name_override = null ) {
		trigger_error( 'PSUDownloader is deprecated, please use \\PSU\\Downloader', E_USER_DEPRECATED );

		$full_path = PSU::UPLOAD_DIR . '/' . $subpath . '/' . $filename;
		$full_path = preg_replace( '!/+!', '/', $full_path );
		$full_path = realpath( $full_path );

		if( strpos( $full_path, PSU::UPLOAD_DIR ) !== 0 ) {
			throw new Exception('Path to download file was outside the global upload directory.');
		}

		if( ! isset( $name_override ) ) {
			$name_override = $filename;
		}

		header('Content-type: application/octet-stream');
		header('Content-disposition: attachment; filename="' . addcslashes($name_override, '"') . '"');
		header('Length: ' . filesize($full_path));

		// help internet explorer over https
		header('Expires: 0');
		header('Cache-control: private');
		header('Pragma: cache');

		readfile( $full_path );
	}//end download
}//end PSUDownloader
