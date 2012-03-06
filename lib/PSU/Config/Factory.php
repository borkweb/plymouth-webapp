<?php

namespace PSU\Config;

/**
 * 
 */
class Factory {
	static $instance = null;

	public function set_config( \PSU\Config $config ) {
		self::$instance = $config;
	}

	public function get_config() {
		if( null === self::$instance ) {
			self::$instance = new \PSU\Config;
			self::$instance->load();
		}

		return self::$instance;
	}
}//end class Factory
