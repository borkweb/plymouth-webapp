<?php

/**
 * The PSUModel framework: a way of dealing with data object, and outputting them easily as HTML forms.
 *
 * @defgroup psumodels PSUModels
 * @{
 */

namespace PSU;

use PSU;

/**
 * A model of some object.
 */
class Model extends \PSU_Hookable
{
	public $data = array();
	public $serialize_include = array();
	private $_incomplete_fields = array();

	public $_form = null;

	/**
	 * @param $datastore PSU\Model\Datastore
	 * @param $filemanager PSU\Model\Filemanager
	 */
	public function __construct( $datastore = null, $filemanager = null ) {
		$this->validation_errors = array();

		$this->datastore( $datastore );
		$this->filemanager( $filemanager );
	}//end __construct

	/**
	 * determines whether or not the model has been completed (based on required fields)
	 */
	public function complete() {
		list( $required, $filled, $percent ) = $this->progress();

		return $percent && $percent >= 1;
	}//end complete

	/**
	 * returns the incomplete fields in the model
	 */
	public function incomplete_fields() {
		return $this->_incomplete_fields;
	}//end incomplete_fields

	/**
	 * Save this model to the datastore.
	 */
	public function save() {
		return $this->datastore()->save( $this );
	}//end save

	/**
	 * Populate model by loading from the datastore.
	 */
	public function load( $id ) {
		$f = $this->datastore()->load( $this, $id );
		$this->_set_form( $f, true );
	}//end load

	/**
	 * Handle the incoming $_FILES array.
	 *
	 * @param $files array the $_FILES array
	 */
	public function uploads( $files ) {
		foreach( $files as $field_name => $file ) {
			// field doesn't exist
			if( ! isset( $this->$field_name ) ) {
				continue;
			}

			// field isn't a formfile
			if( ! ( $this->$field_name instanceof Model\FormFile ) ) {
				continue;
			}

			// nothing uploaded
			if( UPLOAD_ERR_NO_FILE == $file['error'] ) {
				continue;
			}

			$field = $this->$field_name;
			$identifier = $this->filemanager()->upload( $field, $file );
			$field->fileid = $identifier;
		}
	}//end uploads

	/**
	 * Getter/setter for the filemanger.
	 */
	public function filemanager( $new = null ) {
		static $filemanager = null;

		if( $new instanceof Model\Filemanager ) {
			$filemanager = $new;
			$new->parent( $this );
		}

		return $filemanager;
	}//end filemanager

	/**
	 * Getter/setter for the filemanger.
	 */
	public function datastore( $new = null ) {
		static $datastore = null;

		if( $new instanceof Model\Datastore ) {
			$datastore = $new;
		}

		return $datastore;
	}//end datastore

	/**
	 * Set values from a form.
	 */
	public function _set_form( $f = array(), $privileged = false )
	{
		$this->_form = $f;

		if(count($f) == 0)
			return;

		$deletes = isset( $f['_delete_files'] ) ? array_keys( $f['_delete_files'] ) : array();
		unset( $f['_delete_files'] );

		// track which repeatingmodels we iterated over
		$repeatingmodels_seen = array();

		foreach( $f as $k => $v )
		{
			//
			// the models are completely defined before reaching this point. for each incoming
			// value from $f, we will...
			//

			// simple scalar: $f['field'] = 8;
			if($this->$k instanceof Model\FormField)
			{
				// don't allow setting privileged fields in a non-privileged load
				if( $this->$k->privileged && ! $privileged ) {
					continue;
				}

				// don't allow setting formfiles like this, they are set through upload()
				if( $this->$k instanceof Model\FormFile && ! $privileged ) {
					continue;
				}

				try {
					$this->$k->value($v);
				} catch(ValidationException $e) {
					$this->validation_errors[] = $e->getMessage() . ": " . $v;
				}
			}

			// RepeatingModel: $f['phones'] = array(0 => array('number' => '555-1212', 'type' => 'personal'), 1 => ...)
			elseif($this->$k instanceof Model\Repeater)
			{
				$repeatingmodels_seen[$k] = true;

				$class = $this->$k->class;
				$indexes = array();

				foreach($v as $index => $subform)
				{
					// log the indexes we got from the incoming form
					$indexes[] = $index;
					$rm = $this->$k;

					if(isset($rm[$index])) {
						$m = $this->{$k}[$index];
						$m->_set_form( $subform, $privileged );
					} else {
						$m = $this->$k->add( $index );
						$m->_set_form( $subform, $privileged );
					}

					$this->validation_errors = array_merge($this->validation_errors, $m->validation_errors);
					$m->validation_errors = array();
				}

				$remove = array_diff( $this->$k->indexes(), $indexes );

				foreach( $remove as $index ) {
					// cannot unset( $this->$k[$index] ), must use an intermediary
					$rm = $this->$k;
					unset( $rm[$index] );
				}
			}

			elseif($privileged == true)
			{
				// only set non-formfield data if the source is privileged
				$this->$k = $v;
			}
		}

		$repeatingmodels_unseen = array_diff_key( $this->repeatingmodels(), $repeatingmodels_seen );

		// if form did not set values in repeatingmodel children, then clear all those children
		foreach( $repeatingmodels_unseen as $model ) {
			$model->reset();
		}

		$this->do_hook( 'post_set_form' );

		$this->highlight_fields();

		if( $this->filemanager() ) {
			$this->filemanager()->delete( $deletes );
		}
	}//end _set_form

