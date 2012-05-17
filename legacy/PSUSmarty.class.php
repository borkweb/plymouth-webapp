<?php

/**
 * PSU extensions to the Smarty templating engine.
 *
 * @author   Adam Backstrom <ambackstrom@plymouth.edu>
 * @todo     finish commenting tpl functions
 */

/**
 * Required parent class.
 */
require_once('smarty/Smarty.class.php');

/**
 * Used for debugging output.
 */
require_once('PSUTools.class.php');

/**
 * A templating class providing Smarty calls and some custom functionality.
 */

/**
 * PSU extensions to the Smarty templating engine.
 *
 * @author	Adam Backstrom <ambackstrom@plymouth.edu>
 */

class PSUSmarty extends Smarty
{
	// backup tmp directories, in order of preference
	static $DEFAULT_TMP = array('/web/temp/smarty', '/web/temp', '/tmp');
	static $GLOBAL_TEMPLATES = '/web/includes_psu/templates';
	static $STYLE_TEMPLATES = '/web/pscpages/webapp/style/templates';

	const psu_prod_server = 'www.plymouth.edu';
	const psu_dev_server = 'www.dev.plymouth.edu';

	public static $js_registry = array();

	/**
	 * The production web server. Used when referencing webapp, /includes/js, etc.
	 * Override in your script if you need www2 or another server.
	 */
	public $psu_server;

	/**
	 * Note whether or not the output is in xhtml vs. html (affects closing tags, ie. <link/>)
	 *
	 * @name    $xhtml
	 */
	var $xhtml = true;

	/**
	 * Error level to generate when parse() is called.
	 */
	var $parse_error_code = E_USER_WARNING;

