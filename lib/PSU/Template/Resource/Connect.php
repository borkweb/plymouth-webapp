<?php

/**
 * Connect.plymouth.edu template resource for Smarty.
 */
class PSU_Template_Resource_Connect {
	public $domain = 'www.plymouth.edu';
	public $protocol = 'http://';
	public $baseurl;
	public $resource_name = 'connect';
	public $cache;

	/**
	 * How long to persist the template cache, in seconds.
	 */
	public $lifetime = 120;

	/**
	 * Constructor.
	 */
	public function __construct( $cache ) {
		$this->update_baseurl();

		$this->cache = $cache;
	}//end __construct

	/**
	 * Register this resource in the given template.
	 */
	public function register( $tpl ) {
		$tpl->register_resource( $this->resource_name, array(
			$this,
			'template',
			'timestamp',
			'secure',
			'trusted',
		) );
	}//end register

	/**
	 * Set the template fetch protocol.
	 * @param $protocol string http:// or https://
	 */
	public function set_protocol( $protocol ) {
		$this->protocol = $protocol;
		$this->update_baseurl();
	}//end set_protocol

	/**
	 *
	 */
	public function set_domain( $domain ) {
		$this->domain = $domain;
		$this->update_baseurl();
	}//end set_domain

	/**
	 * Combine protocol and domain into our baseurl.
	 */
	private function update_baseurl() {
		$this->baseurl = $this->protocol . $this->domain;
	}//end update_baseurl

	/**
	 *
	 */
	public function template( $tpl_name, &$tpl_source, &$smarty_obj ) {
		$url = $this->url( $tpl_name );

		if( $tpl_source = $this->cache->get( $this->url_key( 'source', $url ) ) ) {
			return true;
		}

		$ch = curl_init( $url );

		curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );
		curl_setopt( $ch, CURLOPT_TIMEOUT, 5 );

		$tpl = curl_exec( $ch );
		$tpl = iconv( 'UTF-8', 'ISO-8859-1', $tpl );

		$tpl_source = $tpl;

		$this->cache->set( $this->url_key('timestamp', $url), $this->generate_expiry_time() );
		$this->cache->set( $this->url_key('source', $url), $tpl, null, $this->generate_expiry_time() );

		return true;
	}//end template

	public function url_key( $key, $url ) {
		$key = $key . ':' . md5($url);
		return $key;
	}

	public function url( $tpl_name ) {
		$append = 'psu-channel=1&output=raw';

		if( strpos( $tpl_name, '?' ) === false ) {
			$tpl_name .= '?' . $append;
		} else {
			$tpl_name .= '&' . $append;
		}

		$url = $this->baseurl . $tpl_name;

		return $url;
	}//end url

	/**
	 *
	 */
	public function timestamp( $tpl_name, &$tpl_timestamp, &$smarty_obj ) {
		$url = $this->url( $tpl_name );
		$tpl_timestamp = (int)$this->cache->get( $this->url_key( 'timestamp', $url ) );

		// no record in memcached; force regeneration
		if( false == $tpl_timestamp || $tpl_timestamp < time() ) {
			$tpl_timestamp = time();
		}

		return true;
	}//end timestamp

	public function generate_expiry_time() {
		return time() + $this->lifetime;
	}//end generate_expiry_time

	/**
	 *
	 */
	public function secure( $tpl_name, &$smarty_obj ) {
		return false;
	}//end secure

	/**
	 *
	 */
	public function trusted( $tpl_name, &$smarty_obj ) {
		return false;
	}//end trusted
}//end PSU_Template_Resource_Connect