	/**
	 * Enable or disable all form fields.
	 */
	public function disabled($flag)
	{
		$disabled = $flag ? 'disabled' : '';
		foreach($this->data as $e)
		{
			if($e instanceof Model\FormField)
			{
				$e->disabled->value = $disabled;
			}
			elseif($e instanceof Model\Repeater)
			{
				$e->disabled($flag);
			}
		}
	}

	/**
	 * Return a list of this model's elements.
	 * @return      array   an array of FormFields
	 */
	public function elements()
	{
		$elements = array();
		foreach($this->data as &$p)
		{
			if($p instanceof Model\FormField)
			{
				$elements[] =& $p;
			}
		}
		return $elements;
	}

	/**
	 * Return a list of this model's repeatingmodel children.
	 */
	public function repeatingmodels() {
		$rm = array();

		foreach( $this->data as $key => $m ) {
			if( $m instanceof Model\Repeater ) {
				$rm[ $key ] = $m;
			}
		}

		return $rm;
	}//end repeatingmodels

	/** @cond NEVER */
	public function __isset($key) {
		return isset($this->data[$key]);
	}

	/** @cond NEVER */
	public function __unset($key) {
		unset($this->data[$key]);
	}

	/**
	 * Get a property. Return by reference, else array operations ($obj->data[] = 9) will fail.
	 *
	 * @param     $key the property name. Properties are considered object elements unless the name is prepended by an underscore.
	 */
	public function &__get($key)
	{
		// this fails as a one-liner ternary return. /boggle

		if(isset($this->data[$key]))
		{
			return $this->data[$key];
		}

		return $n = null;
	}//end __get

	/**
	 * Set a property.
	 *
	 * @param $key the property name. Properties are considered object elements unless the name is prepended by an underscore.
	 * @param $value the value for the property
	 */
	public function __set($key, $value)
	{
		// was the key already set?
		if(isset($this->$key))
		{
			if($this->$key instanceof Model\FormField && ! $value instanceof Model\FormField)
			{
				// $key is formfield, $value is scalar: we're updating the value. lets you do this:
				//    $m->name = new FormText();
				//    $m->name = 'Joe'; // set $m->name->value('Joe')

				$this->$key->value($value);
				return;
			}
		}

		//
		// setting a new key
		//

		if($value instanceof Model\FormField) {
			// formfields get some default values if none were specified

			$value->model( $this );
			
			// fields ending in underscores are privileged by default
			if( substr($key, -1) === '_' )
				$value->privileged = true;

			if(isset($value->name)) {
				if( empty($value->name->value) ) {
					$value->name->value = $key;
				}
			} else {
				$value->name = new HTMLAttribute('name', $key);
			}

			if(isset($value->id)) {
				if( empty($value->id->value) ) {
					$value->id->value = $key;
				}
			} else {
				$value->id = new HTMLAttribute($key);
			}

			if(!isset($value->label) || empty($value->label))
				$value->label = $this->name_to_label($key);
		} elseif($value instanceof Model\Repeater) {
			$value->field_base = $key;
		}

		$this->data[$key] = $value;
	}//end __set

	/** @endcond */

	public function name_to_label($name)
	{
		// rtrim for privileged fields that end in underscore
		$name = rtrim( str_replace("_", " ", $name) );
		$name = ucwords($name) . ":";
		return $name;
	}

	/**
	 * Highlight all required fields that are missing data.
	 */
	public function highlight_fields()
	{
		foreach($this->elements() as $e)
		{
			if($e->required && $e->is_empty())
			{
				$e->addClass('highlight');
			}
			else
			{
				$e->removeClass('highlight');
			}
		}
	}//end highlight_fields