	/**
	 * __construct
	 *
	 * Initial object setup.
	 *
	 * @param	string|boolean $uid_or_auto indicates how cache and template directories should be set. uid specifies a unique id to build names, true generates paths automatically. leave blank or false to specify these paths yourself.
	 */
	function __construct($uid_or_auto=true)
	{
		self::$GLOBAL_TEMPLATES = PSU_LEGACY_DIR . '/templates';
		self::$STYLE_TEMPLATES = PSU_BASE_DIR . '/app/core/templates';

		parent::__construct();

		static::$js_registry = array(
			'jquery' => 'http' . ( $_SERVER['HTTPS'] ? 's':'' ) . '://ajax.aspnetcdn.com/ajax/jQuery/jquery-1.7.js',
			'jquery-ui' => 'http' . ($_SERVER['HTTPS'] ? 's' : '') . '://ajax.googleapis.com/ajax/libs/jqueryui/1.7.2/jquery-ui.min.js',
			'ddm' => '/includes/js/jquery-plugins/jquery.multi-ddm.min.js',
			'my-combined' => '/webapp/my/js/combined.js',
			'my-behavior' => '/webapp/my/js/behavior.js',
			'myjs' => '/webapp/portal/myjs/index.php',
			'style-behavior' => '/webapp/style-bootstrap/js/behavior.js',
			'bootstrap' => '/webapp/style-bootstrap/js/bootstrap.min.js',
		);

		$this->psu_server = (($_SERVER['HTTPS'] == 'on') ? 'https' : 'http') . '://';
		if( PSU::isdev() ) {
			$this->psu_server .= self::psu_dev_server;
		} else {
			$this->psu_server .= self::psu_prod_server;
		}

		// does user want us to set up smarty dirs?
		if($uid_or_auto)
		{
			// what's our temp directory?
			$tmp = null;
			if(isset($GLOBALS['TEMPORARY_FILES']))
			{
				$tmp = $GLOBALS['TEMPORARY_FILES'];
			}
			else
			{
				foreach(self::$DEFAULT_TMP as $this_tmp)
				{
					if(is_dir($this_tmp) && is_writable($this_tmp))
					{
						$tmp = $this_tmp;
						break;
					}
				}
			}

			if($uid_or_auto === true)
			{
				// true means full automatic. use BASE_URL as the unique seed
				if( !isset($GLOBALS['BASE_URL']) )
				{
					// can't be full auto without a base url.
					throw new PSUSmartyException(PSUSmartyException::NO_BASE_URL);
				}

				$md5 = md5($GLOBALS['BASE_URL']);
				$this->compile_dir = $tmp . '/smarty_tc_' . $md5;
			}
			else
			{
				// uid was set, and it wasn't true, so use the string
				$this->compile_dir = $tmp . '/smarty_tc_' . $uid_or_auto;
			}
		}
		elseif(isset($GLOBALS['SMARTY_COMPILE']))
		{
			// user did not want an auto dir, but specified a dir elsewhere
			$this->compile_dir = $GLOBALS['SMARTY_COMPILE'];
		}

		// create compile directory if it's not there yet
		if(!is_dir($this->compile_dir))
		{
			$old_umask = umask(0007);
			mkdir($this->compile_dir);
			umask($old_umask);
		}

		$this->plugins_dir[] = PSU_EXTERNAL_DIR . '/smarty/psu_plugins';

		$this->head = array(
			'js' => array(),
			'google_lazy_js' => array(),
			'css' => array()
		);

		// register any custom functions
		$this->register_function('PSU_GoBar', array($this, 'psu_gobar'));
		$this->register_function('PSU_JS', array($this, 'psu_js'));
		$this->register_function('psu_js', array($this, 'psu_js'));
		$this->register_function('PSU_GOOGLE_LAZY_JS', array($this, 'psu_google_lazy_js'));
		$this->register_function('PSU_CSS', array($this, 'psu_css'));
		$this->register_function('psu_dbug', array($this, 'psu_dbug'));
		$this->register_function('icon', array($this, 'icon'));
		$this->register_function('iconbox', array($this, 'iconbox'));
		$this->register_function('psu_puke', array($this, 'psu_puke'));
		$this->register_function('psu_progress', array($this, 'psu_progress'));
		$this->register_function('psu_authz_js', array($this, 'psu_authz_js'));
		$this->register_outputfilter(array($this, 'psu_head_includes'));
		$this->register_modifier('cdn', array($this, 'cdn'));
		$this->register_modifier('cssslug', array($this, 'cssslug'));

		$this->register_function('paging_querystring', array($this, 'paging_querystring'));
		$this->register_modifier('paging_order', array($this, 'paging_order'));

		$this->register_function('psu_messages', array($this, 'psu_messages'));
		$this->register_function('psu_errors', array($this, 'psu_errors'));
		$this->register_function('psu_successes', array($this, 'psu_successes'));
		$this->register_function('psu_warnings', array($this, 'psu_warnings'));
			
		$https = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on';

		// add in the registry
		$this->assign( 'psureg', PSU::get() );
	}//end __construct

	/**
	 * addJS
	 *
	 * Add a javascript file to the page's header.
	 *
	 * @param	string $url path to the script file
	 */
	function addJS($url, $params=false)
	{
		if( strpos( $url, '/' ) === false ) {
			$url = static::$js_registry[ $url ];
		}//end if

		$url = $this->cdn($url);
		$this->head['js'][$url] = ($params)?$params:array();
	}//end addJS
	
	/**
	 * addGoogleLazyJS
	 *
	 * Add a lazy loaded javascript file to the page's header.
	 *
	 * @param			string $url path to the script file
	 */
	function addGoogleLazyJS($params=false)
	{
		if(is_array($params))
		{
			$this->head['google_lazy_js'][] = $params;
		}//end if
	}//end addJS

	/**
	 * addCSS
	 *
	 * Add a CSS file to the page's header.
	 *
	 * @param    string $url path to the CSS file
	 * @param    array $params tag attributes, in key/value pairs
	 */
	function addCSS($url, $params=false)
	{
		$url = $this->cdn($url);
		$this->head['css'][$url] = ($params)?$params:array();
	}//end addCSS

	/**
	 * Modify a link to point to the CDN.
	 */
	function cdn( $url ) {
		return PSU::cdn($url);
	}//end cdn

