<?php

require_once 'BannerObject.class.php';
require_once 'PSUEventManager.class.php';

/**
 * Container class to hold relationships for a specific user, specified by $this->person.
 *
 * @todo Optimize so that multiple relationships can be loaded through one database call
 */
class MyRelationships {
	/**
	 * An array of the relationships for this person.
	 */
	public $relationships;

	/**
	 * The person whose relationships we are tracking.
	 */
	private $person;

	/**
	 * Constructor.
	 * @param $person \b PSUPerson
	 */
	public function __construct( PSUPerson $person = null ) {
		$this->person( $person );
	}//end __construct

	/**
	 * Get all relationships for this user, or the relationship between two users, optionally
	 * limited by a specific status. For relationships with any person, limited by status,
	 * you may pass null \b or the status as the first argument.
	 *
	 * @param $wpid \b string wpid
	 * @param $status \b string any valid value for relationships.status, or 'all'
	 * @param $grant \b string limit results to users who have granted $grant to this user
	 * @return array|MyRelationship array of relationships, or one relationship if $wpid was provided. false if no relationship was found.
	 */
	public function get() {
		if( ! $this->person ) {
			throw new Exception( 'object is not tied to a person; cannot continue' );
		}

		// default values
		$wpid   = null;

		$argc = func_num_args();
		$argv = func_get_args();

		if( $argc == 3 ) {
			$wpid   = array_shift($argv);
			$status = array_shift($argv);
			$grant  = array_shift($argv);
		} elseif( $argc == 1 || $argc == 2 ) {
			if( PSU::is_wpid( func_get_arg(0), PSU::MATCH_BOTH ) ) {
				$wpid   = array_shift($argv);
				$status = array_shift($argv);
			} else {
				$status = array_shift($argv);
				$grant  = array_shift($argv);
			}
		}

		$status = $status ?: 'confirmed';
		$grant  = $grant  ?: 'all';

		//
		// Determine which relationships to return
		//
		
		$filters = array();

		if( $wpid != null ) {
			$filters[] = function( $relwpid, $relationship ) use ( $wpid ) {
				if( $relwpid == $wpid ) {
					return true;
				}
			};
		}

		if( $status != 'all' ) {
			$filters[] = function( $relwpid, $relationship ) use ( $status ) {
				if( $relationship->status == $status ) {
					return true;
				}
			};
		}

		if( $grant != 'all' ) {
			$person = $this->person;

			$filters[] = function( $relwpid, $relationship ) use ( $person, $grant ) {
				$wpid = $person->wpid;

				if( $relationship->$wpid->permissions( $grant ) ) {
					return true;
				}
			};
		}

		$matched_relationships = array();

		$relationships = $this->relationships($wpid) ?: array();

		// no filters, return all relationships
		if( count($filters) == 0 ) {
			return $relationships;
		}

		if( is_object($relationships) ) {
			$relationships = array( $relationships );
		}

		foreach( $relationships as $relwpid => $relationship ) {
			foreach( $filters as $filter ) {
				if( $filter( $relwpid, $relationship ) == false ) {
					// filter did not pass
					continue 2; // continue out to outer foreach
				}
			}

			$matched_relationships[$relwpid] = $relationship;
		}

		// must return false or a single relationship if one relationship was requested
		if( $wpid !== null ) {
			return $matched_relationships ? array_pop($matched_relationships) : false;
		}

		return $matched_relationships;
	}//end get

	/**
	 * Used to display descriptive text in the tempid invite screen.
	 */
	public static function temp_ticket_description( $relid ) {
		$text = "A family member or friend has invited you to join the myPlymouth portal. To accept this invitation, please login to your myPlymouth account or create a new account.";
		return $text;
	}//end temp_ticket_description

