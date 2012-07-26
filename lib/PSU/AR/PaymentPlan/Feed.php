<?php

namespace PSU\AR\PaymentPlan;

abstract class Feed extends \PSU_DataObject {
	public static $record_collection = null;
	public static $processed_collection = null;
	protected $processed = null;
	protected $records = null;

	public function date_loaded_timestamp() {
		return $this->date_loaded ? strtotime( $this->date_loaded ) : null;
	}//end date_loaded_timestamp

	public function date_parsed_timestamp() {
		return $this->date_parsed ? strtotime( $this->date_parsed ) : null;
	}//end date_parsed_timestamp

	public function date_processed_timestamp() {
		$records = $this->processed();

		$date = null;
		foreach( $records as $record ) {
			if( $record->date_processed > $date ) {
				$date = $record->date_processed;
			}//end if
		}//end foreach

		return $date ? strtotime( $date ) : null;
	}//end date_loaded_timestamp

	public function invalid_id( $it = null ) {
		if( ! $it ) {
			$it = $this->records();
			$it = $it->getIterator();
		}//end if

		return new Feed\InvalidIDFilterIterator( $it );
	}//end invalid

	public function invalid_id_count( $it = null ) {
		$invalid = $this->invalid_id( $it );

		return $invalid->count();
	}//end invalid_id_count

	public function processed( $processed = null ) {
		if( ! $this->processed ) {
			$this->processed = $processed ?: new static::$processed_collection( $this->id, $this->file_name );
			$this->processed->processed = true;
			$this->processed->load();
		}//end if

		return $this->processed;
	}//end processed

	public function processed_difference() {
		return $this->total() - $this->processed_total();
	}//end total

	public function processed_total() {
		$total = 0;

		foreach( $this->processed() as $record ) {
			$total += $record->amount();
		}//end foreach

		return round( $total, 2 );
	}//end total

	public function records( $records = null ) {
		if( ! $this->records ) {
			$this->records = $records ?: new static::$record_collection( $this->id, $this->file_name );
			$this->records->include_processed = true;
			$this->records->file_id = $this->id;
			$this->records->load();
		}//end if

		return $this->records;
	}//end records

	public function total() {
		$total = 0;

		$records = $this->records();
		foreach( $records as $record ) {
			$total += $record->amount();
		}//end foreach

		return round( $total, 2 );
	}//end total

	public function total_undisbursed() {
		$total = 0;

		foreach( $this->records() as $record ) {
			$total += \PSU::nvl( $record->funds_not_disbursed, 0 );
		}//end foreach
		return $total;
	}//end total
}//end class
