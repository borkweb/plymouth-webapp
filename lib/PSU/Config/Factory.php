<?php

namespace PSU\Config;

/**
 * 
 */
class Factory {
	static $instance = null;

	public static function set_config( \PSU\Config $config ) {
		self::$instance = $config;
	}//end set_config

	public static function get_config() {
		if( null === self::$instance ) {
			self::$instance = new \PSU\Config;
			self::$instance->load();
		}

		return self::$instance;
	}//end get_config
}//end class Factory
