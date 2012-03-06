<?php
require_once('PSUTools.class.php');
require_once('PSUEventManager.class.php');
require_once('BannerGeneral.class.php');
require_once('portal.class.php');
require_once('adldap/adLDAP.php');
require_once('webct.class.php');
require_once('workflow.class.php');

class BannerObject implements ArrayAccess
{
	public $data_loaders = array();
	public $failover_data = null;
	public $data = array();
	public $events;
	public $loader_count = 0;

	/**
	 * Add a new failover object.
	 */
	public function add_failover( $loader, $priority = null ) {
		if( !class_exists($loader) ) {
			require_once "$loader.class.php";
		}

		$l = new $loader( $this );
		$priority = isset($priority) ? $priority : $l->priority;

		if( ! $l->loader_preflight( $this->initial_identifier ) ) {
			return false;
		}

		$this->loader_count += 1;

		$this->merge_data_loaders( $l );

		if( !isset($this->failover_data[$priority]) ) {
			$this->failover_data[$priority] = array();
		}

		$this->failover_data[$priority][] = $l;
	}//end add_failover

	/**
	 * Returns true if the object has at least one loader.
	 */
	public function has_loader() {
		return $this->loader_count > 0;
	}//end has_loaders

	/**
	 * Merge in data loaders from a Loader into this banner object.
	 */
	public function merge_data_loaders( $obj, $priority = null ) {
		if( $priority === null ) {
			$priority = $obj->priority;
		}

		foreach( $obj::$loaders as $key => $loader ) {
			$loader_prefixed = '_load_' . $loader; // bio -> _load_bio

			if( method_exists($obj, $loader_prefixed) ) {
				$callback = array($obj, $loader_prefixed);
			} elseif( method_exists($obj, $loader) ) {
				$callback = array($obj, $loader);
			} else {
				throw new Exception( 'bad loader "' . $loader . '" provided in ' . get_class($obj) );
			}

			if( !isset($this->data_loaders[$key]) ) {
				$this->data_loaders[$key] = array();
			}

			if( !isset($this->data_loaders[$key][$priority]) ) {
				$this->data_loaders[$key][$priority] = array();
			}

			$this->data_loaders[$key][$priority][] = $callback;
		}
	}//end merge_data_loaders

	/**
	 * Unused ArrayAccess method.
	 */
	public function offsetExists( $key ) {
	}

	/**
	 * Implemented so that we can refer to properties as $obj.name in WordPress templates.
	 */
	public function offsetGet( $key ) {
		return $this->$key;
	}

	/**
	 * Unused ArrayAccess method.
	 */
	public function offsetSet( $key, $value ) {
	}

	/**
	 * Unused ArrayAccess method.
	 */
	public function offsetUnset( $key ) {
	}

	/**
	 * compares passed array with object properties and
	 * returns whether or not they are identical
	 *
	 * @return boolean
	 */
	public function same($data)
	{
		$same = true;
		foreach((array) $data as $key => $d)
		{
			if($this->$key != $d)
			{
				return false;
			}//end if
		}//end foreach

		return true;
	}//end same

	/**
	 * __construct
	 * 
	 * BannerObject constructor
	 *
	 * @access		public
	 */
	public function __construct()
	{	
		$this->events = new PSUEventManager;
	}//end constructor


	/**
	 * Magic isset()
	 */
	public function __isset($key)
	{
		return isset($this->data[$key]);
	}//end __isset


	/**
	 * Magic __call()
	 */
	public function __call($method, $args = null ) {
		// no possible failovers
		if( count($this->failover_data) == 0 ) {
			return null;
		}

		ksort( $this->failover_data );

		foreach( $this->failover_data as $priority => $failovers ) {
			foreach( $failovers as $failover ) {
				if( method_exists($failover, $method) ) {
					if( $args === null ) {
						return call_user_func( array($failover, $method) );
					} else {
						return call_user_func_array( array($failover, $method), $args );
					}
				}
			}
		}
	}//end __call

	/**
	 * Magic __get()
	 */
	public function &__get($key)
	{
		//if the variable is set already, return that puppy
		if( isset($this->data[$key]) )
		{
			return $this->data[$key];
		}
		elseif(method_exists($this, '_load_'.$key)) //is there a load function for the key?
		{
			$func = '_load_'.$key;
			
			$this->$func();
			
			return $this->data[$key];
		}//end elseif
		elseif(key_exists($key, (array) $this->data_loaders)) //is there another function that loads the data?
		{
			$method_load = is_string($this->data_loaders[$key]) ? '_load_' . $this->data_loaders[$key] : null;

			// try $this->_load_something()
			if( $method_load && method_exists($this, $method_load) )
			{
				$this->$method_load();
			}//end if

			// try all our data loaders
			else {
				ksort($this->data_loaders[$key]); // priority sort
				foreach( $this->data_loaders[$key] as $priority => $callbacks ) {
					foreach( $callbacks as $callback ) {
						call_user_func( $callback );
						if( isset($this->data[$key]) && null !== $this->data[$key] ) {
							return $this->data[$key];
						}
					}
				}
			}

			return $this->data[$key];
		}//end elseif
		elseif(isset($this->failover_data))
		{
			if( is_string($this->failover_data) ) {
				$failover = $this->failover_data;
				$failover =& $this->$failover;
				return $failover->$key;
			}

			ksort( $this->failover_data );

			foreach( $this->failover_data as $priority => $failovers ) {
				foreach( $failovers as $failover ) {
					// try to force a load
					$tmp = $failover->$key;

					if( isset($this->data[$key]) ) {
						return $this->data[$key];
					}
				}
			}
		}//end elseif
		$n = null;
		return $n;
	}//end __get

	/**
	 * Magic __set()
	 */
	public function __set($key, $value)
	{
		$this->data[$key] = $value;
	}//end __set

	/**
	 * Magic unset()
	 */
	public function __unset($key)
	{
		unset($this->data[$key]);
	}//end __unset

	public function destroy() {
		$this->events->destroy();

		unset($this->data);
		unset($this->failover_data);
		unset($this->data_loaders);
		unset($this->events);
	}
}//end BannerObject

class BannerObjectException extends PSUException
{
	const LIBRARY_NOT_FOUND = 1;

	private static $_msgs = array(
		self::LIBRARY_NOT_FOUND => 'The specified library could not be initialized'
	);

	/**
	 * Wrapper construct so PSUException gets our message array.
	 */
	function __construct($code, $append=null)
	{
		parent::__construct($code, $append, self::$_msgs);
	}//end constructor
}//end PSUPersonException
