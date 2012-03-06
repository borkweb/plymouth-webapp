<?php

class PSU_AR_Sum_Balances implements IteratorAggregate {
	public $data;
	public $pidm;
	public $params;
	public $terms = array();

	/**
	 * constructor
	 */
	public function __construct( $pidm, $term_codes, $params = null ) {
		$this->pidm = $pidm;
		$this->params = PSU::params($params);

		if( $term_codes && !is_array( $term_codes ) ) {
			$term_codes = array( $term_codes );
		}//end else

		foreach( $term_codes as $term_code ) {
			$this->terms[ $term_code ] = null;
		}//end foreach

		ksort( $this->terms );
	}//end __construct

	/**
	 * retrieve memos for a person
	 */
	public function get() {
		$args = array(
			'pidm' => $this->pidm,
		);

		$args = PSU::params($args, $this->params);

		$sql = "SELECT ";
		foreach( $this->terms as $term => &$value ) {
			$alias = 't'.$term;

			$sub = "(
				nvl((
					SELECT SUM( tbraccd_amount * DECODE( tbbdetc_type_ind, 'P', -1, 1) )
						FROM tbraccd
						     JOIN tbbdetc
								   ON tbbdetc_detail_code = tbraccd_detail_code
					 WHERE tbraccd_pidm = :pidm
						 AND tbraccd_term_code = :{$alias}
				), 0) + nvl((
					SELECT SUM( tbrmemo_amount * DECODE( tbbdetc_type_ind, 'P', -1, 1) )
						FROM tbrmemo
						     JOIN tbbdetc
								   ON tbbdetc_detail_code = tbrmemo_detail_code
					 WHERE tbrmemo_pidm = :pidm
						 AND tbrmemo_term_code = :{$alias}
						 AND tbrmemo_billing_ind = 'Y'
						 AND tbrmemo_expiration_date >= sysdate
				), 0)
			) \"{$term}\", ";

			$args[ $alias ] = $term;

			$sql .= $sub;
		}//end foreach

		$sql = substr( $sql, 0, -2 );
		$sql .= " FROM dual";

		$results = PSU::db('banner')->GetRow( $sql, $args );
		return $results;
	}//end get

	/**
	 * load rows into term objects
	 */
	public function load( $balances = null ) {
		if( $balances === null ) {
			$balances = $this->get();
		}//end else

		if( $balances ) {
			foreach( $balances as $term => $balance ) {
				$this->terms[ $term ] = $balance;
			}//end foreach
		}//end if

		return $this->terms;
	}//end load

	public function getIterator() {
		return new ArrayIterator( $this->data );
	}//end getIterator

	/**
	 * Return an iterator for all known terms.
	 * @return Iterator
	 */
	public function terms() {
		return $this->terms;
	}//end terms

	/**
	 * Return an iterator for all known term codes.
	 * @return Iterator
	 */
	public function termcodes() {
		return array_keys($this->terms);
	}//end termcodes
}//end class PSU_AR_Sum_Balances
