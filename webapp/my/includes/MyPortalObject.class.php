<?php

require_once dirname(__FILE__) . '/MyMagicGetters.class.php';
require_once dirname(__FILE__) . '/MyMetaContainer.class.php';

/**
 * Abstract class to define behavior of our PortalObjects.
 *
 * @subsection meta Meta
 *
 * Portal objects can store metadata. This data is stored internally in an object,
 * and aliased into the MyPortalObject for quick access.
 *
 * To add metadata, use $obj->meta()->foo = 1. To read metadata, use either $obj->meta->foo
 * when __toString() will be consulted (ie. in a template) or use <code>$obj->meta->foo->value</code>.
 */
abstract class MyPortalObject extends MyMagicGetters {
	/**
	 * The parent object.
	 */
	private $parent = null;

	/**
	 * The meta container.
	 */
	private $meta = null;

	/**
	 * Function to translate a MyPortalObject class name or instance, plus a
	 * kind of database object, into the string that identifies that object.
	 * Used to abstract table and column names from underlying functions so that
	 * common methods can be employed regardless of the child class.
	 *
	 * Example:
	 *
	 * <code>echo MyPortalObject::dbstr('MyUserChannel', 'fk'); // = userchannel_id</code>
	 *
	 * @param $obj_or_class \b MyPortalObject|string the identifying object. allows $this
	 * @param $kind \b string table, fk, meta or targets
	 */
	public static function dbstr( $obj_or_class, $kind ) {
		static $tables = array(
			'MyChannel' => 'channel',
			'MyTab' => 'tab',
			'MyUserChannel' => 'userchannel',
			'MyUserTab' => 'usertab',

			'mychannel' => 'channel',
			'mytab' => 'tab',
			'myuserchannel' => 'userchannel',
			'myusertab' => 'usertab',
		);

		$class = is_string($obj_or_class) ? $obj_or_class : get_class($obj_or_class);

		if( !isset($tables[$class]) ) {
			throw new Exception('Unknown class: ' . $class);
		}

		$base = $tables[$class];

		switch($kind) {
			case 'table': return $base . 's';
			case 'fk': return $base . '_id';
			case 'meta': return $base . 's_meta';
			case 'targets': return $base . 's_targets';
			case 'type': return str_replace('user', '', $base);
			default: throw new Exception('Unknown kind: ' . $kind);
		}
	}//end dbstr

	/**
	 * Deletes a user channel or tab
	 */
	public function delete( $id, $class ) {
		$sql = "INSERT INTO deletes (wp_id, object_type) VALUES (?, ?)";
		PSU::db('portal')->Execute($sql, array($GLOBALS['identifier'], self::dbstr($class, 'type')));

		if( $delete_id = PSU::db('portal')->Insert_ID() )
		{
			$channel = MyUserChannel::fetch($id);
			$channel->delete_id = $delete_id;
			$channel->save();
		}//end if

		return $delete_id;
	}//end delete

