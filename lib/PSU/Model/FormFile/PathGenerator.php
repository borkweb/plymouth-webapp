<?php

namespace PSU\Model\FormFile;

abstract class PathGenerator {
	/**
	 * The path to the file on the filesystem.
	 */
	abstract public function get_path( $field );

	/**
	 * The file's filename. Defaults to the same value as PSU_Model_FormFile_PathGenerator::get_path().
	 * 
	 * May be very different from the value returned by get_path(), which could be sanitized.
	 */
	public function get_filename( $field ) {
		return $this->get_path( $field );
	}//end get_filename

	/**
	 * Give the path generator an opportunity to save a filename back to its datastore.
	 *
	 * @param $field PSU_Model_FormFile the field accepting the upload
	 * @param $file array the element from the $_FILES array
	 * @return null
	 */
	abstract public function save_path( $field, $file );
}
