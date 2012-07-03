<?php

namespace PSU\Webapp;

class AppHost extends Host {
	public function routes() {
		return $this->routes_by_glob( PSU_BASE_DIR . '/routes/app/*.php' );
	}

	public function uri_for_dispatch( $uri ) {
		return substr( $uri, strlen( parse_url( $this->config->get( 'app_url' ), PHP_URL_PATH ) ) );
	}
}
