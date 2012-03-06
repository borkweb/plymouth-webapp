<?php

namespace PSU\TeacherCert;

class ChecklistItems extends Collection {
	static $_name = 'Checklist Items';
	static $child = '\\PSU\\TeacherCert\\ChecklistItem';
	static $table = 'checklist_items';

	static $parent_key = 'gate_id';

	/**
	 *
	 */
	public function _get_order() {
		return 'name';
	}//end get_order
}//end class PSU\TeacherCert\ChecklistItems
