<?php 

/**
 * PSUException is a wrapper class for PHP <a href="http://us.php.net/exceptions">Exceptions</a>. 
 * It provides a framework for passing identifiable exception codes and 
 * messages in a namespace-safe way. This is accomplished for class constants 
 * and an associative array containing message strings.
 *
 * @section classtemplate Class Template
 *
 * Subclasses require a specific __construct(). The following can be used as a template for your new subclass:
 *
 * <pre><code>class MyGreatException extends PSUException {
 *  /**
 *   * Wrapper construct so PSUException gets our message array.
 *   *<b></b>/
 *  function __construct($code, $append=null) {
 *    parent::__construct($code, $append, self::$_msgs);
 *  }
 *}</code></pre>
 * 
 * @section example Example Usage
 * 
 * You decide your Foo object needs to start throwing exceptions, so you add the following to the bottom of `Foo.class.php`:
 * 
 * <pre><code>require_once('PSUException.class.php');
 *class FooException extends PSUException
 *{
 *  const FE_ACCESS_DENIED = 1;
 *  const FE_NOT_ENOUGH_MONKEYS = 2;
 *
 *  private static $_msgs = array(
 *    self::FE_ACCESS_DENIED => 'You do not have access to the requested object',
 *    self::FE_NOT_ENOUGH_MONKEYS => 'You lack the required number of monkeys'
 *  );
 *
 *  /**
 *   * Wrapper construct so PSUException gets our message array.
 *   *<b></b>/
 *  function __construct($code, $append=null)
 *  {
 *    parent::__construct($code, $append, self::$_msgs);
 *  }
 *}</code></pre>
 * 
 * Now, within Foo, you can throw `FooExceptions`:
 * 
 * <pre><code>function get_next_foo($id)
 *{
 *  // magic function that says the user can't read the next foo
 *  if(!$this->can_read_next($id))
 *  {
 *    throw new FooException(FooException::FE_ACCESS_DENIED, $id);
 *  }
 *}</code></pre>
 * 
 * The above code would generate the following exception:
 * 
 * <blockquote><b>Fatal error</b>: Uncaught PSUException: You do not have access to the requested object: ''$id''. (1) thrown in '''/path/to/foo.class.php''' on line '''23'''</blockquote>
 * 
 * <var>$id</var> would be replaced by the second (optional) argument to `FooException()`. This exception can also be caught by other scripts:
 * 
 * <pre><code>try
 *{
 *  $foo->get_next_foo(5);
 *}
 *catch(FooException $e)
 *{
 *  if($e->getCode() == FooException::FE_ACCESS_DENIED)
 *  {
 *    // log the access denied error
 *  }
 *
 *  // re-throw the error
 *  throw $e;
 *}</code></pre>
 *
 * @version			$Rev: 4272 $
 * @author			Adam Backstrom <ambackstrom@plymouth.edu>
 * @copyright		2007, Plymouth State University, ITS
 */

class PSUException extends Exception {
	/**
	 * Constructor function for custom arguments.
	 *
	 * @param			string $code the name of the constant
	 * @param			string $append optional text to append to the standard message
	 * @param			array $msgs array of messages passed in from parent
	 */
	function __construct($code, $append=null, &$msgs=null)
	{
		if($msgs === null)
		{
			$msgs = array();
		}

		if(array_key_exists($code, $msgs))
		{
			// massage message string
			$message = $msgs[$code];
			$append = $append ? ': ' . $append : '';
			$message .= $append . '.';
		}
		else
		{
			$message = 'An unknown exception occured.';
		}

		// pass the fixed-up data to the parent Exception construct
		parent::__construct($message, $code);
	}//end __construct

	/**
	 * Convert the exception to a string.
	 */
	public function __toString()
	{
		return __CLASS__ . ": {$this->message} ({$this->code})\n";
	}//end __toString
}

// vim:ts=2:sw=2:noet:
?>
