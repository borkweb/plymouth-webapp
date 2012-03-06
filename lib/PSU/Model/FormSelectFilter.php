<?php

namespace PSU\Model;

use PSU_Model_FormSelect as FormSelect;
use PSU_Model_HTMLAttribute as HTMLAttribute;

abstract class FormSelectFilter extends FormSelect {
	public function __construct( $args ) {
		$args = \PSU::params( $args );

		$this->maxlength = new HTMLAttribute;

		parent::__construct( $args );
	}

	/**
	 * Returns the bare elements necessary for base functionality:
	 * a hidden form field with the selected value, and a text box for
	 * displaying a human-readable value.
	 */
	public function __toString() {
		if( $this->readonly ) {
			$html = $this->readonly( $this->value4key() );
		} else {
			$ro = new HTMLProperty( 'readonly', true );			
			
			$input_hidden_attr = $this->attributes2string();
			$html = "<input type='hidden' {$this->id} {$this->name}>";
			$html .= "<input type='text' {$this->size} {$this->maxlength} {$ro}>";
		}

		return $html;
	}
}
