<?php

/*
 * PSUController.class.php
 *
 * PSU Controller API
 *
 * @version		1.0.0
 * @author		Adam Backstrom <ambackstrom@plymouth.edu>
 * @copyright 2010, Plymouth State University, ITS
 */ 

require_once 'autoload.php';

/**
 * The controller class allows for a centralized handler for delegating web requests
 * into functions. The path of the web request is used to determine which controller
 * method should handle the request. For example, given an app BASE_DIR of http://my.plymouth.edu/,
 * and a request of http://my.plymouth.edu/tab/edit, the controller would attempt to
 * server the request first with Controller::tab_edit(), then with Controller::tab($edit).
 *
 * \section setup Setup
 *
 * First, set up an .htaccess file that routes all web requests through a single file:
 *
 * <pre><code>RewriteEngine on
 *RewriteCond %{REQUEST_FILENAME} !-f
 *RewriteCond %{REQUEST_FILENAME} !-d
 *RewriteRule (.*) index.php/$1 [L,QSA]
 *RewriteRule ^$ index.php/ [L,QSA]</code></pre>
 *
 * In the above example, common.php is not necessary because web requests are already
 * filtered through a single script, making an auto_prepend_file redundant.
 *
 * Next, subclass PSUController:
 *
 * <pre><code>class SomeController extends PSUController {
 *    public function users() {
 *        // some view logic
 *    }
 *}</code></pre>
 *
 * In your index.php file, do your normal initialization (databases, objects and whatnot) and
 * then delegate the request to your controller:
 *
 * <pre><code>&lt;?php
 *
 *require_once 'PSUTools.class.php';
 *PSU::session_start();
 *
 *include 'whatever/goes/here.class.php';
 *include 'some/other/dependency.class.php'; // some amount of init will happen
 *
 *SomeController::delegate();
 *?&gt;</code></pre>
 *
 * \section usage Usage
 *
 * Now that your controller is set up to handle reqeusts, load your app in the browser. Elements
 * of your URL will be translated into method names as PHP examines the $_SERVER['PATH_INFO']
 * variable. Forward slashes in the path will be translated into underscores (ex. path/to/something
 * changes to path_to_something), and the controller will look to see if it has a method
 * matching that path.
 *
 * The controller will attempt to treat latter parts of the path as arguments if a method
 * name is not found. In the above SomeController:users() example, a request for /users/ambackstrom
 * will first look for the method SomeController::users_ambackstrom(), then fall back to
 * SomeController::users('ambackstrom').
 */
class PSUController 
{
	/**
	 * A list of overloaded methods that cannot be discovered using method_exists().
	 * Used in conjunction with __call().
	 */
	public $overloaded = array();

	/**
	 * constructor
	 *
	 * @param $title \b page title
	 */
	public function __construct( $title = null)
	{
		$this->tpl = new \PSU\Template($title);
	}//end __construct

	/**
	 * Delegates page rendering.
	 *
	 * @param string $path the path to serve
	 * @param string $controller_class class to instantiate as the controller
	 */
	public static function delegate( $path = null, $controller_class = null ) 
	{
		static $controller = null;

		// if no path is specified, grab it from $_SERVER
		if( $path === null ) 
		{
			$path = $_SERVER['PATH_INFO'];
		}//end if

		// remove leading and trailing slashes
		$path = trim($path, '/');

		$original_path = explode( '/', $path );

		// translate directory separators to underscores
		$method = strtolower($path);
		$method = strtr($method, '/', '_');

		// if no method, create index
		if( $method == '' ) 
		{
			$method = 'index';
		}//end if

		$method_parts = explode("_", $method);

		if( $controller === null ) 
		{
			if( !$controller_class )
			{
				$controller = new static;
			}//end if
			else
			{
				// check if a class exists that matches the controllerclass_methodpart
				// e.g. MyController_tab for a url of my/tab/welcome
				if( strpos($method_parts[0], '-') === false && class_exists($controller_class . '_' . $method_parts[0]))
				{
					$controller_class = $controller_class.'_'.$method_parts[0];
					$controller = new $controller_class;
					array_shift($method_parts);

					// if we just bumped the last element off $method_parts, make sure
					// we still check for an index method
					if( count($method_parts) == 0 ) {
						$method_parts = array('index');
					}
				}//end if
				else
				{
					$controller = new $controller_class;
				}//end else
			}//end else
		}//end if

		/*
		 * iterate over all segments in a $method (separated by underscore)
		 * and find the most specific controller method. as we iterate, trailing
		 * segments become arguments to a parent handler. Given tab_edit_welcome:
		 *
		 * Where class_exists($controller_class.'_tab'):
		 * \li ControllerClass_tab::edit_welcome()
		 * \li ControllerClass_tab::edit('welcome')
		 * \li ControllerClass_tab::index('edit', 'welcome')
		 *
		 * Where !class_exists($controller_class.'_tab'):
		 * \li tab_edit_welcome()
		 * \li tab_edit('welcome')
		 * \li tab('edit', 'welcome')
		 * \li view('tab', 'edit', 'welcome')
		 */
		
		$args = array();
		$controller->original_path = $original_path;

		/*
		 * The very first hyphen is either an argument, or a method whose hyphen will be translated
		 * to an underscore. You would never have part-1/part-2/ where part-2 is in the method name,
		 * because the hyphen in part-1 must be translated.
		 */ 
		$first_hyphen = null;
		foreach( $method_parts as $index => $part ) {
			if( strpos($part, '-') !== false ) {
				$first_hyphen = $index;
				break;
			}
		}

		if( $first_hyphen !== null ) {
			for( $i = count($method_parts); $i > $first_hyphen + 1; $i-- ) {
				$part = array_pop($method_parts);
				array_unshift($args, $part);
			}

			$last = count($method_parts) - 1;
		}

		//
		// Search the controller for a matching method
		//

		for( $i = 0, $len = count($method_parts); $i < $len; $i++ ) 
		{
			$last = count( $method_parts ) - 1;
			$tail = $method_parts[$last];

			$method_parts[$last] = str_replace('-', '_', $method_parts[$last]);
			$try_method = implode('_', $method_parts); // reimplode in case this was modified by the class check

			if( method_exists( $controller, $try_method ) || in_array($try_method, $controller->overloaded) ) 
			{
				return call_user_func_array( array($controller, $try_method), $args );
			}

			array_pop($method_parts);
			array_unshift($args, $tail); // put un-replaced tail item at the front of our arguments list

			$try_method = implode("_", $method_parts);
		}

		//
		// Past this point, no method was found
		//
		
		// is there a custom 404 handler in the controller?
		if( method_exists( $controller, 'handle_404' ) ) {
			return call_user_func_array( array($controller, 'handle_404'), $args );
		}
		
		header('HTTP/1.1 404 Not Found');
		die("Page not found: " . $method);
	}//end delegate
	
	public function display( $file = null ) 
	{
		if( $file === null ) 
		{
			$trace = debug_backtrace(false);
			$file = $trace[1]['function'] . ".tpl";
		}//end if

		$this->tpl->display($file);
	}//end display

	public function index() 
	{
		$this->display();
	}//end index
}//end class PSUController