	/**
	 * Callback run after a user has associated a tempid invite with a connect account.
	 */
	public static function temp_qualify_invite( $wp_id, $temp_wpid ) {
		$args = array($wp_id, $temp_wpid);

		$sql = "UPDATE tempids SET wpid = ? WHERE temp_wpid = ?";
		PSU::db('portal')->Execute( $sql, $args );

		$sql = "UPDATE relationships SET target = ? WHERE target = ?";
		PSU::db('portal')->Execute( $sql, $args );

		$sql = "UPDATE relsearch SET wpid1 = ? WHERE wpid1 = ?";
		PSU::db('portal')->Execute( $sql, $args );

		$sql = "UPDATE relsearch SET wpid2 = ? WHERE wpid2 = ?";
		PSU::db('portal')->Execute( $sql, $args );

		$sql = "UPDATE relgrants SET wpid_grantee = ? WHERE wpid_grantee = ?";
		PSU::db('portal')->Execute( $sql, $args );
	}//end temp_qualify_invite

	/**
	 * Grant a permission to a relation.
	 */
	public function grant( $grantee, $permission ) {
		if( $grantee instanceof MyRelationshipPerson ) {
			// great!
		} elseif( PSU::is_wpid( $grantee ) ) {
			$grantee = $this->$grantee;
		} else {
			throw new Exception( 'i don\'t know what to do with that $grantee' );
		}

		$permission = MyPermission::load( $permission );

		//
		// prep done, do checks
		//

		if( ! $grantee->relationship() ) {
			throw new Exception( 'grantee is not part of a relationship' );
		}

		if( $grantee->other()->wpid != $this->person->wpid ) {
			throw new Exception( 'grantee\'s active relationship is not with the active person' );
		}

		//
		// checks done, do grant
		//

		// if permission exists, just return true
		if( $grant = $grantee->permissions($permission) ){
			return $grant;
		}

		$grant = new MyGrant( null, $grantee->other(), $grantee, $permission );
		return $grant->save();
	}//end grant

	/**
	 * Get or set the person object.
	 */
	public function person( PSUPerson $person = null ) {
		if( $person ) {
			if( $this->person ) {
				throw new Exception('this relationship object is already tied to a person');
			}

			$this->person = $person;

			$this->_prefetch_ids();
		}

		return $this->person;
	}//end person

	/**
	 * Prepopulate relationships ids into object once we know which person we are watching.
	 */
	private function _prefetch_ids() {
		$sql = "SELECT wpid2, rel_id FROM relsearch WHERE wpid1 = ?";
		$args = array( $this->person->wpid );

		$rset = PSU::db('portal')->Execute( $sql, $args );

		foreach( $rset as $row ) {
			$this->relationships[ $row['wpid2'] ] = $row['rel_id'];
		}
	}//end _prefetch_ids

	/**
	 * Return all relationships, or the relationships between this person and another person.
	 * If you need to filter by status, use MyRelationships::get().
	 *
	 * @param $wpid
	 * @return MyRelationship|array MyRelationship object, or array of MyRelationship objects
	 * @sa MyRelationships::get()
	 */
	public function relationships( $identifier = null ) {
		$wpid = $relid = null;

		if( PSU::is_wpid( $identifier, PSU::MATCH_BOTH ) ) {
			$wpid = $identifier;
		} elseif( ctype_digit( $identifier ) || is_int( $identifier ) ) {
			$relid = $identifier;
		}

		if( $wpid ) {
			if( isset( $this->relationships[$wpid] ) ) {
				return $this->_relationship( $wpid );
			}

			return $f = false;
		}

		if( $relid ) {
			foreach( $this->relationships as $wpid => $id_or_myr ) {
				if(  !($id_or_myr instanceof MyRelationship) ) {
					if( $relid == $id_or_myr ) {
						return $this->_relationship( $wpid );
					}
				} elseif( $relid == $id_or_myr->id ) {
					return $id_or_myr;
				}
			}

			return $f = false;
		}

		//
		// returning all relationships; make sure they are populated
		//

		foreach( (array) $this->relationships as $wpid => $id ) {
			// touch each
			$this->_relationship( $wpid );
		}

		return $this->relationships;
	}//end relationships

