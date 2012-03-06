<?php

namespace PSU\Downloader;

/**
 * A representation of a file that will be read for download.
 */
class File {
	/**
	 * The global uploads directory used for this file, with leading
	 * and trailing slash.
	 */
	public $upload_dir;

	/**
	 * The application-specific subpath, with trailing slash. Acts as a
	 * jail outside which file access will not be allowed.
	 */
	public $subpath;

	/**
	 * The remainder of the path to the file. May included directories.
	 */
	public $filepath;

	/**
	 * The full filesystem path to the file. False, if the file is
	 * found to not exist.
	 */
	public $realpath;

	/**
	 * The file mime type.
	 * @see File::mimetype()
	 * @access private
	 */
	private $mimetype = null;

	/**
	 * File constructor.
	 *
	 * @param string $upload_dir Global upload directory.
	 * @param string $subpath Application-specific subpath.
	 * @param string $filepath Remainder of path to the file.
	 */
	public function __construct( $upload_dir, $subpath, $filepath ) {
		$this->upload_dir = '/' . trim( $upload_dir, '/' ) . '/';
		$this->subpath    = trim( $subpath, '/' ) . '/';
		$this->filepath   = $filepath;
	}//end __construct

	/**
	 * Return the basename to this file.
	 */
	public function basename() {
		if( ! $this->realpath ) {
			$this->find();
		}

		return basename( $this->realpath );
	}//end basename

	/**
	 * Evaluate the path parts and set $this->realpath to the actual path
	 * to the file.
	 */
	public function find() {
		$path = $this->upload_dir . $this->subpath . $this->filepath;
		$this->realpath = realpath( $path );

		if( false === $this->realpath ) {
			throw new FileNotFoundException();
		}

		if( strpos( $this->realpath, $this->upload_dir ) !== 0 ) {
			throw new InvalidPathException('Path to download file was outside the global upload directory.');
		}

		if( strpos( $this->realpath, $this->upload_dir . $this->subpath ) !== 0 ) {
			throw new InvalidPathException('Path to download file was outside the application upload directory.');
		}
	}//end find

	/**
	 * Return this file's mime type.
	 */
	public function mimetype() {
		if( null === $this->mimetype ) {
			$finfo = finfo_open( FILEINFO_MIME_TYPE );
			$this->mimetype = finfo_file( $finfo, $this->realpath );
		}

		return $this->mimetype;
	}//end mimetype

	/**
	 * Read and output the contents of this file.
	 */
	public function read() {
		if( ! $this->realpath ) {
			$this->find();
		}

		return readfile( $this->realpath );
	}

	/**
	 * Return the size of the file.
	 */
	public function size() {
		if( ! $this->realpath ) {
			$this->find();
		}

		return filesize( $this->realpath );
	}//end size
}
