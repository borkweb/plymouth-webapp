<?php

class PSU_Student_Finaid_Messages implements IteratorAggregate {
	public $messages = array();

	public $pidm;
	public $aid_year;

	/**
	 * @param $pidm
	 * @param $aid_year string i.e. '1011'
	 */
	public function __construct( $pidm, $aid_year = null ) {
		$this->pidm = $pidm;
		$this->aid_year = $aid_year ? $aid_year : \PSU\Student::getAidYear();
	}

	public function load( $message_rows = null ) {
		$this->messages = array();

		if( $message_rows === null ) {
			$message_rows = $this->get_messages();
		}

		foreach( $message_rows as $message_row ) {
			$message = new PSU_Student_Finaid_Message( $message_row );
			$this->messages[] = $message;
		}
	}//end load

	/**
	 * Messages that can be viewed by student's relations.
	 *
	 * @return Iterator
	 */
	public function nonstudent_messages() {
		return new PSU_Student_Finaid_Messages_NonstudentFilterIterator( $this->messages() );
	}

	/**
	 * Non-internal messages.
	 *
	 * @return Iterator
	 */
	public function messages() {
		return new PSU_Student_Finaid_Messages_NoninternalFilterIterator( $this->getIterator() );
	}//end messages

	public function get_messages() {
		$args = array(
			'pidm' => $this->pidm,
			'aidy' => $this->aid_year,
		);

		// remove: AND RTVMESG_STOP_AWRD_PROCESS = 'Y'
		$sql = "
			SELECT RORMESG_FULL_DESC,
				   RORMESG_SHORT_DESC,
				   RORMESG_ACTIVITY_DATE,
				   RORMESG_MESG_CODE,
				   RTVMESG_MESG_DESC
			  FROM RORMESG LEFT JOIN RTVMESG ON RORMESG_MESG_CODE = RTVMESG_CODE
			   WHERE
					RORMESG_PIDM              = :pidm
					AND RORMESG_AIDY_CODE         = :aidy
					AND RORMESG_EXPIRATION_DATE   > SYSDATE
					AND ( RTVMESG_INFO_ACCESS_IND = 'Y' OR RTVMESG_INFO_ACCESS_IND IS NULL )
		";

		$rset = PSU::db('banner')->Execute( $sql, $args );
		return $rset;
	}//end get_messages

	public function getIterator() {
		return new ArrayIterator( $this->messages );
	}//end getIterator
}//end PSU_Student_Finaid_Messages
