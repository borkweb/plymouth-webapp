<?php
require_once $GLOBALS[ 'BASE_DIR' ]. '/includes/HRChecklistAdmin.class.php';

class HRChecklistPDFInfo extends HRChecklistAdmin {

	private $user = null;
	
	//construct
	public function __construct( $checklist_type, $user ) {
		$args = array(
			'checklist_type' => $checklist_type,
		);	
		parent::__construct( $args );
		
		$this->user = $user;
	}

	//gather necessary information for the pdf generation
	function pdf_info() {

		//checklist subject
		$format = 'f m l';
		$info[ 'person_name' ] = parent::get_name( $this->user, $format );

		//checklist categories
		$info[ 'categories' ] = HRChecklist::categories( $this->checklist_type );
		
		//category items
		foreach( $info[ 'categories' ] as &$category ) {
			$category[ 'items' ] = HRChecklist::get_items( $category );
		}	
		
		//checklist info .... date created, id, user, type. the latter two should already be defined before this point.
		$info[ 'checklist_info' ] = HRChecklist::get( $this->user, $this->type );	
		$info[ 'checklist_info' ][ 'title' ] = self::format_type_slug( $info[ 'checklist_info' ][ 'type' ] ).' Checklist';

		//checklist status
		$info[ 'is_complete' ] = HRChecklist::is_complete( $this->type, $info[ 'checklist_info' ][ 'id' ] );
			
		//if a closed date exists 
		if( HRChecklist::get_meta( $info[ 'checklist_info' ][ 'id' ],	'closed', 'activity_date' ) ) {
			$info[ 'checklist_info' ][ 'closed_date' ] = HRChecklist::get_meta( $info[ 'checklist_info' ][ 'id' ],	'closed', 'activity_date' );	
			$info[ 'closed_date' ] = new DateTime( $info[ 'checklist_info' ][ 'closed_date' ][ 'activity_date' ] ); 
			$info[ 'closed_date' ] = $info[ 'closed_date' ]->format( 'l F j, Y' );
		}
		
		return $info;
	}
	
	//transforms a checklist type slug into a usable title for the PDF
	function format_type_slug( $slug ) {
		return ucwords( str_replace( '-', ' ', $slug ) );
	}
}
