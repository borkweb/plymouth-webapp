<?php

namespace PSU\Error;

/**
 * A DeprecationMonitor object intercepts E_DEPRECATED and E_USER_DEPRECATED errors
 * and (optionally) passes them on to a logger.
 */
class DeprecationMonitor {
	/**
	 * Storage place for the previous handler's callback.
	 */
	public $previous_handler = null;

	/**
	 * Error levels we will handle.
	 */
	public $levels = 0;

	/**
	 * Optional logger.
	 */
	public $logger = null;

	/**
	 * Object constructor to set error reporting level.
	 */
	public function __construct() {
		$this->levels = E_DEPRECATED | E_USER_DEPRECATED;
	}

	/**
	 * 
	 */
	public function log( $errno, $errstr ) {
		$trace = debug_backtrace( DEBUG_BACKTRACE_IGNORE_ARGS );

		if( $errno == E_DEPRECATED ) {
			// E_DEPRECATED is thrown internally, so reference the caller
			array_shift($trace);
		} else {
			// E_USER_DEPRECATED is thrown by our own code, so reference the caller's caller.
			// The caller is useless, since it's the line of code throwing the deprecation warning, not
			// the piece of code that's referencing the deprecated code.
			array_shift($trace);
			array_shift($trace);
		}

		$trace_string = $sep = '';
		foreach( $trace as $row ) {
			if( isset( $row['file'] ) ) {
				$trace_string .= sprintf( '%s%s:%d', $sep, $row['file'], $row['line'] );
			} else {
				$trace_string .= sprintf( '%s%s()', $sep, $row['function'] );
			}

			$sep = ', ';
		}

		$msg_html = "<br/><b>Deprecated:</b> $errstr in $trace_string<br/>";

		if( $this->logger ) {
			$this->logger->log( $msg_html, $trace );
		} else {
			print( $msg_html );
		}

		// "false" allows normal error handling to occur
		return true;
	}

	/**
	 * Attempt to set ourselves as the error handler, but only if there
	 * is not an error handler already in use.
	 */
	public static function soft_handler() {
		$monitor = new self;
		$monitor->previous_handler = set_error_handler( array( $monitor, 'log' ), $monitor->levels );

		if( $monitor->previous_handler ) {
			restore_error_handler();
			return null;
		}

		return $monitor;
	}
}