	/**
	 * Return relationship to a specific person, creating MyRelationship object as necessary.
	 * @sa MyRelationships::relationships
	 */
	private function _relationship( $wpid ) {
		if( ! isset( $this->relationships[$wpid] ) ) {
			return $n = null;
		}

		if( ! ( $this->relationships[$wpid] instanceof MyRelationship ) ) {
			$relationship = MyRelationship::load_by_relid( $this->relationships[$wpid] );

			$this->relationships[$wpid] = $relationship;
		}

		return $this->relationships[$wpid];
	}//end _relationship

	/**
	 * Magic getter for shortcutted wpids. Allows for $rs->p0intless fetching.
	 */
	public function __get( $wpid ) {
		if( $relationship = $this->_relationship($wpid) ) {
			return $this->$wpid = $relationship->$wpid;
		}//end if

		return null;
	}//end __get
}//end MyRelationships

/**
 * A single relationship between two people.
 */
class MyRelationship {
	var $id;
	var $initiator;
	var $target;
	var $status;

	/**
	 *
	 */
	public function __construct( $id = null, MyRelationshipPerson $initiator, $target, $status ) {
		$this->id = $id;

		$this->events = new PSUEventManager;

		$this->initiator = $initiator;
		$this->target = $target;

		$this->{$initiator->wpid} = $this->initiator;
		$this->{$target->wpid} = $this->target;

		$this->initiator->other($target);
		$this->target->other($initiator);

		$this->initiator->relationship($this);
		$this->target->relationship($this);

		$this->status = $status;
	}//end __construct

	/**
	 * Insert new record into database.
	 */
	private function insert() {
		$sql = "
			INSERT INTO relationships
				(initiator, target, reltype_initiator, reltype_target, status, created, modified)
			VALUES
				(?, ?, ?, ?, ?, NOW(), NOW())
		";

		$args = array(
			$this->initiator->wpid, $this->target->wpid,
			$this->initiator->type->id, $this->target->type->id,
			$this->status
		);

		if( ! PSU::db('portal')->Execute( $sql, $args ) ) {
			throw new Exception( 'could not create new relationship between those users' );
		}

		$this->id = PSU::db('portal')->Insert_ID();

		//
		// insert relsearch
		//

		$sql = "
			INSERT INTO relsearch
				(wpid1, wpid2, rel_id)
			VALUES
				(?, ?, ?),
				(?, ?, ?)
		";

		$args = array(
			$this->initiator->wpid, $this->target->wpid, $this->id,
			$this->target->wpid, $this->initiator->wpid, $this->id
		);

		PSU::db('portal')->Execute( $sql, $args );

		$this->events->trigger('relationship_inserted');

		return true;
	}//end insert

	/**
	 * Load a relationship specified by id.
	 */
	public static function load_by_relid( $id ) {
		$sql = "SELECT * FROM relationships WHERE id = ?";
		$row = PSU::db('portal')->GetRow( $sql, array($id) );

		$initiator = new MyRelationshipPerson( $row['initiator'], $row['reltype_initiator'] );
		$target = new MyRelationshipPerson( $row['target'], $row['reltype_target'] );

		$status = $row['status'];

		$relationship = new self( $id, $initiator, $target, $status );

		$initiator->relationship($relationship);
		$target->relationship($relationship);

		return $relationship;
	}//end load_by_relid

	/**
	 * Save relationship to the database.
	 */
	public function save() {
		if( $this->id ) {
			return $this->update();
		} else {
			return $this->insert();
		}
	}//end save

	/**
	 * Update record in database.
	 */
	private function update() {
		$sql = "UPDATE relationships SET status = ? WHERE id = ?";
		$args = array($this->status, $this->id);

		PSU::db('portal')->Execute($sql, $args);

		return PSU::db('portal')->Affected_Rows() == 1;
	}//end update
}//end MyRelationship

/**
 * A person in a relationship. This person is unique to this relationship, because
 * permission data between the two people are stored within.
 */
class MyRelationshipPerson extends BannerObject {
	var $wpid;
	var $type;

	public $data = array();

	/**
	 * The parent relationship.
	 */
	private $relationship;

	/**
	 * The other party in the relationship.
	 */
	private $other;

	/**
	 * Permissions granted to the other party.
	 */
	private $grants;

	/**
	 * Permissions the other party has granted to this person.
	 */
	private $permissions;

