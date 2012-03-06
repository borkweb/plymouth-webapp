<?php

namespace PSU\Model;

use PSU;

/**
 * Generic base class for form fields, such as &lt;input> or &lt;button>.
 *
 * PSU_Model_FormField is a generic object for creating and displaying various input-related HTML form fields. 
 * It should be subclassed for specific types of fields, ie. a text input. PSU_Model_FormFields will generally have
 * one or more property that is an PSU_Model_HTMLAttribute. When this field is output as a string, all PSU_Model_HTMLAttributes
 * will be converted to <i>key="value"</i> pairs and output inside the HTML tag.
 *
 * @param $adodb_type
 * @param $hidden True to display this field as &lt;input type="hidden">.
 * @param $readonly True to display this field as text instead of a form field.
 * @param $tag_name A string defining the HTML tag name. This tag should not have any content. If you need opening/closing tags with content, subclass PSU_Model_FormField and override PSU_Model_FormField::__toString().
 * @param $name An PSU_Model_HTMLAttribute.
 * @param $type An PSU_Model_HTMLAttribute.
 * @param $id An PSU_Model_HTMLAttribute.
 * @param $disabled An PSU_Model_HTMLAttribute.
 * @param $value An PSU_Model_HTMLAttribute.
 * @ingroup psumodels
 */
class FormField
{
	/**
	 * Text returned in read-only mode if this field has no value.
	 */
	const no_response = '<i>no response</i>';

	const SANITIZED = 1;
	const UNSANITIZED = 2;

	/// @cond NEVER
	public $_properties = array(
		'adodb_type' => 'C', // http://phplens.com/lens/adodb/docs-datadict.htm
		'class_array' => array(),
		'label_classes' => array(),
		'help_url' => 'help.html',
		'help_frame' => 'genmodelhelp', // something sufficiently unique
		'help_display' => '(help)',
		'hidden' => false,
		'readonly' => false
	);
	/// @endcond

	/**
	 * Class constructor.
	 *
	 * @param $args An associative array (or argument string) of properties that will be assigned to the class. Possible values:
	 */
	public function __construct($args = array())
	{
		$args = PSU::params($args);

		$this->tag_name = 'input'; // default

		$this->name = new HTMLAttribute;
		$this->type = new HTMLAttribute;
		$this->id = new HTMLAttribute;
		$this->disabled = new HTMLAttribute;
		$this->value = new HTMLAttribute;

		foreach($args as $k => $v) {
			if($this->$k instanceof HTMLAttribute) {
				$this->$k->value = $v;
			} elseif($k == 'class') {
				$this->addClass($v);
			} elseif($k == 'label_class') {
				$this->add_label_class($v);
			} else {
				$this->$k = $v;
			}
		}
	}//end __construct

	/**
	 * Represent this field as a field of &lt;input type="hidden">.
	 *
	 * Output will only include the name and value PSU_Model_HTMLAttribute properties.
	 *
	 * @return html string
	 */
	public function as_hidden()
	{
		return '<input type="hidden" ' . $this->name . $this->value . '>';
	}//end as_hidden

	public function clean_class() {
		return strtolower( str_replace( '\\', '-', get_called_class() ) );
	}//end clean_class

	/**
	 * Getter/setter for parent model.
	 */
	public function model( $new = null ) {
		static $model = null;

		if( isset( $new ) ) {
			$model = $new;
		}

		return $model;
	}//end model

	/**
	 * Used when the parent object is serializing itself. Alias to PSU_Model_FormField::value() in this instance.
	 * @return string
	 */
	public function serialize()
	{
		return $this->value();
	}//end serialize

	/**
	 * Shortcut to get this field's readable name.
	 */
	public function name() {
		return $this->name->value;
	}//end name

	/**
	 * PSU_Model_Format a value using this field's formatting function. Uses the callback string or array assigned to PSU_Model_FormField::formatting.
	 *
	 * @param $val value, or array of values to format
	 * @todo Function accepts an array, but won't return one. Should return what it was given.
	 */
	public function format_callback( $val )
	{
		if( ! isset( $this->formatting ) )
			return $val;

		if( is_array( $val ) ) {
			foreach( $val as &$v ) {
				$v = call_user_func( $this->formatting, $v );
			}
		} else {
			$val = call_user_func( $this->formatting, $val );
		}

		return $val;
	}//end format_callback

