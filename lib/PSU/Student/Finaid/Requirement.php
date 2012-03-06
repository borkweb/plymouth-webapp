<?php

class PSU_Student_Finaid_Requirement extends PSU_DataObject {
	public $aliases = array(
		'rrrareq_stat_date' => 'as_of',
		'rtvtrst_desc' => 'status',
		'rtvtrst_sat_ind' => 'satisfies',
		'rtvtreq_short_desc' => 'description',
		'rtvtreq_long_desc' => 'longdesc',
		'rtvtreq_instructions' => 'instructions',
		'rtvtreq_url' => 'url',
		'rtvtreq_code' => 'code',
	);

	public function longdesc_clean() {
		$longdesc = $this->longdesc;
		$longdesc = ltrim( $longdesc, '- ' );
		return $longdesc;
	}

	/**
	 * as_of value as a unix timestamp.
	 */
	public function as_of_timestamp() {
		if( $this->as_of ) {
			return strtotime( $this->as_of );
		}

		return $this->as_of;
	}

	/**
	 * Pull the domain name out of this requirement's URL.
	 */
	public function url_domain() {
		if( ! $this->url ) {
			return null;
		}

		$domain = parse_url( $this->url, PHP_URL_HOST );
		return $domain;
	}

	/**
	 * True if the destination URL is a PDF file.
	 */
	public function url_is_pdf() {
		return ($this->url && substr( $this->url, -4 ) === '.pdf');
	}//end url_is_pdf

	public function has_instructions() {
		return ! empty($this->instructions);
	}
}
