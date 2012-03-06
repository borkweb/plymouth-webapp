<?php

namespace PSU\Model\FormFile\FileManager;

abstract class Base {
	const PATH_FULL = 1; // full path, i.e. /path/to/my/uploads/user20/photo.jpg
	const PATH_PARTIAL = 2; // partial path from some base, i.e. user20/photo.jpg
	const PATH_FILENAME = 4; // filename only, i.e. photo.jpg

	/**
	 * The parent model or PSU\Model\FormFile.
	 */
	public $parent;

	/**
	 * Base URL for file downloading. (No trailing slash.)
	 */
	public $base_url;

	/**
	 * Directory that holds the file uploads. (No trailing slash.)
	 */
	public $upload_dir;

	/**
	 * A class implementing PSU_Model_FormFile_PathGenerator.
	 */
	public $pathgenerator;

	public function __construct( $pathgenerator = null ) {
		if( isset( $pathgenerator ) ) {
			$this->pathgenerator = $pathgenerator;
		}
	}

	/**
	 * Method to return the url 
	 */
	public function url( $field ) {
		$path = $this->path( $field, self::PATH_PARTIAL );

		if( null == $path ) {
			return null;
		}

		return sprintf( "%s/%s/%s", $this->base_url, $path, $this->filename( $field ) );
	}//end url

	/**
	 * Method to return the filename portion of a file path.
	 *
	 * @param $field PSU_Model_FormFile the file whose name we want
	 * @param $portion int one of PSU_Model_FormFile_FileManager::PATH_FULL (default), PSU_Model_FormFile_FileManager::PATH_PARTIAL, or PSU_Model_FormFile_FileManager::PATH_FILENAME
	 * @return string
	 */
	public function path( $field, $portion = null ) {
		if( ! isset( $portion ) ) {
			$portion = self::PATH_FULL;
		}

		$path = $this->pathgenerator->get_path( $field );

		if( null == $path ) {
			return null;
		}

		$path = $this->upload_dir . '/' . $path;

		if( $portion & self::PATH_PARTIAL ) {
			$path = substr( $path, strlen( $this->upload_dir ) + 1 );
		} elseif( $portion & self::PATH_FILENAME ) {
			$path = basename( $path );
		}

		return $path;
	}//end path

	/**
	 * Return the filename.
	 */
	public function filename( $field ) {
		return $this->pathgenerator->get_filename( $field );
	}//end filename

	/**
	 * Save a file. MUST return some identifier that can be stored in the model,
	 * to later indicate that the file has been uploaded.
	 *
	 * @param $field PSU_Model_FormFile the field accepting the file
	 * @param $file array the item from the $_FILES array
	 */
	abstract public function upload( $field, $file );

	/**
	 * Return the parent object.
	 */
	public function parent() {
		$this->parent = $parent;

		return $this->parent;
	}

	// delete an uploaded file
	abstract public function delete();
}//end PSU_Model_FormFile_FileManager_Base