	/**
	 * Test if a field is empty, returns boolean. Some usage of empty() balks with PSU_Model_FormFields, so use this function instead.
	 * @return True if the field is empty, false otherwise.
	 */
	public function is_empty()
	{
		return empty($this->value->value);
	}//end is_empty

	/**
	 * Output this field's &lt;label>. If possible (ie. if the field has an id), this label will have a "for" attribute.
	 *
	 * If this field's "detail" or "required" properties are set to boolean True, the label will receive classes of the same name. Custom classes may be applied using PSU_Model_FormField::add_label_class().
	 *
	 * @return html string
	 */
	public function label() 
	{
		$classes = array_keys($this->label_classes);

		foreach(array('detail', 'required') as $test)
			if(isset($this->$test) && $this->$test)
				$classes[] = $test;

		if($this->required && $this->is_empty())
			$classes[] = 'missing';

		$classes = implode(" ", $classes);

		if( $classes )
			$classes = new HTMLAttribute('class', $classes);

		$for = '';
		if( $this->id->value )
			$for = new HTMLAttribute('for', $this->id->value);

		return sprintf("<label%s%s>%s%s</label>", $classes, $for, $this->label, $this->required ? '<em>*</em>' : '');
	}

	/// @cond NEVER
	public function &__get($key)
	{
		if(isset($this->_properties[$key]))
			return $this->_properties[$key];

		$n = null;
		return $n;
	}

	public function __isset($key)
	{
		return isset($this->_properties[$key]);
	}

	public function __unset($key)
	{
		unset($this->_properties[$key]);
	}//end __unset

	public function __set($key, $value)
	{
		/// allows setting of "disabled" by boolean. replaces PSU_Model_HTMLAttribute(true) with
		/// PSU_Model_HTMLAttribute('disabled'), replaces <code>$field->disabled = true</code>
		/// with <code>$field->disabled->value = 'disabled'</code>.
		if($key == 'disabled')
			if($value instanceof HTMLAttribute)
				$value->value = $value->value ? 'disabled' : '';
			else
				$value = $value ? 'disabled' : '';

		// if the PSU_Model_HTMLAttribute was already set, just update the value. lets you do this:
		//     $this->id = new PSU_Model_HTMLAttribute('foo');
		//     $this->id = 'bar'; // same as $this->id->value = 'bar';
		//     $this->id = new PSU_Model_HTMLAttribute('baz'); // replaces id with a new htmlattribute
		if(isset($this->_properties[$key]) && $this->_properties[$key] instanceof HTMLAttribute && ! $value instanceof HTMLAttribute) {
			// attribute was already set
			$this->_properties[$key]->value = $value;
		} else {
			// new attribute
			$this->_properties[$key] = $value;

			if($value instanceof HTMLAttribute)
			{
				$this->_properties[$key]->attribute = $key;
			}
		}
	}//end __set

	/// @endcond

	/**
	 * Output a readonly version of the field.
	 */
	public function readonly($v, $sanitized = self::UNSANITIZED)
	{
		$html = $v;

		if( empty($html) ) {
			$html = FormField::no_response;
		} else {
			if( $sanitized == self::UNSANITIZED ) {
				$html = htmlentities($html);
			}
		}

		$html = '<span class="readonly">' . $html . '</span>';

		return $html;
	}//end readonly

	/**
	 * Custom function to convert this field to a string. Includes any field "help" text, but not the form's label.
	 * @return html string
	 */
	public function __toString()
	{
		if($this->readonly)
			$html = $this->readonly($this->value());
		else
			$html = '<' . $this->tag_name . $this->attributes2string() . '>';

		if( $help = $this->help() )
			$html .= ' ' . $this->help();

		return $html;
	}//end __toString

	/**
	 * Add a class to this field's &lt;label>.
	 * @param $classes a class string, or array of strings
	 * @see remove_label_class
	 */
	public function add_label_class($classes)
	{
		$classes = is_array($classes) ? $classes : array($classes);
		foreach($classes as $c)
			$this->_properties['label_classes'][$c] = true;
	}

	/**
	 * Remove a class from this field's &lt;label>.
	 * @param $classes a class string, or array of strings
	 * @see add_label_class
	 */
	public function remove_label_class($classes)
	{
		$classes = is_array($classes) ? $classes : array($classes);
		foreach($classes as $c)
			unset($this->_properties['label_classes'][$c]);
	}//end remove_label_class

