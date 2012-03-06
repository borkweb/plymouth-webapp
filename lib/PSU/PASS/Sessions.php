<?php

/**
 *
 */
class PSU_PASS_Sessions implements IteratorAggregate {
	/**
	 * session information will be stored here.
	 */
	public $request = null;

	/**
	 * Child session objects.
	 */
	public $sessions = null;

	public function __construct( $request_id ) {
		// Assign request into this.
		$this->request = $request_id;
	}

	/**
	 * Accept in some raw, iterable sessions data and populate $this->sessions
	 * with objects.
	 */
	public function load( $sessions_rows = null ) {
		if( $sessions_rows === null ) {
			$sessions_rows = $this->sessions( $this->request );
		}

		$this->sessions = array();

		if (!$sessions_rows) return;

		foreach( $sessions_rows as $session_row ) {
			if ($session_row['request_id'] == $this->request) {
				$session = new PSU_PASS_Session( $session_row );
				if($session_row['tutor_pidm']) {
					$session->load_tutor($session_row['tutor_pidm']);
				}
				$this->sessions[] = $session;
			}
		}
		$this->total_time = $this->total_time();
	}//end load

	/**
	 * Retrieve all sessions
	 */
	public function sessions( $request_id ) {
		// Setup SQL $args
		$params = array (
			'request_id' => $request_id
		);

		// Setup $sql query. 
		$sql = "SELECT * 
		 				  FROM psu.pass_session 
						 WHERE request_id = :request_id
						 ORDER BY session_date 
						";

		// Execute and return results
		if ($results = PSU::db('banner')->Execute($sql,$params)) {
			while($row=$results->FetchRow()) {
				$rows[] = $row;
			}
			return $rows;
		}
		return null;
	}//end sessions

	/**
	 * Our sessions iterator.
	 */
	public function getIterator() {
		return new ArrayIterator( $this->sessions );
	}//end getIterator

	/**
	 * Get session times for all sessions.
	 */
	public function total_time() {
		$total_time = 0;
		foreach ( $this->sessions as $session ) {
			$total_time += $session->total_time();
		}
		return $total_time;
	}//end total_time
}//end class PSU_PASS_sessions
