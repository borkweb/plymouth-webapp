<?php

namespace PSU;

use PSU\Config\Exception;

/**
 * Load and manage a configuration.
 */
class Config {
	/**
	 * The current configuration setting.
	 */
	protected $config = array();

	/**
	 * Name of the loaded config file.
	 */
	protected $loaded_file = null;

	/**
	 * The default config file name.
	 */
	const DEFAULT_CONFIG = 'config.ini';

	/**
	 * Attempt to autodiscover the location of the config file, starting
	 * in a specified directory, or __DIR__ if none was specified.
	 */
	protected function find( $cwd = null ) {
		if( null === $cwd ) {
			$cwd = __DIR__;
		}

		while(true) {
			if( file_exists( $config_file = $cwd . '/' . self::DEFAULT_CONFIG ) ) {
				break;
			}

			// stop if we're at the top level directory
			if( '/' === $cwd ) {
				$config_file = null;
				break;
			}

			// go up a directory
			$cwd = dirname($cwd);
		}

		return $config_file;
	}//end find

	/**
	 * Get a single variable from the config. Exclude a $section
	 * to get a variable from the global config section.
	 *
	 *     $config->get( 'api_url' );
	 *     $config->get( 'ape', 'base_url' );
	 */
	public function get( $section, $var = null, $default = null ) {
		if( null === $var ) {
			$var = $section;

			if( isset( $this->config[$var] ) ) {
				return $this->config[$var];
			}

			return array();
		}

		if( isset( $this->config[$section] ) && isset( $this->config[$section][$var] ) )
			return $this->config[$section][$var];
	
		return $default;
	}//end get

	/**
	 * Get an encoded variable from the config.
	 */
	public function get_encoded( $section, $var = null, $default = null ) {
		$value = $this->get( $section, $var, $default );

		if( $value ) {
			$value = base64_decode( $value );
		}

		return $value;
	}//end get_encoded

	/**
	 * Get a JSON-encoded variable from the config.
	 */
	public function get_json( $section, $var = null, $default = null ) {
		$value = $this->get( $section, $var, $default );

		if( $value ) {
			$value = json_decode( $value );
		}

		return $value;
	}//end get_json

	/**
	 * Load a config file. If none is specified, parent directories
	 * will be searched until config.ini is found.
	 */
	public function load( $file = null ) {
		if( null === $file || is_dir( $file ) ) {
			$file = $this->find( $file );
		}

		if( ! $file ) {
			throw new Exception( 'could not find config file' );
		}

		$this->config = parse_ini_file($file, true);
		$this->loaded_file = $file;
	}//end load

	/**
	 * Set a config variable. Exclude the section name as a shortcut
	 * to setting a global config.
	 *
	 *     $config->set( 'api_url', 'https://api.example.com/' );
	 *     $config->set( 'ape', 'base_url', 'https://ape.example.com/' );
	 */
	public function set( $section, $var = null, $value = null ) {
		if( null === $var ) {
			$value = $var;
			$var = $section;

			return $this->config[$var] = $value;
		}

		return $this->config[$section][$var] = $value;
	}//end set
}//end class Config
