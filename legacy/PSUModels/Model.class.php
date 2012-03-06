<?php

/**
 * The PSUModel framework: a way of dealing with data object, and outputting them easily as HTML forms.
 *
 * @defgroup psumodels PSUModels
 * @{
 */

require_once('FormSelect.class.php');
require_once('FormText.class.php');
require_once('FormTextarea.class.php');
require_once('FormNumber.class.php');
require_once('FormFile.class.php');
require_once('FormDate.class.php');
require_once('FormDatetime.class.php');
require_once('FormCheckbox.class.php');
require_once('FormRadio.class.php');
require_once('FormEmail.class.php');
require_once('FormSignoff.class.php');
require_once('FormSelectStates.class.php');

/**
 *
 */
class Model extends HookableClass
{
	public $data = array();
	public $serialize_include = array();

	/**
	 * @param $f a form for populating element values
	 * @param $privileged whether or not the incoming form is from a privileged user
	 */
	public function __construct($f = array(), $privileged = false)
	{
		$this->validation_errors = array();
		$this->set_from_form($f, $privileged);
	}

	/**
	 * Debugging function.
	 */
	public static function debug($msg)
	{
		file_put_contents('/web/temp/zzgrad', date('c') . ' php[' . getmypid() . '] ' . $msg . "\n", FILE_APPEND);
	}

	public function set_from_form($f = array(), $privileged = false)
	{
		if(count($f) == 0)
			return;

		foreach($f as $k => $v)
		{
			//
			// the models are completely defined before reaching this point. for each incoming
			// value from $f, we will...
			//

			// simple scalar: $f['field'] = 8;
			if($this->$k instanceof FormField)
			{
				// don't allow setting privileged fields in a non-privileged load
				if($this->$k->privileged && !$privileged)
				{
					continue;
				}

				try
				{
					$this->$k->value($v);
				}
				catch(ValidationException $e)
				{
					$this->validation_errors[] = $e->getMessage() . ": " . $v;
				}
			}

			// RepeatingModel: $f['phones'] = array(0 => array('number' => '555-1212', 'type' => 'personal'), 1 => ...)
			elseif($this->$k instanceof RepeatingModel)
			{
				$class = $this->$k->class;

				// remove all existing values from the repeatingmodel
				foreach($v as $index => $subform)
				{
					if(isset($this->{$k}[$index]))
					{
						$m = $this->{$k}[$index];
						$this->{$k}[$index]->set_from_form($subform, $privileged);
					}
					else
					{
						$m = $this->{$k}[$index] = new $class($subform, $privileged);
					}

					$this->validation_errors = array_merge($this->validation_errors, $m->validation_errors);
					$m->validation_errors = array();
				}
			}

			elseif($privileged == true)
			{
				// only set non-formfield data if the source is privileged
				$this->$k = $v;
			}
		}

		$this->highlight_fields();
	}

	/**
	 * Enable or disable all form fields.
	 */
	public function disabled($flag)
	{
		$disabled = $flag ? 'disabled' : '';
		foreach($this->data as $e)
		{
			if($e instanceof FormField)
			{
				$e->disabled->value = $disabled;
			}
			elseif($e instanceof RepeatingModel)
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
			if($p instanceof FormField)
			{
				$elements[] =& $p;
			}
		}
		return $elements;
	}

	/** @cond NEVER */
	public function __isset($key) { return isset($this->data[$key]); }
	public function __unset($key) { unset($this->data[$key]); }

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
			if($this->$key instanceof FormField && ! $value instanceof FormField)
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

