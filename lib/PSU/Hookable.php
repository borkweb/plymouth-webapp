<?php

/**
 *
 */
abstract class PSU_Hookable
{
	public $hooks = array();

	/** Add a hook to the queue. */
	public function add_hook($name, $function, $priority = 50)
	{
		if(!isset($this->hooks[$name]))
		{
			$this->hooks[$name] = array();
		}

		if(!isset($this->hooks[$name][$priority]))
		{
			$this->hooks[$name][$priority] = array();
		}

		$this->hooks[$name][$priority][] = $function;
	}//end add_hook

	/** Run a hook. */
	public function do_hook($name)
	{
		if(!isset($this->hooks[$name]))
		{
			return;
		}

		// order the hooks by priority (the key)
		ksort($this->hooks[$name]);

		foreach($this->hooks[$name] as $pri => $funcs)
		{
			foreach($funcs as $func)
			{
				if( is_array( $func ) ) {
					call_user_func( $func );
				} else {
					call_user_func( array( $this, $func ) );
				}
			}
		}
	}
}//end PSU_Hookable
