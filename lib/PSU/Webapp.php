<?php

namespace PSU;

class Webapp {
	protected $config;
	protected $host;

	public function __construct( \PSU\Config $config ) {
		$this->config = $config;
	}

	public function set_host_by_domain( $domain ) {
		if( in_array( $domain, explode( ',', $this->config->get( 'go', 'domain', 'go.plymouth.edu,go' ) ) ) ) {
			$this->host = new Webapp\GoHost( $this->config );
		} else {
			$this->host = new Webapp\AppHost( $this->config );
		}
	}

	public function host() {
		return $this->host;
	}
}
