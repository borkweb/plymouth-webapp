<?php

require_once('FormText.class.php');

/**
 * @ingroup psumodels
 */
class FormTextarea extends FormText
{
	public function __construct($args = array())
	{
		$args = PSU::params($args);

		$this->rows = new HTMLAttribute(10);
		$this->cols = new HTMLAttribute(40);

		parent::__construct($args);

		$this->adodb_type = "XL";

		unset($this->value);
		$this->value = '';
		
		unset($this->size);
		$this->tag_name = 'textarea';
	}

	public function __toString()
	{
		$html = trim(parent::__toString());

		if( ! $this->readonly )
		{
			$html	.= $this->value() . '</' . $this->tag_name . '>';
		}

		return $html;
	}
}
