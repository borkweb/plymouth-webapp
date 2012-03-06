<?php

class PSU_Student_Finaid_Messages_NoninternalFilterIterator extends PSU_FilterIterator {
	public function accept() {
		$message = $this->current();
		return $message->rormesg_mesg_code != 'INT';
	}
}//end class PSU_Student_Finaid_Messages_NoninternalFilterIterator
