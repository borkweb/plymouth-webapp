<?php

class HRChecklist {
	public function __construct() {}//end constructor

	/**
	 * set checklist item response
	 */
	public static function add_item_response( $responder_pidm, $checklist_id, $item_id, $response = null, $notes = null ) {
		$response = ($response !== null ? $response : $_POST[ 'response_'.$item_id ]);
		$notes = ($notes !== null ? $notes : $_POST[ 'notes_'.$item_id ]);

		$args = array();
		$args[] = $checklist_id;
		$args[] = $item_id;
		$args[] = ($response ? $response : 'incomplete');
		$args[] = $notes;
		$args[] = $responder_pidm;

		if( !self::item_response_exists( $checklist_id, $item_id, false ) && !$response ) return false;

		$sql = "INSERT INTO person_checklist_items (
							checklist_id,
							item_id,
							response,
							notes,
							updated_by,
							activity_date	
						) VALUES (
							?,
							?,
							?,
							?,
							?,
							NOW()	
						)";
		return PSU::db('hr')->Execute( $sql, $args );
	}//end add_item_response

	/**
	 * add checklist meta data
	 */
	public static function add_meta( $checklist_id, $key, $value ) {
		$args = array(
			$checklist_id,
			$key,
			$value
		);

		$sql = "INSERT INTO person_checklist_meta (
					  	checklist_id,
							meta_key,
							meta_value,
							activity_date
						) VALUES (
							?,
							?,
							?,
							NOW()
						)";
		return PSU::db('hr')->Execute( $sql, $args );
	}//end add_meta

	/**
	 * sets the item response of the APE item if the employee
	 *   does not have any manual APE permissions
	 */
	public static function auto_mark_ape_attributes( $person, $checklist_id, $initiator_pidm ) {
		$sql = "SELECT count(*) FROM psu_identity.person_attribute WHERE pidm = :pidm AND source LIKE 'ape%'";
		if( ! PSU::db('banner')->GetOne( $sql, array( 'pidm' => $person->pidm ) ) ) {
			return HRChecklist::add_item_response( $initiator_pidm, $checklist_id, 10, 'n/a', 'No manually assigned APE privileges' );
		}//end if

		return false;
	}//end auto_mark_ape_attributes

	/**
	 * sets the item response of the outstanding charges item if the employee
	 *   does not have any outstanding charges
	 */
	public static function auto_mark_outstanding_charges( $person, $checklist_id, $initiator_pidm ) {
		// if the employee doesn't have an outstanding balance, auto-check the item
		if( !$person->bill || ($person->bill && $person->bill->balance && $person->bill->balance['total'] <= 0) ) {
			return HRChecklist::add_item_response( $initiator_pidm, $checklist_id, 3, 'n/a', 'No outstanding charges' );
		}//end if

		return false;
	}//end auto_mark_outstanding_charges

	/**
	 * get checklist categories
	 */
	public static function categories( $type, $get = '*' ) {
		static $categories;

		$id = $type . '-' . $get;

		if( $categories[ $id ] ) {
			return $categories[ $id ];
		}//end if

		$sql = "SELECT {$get} FROM checklist_item_categories WHERE type=? ORDER BY name";
		$args = array( $type );

		$call = $get == '*' ? 'GetAll' : 'GetCol';

		return $categories[ $id ] = PSU::db('hr')->$call($sql, $args);
	}//end checklists

	/**
	 * get checklist items
	 */
	public static function checklist_items( $categories, $get = '*' ) {
		if( $get != '*' ) {
			return PSU::db('hr')->GetCol("SELECT {$get} FROM checklist_items WHERE category_id IN (".implode(',', (array) $categories).")");
		}//end if
		return PSU::db('hr')->GetAll("SELECT * FROM checklist_items WHERE category_id IN (".implode(',', (array) $categories).")");
	}//end checklist_items

	/**
	 * returns the email addresses for list contributors
	 *
	 * @param $people array collection of PSUPerson objects
	 */
	public static function contributor_email( $people ) {
		$emails = array();

		foreach((array)$people as $person) {
			$emails[] = $person->wp_email;
		}//end foreach

		if( PSU::isDev() ) {
			$emails = array(
				'mtbatchelder@plymouth.edu',
			);

			if( $_SESSION['username'] ) {
				$emails[] = $_SESSION['username'].'@plymouth.edu';
			}//end if
		}//end if

		$emails = array_unique($emails);

		return $emails;
	}//end contributor_email

	/**
	 * returns the contributors to the given checklist or checklist section
	 *
	 * @param $subject PSUPerson person the checklist is for
	 * @param $attribute string checklist permission
	 * @param $type_id int type of attribute
	 */
	public static function contributors( $subject, $checklist_id, $attribute = null, $type_id = null, $list = null ) {
		if( $type_id != 1 || $list == 'Department') {
			// get all the supervisors
			$attributes = array(
				'attribute' => array( 
					"pa.type_id" => 2,
					"pa.attribute" => 'supervisor',
			 ));
			$users = PSU::get('idmobject')->getUsersByAttribute($attributes);
				
			foreach((array)$users as $user) {
				$person = new PSUPerson( $user['username'] );

				// only email supervisors whose departments match the person leaving
				if( $person->department == $subject->department ) {
					$people[ $user['username'] ] = $person;
				}//end if
			}//end foreach

			$categories = HRChecklist::categories( 'employee-exit', 'slug' );

			// get emails of non-completed checklist categories
			$attributes = array();
			foreach( $categories as $category ) {
				if( !HRChecklist::is_complete( 'employee-exit', $checklist_id ) ) {
					$attributes[] = array(
						"pa.type_id" => 1,
						"pa.attribute" => 'ape_checklist_employee_exit_'.$category,
					);
				}//end if
			}//end foreach
		} else {
			// get all people that match the given checklist
			$attributes = array(
				'attribute' => array( 
					"pa.type_id" => $type_id,
					"pa.attribute" => $attribute,
			 ));
		}//end else

		$users = PSU::get('idmobject')->getUsersByAttribute($attributes);
			
		foreach((array)$users as $user) {
			$people[ $user['username'] ] = new PSUPerson( $user['username'] );
		}//end foreach

		return $people;
	}//end contributors

	/**
	 * deletes a meta entry for a checklist
	 */
	public static function delete_meta( $checklist_id, $meta_key ) {
		$sql = "DELETE FROM person_checklist_meta WHERE checklist_id = ? AND meta_key = ?";
		return PSU::db('hr')->Execute( $sql, array( $checklist_id, $meta_key ) );
	}//end delete_meta

	/**
	* This function is used to send off an email regaurding information on the employee exit checklist. 
	* This function is a bit smashed together to only work with employee exit checklist. If a future checklist was made this function
	* will have to be rethought.
	*
	* @param $attribute string containing pa.attribute and pa.type keys in order to populate usernames by attribute
	* @param $type_id int 1 = permission 2 = role 6 = admin. Must be one of these numebrs
	* @param $subject PSUPerson the person the email is about. 
	* @param $end_date string the date the person will be leaving the institution
	* @param $list string the checklist the email is using. 
	*/
	public static function email( $subject, $end_date, $attribute, $type_id, $list, $checklist_id, $response ){
		global $config;

		$emails = array();

		$people = self::contributors( $subject, $checklist_id, $attribute, $type_id );
		$emails = self::contributor_email( $people );
		$checklist = self::get( $subject->pidm, 'employee-exit' );
	
		if($emails) {
			$url = $config->get('ape', 'base_url') . '/user/'.$subject->pidm.'/checklist/'.$list;
			$email = new PSUSmarty();

			$email->assign( 'no_outstanding_charges', $response->no_outstanding_charges );
			$email->assign( 'no_ape_attributes', $response->no_ape_attributes );
			$email->assign( 'no_academic_admin', $response->no_academic_admin );

			$email->assign( 'end_date', $end_date);
			$email->assign( 'employee_name', $subject->formatName('f m l'));
			$email->assign( 'employee_username', $subject->username);
			$email->assign( 'subject', $subject);
			$email->assign( 'position', $subject->employee_positions[ $checklist['position_code'] ] );
			$email->assign( 'link', $url );

			$headers = 'Content-type: text/html; charset=UTF-8'."\r\n";
			PSU::mail( $emails , '[Action Required] '.$subject->formatName('f m l').' - Leaving '.date('M j, Y', $end_date) , $email->fetch( $GLOBALS['TEMPLATES'].'/email.checklist.employee-exit.tpl' ), $headers );
		}//end foreach

		return $emails;
	}//end email

	/**
	 * get checklist id
	 */
	public static function get( $pidm, $type, $get = '*' ) {
		if( $get != '*' ) {
			return PSU::db('hr')->GetOne("SELECT {$get} FROM person_checklists WHERE type = ? AND pidm = ?", array( $type, $pidm ) );
		}//end if
		return PSU::db('hr')->GetRow("SELECT * FROM person_checklists WHERE type = ? AND pidm = ?", array( $type, $pidm ) );
	}//end get

	/**
	 * get checklist meta
	 */
	public static function get_meta( $checklist_id, $key = null, $num = 'all') {
		$sql = "SELECT * FROM person_checklist_meta WHERE checklist_id = ?";

		$args = array( $checklist_id );

		if( $key ) {
			$args[] = $key;
			$sql .= " AND meta_key = ?";
		}//end if

		$sql .= " ORDER BY meta_key, activity_date DESC";

		if( $num == 'all' ) {
			return PSU::db('hr')->GetAll( $sql, $args );
		}//end if

		return PSU::db('hr')->GetRow( $sql, $args );
	}//end get_meta

	/**
	 * get specific checklist_items
	 * @param array $args ( default: $args[ 'field' ] == '*' ) list of arguments fields and items   
	 */
	public static function get_checklist_items( $args = array() ) {
		$sql = ' SELECT '.PSU::sql_select_fields( $args['fields'] ).' FROM person_checklist_items WHERE checklist_id=? ';

		if( $items ) {
			if( is_array( $items ) ) {
				$sql .= 'item_id IN ?';
				return PSU::db('hr')->getall($sql, array( $args['checklist_id'], explode( ',', $args['items'] ) ) );
			}//end if
			$sql .= 'item_id=?';
			return PSU::db('hr')->getall($sql, array( $args['checklist_id'], $args['items'] ) );
		}//end if

		return PSU::db('hr')->getall($sql, array( $args['checklist_id'] ) );
		
	}//end get_checklist_items

	/**
	 * returns specific items 
	 * @param array $args ( default: $args[ 'field' ] == '*' ) list of args are field, category and id 
	 */
	public static function get_items( $args ) {

		$fields = PSU::sql_select_fields( $args[ 'fields' ] );
		$sql = 'SELECT '.$fields.' FROM checklist_items WHERE category_id = ? ORDER BY name';
		if( $args[ 'category' ] ) {
			return PSU::db( 'hr' )->GetAll($sql, array( $args[ 'category' ] ) );
		}
		if( $args[ 'id' ] ) {
			return PSU::db( 'hr' )->GetAll( $sql, array( $args[ 'id' ] ) ); 	
		}//

		return $items;
		
	}//end get_items

	/**
	 * hides a checklist for a given user
	 */
	public static function hide( $checklist_id ) {
		$sql = "REPLACE INTO checklist_user_settings (checklist_id, pidm, hidden) VALUES (?, ?, 1)";
		return PSU::db('hr')->Execute( $sql, array( $checklist_id, $_SESSION['pidm'] ) );
	}//end hide

	public static function is_closed( $checklist_id ) {
		return PSU::db('hr')->GetOne("SELECT 1 FROM person_checklists WHERE id = ? AND closed = 1", array( $checklist_id ) );
	}//end is_closed

	/**
	 * determines if checklist is complete
	 */
	public static function is_complete( $list, $checklist_id ) {

		$categories = HRChecklist::categories( $list, 'id' );
		$items = HRChecklist::checklist_items( $categories );

		foreach( $items as $item ) {
			$response = self::item_response( $item['id'], $checklist_id );

			if( $response != 'complete' && $response != 'n/a' ) {
				return false;
			}//end if

		}//end foreach

		return true;
	}//end is_complete

	/**
	 * get checklist item response
	 */
	public static function item_response( $item_id, $checklist_id, $return = 'response', $what = 'GetOne' ) {
		if( $what == 'GetOne' ) {
			return PSU::db('hr')->GetOne("SELECT {$return} FROM person_checklist_items WHERE checklist_id = ? AND item_id = ? ORDER BY activity_date DESC", array($checklist_id, $item_id));
		} else {
			return PSU::db('hr')->GetRow("SELECT {$return} FROM person_checklist_items WHERE checklist_id = ? AND item_id = ? ORDER BY activity_date DESC", array($checklist_id, $item_id));
		}//end if
	}//end item_response

	/**
	 * get checklist responses
	 */
	public static function item_responses( $pidm, $item_id, $get = '*' ) {
		$sql = "SELECT {$get} ,i.activity_date item_date
				 FROM person_checklist_items AS i 
							 INNER JOIN person_checklists AS c 
							 ON c.id=i.checklist_id 
				WHERE i.item_id=? 
					AND c.pidm=?
        ORDER BY i.activity_date DESC";

		$args = array(
			$item_id,
			$pidm
		);

		if( $get != '*' ) {
			return PSU::db('hr')->GetCol($sql, $args);
		}//end if
		return PSU::db('hr')->GetAll($sql, $args);
	}//end item_responses

	/**
	 * return false if checklist response/note does not already exist
	 */
	public static function item_response_exists( $checklist_id, $item_id, $validate = true ) {
		$args = array(
			$checklist_id,
			$item_id
		);

		$response = $_POST['response_'.$item_id];
		$response = trim($response) == '' ? null : $response;

		$notes = $_POST['notes_'.$item_id];
		$notes = trim($notes) == '' ? null : $notes;

		$sql = "SELECT * FROM person_checklist_items WHERE checklist_id = ? AND item_id = ? ORDER BY activity_date DESC LIMIT 0,1";

		$checklist = PSU::db('hr')->GetRow( $sql, $args );

		if( $validate ) {
			$checklist['response'] = trim($checklist['response']) == '' ? null : $checklist['response'];
			$checklist['notes'] = trim($checklist['notes']) == '' ? null : $checklist['notes'];
			
			return $response == $checklist['response'] && !$notes;
		}//end if

		return $checklist;
	}//end item_response_exists

	/**
	 * returns whether or not a meta exists
	 */
	public static function meta_exists( $checklist_id, $meta_key, $meta_value = null) {
		$args = array(
			$checklist_id,
			$meta_key
		);

		if( $meta_value !== null ) {
			$args[] = $meta_value;
		}//end if

		$sql = "SELECT 1 
							FROM person_checklist_meta 
						 WHERE checklist_id = ? 
					     AND meta_key = ?".($meta_value !== null ? " AND meta_value = ?" : "");
		return PSU::db('hr')->GetOne($sql, $args);
	}//end meta_exists

	/**
	 * start a checklist process
	 */
	public static function start( $pidm, $date, $type, $position, $initiator_pidm ) {
		if( $id = self::get( $pidm, $type, $position ) ) {
			return $id;
		}//end if

		$person = new PSUPerson( $pidm );

		$response = new StdClass;

		$sql = "INSERT INTO person_checklists (type, pidm, position_code) VALUES (?, ?, ?)";
		if( PSU::db('hr')->Execute( $sql, array( $type, $pidm, $position ) ) ) {
			$response->id = PSU::db('hr')->Insert_ID();

			$sql = "INSERT INTO person_checklist_meta (
				        checklist_id,
								meta_key,
								meta_value
							) VALUES (
								?,
								'end_date',
								?
							)";
			if( PSU::db('hr')->Execute( $sql, array( $response->id, $date ) ) ) {
				$response->no_outstanding_charges = HRChecklist::auto_mark_outstanding_charges( $person, $response->id, $initiator_pidm );
				$response->no_ape_attributes = HRChecklist::auto_mark_ape_attributes( $person, $response->id, $initiator_pidm );

				return $response;
			}//end if
		}//end if

		return $response;
	}//end class start

	/**
	 * toggle open and close employee checklist
	 */
	public static function toggle_checklist( $checklist_id, $pidm, $status ) {
		$sql = 'UPDATE person_checklists SET closed=? WHERE id=? AND pidm=?';		
		$binds = array(
			$status,
			$checklist_id,
			$pidm
		);
		return PSU::db( 'hr' )->GetOne( $sql, $binds );
	}//end close

	/**
	 * unhides a checklist for a given user
	 */
	public static function unhide( $checklist_id ) {
		$sql = "DELETE FROM checklist_user_settings WHERE checklist_id = ? AND pidm = ?";
		return PSU::db('hr')->Execute( $sql, array( $checklist_id, $_SESSION['pidm'] ) );
	}//end unhide
}//end class HRChecklist
