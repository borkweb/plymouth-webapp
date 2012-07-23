<?php
namespace PSU\AR;

class MiscBillingCharges implements \IteratorAggregate {
	public $charges = array();
	public $ignore_adjustments = true;

	public function count() {
		return sizeof( (array) $this->charges );
	}//end count

	/**
	 * retrieve MiscBillingCharges
	 */
	public function get() {
		$args = array();

		if( $this->id ) {
			$args['the_id'] = $this->id;
			$where .= " AND id = :the_id";
		}//end if

		if( $this->pidm ) {
			$args['pidm'] = $this->pidm;
			$where .= " AND pidm = :pidm";
		}//end if

		if( $this->term_code ) {
			$args['term_code'] = $this->term_code;
			$where .= " AND term_code = :term_code";
		}//end if

		if( $this->data_source ) {
			$args['data_source'] = $this->data_source;
			$where .= " AND data_source = lower(:data_source)";
		}//end if

		if( $this->parent_id ) {
			$args['parent_id'] = $this->parent_id;
			$where .= " AND parent_id = :parent_id";
		} elseif( $this->ignore_adjustments ) {
			$where .= " AND parent_id IS NULL";
		}//end where

		if( $this->deleted ) {
			$where .= " AND deleted = 'Y'";
		} else {
			$where .= " AND deleted IS NULL";
		}//end if

		if( isset( $this->processed ) && $this->processed === false ) {
			$where .= " AND process_date IS NULL";
		}//end if

		$sql = "
			SELECT b.*,
			       (SELECT COUNT(1) FROM misc_billing WHERE parent_id = b.id) num_adjustments 
				FROM misc_billing b
		         JOIN spriden s
			         ON s.spriden_pidm = b.pidm
			        AND s.spriden_change_ind IS NULL
			 WHERE 1 = 1 {$where} 
			 ORDER BY UPPER(spriden_last_name), UPPER(spriden_first_name), spriden_mi, term_code, id";

		$results = \PSU::db('banner')->Execute( $sql, $args );

		return $results ? $results : array();
	}//end get

	public function getIterator() {
		return new \ArrayIterator( $this->charges );
	}//end getIterator

	/**
	 * all miscbillingcharges that have invalid addresses
	 */
	public function invalid_addresses( $it = null ) {
		if( $it === null ) {
			$it = $this->getIterator();
		}//end if

		return new \PSU\AR\MiscBillingCharges\InvalidAddressFilterIterator( $it );
	}//end invalid_address

	public function load( $rows = null ) {
		if( $rows === null ) {
			$rows = $this->get();
		}//end if

		$this->charges = array();

		foreach( $rows as $row ) {
			$class = '\PSU\AR\MiscBillingCharge\\'.ucwords( $row['data_source'] );
			$data = new $class( $row );
			$this->charges[] = $data;
		}//end foreach
	}//end load

	public static function report( $window = 150 ) {
		$data = array();
		$sql = "
			SELECT a.data_source data_source,                                                                                                                                                                                                                                    
						 a.term_code,                                                                                                                                                                                                                                                  
						 COUNT(a.pidm) number_charges,                                                                                                                                                                                                                                 
						 SUM(a.amount) charges,                                                                                                                                                                                                                                        
						 COUNT(b.pidm) number_adjustments,                                                                                                                                                                                                                             
						 SUM(nvl(b.amount,0)) adjustments,                                                                                                                                                                                                                                    
						 SUM(a.amount+NVL(b.amount,0)) final_totals                                                                                                                                                                                                                    
				FROM misc_billing a
						 JOIN stvterm
							 ON a.term_code >= stvterm_code - :term_window
							AND REGEXP_LIKE( stvterm_code, '^[0-9]{6}$' )
							AND stvterm_code = f_get_currentterm('UG')
						 LEFT JOIN misc_billing b
							 ON b.pidm = a.pidm
							AND b.parent_id IS NOT NULL
							AND b.term_code = a.term_code
			 WHERE a.parent_id IS NULL  
			 GROUP BY a.data_source, 
						 a.term_code                                                                                                                                                                                                                                                   
			 ORDER BY
						 a.data_source,                                                                                                                                                                                                                                                
						 a.term_code DESC
		";
		return \PSU::db('banner')->GetAll( $sql, array('term_window' => $window) );
	}//end report

	public function set( $var, $val ) {
		$this->$var = $val;

		return $this;
	}//end set

	/**
	 * all miscbillingcharges that are too old
	 */
	public function too_old( $it = null ) {
		if( $it === null ) {
			$it = $this->getIterator();
		}//end if

		return new \PSU\AR\MiscBillingCharges\TooOldFilterIterator( $it );
	}//end too_old

	public function total() {
		$total = 0;

		foreach( $this as $charge ) {
			$total += $charge->total();
		}//end foreach

		return $total;
	}//end total

	public function unprocessed( $it = null ) {
		if( $it === null ) {
			$it = $this->getIterator();
		}//end if

		return new \PSU\AR\MiscBillingCharges\UnprocessedFilterIterator( $it );
	}//end unprocessed

	public function unprocessed_invalid_addresses( $it = null ) {
		if( $it === null ) {
			$it = $this->getIterator();
		}//end if

		return $this->invalid_addresses( $this->unprocessed( $it ) );
	}//end unprocessed_invalid_addresses

	public function unprocessed_too_old( $it = null ) {
		if( $it === null ) {
			$it = $this->getIterator();
		}//end if

		return $this->too_old( $this->unprocessed( $it ) );
	}//end unprocessed_too_old
}//end class
