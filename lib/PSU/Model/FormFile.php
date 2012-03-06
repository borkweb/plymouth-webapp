<?php

namespace PSU\Model;

use PSU;

/**
 * @ingroup psumodels
 */
class FormFile extends FormField
{
	private $filemanager;

	public function __construct($args = array())
	{
		$args = PSU::params($args);

		$this->fileid = 0;
		$this->adodb_type = 'N';
		$this->filemanager = null;

		parent::__construct($args);

		$this->type->value = 'file';
	}//end __construct

	public function filemanager( $new = null ) {
		static $filemanager;

		if( isset( $new ) ) {
			$filemanager = $new;
			$filemanager->parent( $this );
		}

		return $filemanager;
	}//end filemanager

	public function url() {
		return $this->filemanager()->url( $this );
	}

	public function __toString()
	{
		$filename = $this->filename();

		if( $this->show_link ) {
			$url = $this->url();
			$filename = sprintf( '<a href="%s">%s</a>', htmlentities($url), htmlentities($filename) );
		}

		if($this->readonly) {
			return $this->readonly($filename, PSU_Model_FormField::SANITIZED);
		} else {
			// disabled element, just return the filename
			if((string) $this->disabled) {
				return $filename . ' ' . $this->help();
			}

			$html = '<span class="browse">' . parent::__toString() . '</span>';

			// no file, just output the input box
			if( $this->fileid === 0 ) {
				return $html;
			}

			$uploaded = '<span class="name">' . $filename;

			$uploaded .= sprintf('<input type="hidden"%s%s value="%s">', $this->name, $this->id, $this->fileid);
			$uploaded .= sprintf(' <input type="submit" name="delete_files[%s]" value="Delete">', $this->name->value);
			$uploaded .= ' ' . $this->help() . '</span>';

			return $html . $uploaded;
		}
	}

	public function as_li($show_link = false)
	{
		$this->show_link = $show_link;

		$class = 'file';
		$class .= ($this->fileid > 0) ? ' uploaded' : '';
		$s = parent::as_li($class);

		$this->show_link = false;

		return $s;
	}

	public function filename()
	{
		return $this->filemanager()->filename( $this );
	}

	public function is_empty()
	{
		$v = $this->value();
		return $v == 0;
	}

	public function value()
	{
		if(func_num_args() == 1)
		{
			$this->fileid = (int)func_get_arg(0);
		}
		else
		{
			return (int)$this->fileid;
		}
	}

	/**
	 * Return the filesystem path to this file.
	 */
	public function path( $portion = null ) {
		return $this->filemanager()->path( $this, $portion );
	}//end path
}
