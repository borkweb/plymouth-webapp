<?php

namespace PSU\Model;

/**
 */
class Repeater extends \PSU_Hookable implements \Iterator, \ArrayAccess, \Countable
{
	public $models;
	public $field_base;
	public $class;

	public $datastore;
	public $filemanager;

	public function __construct( $datastore = null, $filemanager = null, $class)
	{
		$this->models = array();

		$this->datastore = $datastore;
		$this->filemanager = $filemanager;

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
	 * Create a new child of this repeatingmodel.
	 */
	public function create_child( $index = null ) {
		$class = $this->class;
		$c = new $class( $this->datastore, $this->filemanager );
		$c->set_index( $this->field_base, $index );

		return $c;
	}//end dummy

	public function indexes() {
		return array_keys( $this->models );
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
	public function add($index = null)
	{
		$c = $this->create_child( $index );
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
}//end PSU_Model_Repeater
