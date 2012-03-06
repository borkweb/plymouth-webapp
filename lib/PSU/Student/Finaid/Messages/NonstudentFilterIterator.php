<?php

class PSU_Student_Finaid_Messages_NonstudentFilterIterator extends PSU_FilterIterator {
	public function accept() {
		$message = $this->current();
		return ! in_array($message->rormesg_mesg_code, array( '11', 'ZERO', 'SAP' ) );
	}
}//end class PSU_Student_Finaid_Messages_NoninternalFilterIterator
