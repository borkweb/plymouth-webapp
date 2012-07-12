<?php

namespace PSU\AR;

class PreBilling {
	public static function add_memo( $args ) {
		if( ! isset( 
			$args->pidm, 
			$args->term_code, 
			$args->date, 
			$args->amount, 
			$args->detail_code 
		) ) {
			throw new \Exception('Pidm, term_code, date, amount, and detail_code must be specified');
		}//end if

		$detail_code = \PSU_AR::detail_code( $args->detail_code );

		$data = array(
			'pidm' => $args->pidm,
			'tran_number' => \PSU_AR_Memos::max_tran_number( $args->pidm ) + 1,
			'term_code' => $args->term_code,
			'detail_code' => $detail_code->detail_code,
			'user' => 'PREBILLING',
			'entry_date' => $args->date,
			'desc' =>  $detail_code->desc,
			'expiration_date' => strtotime('+5 days', $args->date),
			'effective_date' => $args->date,
			'activity_date' => $args->date,
			'srce_code' => 'M',
			'billing_ind' => 'N',
			'create_user' => 'PREBILLING',
			'amount' => $args->amount,
		);
		$memo = new \PSU_AR_Memo( $data );
		return $memo->save();
	}//end add_memo

	public static function apply( $full_part_inds, $residencies, $rate_codes, $fee_codes ) {
		static $fees;

		$memo_term = self::memo_term();

		if( ! $fees[ $memo_term ] ) {
			$fees[ $memo_term ] = new \PSU\AR\Fees\RegistrationFees( $memo_term );
			$fees[ $memo_term ]->load();
		}//end if

		if( ! is_array( $residencies ) ) {
			$residencies = array( $residencies );
		}//end if

		if( ! is_array( $full_part_inds ) ) {
			$full_part_inds = array( $full_part_inds );
		}//end if

		foreach( $full_part_inds as $full_part_ind ) {
			foreach( $residencies as $residency ) {
				foreach( $rate_codes as $rate_code ) {
					$filtered_fees = array();

					foreach( $fee_codes as $fee_code ) {
						$filter = $fees[ $memo_term ]->detail_code( $fee_code );

						if( $residency ) {
							$filter = $fees[ $memo_term ]->residential_code( $residency, $filter );
						}//end if

						$filtered_fees[] = $fees[ $memo_term ]->rate( $rate_code, $filter );
					}//end foreach

					$population = self::population( array(
						'full_part' => $full_part_ind,
						'residential_code' => $residency,
						'rate_code' => $rate_code,
					));

					$date = time();

					foreach( $population as $person ) {
						$person = (object) $person;

						$args = new \Stdclass;
						$args->pidm = $person->pidm;
						$args->term_code = $memo_term;
						$args->date = $date;
						$args->styp_code = $person->styp_code;

						foreach( $filtered_fees as $fee_collection ) {
							foreach( $fees[ $memo_term ]->student_type( $args->styp_code, $fee_collection ) as $fee ) {
								$args->amount = $fee->flat_fee_amount;
								$args->detail_code = $fee->detail_code;

								self::add_memo( $args );
							}//end foreach
						}//end foreach
					}//end foreach
				}//end foreach
			}//end foreach
		}//end foreach
	}//end apply

	/**
	 * clear all prebilling memos
	 */
	public static function clear() {
		$sql = "DELETE FROM tbrmemo WHERE tbrmemo_user = 'PREBILLING'";
		return \PSU::db('banner')->Execute( $sql );
	}//end clear

	/**
	 * return the prebilling_current_term from the parameter table
	 */
	public static function current_term() {
		static $term_code;

		if( ! $term_code ) {
			$term_code = \PSU::db('banner')->GetOne("SELECT value FROM gxbparm WHERE param = 'prebilling_current_term'");
		}//end if

		return $term_code;
	}//end current_term

	/**
	 * return the prebilling_memo_term from the parameter table
	 */
	public static function memo_term() {
		static $term_code;

		if( ! $term_code ) {
			$term_code = \PSU::db('banner')->GetOne("SELECT value FROM gxbparm WHERE param = 'prebilling_memo_term'");
		}//end if

		return $term_code;
	}//end memo_term

	public static function population( $params ) {
		$params = (object) $params;

		$args = array();
		$where = "";

		if( $params->full_part ) {
			$where .= "AND full_part_ind = :full_part ";
			$args['full_part'] = $params->full_part;
		}//end if

		if( $params->residential_code ) {
			$where .= "AND resd_code = :residential_code ";
			$args['residential_code'] = $params->residential_code;
		}//end if

		if( $params->rate_code ) {
			$where .= "AND rate_code = :rate_code ";
			$args['rate_code'] = $params->rate_code;
		}//end if

		if( $params->styp_code ) {
			$where .= "AND styp_code = :styp_code ";
			$args['styp_code'] = $params->styp_code;
		}//end if

		$sql = "
			SELECT *
			  FROM v_prebilling_candidates
			 WHERE 1 = 1 {$where}
		";

		$results = \PSU::db('banner')->Execute( $sql, $args );

		return $results;
	}//end population
}//end class