	/**
	 * Convert some text to values that can be placed in a CSS identifier. Replaces non-alphanumeric
	 * characters with their hex equivalent in 0xFF form.
	 */
	public function cssslug( $string ) {
		$slug = strtolower($string);

		if( ! ctype_alnum($slug) ) {
			$tmp = '';
			for( $i = 0, $len = strlen($slug); $i < $len; $i++ ) {
				$char = $slug[$i];
				if( ! ctype_alnum($char) ) {
					$char = '0x' . ord($char);
				}
				$tmp .= $char;
			}
			$slug = $tmp;
		}

		return $slug;
	}//end cssslug

	function icon( $params, &$tpl ) {
		$icon_tpl = self::$STYLE_TEMPLATES . '/psu-icon.tpl';

		$tpl->assign('psuiconcode', $params['id']);
		$tpl->assign('psuiconcodesub', $params['sub']);
		$tpl->assign('psusubvalue', $params['value']);
		$tpl->assign('psusubvaluetype', $params['type']);
		$tpl->assign('psuiconbox', $params['boxed']);
		$tpl->assign('psuiconsize', $params['size']);
		$tpl->assign('psuiconclass', $params['class']);
		$tpl->assign('psuiconflat', $params['flat']);

		$output = $tpl->fetch($icon_tpl);

		return $output;
	}//end icon

	function iconbox( $params, &$tpl ) {
		$params['boxed'] = true;
		return $this->icon( $params, $tpl );
	}//end iconbox

	/**
	 * parse
	 *
	 * For compatibility with Xtemplates.
	 */
	function parse()
	{
		if($this->parse_error_code > 0)
		{
			trigger_error('parse() was called in PSUSmarty, are you still migrating?', $this->parse_error_code);
		}
	}//end parse

	/**
	 * psu_js
	 *
	 * Add some JS to the header via a template function.
	 */
	function psu_js($params, &$tpl)
	{
		if( ! $params['src'] && ! $params['href'] ) {
			$key = 'id';
		} else {
			$key = isset($params['src']) ? 'src' : 'href';
		}//end else
		$tpl->addJS($params[$key], $params);
	}//end psu_js

	/**
	 * Load the logged-in user's authZ data into the template. Should not be used
	 * with the old-style IDMObject.php. Provides {$AUTHZ} in the template, which
	 * is a copy of $_SESSION['AUTHZ'].
	 */
	function load_authz()
	{
		require_once('IDMObject.class.php');
		$this->assign('AUTHZ', IDMObject::authZ('all'));
	}

	/**
	 * psu_google_lazy_js
	 *
	 * Add some Google Lazy JS to the page via a template function.
	 */
	function psu_google_lazy_js($params, &$tpl)
	{
		$tpl->addGoogleLazyJS($params);
	}

	/**
	 * psu_css
	 *
	 * Add some CSS to the header via a template function.
	 */
	function psu_css($params, &$tpl)
	{
		$key = isset($params['href']) ? 'href' : 'src';
		$tpl->addCSS($params[$key], $params);
	}

	/**
	 * psu_head_includes
	 *
	 * Add CSS and JS files to the page header.
	 */
	function psu_head_includes($output, &$tpl)
	{
		// bail immediately if there aren't includes specified
		if(count($this->head['css']) == 0 && count($this->head['js']) == 0)
		{
			return $output;
		}

		$link_close = $this->xhtml ? "/" : "";

		// also bail if there is no <head> (indicates sub-template)
		preg_match('/<\s*head(?!er)[^>]*>\s+/i', $output, $head_matches, PREG_OFFSET_CAPTURE);
		if(count($head_matches) == 0)
		{
			return $output;
		}

		// init
		$html = '';
		$foot_html = '';

		// <base href=""> tag
		if( isset($GLOBALS['BASE_HREF']) )
		{
			$html .= '<base href="' . $GLOBALS['BASE_HREF'] . '">' . PHP_EOL;
		}

		foreach($this->head['css'] as $url => $params)
		{
			$media = '';
			if(isset($params['media']))
			{
				$media = 'media="' . $params['media'] . '" ';
			}
			$html .= '<link rel="stylesheet" type="text/css" ' . $media . 'href="'.$url.'"'.$link_close.'>'."\n\t";
		}

		foreach($this->head['js'] as $url => $params)
		{
			$html .= '<script type="text/javascript" src="'.$url.'"></script>'."\n\t";
/*
			if($params['head'])
			{
				$html .= $tag;
			}//end if
			else
			{
				$foot_html .= $tag;
			}//end else
*/
		}//end foreach

		// find the first head tag, and insert our new tags after
		$pos = strlen($head_matches[0][0]) + $head_matches[0][1];
		$out = substr($output, 0, $pos) . $this->host_js . $this->pre_js . $html . substr($output, $pos);
/*	
 *	this commented out code block is for js inserted before the closing body tag
		$body_match = '/(<\s*\/\s*body[^>]*>\s+)/i';
		$foot_html = $foot_html."\n\t".$this->post_js."\n\t";
		if(preg_match($body_match, $out))
		{
			$out = preg_replace($body_match, $foot_html.'\1', $out);
		}//end if
		else
		{
			$out .= $foot_html;
		}//end else
 */		
		return $out;
	}//end psu_head_includes