	/**
	 * Assign an index to a repeating model child.
	 *
	 * This function is intended to be used in children of RepeatingModels only. It modifies all field names
	 * and ids to prevent collisions between same-named fields in sibling models.
	 *
	 * @param $basename the base name of the field, prepended to tag name and id
	 * @param $i the index
	 */
	public function set_index($basename, $i)
	{
		$this->basename = $basename;
		$this->lastindex = $this->index;
		$this->index = $i;

		foreach($this->data as $k => $p)
		{
			if(! $p instanceof Model\FormField)
			{
				continue;
			}

			$this->$k->name->value = "{$basename}[$i][$k]";
			$this->$k->id->value = "{$basename}_{$i}_$k";
		}
	}//end set_index

	/**
	 * Output any classes for this model's form.
	 */
	public function classes() {
		list( $required, $filled, $percent ) = $this->progress();

		// highlight missing form fields if we have partial entry
		if( $percent > 0 ) {
			return 'highlight';
		}

		return '';
	}//end class

	/**
	 * Return an array noting how many fields are required, and how many are filled in, and the percent complete.
	 */
	public function progress()
	{
		$this->_incomplete_fields = array();

		$required = $percent = $filled = 0;
		foreach($this->elements() as $e)
		{
			if($e->required)
			{
				$required++;
				if(!$e->is_empty())
				{
					$filled++;
				}
				else 
				{
					$this->_incomplete_fields[ $e->name() ] = $e;
				}
			}
		}

		foreach($this->data as $m)
		{
			if(! $m instanceof Model\Repeater)
			{
				continue;
			}

			list($r, $f, $p) = $m->progress();
			$required += $r;
			$filled += $f;
		}

		if($required > 0)
		{
			$percent = (float)$filled / $required;
			$percent = round($percent, 2);
		}

		return array($required, $filled, $percent);
	}//end progress

	/**
	 * Set the readonly flag on all FormFields within this model.
	 */
	public function readonly($flag)
	{
		foreach($this->data as $k => $e) {
			if( $e instanceof Model\FormField )
				$e->readonly = $flag;
			elseif( $e instanceof Model\Repeater )
				$e->readonly($flag);
		}
	}//end readonly

	/**
	 * Get or set the the contents of this model. Interfaces with Model::serialize() for getting, and Model::_set_form() for setting.
	 *
	 * @sa serialize
	 * @sa _set_form
	 */
	public function form( $f = null )
	{
		if( $f === null )
		{
			return $this->serialize();
		}

		return $this->_set_form( $f );
	}//end form

	/**
	 * Dump the ADOdb data dictionary-compatible definition for this model.
	 */
	public function schema()
	{
		$children = array();

		foreach($this->data as $k => $v)
		{
			if($v instanceof Model\FormField)
			{
				$schema .= $v->schema() . ",\n";
			}
			elseif($v instanceof Model\Repeater)
			{
				$class = $v->class;
				$c = new $class;

				list($children[$class], ) = $c->schema();

				unset($c);
			}
		}

		$schema = rtrim($schema, "\n,");
		return array($schema, $children);
	}//end schema

	/**
	 * Serialize the model. Data will only be serialized if it is scalar or has a serialize() method.
	 *
	 * @return array an array representation of the model which can be used to reconstruct the object
	 */
	public function serialize()
	{
		$serialized = array();

		foreach($this->data as $k => $v)
		{
			if(is_object($v) && method_exists($v, 'serialize'))
			{
				$serialized[$k] = $v->serialize();
			}

			// if it's not whitelisted, skip it
			elseif( !in_array($k, $this->serialize_include) )
			{
				continue;
			}

			// whitelisted property, carry on

			elseif(is_scalar($v))
			{
				$serialized[$k] = $v;
			}
			elseif(is_array($v))
			{
				switch(count($v))
				{
					case 0: $serialized[$k] = null; break;
					default: $serialized[$k] = $v;
				}
			}
		}

		return $serialized;
	}//end serialize

	/**
	 * Shortcut for $model->field->value(). Example usage: $model->v('field');
	 * @param $field the field name
	 */
	public function v( $field )
	{
		if( ! isset($this->$field) ) 
			throw new Exception("field does not exist: $field");

		if( ! $this->$field instanceof Model\FormField )
			throw new Exception("field is not a formfield: $field");

		return $this->$field->value();
	}//end v

	/** @cond NEVER */
	public function rewind() { reset($this->data); }
	public function current() { return current($this->data); }
	public function key() { return key($this->data); }
	public function next() { return next($this->data); }
	public function valid() { return $this->current() !== false; }
	/** @endcond */
}//end PSU_Model

/** @} */
