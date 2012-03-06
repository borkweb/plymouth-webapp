<?php

namespace PSU\Model;

/**
 * @ingroup psumodels
 */
class FormSelectStates extends FormSelect {
	public function __construct( $args = array() ) {
		parent::__construct( $args );

		$this->options = self::_get_states();
	}

	public static function _get_states() {
		static $states = array(
			  array('AL', 'Alabama')
			, array('AK', 'Alaska')
			, array('AZ', 'Arizona')
			, array('AR', 'Arkansas')
			, array('CA', 'California')
			, array('CO', 'Colorado')
			, array('CT', 'Connecticut')
			, array('DC', 'District of Columbia')
			, array('DE', 'Delaware')
			, array('FL', 'Florida')
			, array('GA', 'Georgia')
			, array('HI', 'Hawaii')
			, array('ID', 'Idaho')
			, array('IL', 'Illinois')
			, array('IN', 'Indiana')
			, array('IA', 'Iowa')
			, array('KS', 'Kansas')
			, array('KY', 'Kentucky')
			, array('LA', 'Louisiana')
			, array('ME', 'Maine')
			, array('MD', 'Maryland')
			, array('MA', 'Massachusetts')
			, array('MI', 'Michigan')
			, array('MN', 'Minnesota')
			, array('MS', 'Mississippi')
			, array('MO', 'Missouri')
			, array('MT', 'Montana')
			, array('NE', 'Nebraska')
			, array('NV', 'Nevada')
			, array('NH', 'New Hampshire')
			, array('NJ', 'New Jersey')
			, array('NM', 'New Mexico')
			, array('NY', 'New York')
			, array('NC', 'North Carolina')
			, array('ND', 'North Dakota')
			, array('OH', 'Ohio')
			, array('OK', 'Oklahoma')
			, array('OR', 'Oregon')
			, array('PA', 'Pennsylvania')
			, array('RI', 'Rhode Island')
			, array('SC', 'South Carolina')
			, array('SD', 'South Dakota')
			, array('TN', 'Tennessee')
			, array('TX', 'Texas')
			, array('UT', 'Utah')
			, array('VT', 'Vermont')
			, array('VA', 'Virginia')
			, array('WA', 'Washington')
			, array('WV', 'West Virginia')
			, array('WI', 'Wisconsin')
			, array('WY', 'Wyoming')
		);

		return $states;
	}
}
