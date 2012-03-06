<?php

namespace PSU\Model;

/**
 * @ingroup psumodels
 */
class FormNumber extends FormText
{
	public function __construct( $args = array() ) {
		parent::__construct( $args );

		$this->adodb_type = 'N';
	}

	public function value()
	{
		if(func_num_args() == 1)
		{
			$v = (int)func_get_arg(0);
			parent::value($v);
		}
		else
		{
			return parent::value();
		}
	}
}