	/**
	 * @param $wpid \b string the person's wpid
	 * @param $type \b MyRelationshipType|array|int MyRelationshipType object, or array of ($code, $gender), or a specific type's id
	 * @param $data \b array extra data provided in some situations (ie. creation of a temp id)
	 */
	public function __construct( $wpid, $type ) {
		parent::__construct();

		$this->events->bind('relationship_set', array($this, 'got_relationship'));

		$this->wpid = $wpid;

		if( $type instanceof MyRelationshipType ) {
			$this->type = $type;
		} elseif( is_array($type) ) {
			list( $atvxref_code, $gender ) = $type;
			$this->type = MyRelationshipType::load( $atvxref_code, $gender );
		} else {
			$rt = MyRelationshipType::load( $type );

			if( $rt instanceof MyRelationshipType ) {
				$this->type = $rt;
			} elseif( is_array($rt) ) {
				throw new Exception('ambiguious relationship type provided, please specify gender or type id');
			} else {
				throw new Exception('unknown error while retrieving relationship type');
			}
		}
	}//end __construct

	/**
	 * Permissions this user has granted to $this->other.
	 */
	public function grants( $perm = null ) {
		return $this->_permissions( __FUNCTION__, $perm );
	}//end grants

	/**
	 * Set/get the other person in the relationship.
	 */
	public function other( $other = null ) {
		if( $other ) {
			$this->other = $other;
		}

		return $this->other;
	}//end other

	/**
	 * Permissions this user has been granted by $this->other.
	 */
	public function permissions( $perm = null ) {
		return $this->_permissions( __FUNCTION__, $perm );
	}//end permissions

	/**
	 * Backend function to return all grants or permissions, or check for one active permission.
	 *
	 * @param $type \b string grants or permissions
	 * @param $has_permission \b MyPermission|string|int 
	 * @return array|bool Array of all grants/permissions, or true/false if checking one permission.
	 * @todo: make this double-insert-safe with locks, perhaps?
	 */
	public function _permissions( $type, $has_permission = null ) {
		if( $type === 'grants' ) {
			$grantor = $this;
			$grantee = $this->other;
		} elseif( $type === 'permissions' ) {
			$grantor = $this->other;
			$grantee = $this;
		} else {
			throw new Exception( 'unknown value for $type' );
		}

		if( $this->$type === null ) {
			$this->$type = MyGrant::load_grants( $grantor, $grantee );
		}
	
		/* 
		 * checking for a single active permission... temporarily disabling this so we can see checked boxes on
		 * confirmed or unconfirmed relationships
		 * 
		 * @TODO determine if we want this check in this method or in another place. We'll need checking
		 * for confirmed status when we render portal data, but don't want it for grants and perms in the
		 * invite channel.
		 */
		if( $has_permission ) {
			/*if( $this->relationship()->status !== 'confirmed' ) {
				return false;
			} */
			
			$has_permission = MyPermission::load( $has_permission );
			
			foreach( $this->$type as $grant ) {
				if( $grant->permission->id === $has_permission->id ) {
					return $grant;
				}
			}

			return false;
		}
		
		// returning all permissions
		return $this->$type;
	}//end _permissions

	/**
	 * Factory to create a new temporary ID for this email address.
	 */
	public static function tempid_create( $email, $first_name, $last_name, $type ) {
		$wpid = self::tempid_generate_id( $email );

		$tp = new self( $wpid, $type );

		// when the relationship object is added, save our name data into the meta table
		$extra_args = array('first_name' => $first_name, 'last_name' => $last_name);
		$tp->events->bind('relationship_set', array($tp, 'force_save_meta'), $extra_args);

		return $tp;
	}//end tempid_create

	/**
	 * Add the relid to our person object.
	 */
	public function got_relationship() {
		$this->person->rel_id = $this->relationship()->id;
	}//end relid_in_person

	/**
	 * This binding is deferred until we have a relationship to bind to.
	 */
	public function force_save_meta( $first_name, $last_name ) {
		$this->relationship()->events->bind('relationship_inserted', array($this, 'tempid_meta_update'), array($first_name, $last_name));
	}//end force_save_meta

