<?php

namespace PSU\API\Command;

use \PSU\API\Command;

class JSON extends Command {

	/**
	 * handle the response and decode json
	 **/
	protected function process() {
		// get the response
		$response = $this->getResponse();

		// grab the body and parse it
		$result = json_decode( $response->getBody( TRUE ) );

		// if it isn't ok...throw an error
		if( 'OK' != $result->status ) {
			throw new \Exception('API Response Error: ' . $result->status);
		}//end if

		// set the result
		$this->result = $result->data;
	}//end process
}//end class PSU\API\Command\JSON
