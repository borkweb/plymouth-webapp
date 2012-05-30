<?php

abstract class MyMagicGetters {
	public $data = array();

	/**
	 * Magic getter.
	 */
	public function &__get($k) {
		return $this->data[$k];
	}//end __get

	/**
	 * Magic setter.
	 */
	public function __set($k, $v) {
		$this->data[$k] = $v;
	}//end __set

	/**
	 * Magic isset.
	 */
	public function __isset($k) {
		return isset($this->data[$k]);
	}//end __isset

	/**
	 * Magic unset.
	 */
	public function __unset($k) {
		unset($this->data[$k]);
	}//end __unset
}//end class MyMagicGetters
