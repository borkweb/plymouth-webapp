<?php
/**
 * Provides functionallity for the administrative interface for the HR checklist
 */
class HRChecklistAdmin extends HRChecklist {

	//Checklist type
  public $type = 'employee-exit';

	//display only incomplete checklists
	public $is_incomplete = false;
	
	//acs or desc?
	public $sort = 'DESC';

	//Filter for checklists
	public $search  = null;

	//offset for pagination
	public $offset = 0;

	//limit for pagination
	public $limit = 10;

	//this is where all the checklists gets populated
	public $checklists = array();

	/**
	 *  args is an associative array withi key value pairs chosen from this list chosen from this list
	 *
	 *	type = Checklist type
	 *	is_incomplete (boolean) = decides whether to return completeted checklists
	 *	sort (enum) = ASC, DESC
	 *	search = Term to filter results TODO
	 *
	 */	
	function __construct( $args = null )	{

		if( $args != null ) {
			foreach( $args as $k=>$v )	{
				$this->$k=$v;
			}//end foreach
		}//end if
				
	}//end construct

	/**
	 * returns all employee checklists of the type in the constructer
	 */
	public function get_all_checklists( $hidden = false ) {
		if( !$this->type )
			return false;

		$args = array( 
			$this->type,
			false,
			$_SESSION['pidm'],
		);//end array

		if( ! $hidden ) {
			$where = " AND NOT EXISTS( SELECT 1 FROM checklist_user_settings u WHERE u.checklist_id = p.id AND pidm = ? )";
		} else {
			$where = " AND EXISTS( SELECT 1 FROM checklist_user_settings u WHERE u.checklist_id = p.id AND pidm = ? )";
		}//end else

		$sql = "
			SELECT p.* 
			FROM 
				person_checklists p
				JOIN phonebook.phonebook ph
					ON ph.pidm = p.pidm
				LEFT JOIN person_checklist_meta m
					ON m.checklist_id = p.id
					AND m.meta_key = 'end_date'
			WHERE 
				p.type=?
			AND
				p.closed=?
				{$where}
			ORDER BY
				m.meta_value,
				ph.name_last,
				ph.name_first,
				p.activity_date
			".$this->sort;

		$this->checklists['pending'] = PSU::db('hr')->GetAll( $sql, $args );

		if( !(bool)$this->is_incomplete ) {

			$args = array( 
				$this->type,
				true,
				$_SESSION['pidm'],
			);//end array

			$params = array(
				'sort' => 'desc'
			);
			$this->checklists['closed'] = PSU::db( 'hr' )->PageExecute( $sql, $this->limit, $this->offset, $args );
			
	 		$this->pagination = PSU::paginationInfo( $params, $this->checklists['closed'] );
			$this->pagination[ 'display_num' ] = $this->pagination['display_end'] - $this->pagination['display_start'] + 1;

		}//end if
	}

	/**
	 * returns the persons name replacing a pidm
	 */
	public static function get_name( $pidm, $format = 'l, f' ) {
		$person = new PSUPerson( $pidm );
		$name = $person->formatName( $format );
		$person->destroy();
		return $name;
	}//end get_name
	
	public static function is_category_complete( $category, $checklist_id ) {

		$items = HRChecklist::get_items( array( 'category' => $category ) );

		foreach ( (array)$items as $item ) {
			if( HRChecklist::item_response( $item[ 'id' ], $checklist_id ) == 'complete' ) {
				$complete_count++;
			}//endif
		}//end foreach

		if( count($items) == $complete_count )	{
			return true;
		}//end if

		return false;

	}
	public static function last_updated_by( $category, $checklist_id ) {

		$sql = '
			SELECT 
				activity_date date,
				updated_by who
			FROM
				person_checklist_items
			WHERE
				item_id IN (
					SELECT id 
					FROM  checklist_items ci 
					WHERE ci.category_id=?
				) AND
				checklist_id=?
			ORDER BY date DESC
					';
			$binds = array( $category );
			$binds[] = $checklist_id;
			return PSU::db( 'hr' )->getone( $sql, $binds );
			
	}

	/**
	 * retrieves all of the checklists and associated meta data selected in the contructor args or the defaults 
	 */
	public function populate_checklists() {

		$this->get_all_checklists();

		$this->categories = HRChecklist::categories( $this->type );

		if( $this->checklists[ 'pending' ] ) {

			foreach( $this->checklists[ 'pending' ] as &$checklist )	{
				$checklist[ 'closed' ] = HRChecklist::is_complete( $this->type, $checklist[ 'id' ] );
				$checklist[ 'person_name' ] = self::get_name( $checklist[ 'pidm' ] );
				$checklist[ 'meta' ][ 'end_date' ] = HRChecklist::get_meta( $checklist[ 'id' ], 'end_date', 1 );

				foreach( $this->categories as $category ) {
					$category[ 'is_complete' ] = self::is_category_complete( $category[ 'id' ], $checklist[ 'id' ] );
					$category[ 'updated' ] = self::last_updated_by( $category[ 'id' ], $checklist[ 'id' ] );
					$category[ 'reminder' ] = HRChecklist::get_meta( $checklist[ 'id' ], 'reminder_'.$category['slug'], 1 );
					$items = HRChecklist::get_items( array( 'category' => $category['id'] ) );
					$category[ 'items' ] = array();
					foreach( $items as $item ) {
						$item['response'] = HRChecklist::item_response( $item[ 'id' ], $checklist['id'], '*', 'GetRow' );
						$category['items'][ $item['slug'] ] = $item;
					}//end foreach
					$checklist[ 'category' ][] = $category; 
				}//end foreach

			}//end foreach

		}//end if	
		$checklist_temp = array();

		if( $this->checklists[ 'closed' ] ) {

			while($c = $this->checklists[ 'closed' ]->fetchrow())	{
				$c[ 'closed' ] = HRChecklist::is_complete( $this->type, $c[ 'id' ] );
				$c[ 'person_name' ] = self::get_name( $c[ 'pidm' ] );
				$c[ 'meta' ]['closed'] = HRChecklist::get_meta( $c[ 'id' ], 'closed', 1 );
				$c[ 'meta' ][ 'end_date' ] = HRChecklist::get_meta( $c[ 'id' ], 'end_date', 1 );

				foreach( $this->categories as $category ) {
					$category[ 'is_complete' ] = self::is_category_complete( $category[ 'id' ], $c[ 'id' ] );
					$category[ 'updated' ] = self::last_updated_by( $category[ 'id' ], $c[ 'id' ] );
					$category[ 'reminder' ] = HRChecklist::get_meta( $c[ 'id' ], 'reminder_'.$category['slug'], 1 );
					$c[ 'category' ][] = $category; 
				}//end foreach
				$checklist_temp[] = $c;
			}//end foreach
			$this->checklists[ 'closed' ] = $checklist_temp;

		}//end if	

	}//end populate_checklists
}