	/**
	 * Fetch a channel or tab into the portal object
	 */
	public static function fetchRow( $id, $class ) {
		$sql = sprintf("
			SELECT *
			FROM %s
			WHERE id = %d
			", self::dbstr($class, 'table'), $id);
	
		$rset = PSU::db('portal')->GetRow($sql);
		
		return $rset;
	}

	public static function fetch( $id, $class ) {
		$rset = self::fetchRow( $id, $class );
		$o = new $class($rset);
		return $o;
	}//end fetch

	/**
	 * Function to get meta, with autoinitialization.
	 */
	public function meta() {
		if( $this->meta === null ) {
			$this->meta = new MyMetaContainer;
			$this->meta->parent($this);
		}

		return $this->meta;
	}//end meta

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
	 * Function to execute save logic. If you need a save that does appropriate
	 * user validation you should call save() instead
	 */
	public function _save() {
		static $columns = array();
		static $ignore_columns = array('id', 'activity_date');
		
		// build column list for this class's table
		if( !isset($columns[$class]) ) {
			$columns[$class] = array();

			$sql = sprintf("DESCRIBE %s", self::dbstr($this, 'table'));
			$rset = PSU::db('portal')->Execute($sql);
			
			foreach( $rset as $row ) {
				$field = $row['Field'];

				// skip certain columns
				if( in_array($field, $ignore_columns) ) {
					continue;
				}

				$columns[$class][] = $row['Field'];
			}
		}

		//
		// build the query and save the record
		// 

		$save_vals = array();
		$save_cols = array();

		if( $this->id ) {
			// doing an update
			
			foreach( $columns[$class] as $fieldname ) {
				if ( isset($this->$fieldname )) {
					$save_cols[] = sprintf("%s = %s", $fieldname, PSU::db('portal')->qstr($this->$fieldname));
				}
			}

			$save_cols = implode(',', $save_cols);

			$sql = sprintf("UPDATE %s SET %s WHERE id = %d", self::dbstr($this, 'table'), $save_cols, $this->id);
		} else {
			// doing an insert

			foreach( $columns[$class] as $fieldname ) {
				if( isset($this->$fieldname) ) {
					$save_cols[] = $fieldname;
					$save_vals[] = PSU::db('portal')->qstr($this->$fieldname);
				}
			}

			$save_cols = implode(",", $save_cols);
			$save_vals = implode(",", $save_vals);

			$sql = sprintf("INSERT INTO %s (%s) VALUES (%s)", self::dbstr($this, 'table'), $save_cols, $save_vals);
		}

		$result = PSU::db('portal')->Execute($sql);

		if( !$this->id ) {
			// did an insert. get the id
			$this->id = PSU::db('portal')->Insert_ID();
		}

		// TODO: error handling

		$this->meta()->save();
	}//end _save
	
	/**
	 * Function to save or update object data back into the database
	 */
	public function save() {
		$class = get_class($this);
		$portal = new MyPortal($GLOBALS['identifier']);

		//
		// check the database to ensure this user owns the tab in question
		// 
		// TODO: do we have to instantiate the whole MyPortal object?
		//
		if(self::dbstr($class, 'type') == 'channel') {
			if($portal->tabs($this->usertab_id, false)->wp_id != $GLOBALS['identifier']) {
				if(!$portal->is_admin()){
					return false;
				}
			}	
		}//end if
		elseif(self::dbstr($class, 'type') == 'tab') {
			if($this->wp_id != $GLOBALS['identifier'])
				return false;
		}//end elseif

		// if we've passed all the validation checks, save the data
		$this->_save();
	}//end save

	/**
	 * Function to save targeting data of a channel, usertab, or tab
	 */
	public static function save_targets( $new_targets, $id = null, $class ) {
		$sql = sprintf("SELECT target_id FROM %s WHERE %s = %d", self::dbstr($class, 'targets'), self::dbstr($class, 'fk'), $id);
		$old_targets = PSU::db('portal')->GetCol($sql);
		if( $new_targets != null ) {
			$targets_to_delete = array_diff( $old_targets, $new_targets );
			$targets_to_add = array_diff( $new_targets, $old_targets );

			if( $targets_to_delete != null ) {
				foreach($targets_to_delete as $key => $value) {
					$sql = sprintf(
						"DELETE FROM %s WHERE %s = %d AND target_id = %d", 
						self::dbstr($class, 'targets'), 
						self::dbstr($class, 'fk'), 
						$id,
						$value
					);
					PSU::db('portal')->Execute($sql);
				}
			}
			
			if( $targets_to_add != null ) {
				foreach($targets_to_add as $key => $value) {
					$sql = sprintf(
						"INSERT INTO %s (%s, target_id) VALUES (%d, %d)", 
						self::dbstr($class, 'targets'), 
						self::dbstr($class, 'fk'), 
						$id,
						$value
					);
					PSU::db('portal')->Execute($sql);
				}
			}
		}else {
			return false;
		}	
	}

	/**
	 * Get or set targeting status. Protected, single-instance method.
	 */
	protected static function _use_targeting( $targeting = null ) {
		static $value = true;

		if( $targeting !== null ) {
			$value = $targeting;
		}

		return $value;
	}//end _use_targeting

	/**
	 * Public interface to targeting getter/setter. Ensures that all inherited classes
	 * access the same value.
	 */
	public static function use_targeting( $targeting = null ) {
		return MyPortalObject::_use_targeting( $targeting );
	}//end use_targeting

	/**
	 * function to lazy load target data for a channel or tab
	 */
	public static function targets( $id, $class ) {
		$sql = sprintf("SELECT target_id FROM %s WHERE %s = %d", self::dbstr($class, 'targets'), self::dbstr($class, 'fk'), $id);
		return PSU::db('portal')->GetCol($sql);
	} //end targets

	/**
	 * function to lazy load target names data for a channel or tab
	 */
	public static function targetNames( $id, $class ) {
		$sql = sprintf('SELECT targets.value FROM %1$s, targets WHERE targets.id = %1$s.target_id AND %2$s = %3$d', self::dbstr($class, 'targets'), self::dbstr($class, 'fk'), $id);
		return PSU::db('portal')->GetCol($sql);
	} //end targetNames

	/**
	 * Return the SQL code used to target elements.
	 */
	public static function targetSQL(PSUPerson $person, $class) {
		$response = array(
			'tables' => '',
			'where' => array("(t.type = 'public' AND t.value = 'public')")
		);

		// don't use targeting if the global identifier is 0 (editing the default layout)
		if( $GLOBALS['identifier'] ) {
			self::use_targeting( false );
		}//end if
	
		// should we show everything, regardless of targeting?
		if( ! self::use_targeting() || IDMObject::authZ('role', 'myplymouth') ) {
			$response['where'] = 'AND 1=1';
			return $response;
		}

		// join with center table and targets table
		$response['tables'] = sprintf('
			LEFT JOIN %3$s ON %1$s.id = %3$s.%2$s
			LEFT JOIN targets t ON %3$s.target_id = t.id
			', self::dbstr($class, 'table'), self::dbstr($class, 'fk'), self::dbstr($class, 'targets'));

		foreach( $_SESSION['AUTHZ']['sql'] as $subtype => $in_sql ) {
			$response['where'][] = sprintf("(t.type = 'authz' AND t.subtype = '%s' AND value IN %s)", $subtype, $in_sql);
		}

		if( $person->ad_rules_sql ) {
			$response['where'][] = sprintf("(t.type = 'ad' AND value IN %s)", $person->ad_roles_sql);
		}

		if( $person->banner_roles_sql ) {
			$response['where'][] = sprintf("(t.type = 'banner' AND value IN %s)", $person->banner_roles_sql);
		}

		$response['where'] = sprintf("AND (%s)", implode(' OR ', $response['where']));

		return $response;
	}//end targetSQL

	/**
	 * Constructor.
	 * @todo if params id is set, load by id
	 */
	public function __construct( array $params = array() ) {
		// load meta before $params is parsed. some meta may be overridden during
		// the $params['meta'] looping below, where MyMetaContainer will mark
		// them as custom if they have changed from preloaded values
		if( isset($params['id']) ) {
			$this->meta()->load( $params['id'], null, true );
		}

		$this->meta()->setDefaultChanged(true);

		foreach($params as $key => $value) {
			if( $key == 'meta' ) {
				foreach($value as $meta_key => $meta_value) {
					$this->meta()->$meta_key = $meta_value;
				}
			} else {
				$this->$key = $value;
			}
		}
	}//end __construct

	/**
	 * Magic setter. If target is MyMeta, delegate down to $this->meta.
	 */
	public function __set($k, $v) {
		if( $this->$k instanceof MyMeta ) {
			$this->meta()->$k = $v;
		} else {
			parent::__set($k, $v);
		}
	}//end __set
}//end class MyPortalObject
