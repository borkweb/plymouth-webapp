<?php

namespace Mobile;

class Directory {

	/**
	 * Public method to get the results of a search query from the Directory API
	 * @param string $query A string containing the search query
	 */
	public static function get_results($query) {
		// Initialize the search results array, in case the API fails
		$search_results = array();

		// PSU::api uses Guzzle for its HTTP responses. We need to catch an exception, in case the call fails
		try {
			// Get the search results with the PSU REST API
			$search_results = (array) \PSU::api('backend')->get('directory/search/' . urlencode( $query ) );
		}   
		catch (Guzzle\Http\Message\BadResponseException $e) {
			// Lets grab the exception and put it into the session
			$_SESSION['errors'][] = $e->getMessage();

			// Let's get the response data so we can see the problem
			$response = $e->getResponse();

			// Let's grab the HTTP status and status code
			$response_data['status'] = $response->getReasonPhrase();
			$response_data['status_code'] = $response->getStatusCode();
		}

		return $search_results;
	}

	/**
	 * Public method to clean the results returned from the API
	 * @param array $results An array containing the results obtained from the API
	 */
	public static function clean_results(&$results) {
		// Remove Title's labeled as "unknown"
		// Remove Department's labeled as "Student Distribution"
		foreach ( $results as &$result ) { 
			// Iterate over each object property in the result
			foreach ( $result as $property_name => $property ) { 
				// If the property's value is null, or it contains the word "unknown"
				if ( $property == null || stristr( $property, 'unknown' ) !== false || stristr( $property, 'Student Distribution' ) !== false ) { 
					// Remove the property
					unset( $result->$property_name );
				}   
			}   
		}
	}

} // End class Directory
