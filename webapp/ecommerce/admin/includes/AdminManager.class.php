<?php

/**
 * AdminManager.class.php
 * 
 * ===Modification History===
 * ALPHA 22-june-2009  [nrp]  original<br/>
 */
 
 /**
  * AdminManager.class.php
  *
  * Class for handleing the management rfunctionality for the processor table
  *
  * @version	1.0
  * @module	AdminManager.class.php
  * @author	Nathan Porter <nrporter@plymouth.edu>
  * @copyright 	2009, Plymouth State University, ITS
  */

class AdminManager
{
	
	public $db;	

	/**
	 * __construct
	 *
	 * constructor for AdminManager. Connects to db
	 * 
	 * @since	version 1.0
	 * @access	public
	 */
	public function __construct(){
	
		$this->db=&$GLOBALS['BANNER'];
		//$this->db=PSUDatabase::connect('oracle/test_psu/fixcase');

	}	

	/**
	 * addProcess
	 *
	 * function to add a process to the database
	 *
	 * @since	version 1.0
	 * @access	public
	 * @param	array $process array of information about process to add
	 * @return	boolean $success returns wether or not the query succeeded
	 */
	public function addProcess($process){
		
		foreach($process as &$attr){
			$attr = $this->db->qstr(trim($attr));
		}		

		$query = "INSERT INTO PSU.ECOMMERCE_PROCESSOR (name, code, type, class) VALUES (".$process['name'].", ".$process['code'].", ".$process['type'].", ".$process['class'].")";
		//$this->db->debug=true;
		$success = $this->db->Execute($query);
		return $success;

	}

	/**
	 * getAllProcesses
	 *
	 * function called to retrieve all processes
	 *
	 * @since	version 1.0
	 * @access	public
	 */
	public function getAllProcesses(){
	
		$query = "SELECT * FROM PSU.ECOMMERCE_PROCESSOR";
		$processes = $this->db->GetAll($query);
		return $processes;
	
	}

	/**
	 * deleteProcess
	 *
	 * function called to delete a process from the database
	 *
	 * @since	version 1.0
	 * @access	public
	 * @param	integer $id id of the process to be deleted
	 * @return	boolean $success returns wether or not the delete succeeded
	 */
	public function deleteProcess($id){
		
		$query = "DELETE FROM PSU.ECOMMERCE_PROCESSOR WHERE id='".$id."'";
			
		$success = $this->db->Execute($query);
		return $success;
	}

	/**
	 * getProcess
	 *
	 * return an associative array of a process
	 *
	 * @since	version 1.0
	 * @access	public
	 * @param	integer $id id of the process to be retrieved
	 * @return	array $process returns an associative array process of a process
	 */
	public function getProcess($id){
	
		$query = "SELECT * FROM PSU.ECOMMERCE_PROCESSOR WHERE id='".$id."'";
		$process = $this->db->GetRow($query);
		return $process;
	
	}

	/**
	 * getProcess
	 *
	 * update a process in the database
	 *
	 * @since	version 1.0
	 * @access	public
	 * @param	array $process associative array of information to update a process
	 * @return boolean $success returns wether or not the update succeeded
	 */
	public function updateProcess($process){
	
		foreach($process as &$attr){
			$attr = $this->db->qstr(trim($attr));
		}
		
		$query = "UPDATE PSU.ECOMMERCE_PROCESSOR 
				  SET name=".$process['name'].", 
					  code=".$process['code'].", 
					  type=".$process['type'].", 
					  class=".$process['class']." 
				  WHERE id=".$process['id'];

		$success = $this->db->Execute($query);
		return $success;
	}

}

?>
