<?php

/**
 * A class for handling metadata.
 */
class MyMeta {
	public $key;
	public $value;
	public $id;
	public $changed = false;

	/**
	 * Class our metadata came from. Keep track of this for layout
	 * cloning purposes, so we can clone userchannel meta but not
	 * channel meta.
	 */
	public $class;

	private $container; // our parent container
	private $parent; // our parent portalobject (MyUserTab, MyChannel, MyUserChannel, etc.)

	/**
	 * Meta constructor.
	 * @param $value
	 * @param $key
	 * @param $id
	 */
	public function __construct( $value = null, $key = null, $id = null ) {
		$this->id = $id;
		$this->key = $key;
		$this->value = $value;
	}//end __construct

	/**
	 * Get or set the MetaContainer.
	 */
	public function container( MyMetaContainer $container = null ) {
		if( $container !== null ) {
			$this->container = $container;
		}

		return $this->container;
	}//end parent

	/**
	 * Get or set the parent.
	 */
	public function parent( $parent = null ) {
		if( $parent !== null ) {
			$this->parent = $parent;
		}

		return $this->parent;
	}//end parent

	/**
	 * magic toString
	 */
	public function __toString(){
		return $this->value;
	}//end __toString
}//end class MyMeta