	/**
	 * Add a class to this form field.
	 * @param $classes a class string, or array of strings
	 * @see removeClass
	 */
	public function addClass($classes)
	{
		$classes = is_array($classes) ? $classes : array($classes);
		foreach($classes as $c)
			$this->_properties['class_array'][$c] = true;
	}

	/**
	 * Remove a class from this form field.
	 * @param $classes a class string, or array of strings
	 * @see addClass
	 */
	public function removeClass($classes)
	{
		$classes = is_array($classes) ? $classes : array($classes);
		foreach($classes as $c)
			unset($this->_properties['class_array'][$c]);
	}

	/**
	 * Find all PSU_Model_HTMLAttribute properties in this field and convert to a string of HTML attributes.
	 */
	public function attributes2string()
	{
		$html = '';
		$this->class = new HTMLAttribute(implode(' ', array_keys($this->_properties['class_array'])));
		foreach($this->_properties as $p)
			if($p instanceof HTMLAttribute)
				$html .= $p;
		return $html;
	}

	/**
	 * Represent this field within a &lt;div> and return the resulting HTML.
	 */
	public function as_div()
	{
		$html = (string)$this;
		$html = '<div class="formrow">' . $this->label() . $html . '</div>';
		return $html;
	}

	/**
	 * Represent this field with its label, with no other wrapped content.
	 */
	public function labeled()
	{
		return $this->label() . ' ' . $this->__toString();
	}//end as_labeled_field

	/**
	 * Represent this field within an &lt;li> and return the resulting HTML.
	 */
	public function as_li($classes = '')
	{

		if(is_array($classes))
			$classes = !empty($classes) ? " class=\"" . implode(" ", $classes) . "\"" : '';
		else
			$classes = !empty($classes) ? " class=\"$classes\""  : '';

		$hidden = $this->hidden ? 'style="display:none;" ' : '';

		$html = (string)$this;

		if( $this->readonly ) {
			$html = '<div class="uneditable-input">' . $html . '</div>';
		}

		$html = "<li " . $hidden . $classes . ">" . $this->label() . $html . ' ' . '</li>';
		return $html;
	}//end as_li

	/**
	 * Generate a help link for this element and return the result as HTML.
	 */
	public function help()
	{
		if(!isset($this->help))
			return '';

		$html = sprintf('<a href="%s" target="%s" class="helper" title="%s">%s</a>', $this->help_url,
			$this->help_target, htmlentities($this->help), $this->help_display);

		return $html;
	}//end help

	/**
	 * Generate an ADOdb DataDictionary-compatible schema definition for this model.
	 */
	public function schema( $args = null )
	{
		$defaults = array(
			'class' => get_class($this),
			'default' => $this->default,
			'length' => $this->maxlength instanceof HTMLAttribute ? $this->maxlength->value : $this->maxlength,
			'name' => $this->name->value,
			'type' => $this->adodb_type
		);

		// $args needs to be an array. either take what we were given, convert
		// a string to an array, or use an empty array.
		$args = $args == null ? array() : (is_string($args) ? parse_str($args) : $args);
		$args = array_merge($defaults, $args);

		if( is_string($args['default']) )
			$args['default'] = "'" . $args['default'] . "'";
		elseif( $args['default'] === null )
			$args['default'] = 'NULL';

		//
		// return the schema
		//

		if( $args['length'] )
			$args['type'] = sprintf("%s(%d)", $args['type'], $args['length']);

		return sprintf("%-30s %-8s NULL DEFAULT %s /* %s */", $args['name'], $args['type'], $args['default'], $args['class']);
	}//end schema

	/**
	 * Returns true if this field's value passes validation. This function will always return true.
	 * @exception PSU\Model\ValidationException Thrown if validation fails.
	 * @return boolean
	 */
	public function validate( $return = true )
	{
		$filter_class = $this->clean_class();
		$action = $filter_class . '_validate_' . $this->name->value;
		if( \PSU::has_action( $action ) ) {
			\PSU::do_action( $action , $this );
		}//end if

		return $return;
	}//end validate

	/**
	 * Test if this field's value property is set to a specific value.
	 * @param $v The value to test for.
	 */
	public function in_value($v)
	{
		return $this->value() == $v;
	}//end in_value
}
