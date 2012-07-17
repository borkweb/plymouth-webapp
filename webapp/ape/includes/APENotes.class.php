<?php
/*
 * APENotes Class
 *
 * This class is used for managing users notes, editing, deleting, and creating them.
 *
 * Written by David Allen
 */
class APENotes{
	public function get_notes( $wp_id ){
		//get the notes from a person based on their wpid
			$sql = "SELECT * FROM psu_identity.person_notes WHERE wp_id = :wp_id AND deleted=0";
			$data = array(
					'wp_id'=>$wp_id,
				);
			return PSU::db('banner')->GetAll( $sql, $data );
	}//end function get_notes
	
	public function get_note( $id ){
		//get the notes from a person based on their wpid
			$sql = "SELECT  * FROM psu_identity.person_notes WHERE id = :id AND deleted=0";
			$data = array(
					'id'=>$id,
				);
		return PSU::db('banner')->GetRow( $sql, $data );
	}//end function get_notes

	public function edit_note( $note_id, $note, $status ){
		//edit a note by its id
		$sql = "UPDATE psu_identity.person_notes SET  note = :note, status = :status, deleted=0 WHERE id = :note_id";
		$data = array(
			'note' => $note,
			'status' => $status,		
			'note_id' => $note_id,
	
		);
		return PSU::db('banner')->Execute( $sql, $data );
	}//end function edit_note

	public function delete_note( $note_id ){
		//delete a note by its id
		$sql = "UPDATE psu_identity.person_notes SET deleted=1 WHERE id = :note_id";
		$data = array(
				'note_id' => $note_id,
		);
		return PSU::db('banner')->Execute( $sql, $data );
	}//end function delete_note

	public function add_note( $wp_id, $note, $status ){
		//add a note for a person
		$sql = "INSERT INTO psu_identity.person_notes (wp_id, note, status, deleted) VALUES (:wp_id, :note, :status, :deleted)";
		$data = array(
				'wp_id' => $wp_id,
				'note' => $note,
				'status' => $status,
				'deleted' => 0,
		);
		return PSU::db('banner')->Execute( $sql, $data );
	}//end function add_note

	public function note_count( $wp_id ){	
	//grab the number of notes for a user
		$sql = "SELECT COUNT(id) FROM psu_identity.person_notes WHERE wp_id = :wp_id AND deleted = 0";
		$data = array(
				'wp_id' => $wp_id,
		);
		return PSU::db('banner')->GetOne( $sql, $data );
	}//end function note_count
}//end class APENoes
