<?php

namespace PSU\Model\FormFile;

class FileManager extends FileManager\Base {
	/**
	 * Handle a file upload, moving the file into the correct place on the filesystem and
	 * providing the file's identifier back to our parent.
	 *
	 * @param $field PSU_Model_FormFile the target field
	 * @param $file a single file from the $_FILES array
	 */
	public function upload( $field, $file ) {
		$path = $this->path( $field );
		$dir = dirname( $path );

		if( ! is_dir( $dir ) ) {
			mkdir( $dir, 02770, true );
			chmod( $dir, 02770 );
		}

		if( ! $this->move_uploaded_file( $file['tmp_name'], $path ) ) {
			throw new Exception( 'Could not accept file upload.' );
		}

		chmod( $path, 0660 );

		$id = $this->pathgenerator->save_path( $field, $file );
		return $id;
	}//end upload

	/**
	 *
	 */
	public function move_uploaded_file( $src, $dest ) {
		return move_uploaded_file( $src, $dest );
	}//end move_uploaded_file

	/**
	 * Overrideable is_uploaded_file().
	 */
	public function is_uploaded_file( $path ) {
		return is_uploaded_file( $path );
	}//end is_uploaded_file

	/**
	 *
	 */
	public function delete() {
		return false;
	}//end delete
}//end PSU_Model_FormFile_FileManager