	/**
	 * Update the tempid_meta table.
	 */
	public function tempid_meta_update( $first_name, $last_name ) {
		$this->person->rel_id = $this->relationship()->id;

		$sql = "
			INSERT INTO tempids_meta
				(temp_wpid, rel_id, first_name, last_name)
			VALUES
				(?, ?, ?, ?)
			ON DUPLICATE KEY UPDATE
				first_name = ?, last_name = ?
		";

		$args = array($this->wpid, $this->person->rel_id, $first_name, $last_name, $first_name, $last_name);

		PSU::db('portal')->Execute($sql, $args);
	}//end tempid_meta_update

	/**
	 * Get the email address for a tempid.
	 */
	public static function tempid_email( $wpid ) {
		$sql = "
			SELECT email
			FROM tempids t
			WHERE t.temp_wpid = ?
		";

		$args = array($wpid);

		$email = PSU::db('portal')->GetOne( $sql, $args );

		return $email;
	}//end tempid_email

	/**
	 * Fetch metadata for this wpid/relid combo.
	 */
	public static function tempid_meta_select( $wpid, $relid ) {
		$sql = "
			SELECT *
			FROM tempids t LEFT JOIN tempids_meta m ON t.temp_wpid = m.temp_wpid
			WHERE t.temp_wpid = ? AND m.rel_id = ?
		";

		$args = array($wpid, $relid);

		$user = PSU::db('portal')->GetRow( $sql, $args );

		return $user;
	}//end tempid_meta_select

	/**
	 * Get or create a temporary invitation wpid for the given email address.
	 *
	 * @param $email \b string
	 * @param $pass \b int this function may be called more than once if a user received a tempid while between our SELECT and INSERT; $pass keeps track of how many times we've been called recursively
	 */
	public static function tempid_generate_id($email, $pass = 1) {
		$email = strtolower($email);

		if( $pass > 2 ) {
			throw new Exception('trouble creating tempid');
		}

		$sql = "SELECT temp_wpid FROM tempids WHERE email = ?";
		$rset = PSU::db('portal')->Execute($sql, array($email));

		if( $rset->RecordCount() == 1 ) {
			$row = $rset->FetchRow();
			return $row['temp_wpid'];
		} elseif( $rset->RecordCount() == 0 ) {
			$temp_wpid = sl_generate_wpid('t');

			$sql = "
				INSERT INTO tempids
					(temp_wpid, email)
				VALUES
					(?, ?)
			";

			PSU::db('portal')->Execute( $sql, array($temp_wpid, $email) );

			if( PSU::db('portal')->ErrorNo() == 0 ) {
				return $temp_wpid;
			}
		}

		// if there was an error, try again
		return self::tempid_generate_id( $email, $pass + 1 );
	}//end tempid_generate

	/**
	 * Set/get the parent MyRelationship.
	 */
	public function relationship( MyRelationship $rel = null ) {
		if( $rel ) {
			$this->relationship = $rel;
			$this->events->trigger('relationship_set');
		}

		return $this->relationship;
	}//end relationship

	/**
	 * lazy load the person object
	 */
	public function _load_person() {
		$this->person = PSUPerson::get($this->wpid);
	}//end _load_person
}//end MyRelationshipPerson

/**
 * Type of relationships: uncle, son, etc. Use $rt->inverse() to retrieve an array of
 * the inverse relationships.
 */
class MyRelationshipType {
	var $id;
	var $name;
	var $atvxref_code;
	var $atvxref_inverse;
	var $gender;

	var $inverse;

	static $cache;

	public function __construct( $id, $name, $gender, $atvxref_code, $atvxref_inverse ) {
		$this->id = $id;
		$this->name = $name;
		$this->gender = $gender;
		$this->atvxref_code = $atvxref_code;
		$this->atvxref_inverse = $atvxref_inverse;
	}//end __construct

	/**
	 * Convert a $gender argument into a one-character, uppercase argument.
	 */
	public static function gender_sanitize( $gender ) {
		$gender = substr($gender, 0, 1);
		$gender = strtoupper($gender);

		return $gender;
	}//end gender_sanitize

