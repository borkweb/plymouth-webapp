<?php

require_once dirname(__FILE__) . '/MyMeta.class.php';

/**
 * A store for MyMeta objects.
 *
 * The following special keys are considered invalid for meta key names:
 *
 * \li parent
 * \li container
 */
class MyMetaContainer extends MyMagicGetters {
	/**
	 * The parent MyPortalObject.
	 */
	private $parent;

	/**
	 * Consulted when setting $meta->changed.
	 * @sa __set
	 * @sa setDefaultChanged
	 */
	public $defaultChanged = false;

	/**
	 * Overrride setter to automatically create MyMeta objects.
	 */
	public function __set($k, $v) {
		//
		// preexisting meta key
		//
		if( isset($this->data[$k]) ) {
			if( $v instanceof MyMeta ) {
				$this->data[$k] = $v;

				$this->data[$k]->container($this);
				$this->data[$k]->parent($this->parent);

				/// @todo for now, just update changed if we replaced MyMeta. room for optimization here? (check id + value?)
				$this->data[$k]->changed = $this->defaultChanged;
			} elseif( $this->data[$k]->value != $v ) {
				// only update if the value changed (also, update $m->changed)
				$this->data[$k]->value = $v;
				$this->data[$k]->changed = $this->defaultChanged;
			}//end if
		}
		
		//
		// new meta key (doesn't exist in container)
		//
		else {
			$meta = null;

			if( ! $v instanceof MyMeta ) {
				$meta = new MyMeta( $v, $k );
			} else {
				$meta = $v;
			}

			$meta->container($this);
			$meta->parent($this->parent);

			$this->data[$k] = $meta;
		}

		// if meta does not have a class associated with it, default to container's parent class
		if( !isset($this->data[$k]->class) ) {
			$this->data[$k]->class = get_class($this->parent);
		}
	}//end __set

	/**
	 * Get all meta from the object. Simply returns $this->data, since we enforce
	 * MyMeta objects in data.
	 */
	public function get() {
		return $this->data;
	}//end $return

	/**
	 * Load metadata from the database into this container. By default, load() will use $this
	 * to determine which table to pull from, but that can be overridden if you need
	 * to load metadata from another object, for example a UserChannel loading meta from its
	 * base channel.
	 *
	 * @param $id \b int the id of the target object. defaults to $this->id
	 * @param $obj_or_class \b MyPortalObject|string the object or class name of the target object
	 * @param $replace \b bool whether or not to replace existing meta with the new meta
	 */
	public function load( $id = null, $obj_or_class = null, $replace = false) {
		if( $id === null ) {
			$id = $this->parent->id;
		}

		if( $obj_or_class === null ) {
			$class = get_class($this->parent);
		} elseif( is_object($obj_or_class) ) {
			$class = get_class($obj_or_class);
		} else {
			$class = $obj_or_class;
		}

		// true/false whether or not class we're pulling for is the same as class
		// we're an instance of. this is used to determine if meta unique ids should
		// be passed to the new meta object. otherwise, inherited meta would have
		// the same id as its parent, which could cause conflicts.
		$type_matches = $class == get_class($this->parent);


		$sql = sprintf("
			SELECT *
			FROM %s
			WHERE %s = %d
			", MyPortalObject::dbstr($class, 'meta'), MyPortalObject::dbstr($class, 'fk'), $id);

		$rset = PSU::db('portal')->Execute($sql);

		foreach($rset as $row) {
			if( !$type_matches ) {
				$row['id'] = null;
			}

			if( !isset($this->{$row['meta_key']}) || $replace ) {
				$this->{$row['meta_key']} = new MyMeta( $row['meta_value'], $row['meta_key'], $row['id'] );
				$this->{$row['meta_key']}->class = $class;
			}
		}
	}//end load

	/**
	 * Get or set the parent.
	 */
	public function parent( $parent = null ) {
		if( $parent !== null ) {
			$this->parent = $parent;
		}

		return $this->parent;
	}//end parent

	/**
	 * Function to save this meta collection to the database. Will only save meta marked as "changed."
	 */
	public function save() {
		// cache prepared statement-style queries per table
		$statements = array();

		$base_query = "
			INSERT INTO %s (id, %s, meta_key, meta_value, activity_date)
			VALUES (?, ?, ?, ?, NOW())
			ON DUPLICATE KEY UPDATE meta_value = ?, activity_date = NOW()
		";

		// all metadata in this container gets saved to the same table
		$class = get_class($this->parent);

		if( !isset($statements[$class]) ) {
			$statements[$class] = sprintf($base_query, MyPortalObject::dbstr($class, 'meta'), MyPortalObject::dbstr($class, 'fk'));
		}

		foreach($this->data as $meta ) {
			if( ! $meta instanceof MyMeta ) {
				continue;
			}

			if( ! $meta->changed ) {
				continue;
			}

			PSU::db('portal')->Execute($statements[$class], array($meta->id, $this->parent->id, $meta->key, $meta->value, $meta->value));
		}
	}//end save

	/**
	 * Update the defaultChanged value. Pass "false" if you want MyMeta value updates to
	 * have their "changed" property left alone. Example:
	 *
	 * <pre><code>$mc = new MyMetaContainer;
	 *$mc->setDefaultChanged(false);
	 *$mc->foo = 12;
	 *$mc->foo = 13;
	 *var_dump($mc->foo->changed); // bool(false)
	 *
	 *$mc->setDefaultChanged(true);
	 *$mc->foo = 14;
	 *var_dump($mc->foo->changed); // bool(true)</code></pre>
	 *
	 * Use this if you need to prepopulate meta and you know the incoming values have
	 * not been changed from their state in whatever store you are using. (For example,
	 * if you are merging meta from two sources and their is potential for key overlap.)
	 */
	public function setDefaultChanged( $bool ) {
		$this->defaultChanged = $bool;
	}//end setDefaultChanged
}//end class MyMetaContainer
