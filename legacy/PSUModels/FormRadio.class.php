<?php

require_once('FormMultiSelectable.class.php');

/**
 * @ingroup psumodels
 */
class FormRadio extends FormMultiSelectable
{
	public function __construct($args = array())
	{
		$args = PSU::params($args);

		$args['type'] = 'radio';
		parent::__construct($args);
		
		$this->selected = array();
	}

	public function __toString()
	{
		$html = '<ul>';

		foreach($this->options as $o)
		{
			$html .= '<li>' . $this->option($o) . "</li>\n";
 		}
		$html .= '</ul>';

		return $html;
	}

	public function as_tr()
	{
		$html = '<tr><td class="label">' . $this->label() . '</td>';
		foreach($this->options as $o)
		{
			$html .= '<td>' . $this->option($o, false) . '</td>';
		}
		$html .= '</tr>';

		return $html;
	}

	public function option($o, $label = true)
	{
		static $is_array = null;

		if($is_array === null)
		{
			$is_array = is_array($this->options[0]);
		}

		$index = 0;
		if(is_string($o))
		{
			foreach($this->options as $new_o)
			{
				list($key, $value) = $is_array ? $new_o : array($new_o, $new_o);
				if($o == $key)
				{
					$o = $new_o;
					break;
				}
				$index++;
			}
		}

		$id = $this->id->value;
		$this->id->value = $id . $index;

		list($key, $value) = $is_array ? $o : array($o, $o);
		$attributes = $this->attributes2string();
		$checked = in_array($key, $this->selected) ? ' checked="checked"' : '';

		if($this->readonly)
		{
			$html = sprintf('<span class="readonly">%s</span>', $checked ? '(Yes)' : '(No)');
		}
		else
		{
			$html = '<input class="radio"' . $attributes . ' value="' . htmlentities($key) . '"' . $checked . '>';
		}

		if($label)
		{
			$html .= ' <label class="checkbox" for="' . $this->id->value . '">' . $value . "</label>\n";
		}

		$this->id = $id;

		return $html;
	}
}//end FormRadio