	/**
	 * psu_gobar
	 *
	 * Insert the Go Bar into the current page.
	 */
	function psu_gobar($params, &$tpl)
	{
		// get the template data
		$username = isset($params['username']) ? $params['username'] : $_SESSION['username'];
		$tpl->assign('psu_gobar_42199_user', $username);
		$go_html = $this->fetch('/web/pscpages/webapp/gobar/templates/gobar.tpl');
		$tpl->clear_assign('psu_gobar_42199_user');

		// make sure parent template uses the correct CSS and JS files
		$tpl->addCSS('/webapp/gobar/templates/style.css');
		$tpl->addJS('/webapp/gobar/gobar.js');

		return $go_html;
	}//end psu_gobar

	/**
	 * Output the user's authZ data as a JavaScript object.
	 */
	function psu_authz_js( $params, &$tpl )
	{
		$search = array('permission', 'role');

		$js = "<script type=\"text/javascript\">\nvar AUTHZ = {\n";
		foreach($search as $type)
		{
			$attributes = array_reduce($_SESSION['AUTHZ'][$type], create_function('$v,$w', 'return $v .= $w["attribute"] . ":1, ";'));
			$attributes = substr($attributes, 0, -2);
			$js .= "$type: { $attributes },\n";
		}

		// remove trailing comma, IE can't handle it.
		$js = substr($js, 0, -2) . "\n}\n</script>";;

		return $js;
	}//end psu_authz_js


	/**
	 * Override the normal fetch function, dropping in some template data before load.
	 * See Smarty documentation for fetch() parameters.
	 */
	function fetch($resource_name, $cache_id = null, $compile_id = null, $display = false)
	{
		// use PHP prefix like xtemplate
		$this->assign('PHP', $GLOBALS);

		// get message count
		$msg_count = isset($_SESSION['messages']) ? count($_SESSION['messages']) : 0;
		$msg_count += isset($_SESSION['errors']) ? count($_SESSION['errors']) : 0;
		$this->assign('msg_count', $msg_count);

		return parent::fetch($resource_name, $cache_id, $compile_id, $display);
	}

	/**
	 * _psu_messages
	 *
	 * List all messages currently stored in the session.
	 */
	function _psu_messages($kind, $params, &$tpl)
	{
		if($kind != 'messages' && $kind != 'errors' && $kind != 'warnings' && $kind != 'successes')
		{ 
			throw new PSUSmartyException(PSUSmartyException::UNKNOWN_MESSAGE_TYPE);
		}

		if(count($_SESSION[$kind]) == 0)
		{
			return '';
		}

		$tpl->assign('msg_messages', $_SESSION[$kind]);
		$tpl->assign('msg_class', $kind);

		if(is_file($this->template_dir . '/messages.tpl'))
		{
			$messages_tpl = $this->template_dir . '/messages.tpl';
		}
		else
		{
			$messages_tpl = self::$GLOBAL_TEMPLATES . '/messages.tpl';
		}
		
		$output = $tpl->fetch($messages_tpl);
		$_SESSION[$kind] = array();

		$tpl->clear_assign('msg_messages');
		$tpl->clear_assign('msg_class');

		return $output;
	}

