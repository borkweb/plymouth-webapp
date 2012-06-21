<?php

namespace PSU\Webapp;

abstract class Host {
	protected $config;

	abstract public function routes();

	public function __construct( \PSU\Config $config ) {
		$this->config = $config;
	}

	/**
	 * Load routes for this host.
	 */
	protected function routes_by_glob( $directory ) {
		$files = glob( $directory );

		foreach( $files as $file ) {
			$route = basename( $file, '.php' );
			with( '/' . $route, $file );
		}
	}

	/**
	 * Accept in the current request URI, and return it in a format
	 * suitable for klein dispatch(). For example, trim of leading
	 * directories that are ignored by this host.
	 */
	public function uri_for_dispatch( $uri ) {
		return $uri;
	}
}
