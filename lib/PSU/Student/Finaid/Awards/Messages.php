<?php

class PSU_Student_Finaid_Awards_Messages implements IteratorAggregate, ArrayAccess {
	public $messages = array();

	public $pidm;
	public $aid_year;

	public function __construct( $pidm, $aid_year ) {
		$this->pidm = $pidm;
		$this->aid_year = $aid_year;
	}

	public function has_message( $fund_code ) {
		return isset( $this[$fund_code] );
	}

	public function load( $message_rows = null ) {
		if( $this->messages ) {
			return;
		}//end if

		if( $message_rows === null ) {
			$message_rows = $this->get_messages();
		}

		foreach( $message_rows as $message_row ) {
			$message = new PSU_Student_Finaid_Awards_Message( $message_row );
			$this->messages[ $message->fund_code ] = $message;
		}//end foreach
	}//end load

	/**
	 * Get a single message by fund code.
	 */
	public function message( $term_code ) {
		return $this[$term_code];
	}

	public function offsetGet( $key ) {
		return isset( $this->messages[$key] ) ? $this->messages[$key] : null;
	}

	public function offsetExists( $key ) {
		return isset( $this->messages[$key] );
	}

	public function offsetUnset( $key ) {
		unset( $this->messages[$key] );
	}

	public function offsetSet( $key, $value ) {
		if( is_null($key) ) {
			$this->messages[] = $value;
		} else {
			$this->messages[$key] = $value;
		}
	}

	public function get_messages() {
		$web_rules = PSU_Student_Finaid::web_rules( $this->aid_year );

		$args = array(
			'pidm' => $this->pidm,
			'aidy' => $this->aid_year,
			'fund_zero_amt' => $web_rules['fund_zero_amount'],
		);

		$sql = "
      SELECT rfrmesg_fund_code,
             rtvmesg_mesg_desc,
             rfrbase_fund_title,
             rfrbase_fund_title_long,
             rprawrd_activity_date
        FROM rfrmesg,
             rtvmesg,
             rfrbase,
             rprawrd,
             rtvawst
       WHERE rfrbase_info_access_ind = 'Y'
         AND rprawrd_fund_code = rfrbase_fund_code
         AND rprawrd_awst_code = rtvawst_code
         AND rtvmesg_info_access_ind = 'Y'
         AND rfrmesg_mesg_code = rtvmesg_code
         AND rprawrd_fund_code = rfrmesg_fund_code
         AND rprawrd_aidy_code = rfrmesg_aidy_code
         AND rprawrd_aidy_code = :aidy
         AND rprawrd_pidm = :pidm
         AND NVL(rprawrd_info_access_ind, 'Y') = 'Y'
         AND (  (:fund_zero_amt = 'N' AND rprawrd_offer_amt > 0) OR :fund_zero_amt = 'Y')
         AND rtvawst_info_access_ind = 'Y'
         AND NVL (rprawrd_offer_amt, 0) > 0
       ORDER BY rprawrd_fund_code ASC
		";

		$rset =  PSU::db('banner')->Execute( $sql, $args );
		return $rset;
	}//end get_messages

	public function getIterator() {
		return new ArrayIterator( $this->messages );
	}//end getIterator
}//end PSU_Student_Finaid_Awards_Messages
