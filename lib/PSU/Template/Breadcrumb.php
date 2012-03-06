<?php

namespace PSU\Template;

class Breadcrumb {
	protected $label = null;
	protected $href = null;

	public function __construct( $label, $href = null ) {
		$this->label = $label;
		$this->href = $href;
	}

	public function __toString() {
		if( $this->href )
			return sprintf( '<a href="%s">%s</a>', htmlentities($this->href), htmlentities($this->label) );
		else
			return htmlentities($this->label);
	}
}
