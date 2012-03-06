<?php

namespace PSU\Error;

class Logger {
	public $file;

	public function __construct( $file ) {
		if( ! is_writable( $file ) ) {
			$file = null;
		}

		$this->file = $file;
	}

	public function log( $msg, $trace ) {
		if( ! isset($this->file) ) {
			return;
		}

		$msg = strip_tags($msg);

		// Skipping this for now, WordPress is very noisy
		if( strpos( $msg, 'Function set_magic_quotes_runtime()' ) !== false ) {
			return;
		}

		// Can't do much about Moodle errors
		foreach( $trace as $level ) {
			if( 0 === strpos( $level['file'], '/web/pscpages/webapp/courses/' ) ) {
				return true;
			}
		}

		$long_msg = sprintf( "[%s] %s\n", strftime('%F %T'), $msg );
		file_put_contents( $this->file, $long_msg, FILE_APPEND );
	}
}
