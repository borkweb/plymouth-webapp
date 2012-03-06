<?php

namespace PSU\API\Command;

use \PSU\API\Command;

class Raw extends Command {

	/**
	 * grab the response and return the body
	 **/
	protected function process() {
		// get the response
		$response = $this->getResponse();

		// grab the body and assign it to the result
		$this->result = $response->getBody( TRUE );
	}//end process
}//end class PSU\API\Command\Raw