	/**
	 * psu_messages
	 *
	 * Template tag handler to display messages.
	 *
	 * @param    array $params
	 * @param    PSUSmarty $tpl
	 */
	function psu_messages($params, &$tpl)
	{ 
		return $this->_psu_messages('messages', $params, $tpl); 
	}
	
	/**
	 * psu_errors
	 * 
	 * Template tag handler to display errors.
	 *
	 * @param    array $params
	 * @param    PSUSmarty $tpl
	 */
	function psu_errors($params, &$tpl)
	{ 
		return $this->_psu_messages('errors', $params, $tpl); 
	}

	/**
	 * Template tag handler to display warnings.
	 *
	 * @param    array $params
	 * @param    PSUSmarty $tpl
	 */
	function psu_warnings($params, &$tpl)
	{ 
		return $this->_psu_messages('warnings', $params, $tpl); 
	}
	
	/**
	 * Template tag handler to display success messages.
	 *
	 * @param    array $params
	 * @param    PSUSmarty $tpl
	 */
	function psu_successes($params, &$tpl)
	{ 
		return $this->_psu_messages('successes', $params, $tpl); 
	}
	
	/**
	 * psu_debug
	 * 
	 * Interface to PSUTools::dbug()
	 */
	function psu_dbug($params, $tpl)
	{
		return PSUTools::get_dbug($params['var']);
	}//end psu_dbug

	/**
	 * psu_progress
	 *
	 * Insert a progress bar into the current page.
	 */
	function psu_progress($params, &$tpl)
	{
		if($params['text'])
		{
			$params['bars'][] = array(
				'text' => $params['text'],
				'percent' => $params['percent'],
				'url' => $params['url'],
				'class' => $params['class'],
				'selected' => $params['selected']
			);
		}//end if
		elseif(!$params['text'] && !$params['bars'])
		{
			$params['bars'][] = array();
		}//end elseif
	
		// get the template data
		$tpl->assign('progress_id', $params['id']);
		$tpl->assign('progress_bars', $params['bars']);
		$progress_html = $this->fetch( self::$STYLE_TEMPLATES . '/progress_bar.tpl');

		// make sure parent template uses the correct CSS and JS files
		$tpl->addCSS( '/app/core/css/style-psuprogress.css' );

		return $progress_html;
	}//end psu_gobar

	/**
	 * psu_puke
	 * 
	 * Interface to PSUTools::puke()
	 */
	function psu_puke($params, $tpl)
	{
		return PSUTools::get_puke($params['var'], true);
	}//end psu_puke

	/**
	 * psu_recaptcha
	 *
	 * Display a captcha on the page.
	 */
	function psu_recaptcha($params, $tpl)
	{
	}//end psu_recaptcha

	/**
	 * Determines if a query string "order" parameter should be "asc" or "desc"
	 * based on the current sort field and the target sort field. You must call
	 * these fields "order" and "sort" for this to work.
	 *
	 *   PHP: $current = array('sort' => 'email', 'order' => 'asc');
	 *
	 *   // in this example, the target sort will be the same as the current sort, so we flip the order
	 *   Template:  search?{paging_querystring current=$current order=$current|@paging_order:'email' sort='email'}
	 *   Output:    search?sort=email&order=desc
	 *
	 *   // here, the sorting field changes, so we use the default ordering of 'asc'
	 *   Template:  search?{paging_querystring current=$current order=$current|@paging_order:'height' sort='height'}
	 *   Output:    search?sort=birthday&order=asc
	 *
	 * @param array $flags the current parameters you passed to paging_querystring
	 * @param string $sort_target the field the link will sort by
	 * @param string $default default sorting for this column ('asc' or 'desc')
	 */
	function paging_order($flags, $sort_target, $default = 'asc')
	{
		$default = strtolower($default) == 'desc' ? 'desc' : 'asc';

		if(!is_array($flags))
		{
			die('querystring_desc flags must be an array. did you remember your @?');
		}

		if($flags['sort'] == $sort_target)
		{
			// currently sorting by the target, flip it
			return $flags['order'] == 'desc' ? 'asc' : 'desc';
		}
		else
		{
			return $default;
		}
	}//end paging_order

