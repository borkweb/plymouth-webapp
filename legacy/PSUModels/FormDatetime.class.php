<?php

require_once('FormDate.class.php');

/**
 * A text field formatted to accept date and time strings, similar to FormDate.
 *
 * This can be a useful shortcut for storing timestamps in the model.
 *
 * @ingroup psumodels
 */
class FormDatetime extends FormDate
{
	const datetime_format = '%m/%d/%Y %H:%M:%S';

	function __construct($args = array())
	{
		$args = PSU::params($args);

		if(!isset($args['size']))
		{
			// MM/DD/YYYY HH:MM:SS
			$args['size'] = 19;
		}

		parent::__construct($args);

		$this->formatting = 'FormDatetime::convert_datetimestring';
	}

	public function value()
	{
		if(func_num_args() == 1)
		{
			$v = func_get_arg(0);

			// ctype_digit will fail on timestamps in the past, so typecast here
			if( substr($v, 0, 1) == '-' && ctype_digit(substr($v, 1)) )
			{
				$v = (int)$v;
			}

			if( is_int($v) || ctype_digit($v) )
			{
				$v = strftime(self::datetime_format, (int)$v);
			}

			// unset default field value
			if($v == 'MM/DD/YYYY HH:MM:SS')
			{
				$v = '';
			}

			parent::value($v);
		}
		else
		{
			return parent::value();
		}
	}

	public static function convert_datetimestring($v)
	{
		$v = strtotime($v);
		return strftime(self::datetime_format, $v);
	}

	function validate($v)
	{
		if(($v = strtotime($v)) === false)
		{
			throw new ValidationException('Could not convert string to a date and time, please use MM/DD/YYYY HH:MM:SS. Your value');
		}
	}
}