		if($value instanceof FormField) {
			// formfields get some default values if none were specified
			
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
		} elseif($value instanceof RepeatingModel) {
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
	 * This function is intended to be used in children of RepeatingModels only. It modified all field names
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
			if(! $p instanceof FormField)
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
			}
		}

		foreach($this->data as $m)
		{
			if(! $m instanceof RepeatingModel)
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
			if( $e instanceof FormField )
				$e->readonly = $flag;
			elseif( $e instanceof RepeatingModel )
				$e->readonly($flag);
		}
	}//end readonly

	/**
	 * Get or set the the contents of this model. Interfaces with Model::serialize() for getting, and Model::set_from_form() for setting.
	 *
	 * @sa serialize
	 * @sa set_from_form
	 */
	public function form( $f = null )
	{
		if( $f === null )
		{
			return $this->serialize();
		}

		return $this->set_from_form( $f );
	}//end form

	/**
	 * Dump the ADOdb data dictionary-compatible definition for this model.
	 */
	public function schema()
	{
		$children = array();

		foreach($this->data as $k => $v)
		{
			if($v instanceof FormField)
			{
				$schema .= $v->schema() . ",\n";
			}
			elseif($v instanceof RepeatingModel)
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

		if( ! $this->$field instanceof FormField )
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
}//end Model

/**
 */
class RepeatingModel extends HookableClass implements Iterator, ArrayAccess, Countable
{
	public $models;
	public $field_base;
	public $class;

	public function __construct($class)
	{
		$this->models = array();

		$this->field_base = '';
		$this->class = $class;
	}

	/**
	 * Enable or disable all form fields.
	 */
	public function disabled($flag)
	{
		foreach($this->models as $m)
		{
			$m->disabled($flag);
		}
	}

	/**
	 * Return the progress for all child models.
	 */
	public function progress()
	{
		$required = $filled = $percent = 0;

		foreach($this->models as $m)
		{
			list($r, $f, $p) = $m->progress();

			$required += $r;
			$filled += $f;
		}

		if($required > 0)
		{
			$percent = (float)$filled / $required;
		}

		return array($required, $filled, $percent);
	}

	/**
	 *
	 */
	public function readonly($flag)
	{
		foreach($this->models as $m)
		{
			$m->readonly($flag);
		}
	}//end readonly

	/**
	 * Remove all child models.
	 */
	public function reset()
	{
		$this->models = array();
	}

	/**
	 * Add a new model to the end of the model list.
	 * @param      $index your index, or null (the default) to auto-generate an index in 1-up mode
	 * @param      $f the array of data to load into the model
	 * @param      $privileged whether or not the incoming form is privileged
	 */
	public function add($index = null, $f = array(), $privileged = false)
	{
		$class = $this->class;
		$c = new $class($f, $privileged);
		$this[$index] = $c;

		return $c;
	}//end add

	/**
	 * Alias for serialize().
	 */
	public function form()
	{
		return $this->serialize();
	}//end form

	/**
	 * Return an array of arrays containing model data.
	 *
	 * @return    array the array of data
	 */
	public function serialize()
	{
		$models = array();

		foreach($this->models as $index => $model)
		{
			$models[$index] = $model->serialize();
		}

		return $models;
	}//end serialize

	/** Iterator functions. */
	public function rewind() { reset($this->models); }
	public function current() { return current($this->models); }
	public function key() { return key($this->models); }
	public function next() { return next($this->models); }
	public function valid() { return $this->current() !== false; }

	/** ArrayAccess functions */
	public function offsetExists($offset) { return isset($this->models[$offset]); }

	public function offsetUnset($offset)
	{
		unset($this->models[$offset]);
		$this->updateZeroIndexes();
	}

	public function offsetGet($offset) {
		if(isset($this->models[$offset]))
		{
			return $this->models[$offset];
		}

		return null;
	}

	public function offsetSet($offset, $value) {
		if( $offset == null )
		{
			$offset = count($this);
			while( isset($this[$offset]) )
			{
				$offset++;
			}
		}
		
		$this->models[$offset] = $value;
		$this->models[$offset]->set_index($this->field_base, $offset);

		$this->updateZeroIndexes();

		$this->models[$offset]->do_hook('index_set');
	}


	/** Countable function */
	public function count()
	{
		return count($this->models);
	}

	public function getIterator()
	{
		return $this;
	}

	/**
	 * Get the model at the numbered index, starting from zero. Ignores the model's actual $index value.
	 * Useful for getting the first, second, etc. model.
	 */
	public function modelAtZeroIndex($idx)
	{
		foreach($this->models as $m)
		{
			if($m->zeroIndex == $idx)
			{
				return $m;
			}
		}

		return null;
	}//end zeroIndex

	/**
	 * Update the zero-based indexes of our models.
	 */
	public function updateZeroIndexes()
	{
		$i = 0;
		foreach($this->models as $m)
		{
			$m->zeroIndex = $i++;
		}
	}//end updateZeroIndexes
}//end RepeatingModel

/**
 */
class HookableClass
{
	public $hooks = array();

	/** Add a hook to the queue. */
	public function add_hook($name, $function, $priority = 50)
	{
		if(!isset($this->hooks[$name]))
		{
			$this->hooks[$name] = array();
		}

		if(!isset($this->hooks[$name][$priority]))
		{
			$this->hooks[$name][$priority] = array();
		}

		$this->hooks[$name][$priority][] = $function;
	}//end add_hook

	/** Run a hook. */
	public function do_hook($name)
	{
		if(!isset($this->hooks[$name]))
		{
			return;
		}

		// order the hooks by priority (the key)
		ksort($this->hooks[$name]);

		foreach($this->hooks[$name] as $pri => $funcs)
		{
			foreach($funcs as $func)
			{
				call_user_func(array($this, $func));
			}
		}
	}
}//end HookableClass

/** @} */
