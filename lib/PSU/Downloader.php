<?php

namespace PSU;

use \PSU\Downloader\File;

/**
 * Simple interface for allowing file downloads from the PSU::UPLOAD_DIR.
 *
 * @see PSU::UPLOAD_DIR
 */
class Downloader {
	/** The sitewide parent for all uploaded files. */
	public $upload_dir;

	/**
	 * Class constructor.
	 */
	public function __construct() {
		$this->upload_dir = \PSU::UPLOAD_DIR;
	}

	/**
	 * Convenience function to do quick 404.
	 */
	public static function do404( $message = 'Error &mdash; File Not Found' ) {
		header( 'HTTP/1.1 404 Not Found' );

		if( $message ) {
			echo $message;
		}
	}//end do404

	/**
	 * Pass a file back to the client. Your application should not send any output
	 * before or after this function.
	 *
	 * @param    string    $subpath          The path to the file's parent directory, e.g. webapp/calllog/attachments/.
	 * @param    string    $filepath         The file to push to the client.
	 * @param    string    $name_override    An alternate name to pass to the client in the Content-disposition header.
	 */
	public function download( $subpath, $filepath, $name_override = null ) {
		if( null === $name_override ) {
			$name_override = basename( $filepath );
		}

		// Run find() here, before headers are sent.
		$file = new File( $this->upload_dir, $subpath, $filepath );
		$file->find();

		header('Content-disposition: attachment; filename="' . addcslashes($name_override, '"') . '"');

		// help internet explorer over https
		header('Expires: 0');
		header('Cache-control: private');
		header('Pragma: cache');

		$this->_readfile( $file, 'application/octet-stream' );
	}//end download

	/**
	 * Output the contents of a file, a la the built-in readfile().
	 * @param string $subpath Application subpath jail for this file.
	 * @param string $filepath Remainder of path to the file, including any directories.
	 * @param string $content_type Content-Type header override.
	 */
	public function readfile( $subpath, $filepath, $content_type = null ) {
		$file = new File( $this->upload_dir, $subpath, $filepath );
		$file->find();

		$this->_readfile( $file, $content_type );
	}//end read

	/**
	 * Output the contents of a file, along with appropriate Content-Type
	 * and Length headers.
	 *
	 * @param    string    $file    The \PSU\Downloader\File object.
	 * @param    string    $content_type  Content-Type header to pass. If blank, the file will be asked to return its own mimetype.
	 */
	private function _readfile( $file, $content_type = null ) {
		if( null === $content_type ) {
			$content_type = $file->mimetype();
		}

		header( 'Content-type: ' . $content_type );
		header( 'Length: ' . $file->size() );
		$file->read();
	}
}//end PSUDownloader
