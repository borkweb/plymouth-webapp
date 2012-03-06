<?php

namespace PSU\Model;

/**
 * @ingroup psumodels
 */
class HTMLAttribute
{
	var $attribute;
	var $value;

	/**
	 * Create a new PSU_Model_HTMLAttribute. $attribute may be omitted to just set the value.
	 * This is done by PSU_Model_FormFields so the constructor can say <code>$this->attr = new PSU_Model_HTMLAttribute('thing')</code>
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
		if(empty($this->value))
		{
			return '';
		}

		return sprintf(' %s="%s"', $this->attribute, htmlentities($this->value));
	}//end __toString
}//end PSU_Model_HTMLAttribute
