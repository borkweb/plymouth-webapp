<?php

namespace PSU\Model;

class HTMLProperty extends HTMLAttribute {
	public function __toString() {
		if( ! $this->value ) {
			return '';
		}

		return " {$this->attribute}";
	}
}
