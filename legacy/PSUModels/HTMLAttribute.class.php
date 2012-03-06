<?php

/**
 * @ingroup psumodels
 */
class HTMLAttribute
{
	var $attribute;
	var $value;

	/**
	 * Create a new HTMLAttribute. $attribute may be omitted to just set the value.
	 * This is done by FormFields so the constructor can say <code>$this->attr = new HTMLAttribute('thing')</code>
	 * and later set $this->attr->attribute in the __set() method.
	 */
	public function __construct($attribute = '', $value = '')
	{
		if($value == '')
		{
			$this->value = $attribute;;
		}
		else
		{
			$this->attribute = $attribute;
			$this->value = $value;
		}
	}//end __construct

	public function __toString()
	{
		if( !is_int($this->value) && empty($this->value) )
		{
			return '';
		}

		return sprintf(' %s="%s"', $this->attribute, htmlentities($this->value));
	}//end __toString
}