	/**
	 * Returns all relationship types
	 */
	public static function get() {
		static $types;

		$sql = "SELECT * FROM reltypes ORDER BY name";

		$rset = PSU::db('portal')->Execute( $sql );

		foreach( $rset as $row ) {
			$relationship_type = new self($row['id'], $row['name'], $row['gender'], $row['atvxref_code'], $row['atvxref_inverse']);

			$types[$row['id']] =& $relationship_type;

			unset($relationship_type);
		}

		return $types;
	}//end get

	/**
	 * Get all relationships in a jsonified string.
	 */
	public static function get_json() {
		static $json = null;

		if( $json === null ) {
			$types = self::get();
			$tmp = array();

			foreach($types as $type) {
				$tmp[] = array( $type->id, $type->name, $type->atvxref_code, $type->atvxref_inverse );
			}

			$json = json_encode($tmp);
		}

		return $json;
	}//end get_json

	/**
	 * Return the inverse relationship(s), optionally limiting to a gender (including gender-neutral).
	 *
	 * @param $gender \b string M ("male") or F ("female"). Does not conflict with gender-neutral relationships.
	 * @return MyRelationshipType|array A single MyRelationshipType if a gender was specified, otherwise all possible relationship types as an array (even if only one inverse exists)
	 */
	public function inverse( $gender = null ) {
		if( $gender === null ) {
			return $this->inverse;
		}

		$gender = self::gender_sanitize($gender);

		if( $this->inverse == null ) {
			$this->inverse = self::load( $this->atvxref_inverse );
		}

		foreach( $this->inverse as $inverse ) {
			if( $inverse->gender == $gender ) {
				return $inverse;
			}
		}
	}//end inverse

	/**
	 * Load a relationship type by reltype.id or the ATVXREF code.
	 *
	 * @param $id numeric id, or xref code
	 * @param $gender \b string M or F
	 * @return MyRelationshipType|array MyRelationshipType if a numeric id gender was specified (ie. a query which only has one result), otherwise an array of MyRelationshipTypes
	 */
	public static function load( $id, $gender = null ) {
		static $cache;

		$loadby = null;

		if( is_int($id) || ctype_digit($id) ) {
			$id = (int)$id;
			$loadby = 'id';
		} else {
			$loadby = 'atvxref_code';
		}

		// cache all, regardless of gender
		if( ! isset($cache[$id]) ) {
			$sql = "SELECT * FROM reltypes WHERE $loadby = ?";
			$args = array($id);

			$rset = PSU::db('portal')->Execute( $sql, $args );

			if( $rset === false ) {
				throw new Exception("unknown relationship type");
			}

			foreach( $rset as $row ) {
				$relationship_type = new self($row['id'], $row['name'], $row['gender'], $row['atvxref_code'], $row['atvxref_inverse']);

				if( !isset($cache[$row['atvxref_code']]) ) {
					$cache[$row['atvxref_code']] = array();
				}

				$cache[$row['id']] =& $relationship_type;
				$cache[$row['atvxref_code']][] =& $relationship_type;

				unset($relationship_type);
			}
		}

		// return a specific gender, if necessary
		if( $gender !== null ) {
			$gender = self::gender_sanitize($gender);

			foreach( $cache[$id] as $reltype ) {
				if( $reltype->gender == $gender || $reltype->gender == 'N' ) {
					return $reltype;
				}
			}

			throw new Exception('could not find a relationship matching that gender');
		}

		// return array containing one or more types
		return $cache[$id];
	}//end load
}//end MyRelationshipType

/**
 * Portal permissions.
 */
class MyPermission {
	public $id;
	public $code;
	public $name;
	public $url;

	/**
	 *
	 */
	public function __construct( $id, $code, $name, $url = null ) {
		$this->id = $id;
		$this->code = $code;
		$this->name = $name;
		$this->url = $url;
	}//end __construct
	