	/**
	 * Set the numeric indexes of the first/prev/next/last links. Relies on count being set.
	 */
	public static function paging_set_indexes( $flags ) {
		$flags['range_start'] = $flags['offset'];
		$flags['range_end'] = $flags['offset'] + $flags['limit'];

		$flags['first'] = 0;
		$flags['prev'] = $flags['offset'] - $flags['limit'];

		$flags['next'] = $flags['offset'] + $flags['limit'];
		$flags['last'] = $flags['total'] - $flags['limit'];

		if($flags['prev'] < $flags['first']) {
			$flags['prev'] = $flags['first'];
		}

		if($flags['next'] > $flags['total']) {
			$flags['next'] = null;
		}

		if($flags['offset'] == $flags['first']) {
			$flags['prev'] = $flags['first'] = null;
		}

		if($flags['range_end'] == $flags['total']) {
			$flags['next'] = $flags['last'] = null;
		}

		return $flags;
	}//end paging_set_indexes

	/**
	 * Output a query string used for paging search results. In addition to the
	 * three parameters below, you add or override values in $current by listing
	 * them as parameters.
	 *
	 *   PHP:        $current = array('count' => 50, 'sort' => 'email', 'order' => 'desc');
	 *   Template:   page.html?{paging_querystring current=$current count=100 sort='birthday'}
	 *   Output:     page.html?count=100&sort=birthday&order=desc
	 *
	 * See paging_order if you need to intelligently update the sort order when
	 * changing the sort field.
	 * 
	 * @param array $current your current search parameters as an associative array (required)
	 * @param string $show a comma-separated list of parameters to include in the output (optional, overrides $hide)
	 * @param string $hide a comma-separated list of parameters to exclude in the output (optional)
	 */
	function paging_querystring($params, &$smarty)
	{
		$variables = $params['current'];

		if(isset($params['show']))
		{
			$tmp = array(); // vars to keep
			$show = explode(',', $params['show']);
			foreach($show as $key)
			{
				$tmp[$key] = $variables[$key];
			}
			$variables = $tmp;
		}
		elseif(isset($params['hide']))
		{
			$hide = explode(',', $params['hide']);
			foreach($hide as $key)
			{
				unset($variables[$key]);
			}
		}

		if(isset($params['arrow']))
		{
			if($params['current']['sort'] == $params['sort'])
			{
				$this->assign($params['arrow'], $params['order'] == 'desc' ? '&#9650;' : '&#9660;');
			}
			else
			{
				$this->clear_assign($params['arrow']);
			}
		}


		unset($params['current'], $params['hide'], $params['show']);

		foreach($params as $k => $v)
		{
			$variables[$k] = $v;
		}

		$qs = array();
		foreach($variables as $k => $v)
		{
			if($v !== null && $v !== '')
			{
				$qs[] = urlencode($k) . '=' . urlencode($v);
			}
		}

		$qs = implode('&', $qs);

		return $qs;
	}//end paging_querystring

	/**
	 * Remove CSS added by $this->addJS.
	 */
	public function removeCSS( $url ) {
		unset( $this->head['css'][$url] );
	}//end removeJS

	/**
	 * Remove javascript added by $this->addJS.
	 */
	public function removeJS( $url ) {
		unset( $this->head['js'][$url] );
	}//end removeJS
}//end class PSUSmarty

/**
 * Base class for our custom exception.
 */
require_once('PSUException.class.php');

/**
 * PSUSmartyException
 *
 * An exception class for PSUSmarty.
 *
 * @package Exceptions
*/
class PSUSmartyException extends PSUException
{
	const NO_BASE_URL = 1;
	const NO_TMP = 2;
	const UNKNOWN_MESSAGE_TYPE = 3;

	private static $_msgs = array(
		self::NO_BASE_URL => 'global $BASE_URL must be provided for automatic template directories',
		self::NO_TMP => 'No temp directory found, please specify with $GLOBALS[\'TEMPORARY_FILES\']',
		self::UNKNOWN_MESSAGE_TYPE => 'Unknown message type'
	);

	/**
	 * __construct
	 *
	 * Wrapper construct so PSUException gets our message array.
	 */
	function __construct($code, $append=null)
	{ 
		parent::__construct($code, $append, self::$_msgs);
	}
}

?>
