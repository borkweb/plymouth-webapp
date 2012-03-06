<?php

namespace PSU\Model;

/**
 * Class for elements which could theoretically hold multiple values: checkboxes,
 * select boxes... just those two, I guess. Also useful for radio buttons.
 * @ingroup psumodels
 */
class FormMultiSelectable extends FormField
{
	/** Get or set the currently selected items. */
	public function value()
	{
		if(func_num_args() == 0)
		{
			// getting a value
			return $this->selected;
		}

		//
		// otherwise, setting a value
		//

		$v = func_get_arg(0);

		// replace blank entries with an empty array
		if(is_array($v) && count($v) == 1 && $v[0] == '')
		{
			$v = array();
		}
		elseif($v == '')
		{
			$v = array();
		}

		$v = is_array($v) ? $v : array($v);

		// override the value with valid values
		if($this->validation)
		{
			$v = call_user_func($this->validation, $v);
		}
		else
		{
			$v = $this->validate($v);
		}

		$this->selected = $v;
	}//end value

	/** Test if the value is empty, since empty() can't run on the value() function directly. */
	public function is_empty()
	{
		$v = $this->value();
		return empty($v);
	}//end is_empty

	public function __set($k, $v)
	{
		if($k == 'selected' && !is_array($v))
		{
			$v = array($v);
		}

		if($k == 'options' && is_string($v))
		{
			// options should be an array. presence of a string indicates some
			// sort of callback, so make a note of the callback and repopulate
			// the value.

			if(strpos($v, '::') !== false)
			{
				$this->validation = explode('::', $v);
			}
			else
			{
				$this->validation = $v;
			}

			$v = call_user_func($this->validation);
		}

		parent::__set($k, $v);
	}//end __set

	/** Return true if the supplied value is selected. */
	public function in_value($q)
	{
		return in_array($q, $this->selected);
	}//end in_value

	/**
	 * Return the human-readable value for the selected key, or a specified key.
	 */
	public function value4key()
	{
		if(func_num_args() == 0)
		{
			$key = array_pop($this->value());
		}
		else
		{
			$key = func_get_arg(0);
		}
		
		// we have options that are array($key, $value)
		if( is_array($this->options[0]) ) {
			foreach($this->options as $o)
			{
				list($k, $v) = $o;

				if($key == $k)
				{
					return $v;
				}
			}
		}

		// non-array options just have the key returned (key is value)
		elseif( $key ) {
			return $key;
		}

		return null;
	}//end value4key

	/**
	 * Determine if a list of values is valid for this field's options.
	 * @param $needle array a one- or two-dimensional array of options to test
	 * @return array a list of the valid options
	 */
	public function validate($needle)
	{
		$return_array = false;

		// validate against a list of keys from haystack. haystack may be
		// array(1, 2, 3) or array(array(1, Something), array(2, Other))
		$keys = array();
		if(is_array($this->options[0]))
		{
			foreach($this->options as $a)
			{
				$keys[] = $a[0];
			}
		}
		else
		{
			$keys = $this->options;
		}

		// check input
		if(is_array($needle))
		{
			$return_array = true;
		}
		else
		{
			$return_array = false;
			$needle = array($needle);
		}

		// find which of the input was valid
		$valid = array();
		foreach($needle as $v)
		{
			if(in_array($v, $keys))
			{
				$valid[] = $v;
			}
		}

		// return array, if necessary
		if($return_array)
		{
			return $valid;
		}

		// otherwise, return string
		if(count($valid) == 0)
		{
			// validation failed, no match
			return '';
		}

		// validation succeeded, return one value
		return parent::validate( $valid[0] );
	}//end validate

	/**
	 * Serialize this formfield.
	 */
	function serialize()
	{
		$v = $this->value();
		switch(count($v))
		{
			case 0: return null;
			case 1: return $v[0];
			default: return $v;
		}
	}//end serialize
}//end PSU_Model_FormMultiSelectable
