<?php

namespace PSU\Model;

use PSU;

/**
 * @ingroup psumodels
 */
class FormText extends FormField
{
	public function __construct($args = array())
	{
		$args = PSU::params($args, array(
			'type' => 'text'
		));

		$this->adodb_type = "C";
		$this->maxlength = new HTMLAttribute;
		$this->size = new HTMLAttribute(20);
		$this->value = new HTMLAttribute;
		$this->placeholder = new HTMLAttribute;
		$this->autofocus = new HTMLProperty;

		parent::__construct($args);
	}

	public function value()
	{
		if(func_num_args() == 1)
		{
			$v = func_get_arg(0);

			if( is_string($v) ) {
				$v = trim($v);
			}

			// empty values don't need validation
			if(!empty($v))
			{
				if( $this->maxlength->value )
				{
					$v = substr($v, 0, $this->maxlength->value);
				}

				$this->validate($v);
				$v = $this->format_callback($v);
			}

			$this->value->value = $v;
		}
		else
		{
			return $this->value->value;
		}
	}

	public function is_empty()
	{
		$v = $this->value();
		return empty($v);
	}

	public function serialize()
	{
		$v = $this->value();
		return empty($v) ? null : $v;
	}
}
