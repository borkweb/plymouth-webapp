<?php

namespace PSU\API;

abstract class Command extends \Guzzle\Service\Command\AbstractCommand {
	/**
	 * prepares a request for execution
	 **/
	protected function build() {
		$method = $this->get('method');

		// ensure the appropriate method is being used
		if( null === constant( '\\Guzzle\\Http\\Message\\RequestInterface::' . strtoupper( $method ) ) ) {
			throw new \InvalidArgumentException('You must use a valid REST method.');
		}//end if

		$url = $this->get('service');

		// convert passed arguments into a collection
		if( $args = $this->get('args') ) {
			$args = new \Guzzle\Common\Collection( (array) $args );

			$url = \Guzzle\Guzzle::inject( $url, $args );
		}//end if

		// set up the request object
		$this->request = $this->client->$method( $url );

		// assign query data if passed
		if( $query = $this->get('query') ) {
			$this->request->getQuery()->merge( $query );
		}//end if
	}//end build
}//end class \PSU\API\Command
