<?php

namespace PSU\Model;

use PSU;

/**
 * A control with &lt;input type="checkbox">
 * @ingroup psumodels
 */ 
class FormCheckbox extends FormMultiSelectable
{
	public function __construct($args = array())
	{
		$args = PSU::params($args);

		$args['type'] = 'checkbox';

		if(!isset($args['options']))
		{
			$args['options'] = array('Yes');
			$args['maxlength'] = 3;
		}

		parent::__construct($args);
		
		if( !$this->selected )
		{
			$this->selected = array();
		}
	}//end __construct

	/**
	 *
	 */
	public function attributes2string()
	{
		$name = $this->name->value;
		$this->name->value .= "[]";

		$s = parent::attributes2string();

		$this->name->value = $name;
		return $s;
	}//end attributes2string

	/**
	 * Convert to a string.
	 */
	public function __toString()
	{
		$optcount = count($this->options);

		$is_array = is_array($this->options[0]);
		$id = $this->id->value;

		$single_box = $optcount == 1;

		// need to have at least one value present, or it won't make it through during submit
		$html = '<input type="hidden" name="' . $this->name->value . '[]" value="">';

		if(! $single_box)
		{
			$html .= '<ul>';
		}

		$i = -1;
		foreach($this->options as $o)
		{
			list($key, $value) = $is_array ? $o : array($o, $o);

			// for checkboxes, modify display. examples:
			//       before            after
			//       ================  ================
			//       name="myname"     name="myname[]"
			//       id="myname"       id="myname0"
			if(! $single_box)
			{
				$this->id->value = $id . ++$i;
			}

			$attributes = $this->attributes2string();
			$checked = in_array($key, $this->selected) ? ' checked="checked"' : '';

			if($this->readonly)
			{
				$thisHTML = sprintf('<span class="readonly">%s</span>', $checked ? '(Yes)' : '(No)');
			}
			else
			{
				$thisHTML = '<input class="checkbox"' . $attributes . ' value="' . htmlentities($key) . '"' . $checked . '>';
			}

			if(! $single_box)
			{
				$thisHTML = '<li>' . $thisHTML . ' <label class="checkbox" for="' . $this->id->value . '">' . $value . '</label></li>';
			}

			$html .= $thisHTML . "\n";
 		}
		$this->id->value = $id;

		if(! $single_box)
		{
			$html .= '</ul>';
		}

		$html .= ' ' . $this->help();

		return $html;
	}//end __toString
}
