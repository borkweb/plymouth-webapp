<?php

namespace PSU\Model;

use PSU;

/**
 * A text field formatted to accept dates. Input is filtered through <a href="http://php.net/strtotime">strtotime()</a>. The &lt;input>
 * element will have a class of "datepicker" by default so that a JavaScript datapicker can be bound easily.
 * @ingroup psumodels
 */
class FormDate extends FormText
{
	const date_format = '%m/%d/%Y';

	function __construct($args = array())
	{
		$args = PSU::params($args);

		$this->formatting = array(__CLASS__, 'convert_datestring');

		if(!isset($args['size']))
		{
			$args['size'] = 11;
		}

		parent::__construct($args);

		$this->addClass('datepicker');
		$this->adodb_type = 'D';
	}

	public function serialize()
	{
		return strtotime($this->value());
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
				$v = strftime('%m/%d/%Y', (int)$v);
			}

			// unset default field value
			if($v == 'MM/DD/YYYY')
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

	public static function convert_datestring($v)
	{
		$v = strtotime($v);
		return strftime(self::date_format, $v);
	}

	function validate($v)
	{
		if(($v = strtotime($v)) === false)
		{
			throw new ValidationException('Could not convert string to a date, please use MM/DD/YYYY. Your value');
		}

		return parent::validate();
	}
}
