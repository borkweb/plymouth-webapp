<?php

namespace PSU\Model;

use PSU;

/**
 * @ingroup psumodels
 */
class FormEmail extends FormText
{
	public function __construct($args = array())
	{
		$args = PSU::params($args);

		if(!isset($args['size']))
		{
			$args['size'] = 20;
		}

		if(!isset($args['maxlength']))
		{
			$args['maxlength'] = 90;
		}

		parent::__construct($args);
	}

	public function validate($v)
	{
		if($v == '') {
			return;
		}

		$v = filter_var( $v, FILTER_VALIDATE_EMAIL );

		if( $v === false ) {
			throw new ValidationException('That is not a valid email address');
		}

		return parent::validate();
	}
}
