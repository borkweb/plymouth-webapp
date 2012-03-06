<?php

class PeopleFu {
	public static function get() {
		return new self;
	}
}

// add api instance shortcut
PSU::get()->add_shortcut( 'peoplefu', array('PeopleFu', 'get') );

// add database shortcut
PSU::get()->add_database( 'peopledb', 'oracle/peopledb/fixcase' );
