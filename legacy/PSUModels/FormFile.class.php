<?php

require_once('FormField.class.php');

/**
 * @ingroup psumodels
 */
class FormFile extends FormField
{
	public function __construct($args = array())
	{
		$args = PSU::params($args);

		$this->fileid = 0;
		$this->adodb_type = 'N';

		parent::__construct($args);

		$this->type->value = 'file';
	}

	public function __toString()
	{
		$filename = $this->filename();
		if($this->show_link)
		{
			$filename = sprintf('<a href="%s/f/%s/%s">%s</a>', $GLOBALS['APP_URL'], $this->fileid, $filename, $filename);
		}

		if($this->readonly)
		{
			return $this->readonly($filename);
		}
		else
		{
			// disabled element, just return the filename
			if((string) $this->disabled)
			{
				return $filename . ' ' . $this->help();
			}

			$html = '<span class="browse">' . parent::__toString() . '</span>';

			// no file, just output the input box
			if($this->fileid === 0)
			{
				return $html;
			}

			$uploaded = '<span class="name">';
			$uploaded .= sprintf('%s <input type="hidden"%s%s value="%s">', $filename, $this->name, $this->id, $this->fileid);
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
		if($this->fileid === 0)
		{
			return '';
		}

		$sql = "SELECT filename FROM APP_2008_FILES WHERE fileid = :fileid";
		$filename = PSU::db('psp')->GetOne($sql, array('fileid' => $this->fileid));
		return $filename;
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
}