	/**
	 * Returns all permission types
	 */
	public static function get() {
		static $perms = null;

		if( $perms === null ) {
			$sql = "SELECT * FROM relpermissions WHERE suppress = 0 ORDER BY name";

			$rset = PSU::db('portal')->Execute( $sql );

			foreach( $rset as $row ) {
				$perm = self::load( $row['id'], $row );
				$perms[$perm->code] =& $perm;
				unset( $perm );
			}
		}

		return $perms;
	}//end get

	/**
	 *
	 */
	public static function load( $ident, $row = null ) {
		static $cache;

		if( $ident instanceof MyPermission ) {
			// sweet.
			return $ident;
		}
		
		if( isset( $cache[$ident] ) ) {
			return $cache[$ident];
		}

		$loadby = null;

		if( $row === null ) {
			if( is_int($ident) || ctype_digit($ident) ) {
				$sql = "
					SELECT *
					FROM relpermissions
					WHERE id = ?
					";

				$args = array($ident);
			} else {
				$sql = "
					SELECT *
					FROM relpermissions
					WHERE
					code = ? OR
					name = ?
					";

				$args = array($ident, $ident);
			}
			
			$row = PSU::db('portal')->GetRow( $sql, $args );
		}
		
		if( !$row ) {
			throw new Exception('permission not found');
		}

		$perm = new self( $row['id'], $row['code'], $row['name'], $row['url'] );
		
		$cache[ $row['id'] ] =& $perm;
		$cache[ $row['code'] ] =& $perm;
		$cache[ $row['name'] ] =& $perm;
		
		return $cache[ $row['id'] ];
	}//end load
}//end MyPermission

/**
 * Granted permissions.
 */
class MyGrant {
	public $id;
	public $grantor;
	public $grantee;
	public $permission;
	public $date_granted;

	/**
	 *
	 */
	public function __construct( $id = null, MyRelationshipPerson $grantor, MyRelationshipPerson $grantee, MyPermission $permission, $date_granted ) {
		$this->id = $id;
		$this->grantor = $grantor;
		$this->grantee = $grantee;
		$this->permission = $permission;
		$this->date_granted = $date_granted;
	}//end __construct

	public function delete() {
		if( ! $this->id ) {
			throw new Exception( 'cannot delete grant that has not been saved' );
		}

		$sql = "
			DELETE FROM relgrants
			WHERE id = ?
		";

		$args = array( $this->id );

		PSU::db('portal')->Execute( $sql, $args );

		if( PSU::db('portal')->ErrorNo() > 0 ) {
			return false;
		}

		return true;
	}//end

	/**
	 * Insert new grant into database.
	 */
	private function insert() {
		$sql = "
			INSERT INTO relgrants
				(wpid_grantor, wpid_grantee, relpermission_id, activity_date)
			VALUES
				(?, ?, ?, NOW())
		";

		$args = array($this->grantor->wpid, $this->grantee->wpid, $this->permission->id);

		PSU::db('portal')->Execute( $sql, $args );

		if( PSU::db('portal')->ErrorNo() > 0 ) {
			return false;
		}

		$this->id = PSU::db('portal')->Insert_ID();

		return $this;
	}//end insert

	/**
	 * Load grants between two people. Note that this load is unidirectional, between a grantor and a grantee.
	 */
	public static function load_grants( MyRelationshipPerson $grantor, MyRelationshipPerson $grantee ) {
		$sql = "
			SELECT *
			FROM relgrants rg
			WHERE
				rg.wpid_grantor = ? AND
				rg.wpid_grantee = ?
		";

		$args = array($grantor->wpid, $grantee->wpid);

		$rset = PSU::db('portal')->Execute( $sql, $args );

		$grants = array();
		foreach( $rset as $row ) {
			$permission = MyPermission::load( $row['relpermission_id'] );

			$grant = new self($row['id'], $grantor, $grantee, $permission, strtotime( $row['activity_date'] ) );
			$grants[ $permission->code ] = $grant;

			unset($grant);
		}

		return $grants;
	}//end load_grants

	/**
	 * Save this grant to the database.
	 */
	public function save() {
		if( $this->id ) {
			return $this->update();
		} else {
			return $this->insert();
		}
	}//end save

	/**
	 * Update grant in database.
	 */
	private function update() {
		return false;
	}//end update
}//end MyGrant
