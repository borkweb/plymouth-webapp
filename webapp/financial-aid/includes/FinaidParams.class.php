<?php

class FinaidParams implements ArrayAccess {
	const SESSION_KEY = 'webapp-finaid';

	public function offsetExists( $key ) {
		return isset( $_SESSION[ self::SESSION_KEY ] );
	}

	public function offsetGet( $key ) {
		return $_SESSION[ self::SESSION_KEY ][ $this->key($key) ];
	}

	public function offsetSet( $key, $value ) {
		if( !isset( $_SESSION[ self::SESSION_KEY ] ) ) {
			$_SESSION[ self::SESSION_KEY ] = array();
		}

		$_SESSION[ self::SESSION_KEY ][ $this->key($key) ] = $value;
	}

	public function offsetUnset( $key ) {
		unset( $_SESSION[ self::SESSION_KEY ][ $this->key($key) ] );
	}

	public function key( $key ) {
		return $key;
	}
}
