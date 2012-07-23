<?php
namespace PSU\AR\Sum;

class Term implements \IteratorAggregate {
	public $data;
	public $pidm;
	public $params;
	public $terms = array();

	/**
	 * constructor
	 */
	public function __construct( $pidm, $term_codes, $params = null ) {
		$this->pidm = $pidm;
		$this->params = \PSU::params($params);

		if( $term_codes && !is_array( $term_codes ) ) {
			$term_codes = array( $term_codes );
		}//end if

		foreach( $term_codes as $term_code => $value) {
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

		$params = '';

		// parse params into parameter string
		foreach( $this->params as $key => $param ) {
			if($key == 'bill_date' || $key == 'as_of_date') {
				$params .= ", p_".$key." => to_date('" . $param ."', 'RRRR-MM-DD')";
			} else {
				$params .= ", p_".$key." => :".$key;
				$args[ $key ] = $param;
			}//end else
		}//end foreach

		// build the dml statement
		$sql = "BEGIN \n";
		foreach( $this->terms as $term => $value ) {
			$args['in_term_'.$term] = $term;
			$sql .= ":out_term_".$term." := tb_receivable.f_sum_balance(p_pidm => :pidm, p_term_code => :in_term_".$term." ".$params."); \n";
		}//end foreach
		$sql .= "END;";

		$stmt = \PSU::db('banner')->PrepareSP($sql);

		// prepare the in parameters
		foreach( $args as $key => &$value ) {
			\PSU::db('banner')->InParameter($stmt, $value, $key);
		}//end foreach

		// prepare the out parameters
		foreach( $this->terms as $term => &$value ) {
			\PSU::db('banner')->OutParameter($stmt, $this->terms[ $term ], 'out_term_'.$term);
		}//end foreach

		// execute the dml
		return \PSU::db('banner')->Execute($stmt);
	}//end get

	/**
	 * load rows into term objects
	 */
	public function load( $balances = null ) {
		if( $rows === null ) {
			$this->get();
		} else {
			$this->terms = $balances;
		}//end else
	}//end load

	public function getIterator() {
		return new \ArrayIterator( $this->data );
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
}//end class
