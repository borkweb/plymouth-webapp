<?php

namespace PSU\Template;

/**
 * Breadcrumb container. Push breadcrumbs into the stack, then output
 * the whole thing as a string.
 */
class Breadcrumbs implements \IteratorAggregate, \Countable {
	protected $children = array();

	public function count() {
		return count( $this->children );
	}

	public function push( $crumb ) {
		if( func_num_args() > 1 ) {
			$this->children = array_merge( $this->children, func_get_args() );
			return;
		}

		if( is_array( $crumb ) ) {
			$this->children = array_merge( $this->children, $crumb );
			return;
		}

		array_push( $this->children, $crumb );
	}

	public function unshift( $crumb ) {
		array_unshift( $this->children, $crumb );
	}

	public function getIterator() {
		return new \ArrayIterator( $this->children );
	}

	public function get( $before = '', $after = '', $sep = ' / ' ) {
		$html = '';
		$first = true;

		foreach( $this as $crumb ) {
			if( $first ) {
				$first = false;
			} else {
				$html .= $sep;
			}

			$html .= (string)$crumb;
		}

		return $before . $html . $after;
	}

	public function __toString() {
		return $this->get();
	}
}
