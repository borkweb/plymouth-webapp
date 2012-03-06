<?php

class PSU_Student_Finaid_Message extends PSU_DataObject {
	public $aliases = array(
		'rormesg_mesg_code' => 'code',
		'rormesg_short_desc' => 'short',
		'rormesg_full_desc' => 'full',
		'rtvmesg_mesg_desc' => 'default_desc',
	);

	public function activity_date_timestamp() {
		return strtotime($this->rormesg_activity_date);
	}

	public function clean( $str ) {
		$str = ltrim( $str, '- ' );
		return $str;
	}

	public function full_clean() {
		return $this->clean( $this->full );
	}

	public function default_desc_clean() {
		return $this->clean( $this->default_desc );
	}

	public function full_message() {
		$messages = array();

		$messages[] = $this->default_desc_clean();
		$messages[] = $this->full_clean();

		$messages = array_filter($messages);
		$messages = implode(' ', $messages);

		return $messages;
	}
}
