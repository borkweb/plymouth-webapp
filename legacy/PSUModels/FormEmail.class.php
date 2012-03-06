<?php

require_once('FormText.class.php');

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
		if($v == '')
		{
			return;
		}

		//
		// lifted from WordPress 2.7.1's is_email() function
		//

		$chars = "/^([a-z0-9+_]|\\-|\\.)+@(([a-z0-9_]|\\-)+\\.)+[a-z]{2,6}\$/i";

		if(strpos($v, '@') !== false && strpos($v, '.') !== false)
		{
			if(preg_match($chars, $v))
			{
				return;
			}
		}

		throw new ValidationException('That is not a valid email address');
	}
}
