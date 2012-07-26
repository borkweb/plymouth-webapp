<?php

namespace PSU\Person;

class Note implements \PSU\ActiveRecord{
	static $id;
	static $note;
	static $wp_id;
	static $status;
	static $deleted;

	public function __construct( $args ){
		$this->wp_id = $args['wp_id'];
		$this->note = $args['note'];
		$this->status = $args['status'];
		$this->id = $args['id'];
	}
	
	public static function get( $id ){
		return self::row( $id );
	}

	public static function row( $id ){
		$sql = "SELECT * FROM psu_identity.person_notes
			   WHERE id = :id";
		$data = array(
				'id' => $id,
			);
		$args = \PSU::db('banner')->GetRow( $sql, $data );

		return new self ( $args );
	}

	public function save( $method = NULL ){
		//this function will save notes
		if( $method == "update" || isset($this->id) ){
			$sql = "UPDATE psu_identity.person_notes 
				      SET  note = :note,
					      status = :status 
				    WHERE id = :note_id";
			$args = array(
				'note' => $this->note,
				'status' => $this->status,		
				'note_id' => $this->id,
		
			);
		}elseif( $method == "insert" || !isset($this->id)){
			$sql = "INSERT INTO psu_identity.person_notes 
				        (wp_id,
					     note, 
						status, 
					    deleted) 
				   VALUES (:wp_id, :note, :status, :deleted)";
			$args = array(
				'wp_id' => $this->wp_id,
				'note' => $this->note,
				'status' => $this->status,
				'deleted' => 0,
			);

		}
		
		return \PSU::db('banner')->Execute( $sql, $args );

	}//end function save

	public function edit( $args = NULL){
		//this function will edit existing notes
		$this->wp_id = filter_var( $args['wp_id'], FILTER_SANITIZE_STRING );
		$this->note = filter_var( $args['note'], FILTER_SANITIZE_STRING );
		$this->status = filter_var( $args['status'], FILTER_SANITIZE_STRING );
	}//end function edit

	public function delete(){
		//this function will delete a note
		$sql = "UPDATE psu_identity.person_notes 
				 SET deleted=1 
                   WHERE id = :note_id";
		$data = array(
				'note_id' => $this->id,
		);
		return \PSU::db('banner')->Execute( $sql, $data );

	}//end function delete

	protected function _prep_args(){
		$args = array(
			'id' => $this->id,
			'wp_id' => $this->wp_id,
			'note' => $this->note,
			'status' => $this->status,
			'deleted' => $this->deleted,

		);
		return $args;
	}


}
