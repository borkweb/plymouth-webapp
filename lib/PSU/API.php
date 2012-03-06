<?php

namespace PSU;

require_once 'guzzle.phar';

class API {
	protected $appid;

	protected $appkey;

	protected $client;

	protected $command_class = '\\PSU\\API\\Command\\JSON';

	/**
	 * Constructor
	 **/
	public function __construct( $config = array(), $client = null ) {
		$config = \PSU::params( $config );

		// Let applications override appid and appkey
		if( defined('PSU_API_APPID') && defined('PSU_API_APPKEY') ) {
			$config['appid'] = PSU_API_APPID;
			$config['appkey'] = PSU_API_APPKEY;
		}

		$this->appid = $config['appid'];
		$this->appkey = $config['appkey'];

		if( ! $client ) {
			$this->client = $this->init_client( $config );
		}//end if
	}//end constructor

	/**
	 * returns the Guzzle client
	 **/
	public function client() {
		return $this->client;
	}//end client

	public function command_class( $command_class = null ) {
		if( null === $command_class ) {
			return $this->command_class;
		}//end if

		$this->command_class = $command_class;
	}//end command_class

	/**
	 * executes a DELETE operation
	 *
	 * @param \Guzzle\Service\Command\AbstractCommand|string $command_or_url Guzzle command or service URL
	 * @param mixed $args Arguments that are a part of the URL
	 * @param mixed $query Query string parameters
	 * @param string $command_class Class to use for execution
	 **/
	public function delete( $command_or_url, $args = array(), $query = array(), $command_class = null ) {
		return $this->run( 'delete', $command_or_url, $args, $query, $command_class );
	}//end delete

	/**
	 * executes a GET operation
	 *
	 * @param \Guzzle\Service\Command\AbstractCommand|string $command_or_url Guzzle command or service URL
	 * @param mixed $args Arguments that are a part of the URL
	 * @param mixed $query Query string parameters
	 * @param string $command_class Class to use for execution
	 **/
	public function get( $command_or_url, $args = array(), $query = array(), $command_class = null ) {
		return $this->run( 'get', $command_or_url, $args, $query, $command_class );
	}//end get

	/**
	 * executes a HEAD operation
	 *
	 * @param \Guzzle\Service\Command\AbstractCommand|string $command_or_url Guzzle command or service URL
	 * @param mixed $args Arguments that are a part of the URL
	 * @param mixed $query Query string parameters
	 * @param string $command_class Class to use for execution
	 **/
	public function head( $command_or_url, $args = array(), $query = array(), $command_class = null ) {
		return $this->run( 'head', $command_or_url, $args, $query, $command_class );
	}//end head

	/**
	 * executes a OPTIONS operation
	 *
	 * @param \Guzzle\Service\Command\AbstractCommand|string $command_or_url Guzzle command or service URL
	 * @param mixed $args Arguments that are a part of the URL
	 * @param mixed $query Query string parameters
	 * @param string $command_class Class to use for execution
	 **/
	public function options( $command_or_url, $args = array(), $query = array(), $command_class = null ) {
		return $this->run( 'options', $command_or_url, $args, $query, $command_class );
	}//end head

	/**
	 * executes a POST operation
	 *
	 * @param \Guzzle\Service\Command\AbstractCommand|string $command_or_url Guzzle command or service URL
	 * @param mixed $args Arguments that are a part of the URL
	 * @param mixed $query Query string parameters
	 * @param string $command_class Class to use for execution
	 **/
	public function post( $command_or_url, $args = array(), $query = array(), $command_class = null ) {
		return $this->run( 'post', $command_or_url, $args, $query, $command_class );
	}//end post

	/**
	 * executes a PUT operation
	 *
	 * @param \Guzzle\Service\Command\AbstractCommand|string $command_or_url Guzzle command or service URL
	 * @param mixed $args Arguments that are a part of the URL
	 * @param mixed $query Query string parameters
	 * @param string $command_class Class to use for execution
	 **/
	public function put( $command_or_url, $args = array(), $query = array(), $command_class = null ) {
		return $this->run( 'put', $command_or_url, $args, $query, $command_class );
	}//end put

	/**
	 * executes a command
	 *
	 * @param string $method HTTP method
	 * @param \Guzzle\Service\Command\AbstractCommand|string $command_or_url Guzzle command or service URL
	 * @param mixed $args Arguments that are a part of the URL
	 * @param mixed $query Query string parameters
	 * @param string $command_class Class to use for execution
	 **/
	protected function run( $method, $command_or_url, $args = array(), $query = array(), $command_class = null ) {
		// if the $command_or_url is already an object, then just execute the request
		if( is_object( $command_or_url ) ) {
			return $this->client->execute( $command_or_url );
		}//end if

		// set up args
		if( ! is_object( $args ) && ! is_array( $args ) ) {
			$args = \PSU::params( $args, $args );
		}//end if

		// set up query string
		if( ! is_object( $query ) && ! is_array( $query ) ) {
			$query = \PSU::params( $query, $query );
		}//end if

		$command_class = $command_class ?: $this->command_class;

		$command = new $command_class(
			array(
				'method' => $method,
				'service' => $command_or_url,
				'args' => $args,
				'query' => $query,
			)
		);

		return $this->client->execute( $command );
	}//end run

	/**
	 * generates a Guzzle client
	 **/
	protected function init_client( $config ) {
		$default = array(
			'base_url' => '{{scheme}}://api.{{domain}}/{{version}}/?appid={{appid}}&appkey={{appkey}}',
			'scheme' => 'https',
			'version' => '0.1',
			'domain' => \PSU::isDev() ? 'dev.plymouth.edu' : 'plymouth.edu',
		);

		$required = array(
			'appid',
			'appkey',
			'base_url',
		);

		$config = \Guzzle\Common\Inspector::prepareConfig( $config, $default, $required );

		$client = new \Guzzle\Service\Client(
			$config->get('base_url'),
			$config
		);

		$client->setConfig( $config );

		return $client;
	}//end init_client
}//end class \PSU\API
