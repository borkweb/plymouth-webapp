<?php

class PSU_Oracle_Columns_RequiredFilterIterator extends PSU_FilterIterator {
	public function accept() {
		$record = $this->current();

		return $record->nullable == 'N' && !$record->default_length;
	}//end accept
}//end PSU_Oracle_Columns_RequiredFilterIterator
