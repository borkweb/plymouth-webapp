<?php

require_once 'PSUTools.class.php';

class PSUEventManager {
	var $events = array();
	var $debug = false;

	public static function uniqid() {
		static $i = 0;
		return 'E' . $i++;
	}

	/**
	 *
	 */
	function __construct() {
		if( defined('PSU_EVENT_DEBUG') && PSU_EVENT_DEBUG ) {
			$this->debug = true;
		}

		$this->ident = self::uniqid();
	}//end __construct

	function destroy() {
		unset($this->events);
	}

	/**
	 * Bind to an event.
	 */
	function bind( $event, $callback, $args = null ) {
		if( !isset( $this->events[$event] ) ) {
			$this->events[$event] = array();
		}

		$idx = PSU::_filter_build_unique_id( $event, $callback, 10 );

		if( $this->debug ) {
			if( is_array($callback) ) {
				echo "[", $this->ident, "] Binding ", get_class($callback[0]), "->", $callback[1], "<br>";
			} else {
				echo "[", $this->ident, "] Binding ", $callback, "<br>";
			}
		}

		$this->events[$event][$idx] = array($callback, $args);
	}//end bind

	/**
	 * Trigger an event.
	 */
	function trigger( $event ) {
		if( !isset($this->events[$event]) ) {
			return;
		}

		foreach( $this->events[$event] as $callback_and_args ) {
			list($callback, $args) = $callback_and_args;

			if( $this->debug ) {
				if( is_array($callback) ) {
					echo "[", $this->ident, "] Calling ", get_class($callback[0]), "->", $callback[1], "<br>";
				} else {
					echo "[", $this->ident, "] Calling ", $callback, "<br>";
				}
			}

			if( isset($args) ) {
				call_user_func_array( $callback, $args );
			} else {
				call_user_func( $callback );
			}
		}
	}//end trigger
}//end PSUEventManager
