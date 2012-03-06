<?php

namespace PSU\Model;

use PSU;

/**
 * @ingroup psumodels
 */
class FormSelect extends FormMultiSelectable
{
	public function __construct($args = array())
	{
		$args = PSU::params($args);

		$this->size = new HTMLAttribute();
		$this->multiple = new HTMLAttribute();

		$this->options = array();
		$this->selected = array();

		$this->tag_name = 'select';

		$this->hasBlank = true;

		parent::__construct($args);
	}

	/**
	 *
	 */
	public function attributes2string()
	{
		$name = null;

		if( isset($this->multiple) && $this->multiple->value == 'multiple') {
			$name = $this->name->value;
			$this->name->value .= "[]";
		}

		$s = parent::attributes2string();

		if( $name !== null ) {
			$this->name->value = $name;
		}

		return $s;
	}//end attributes2string

	/**
	 * Custom setter to handle "multiple" PSU_Model_HTMLAttribute
	 */
	public function __set($k, $v) {
		if($k == 'multiple') {
			if( $v instanceof HTMLAttribute ) {
				$v->value = $v->value ? 'multiple' : '';
			} else {
				$v = $v ? 'multiple' : '';
			}
		}

		parent::__set($k, $v);
	}//end __set

	public function __toString()
	{
		if($this->readonly)
		{
			$html = $this->readonly($this->value4key());
		}
		else
		{
			$is_array = is_array($this->options[0]);

			$html = '<select' . $this->attributes2string() . '>';

			if( $this->hasBlank )
			{
				$html .= '<option value=""></option>';
			}

			foreach($this->options as $o)
			{
				list($key, $value) = $is_array ? $o : array($o, $o);
				$s = in_array($key, $this->selected) ? ' selected="selected"' : '';
				$html .= sprintf('<option value="%s"%s>%s</option>', htmlentities($key), $s, htmlentities($value));
			}

			$html .= '</select>';
		}

		$html .= ' ' . $this->help();

		return $html;
	}

	/**
	 * Convenience function for yes/no selects.
	 */
	public static function yesno() {
		return array( array('Y', 'Yes'), array('N', 'No') );
	}//end yesno
}
