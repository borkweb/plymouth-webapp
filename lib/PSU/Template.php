<?php

namespace PSU;

/**
 * PSU extensions to the Smarty templating engine.
 *
 * @author   Adam Backstrom <ambackstrom@plymouth.edu>
 * @todo     finish commenting tpl functions
 */

/**
 * Required parent class.
 */
require_once('autoload.php');

/**
 * Used for popular link population
 */
require_once('go.class.php');

/*
 * A templating class providing Smarty calls and some custom functionality.
 */

/**
 * PSU extensions to the Smarty templating engine.
 *
 * If you use this class, please set <var>$GLOBALS['TEMPLATES']</var> to your application's template directory.
 *
 * @author	Adam Backstrom <ambackstrom@plymouth.edu>
 */

class Template extends \PSUSmarty
{
	// backup tmp directories, in order of preference
	static $DEFAULT_TMP = array('/web/temp', '/tmp');
	static $GLOBAL_STYLE = '/web/pscpages/webapp/style/templates';

	const psu_prod_server = 'www.plymouth.edu';
	const psu_dev_server = 'www.dev.plymouth.edu';

	/**
	 * The production web server. Used when referencing webapp, /includes/js, etc.
	 * Override in your script if you need www2 or another server.
	 */
	public $psu_server = 'http://www.plymouth.edu';

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
	function __construct($params = null, $fluid = false, $uid_or_auto = true)
	{
		static::$GLOBAL_STYLE = PSU_BASE_DIR . '/app/core/templates';

		parent::__construct($uid_or_auto);

		if( $_SESSION['impersonate'] ) {
			\PSU::get('log/impersonate')->write('Impersonation'.(\PSU::isDev() ? ' on dev server' : '').': accessing '.$_SERVER['REQUEST_URI'].($_SERVER['HTTP_REFERER'] ? ' via '.$_SERVER['HTTP_REFERER'] : ''), $_SESSION['username'], serialize( $_REQUEST ) );
		}//end if

		if($GLOBALS['TEMPLATES']) $this->template_dir = $GLOBALS['TEMPLATES'];

		if( !isset($GLOBALS['USE_APPLICATION_STYLE']) ) {
			$GLOBALS['USE_APPLICATION_STYLE'] = true;
		}

		if($params) 
		{
			parse_str($params, $params);

			$key = key($params);

			if(!$params[$key])
			{
				$params['page_title'] = str_replace('_', ' ', $key);
			}//end if
		}//end if

		$this->page_title = $params['page_title'];
		$this->app_title = $params['app_title'] ? $params['app_title'] : ($GLOBALS['TITLE'] ? $GLOBALS['TITLE'] : 'PSU Webapp');

		$this->fluid = $params['fluid'] ? $params['fluid'] : $fluid;

		// register any custom functions
		$this->register_block('box', array($this, 'psu_box'));
		$this->register_block('col', array($this, 'psu_col'));
		$this->register_block('message', array($this, 'psu_message'));
		$this->register_modifier('yesno', array($this, 'yesno'));
		$this->register_modifier('pluralize', array($this, 'pluralize'));
		$this->register_modifier('query_string', 'http_build_query');
		$this->register_function('myrel_access', array($this, 'myrel_access'));
		$this->register_function('myrel_list', array($this, 'myrel_list'));
		$this->register_function('randomid', array($this, 'randomid'));
		$this->register_function('nav', array($this, 'nav'));
		$this->register_function('navselect', array($this, 'navselect'));
		$this->register_modifier('bool2str', array($this, 'bool2str'));

		$this->content_classes = array(
			\PSU::isDev() ? 'webapp-dev' : 'webapp-prod'
		);

		$this->body_style_classes = array();

		$this->body_style_classes[] = strtolower( 'month-' . date('F') );
		$this->body_style_classes[] = strtolower( 'weekday-' . date('l') );
		$this->body_style_classes[] = 'week-' . date('W');
		$this->body_style_classes[] = 'day-of-year-' . date('z');
		$this->body_style_classes[] = 'day-of-month-' . date('j');
		$this->body_style_classes[] = 'hour-' . date('H');
		$this->body_style_classes[] = 'minute-' . date('i');
		if( $_SESSION['username'] ) {
			$this->body_style_classes[] = 'user-' . $_SESSION['username'];
		}//end if

		if( $_SESSION['wp_id'] ) {
			$this->body_style_classes[] = 'user-' . $_SESSION['wp_id'];
		}//end if

		if($GLOBALS['FANCY_TPL']) $this->body_style_classes[] = 'extra-tag-styles';

		$this->assign('facebook_api', \PSU::fbAPI());
		$this->assign('facebook_enable', $GLOBALS['FACEBOOK_ENABLE'] == true);

		$go = new \go($_SESSION['wp_id'] ? $_SESSION['wp_id'] : $_SESSION['username']);	

		$hot_links = $go->cacheGetSites($_SESSION['wp_id'] || $_SESSION['username'] ? 'popular-me' : 'popular-everyone');
		if(sizeof($hot_links) < 5 && $_SESSION['username']) {
			$everyone_links = $go->cacheGetSites('popular-everyone');
			$hot_links = array_merge( $hot_links, $everyone_links );
			$hot_links = array_unique( $hot_links );
		}//end if

		$this->assign('webapp_hot_links', $hot_links);

		// cdn base url; omit trailing slash
		$this->assign('cdn', substr(\PSU::cdn(), 0, -1));

		if( (\PSU::mobile() && $_COOKIE['psumobile'] != 'disable') || $_COOKIE['psumobile'] == 'force') {
			$this->mobile = true;
		}//end if
	}//end __construct

	/**
	 * Convert a boolean to string "true" or "false".
	 */
	function bool2str( $var ) {
		return $var ? 'true' : 'false';
	}//end bool2str

	/**
	 * Creates a myrelationship select box for users that have granted a specific access
	 */
	function myrel_access($params, &$smarty)
	{
		return $this->myrel_permission_grants($params, 'select');
	}//end myrel_access

	/**
	 * Creates a myrelationship unordered list for users that have granted a specific access
	 */
	function myrel_list($params, &$smarty)
	{
		return $this->myrel_permission_grants($params, 'list');
	}//end myrel_list

	/**
	 * renders a template that displays relationships that have granted a specific permission
	 */
	function myrel_permission_grants($params, $type = 'select') {
		
		if( !$params['permission'] ) {
			return '<strong><code>Permission must be specified in order to use myrel_'.$type.'</code></strong>';
		}//end if
		 
		// instantiate a new smarty object because we don't want to inherit 
		// or override any variables
		$tpl = new self();

		if( $params['user'] instanceof \PSUPerson) {
			$myuser = $params['user'];
		} elseif( $params['user'] ) {
			$myuser = \PSUPerson::get( $params['user'] );
		} elseif( $_SESSION['wp_id'] ) {
			$myuser = \PSUPerson::get( $_SESSION['wp_id'] );
		} else {
			$myuser = \PSUPerson::get( $_SESSION['pidm'] );
		}//end else
		
		if( $params['selected'] instanceof \PSUPerson) {
			$selected_user = $params['selected'];
		} elseif( $params['selected'] ) {
			$selected_user = \PSUPerson::get( $params['selected'] );
		}//end else

		$myuser->pidm;
		
		if($params['hide_self']){
			if(!isset($myuser->pidm)){
				$tpl->assign('family_member', true);
			}else {
				$tpl->assign('family_member', false);
			}
		}

		$tpl->assign('myuser', $myuser);
		$tpl->assign('selected', $selected_user);
		$tpl->assign('type', $type);
		$tpl->assign('permission', $params['permission']);
		$tpl->assign('identifier', $params['identifier'] ? $params['identifier'] : 'id');

		if( $params['url'] ) {
			if( strpos( $params['url'], '?' ) === false ) {
				$tpl->assign('no_url_params', true);	
			}
			$tpl->assign('url', $params['url']);
		}//end if

		//check if we want a question mark added to our identifier
		if( $params['no_qm'] ) {
			$tpl->assign('no_qm', true);
		}//end question mark check
		
		return $tpl->fetch( PSU_BASE_DIR . '/app/core/templates/myrelationships.permission_grants.tpl');
	}//end myrel_permission_grants
	
	/**
	 * Creates a navigation list
	 */
	function nav($params, &$smarty)
	{
		$smarty->assign('params', $params);

		$nav = $smarty->fetch( PSU_BASE_DIR . '/app/core/templates/nav.tpl');

		return $nav;
	}//end nav

	/**
	 * Creates a navigation list as a select
	 */
	function navselect($params, &$smarty)
	{
		$smarty->assign('params', $params);

		$nav = $smarty->fetch( PSU_BASE_DIR . '/app/core/templates/nav-select.tpl');

		return $nav;
	}//end navselect

	/**
	 * Return
	 */
	function pluralize( $array, $one, $many, $none = '' )
	{
		switch( count($array) )
		{
			case 0: return $none;
			case 1: return $one;
			default: return $many;
		}
	}//end pluralize

	/**
	 * Creates a content box using 960
	 */
	function psu_box($params, $content, &$smarty, &$repeat)
	{
		$params['content'] = $content;

		if($params['style']) $params['style'] .= '-box';

		$this->assign('box', $params);
		return $this->fetch( PSU_BASE_DIR . '/app/core/templates/box.tpl');
	}//end psu_box

	/**
	 * Creates a content column using 960
	 */
	function psu_col($params, $content, &$smarty, &$repeat)
	{
		$params['content'] = $content;
		
		if($params['size'])
		{
			$this->assign('webapp_max_box_size', $params['size']);
		}//end if

		$this->assign('col', $params);

		$col = $this->fetch( PSU_BASE_DIR . '/app/core/templates/col.tpl');

//		$this->clear_assign('webapp_max_box_size');

		return $col;
	}//end psu_col

	/**
	 * Creates a content box using 960
	 */
	function psu_message($params, $content, &$smarty, &$repeat)
	{
		$params['content'] = $content;

		$this->assign('msg_class', $params['type']);
		$this->assign('content', $content);
		return $this->fetch( PSU_BASE_DIR . '/app/core/templates/message.tpl');
	}//end psu_message

	/**
	 * Generate a random id for an html element.
	 */
	function randomid($params, &$smarty)
	{
		return "id-" . md5(mt_rand().time());
	}//end randomid


	/**
	 * sets the page to be rendered as a channel
	 */
	function channel($params = null, $is_channel = true)
	{
		if( !is_array($params) )
		{
			$this->channel_callback = $params;
		}//end if
		else
		{
			foreach($params as $key => $value)
			{
				$this->$key = $value;
			}//end foreach
		}//end else

		$this->is_channel = $is_channel;

		if($this->is_channel)
		{
			$this->assign('psutemplate_is_channel', true);
			require_once 'channel.class.php';
			\Channel::start();
		}//end if
	}//end channel

	function webapp_content($location, $content = null)
	{
		if(!$content)
		{
			foreach((array) $this->template_dir as $dir)
			{
				if(file_exists($template_file = $dir.'/_'.$location.'.tpl'))
				{
					$content = $this->fetch($template_file, null, null, false);
				}//end if
				elseif($location == 'head')
				{
					$content = '<h1 class="grid_16"><a href="'.$GLOBALS['BASE_URL'].'/">'.$this->app_title.'</a></h1>';
				}//end else
				elseif($location == 'host_js')
				{
					$content = $this->fetch( PSU_BASE_DIR . '/app/core/templates/_host_js.tpl');
				}//end elseif
			}//end foreach
		}//end if

		if($location == 'pre_js')
		{
			$this->pre_js = $content;
		}//end if
		elseif($location == 'host_js')
		{
			$this->host_js = $content;
		}//end elseif
		else
		{
			$this->assign('webapp_content_'.$location, $content);
		}//end else
	}//end webapp_content

	/**
	 * Convert PHP true/false to Yes/No.
	 */
	function yesno( $var )
	{
		return $var ? 'Yes' : 'No';
	}//end yesno

	function display($file, $wrap = true, $cache_id = null, $compile_id = null)
	{
		if($wrap && !$this->is_channel)
		{
			$this->assign('webapp_page_title', $this->page_title);
			$this->assign('webapp_app_title', $this->app_title);
			$this->assign('webapp_content_classes', implode(' ', $this->content_classes));

			if( isset($this->body_style_classes) ) {
				if( is_array($this->body_style_classes) ) {
					$this->assign('body_style_classes', implode(' ', $this->body_style_classes));
				} else {
					$this->assign('body_style_classes', $this->body_style_classes);
				}
			} else {
				$this->assign('body_style_classes', basename( $file, '.tpl' ) );
			}

			if( isset($this->body_id) ) {
				$this->assign('body_id', $this->body_id);
			}

			$this->addCSS('/app/core/css/all960.css');

			if( $this->fluid ) {
				$this->addCSS('/app/core/css/fluid960.css');
			}//end if

			$this->addCSS('/app/core/css/style.css?v=1.2');
			if( \PSU::isDev() && $_SESSION['username'] == 'mtbatchelder' ) {
				$this->addCSS('/app/core/css/mtbatchelder.css');
			}//end if

			if($GLOBALS['BASE_URL'] == 'http://'.$_SERVER['HTTP_HOST'].'/webapp/style')
			{
				if($_GET['old'])
				{
					$this->addCSS('/app/core/css/old_webapp_style.css');
				}//end if
			}//end if
			else
			{
			}//end if

			if($GLOBALS['channel_styles'])
			{
				$this->addCSS('/webapp/my/templates/override.css');
				$this->addCSS('/webapp/my/templates/channels.css');
			}//end if

			$this->addJS('http'.($_SERVER['HTTPS'] ? 's':'').'://ajax.aspnetcdn.com/ajax/jQuery/jquery-1.7.js', array('head'=>true));
			$this->addJS('/app/core/js/standard/psu-standard.min.js');
			$this->addJS('/app/core/js/bootstrap/bootstrap.min.js');

			if( $this->channel_container ) {
				$this->addJS('/js/jquery-plugins/shadowbox/shadowbox.js');
				$this->addCSS('/js/jquery-plugins/shadowbox/shadowbox.css');
				$this->addJS('/webapp/my/js/combined.js?v=1');
				$this->addJS('/webapp/portal/myjs/index.php?v=1.6');
				$this->addJS('/webapp/my/js/loaded.js?v=1');
			}//end if

			$this->webapp_content('css');
			$this->webapp_content('host_js');
			$this->webapp_content('pre_js');
			$this->webapp_content('js');
			$this->webapp_content('head');
			$this->webapp_content('nav');
			$this->webapp_content('avant_body');
			$this->webapp_content('body', $this->fetch($file, $cache_id, $compile_id, false));
			$this->webapp_content('apres_body');
			$this->webapp_content('foot');
			$this->webapp_content('apres_foot', $this->fetch( PSU_BASE_DIR . '/app/core/templates/apres_foot.tpl', $cache_id, $compile_id, false));
			$this->webapp_content('apres_foot_center');
	

			if( !$GLOBALS['suppress_theme'] ) {
				$this->addCSS('/webapp/themes/my/my.php');
				$this->addJS('/webapp/themes/my/js.php');
			}//end if

			$this->fetch( PSU_BASE_DIR . '/app/core/templates/main.tpl', $cache_id, $compile_id, true);
		}//end if
		else
		{
			$this->fetch($file, $cache_id, $compile_id, true);
		}//end else

		if($this->is_channel)
		{
			$params = array(
				/*'callback' => $this->channel_callback ? $this->channel_callback : '$.my.unifiedJSON',*/
				'callback' => $this->channel_callback ? $this->channel_callback : '$.my.channelLoad',
				'channel_id' => $this->channel_id ? $this->channel_id : $_GET['channel_id']
			);

			if( $this->channel_js_callback ) {
				$params['js_callback'] = $this->channel_js_callback;
			}//end if

			if( $this->state ) {
				$params['state'] = $this->state;
			}//end if

			if( $this->title ) {
				$params['title'] = $this->title;
			}//end if

			$this->addJS('http'.($_SERVER['HTTPS'] ? 's':'').'://ajax.googleapis.com/ajax/libs/jquery/1.4.2/jquery.min.js', array('head'=>true));
			$this->addJS('/webapp/portal/myjs/index.php');
			$this->addJS('http://ajax.googleapis.com/ajax/libs/jqueryui/1.7.2/jquery-ui.min.js');
			$this->addJS('/includes/js/jquery-plugins/jquery.multi-ddm.min.js');
			$this->addJS('/webapp/my/js/combined.js');
			$this->addJS('/app/core/js/behavior.js');
			$this->addJS('/webapp/my/js/behavior.js');

			$this->assign('js_init', $params['js_callback']);

			$params['template_vars'] = $this->get_template_vars();

			if( $_GET['output_method'] == 'js' || $_GET['output'] == 'js') {
				\Channel::out('callback', $params);
			} else {
				\Channel::out('html', $params);
			}
		}//end if
	}//end display

	/**
	 * _psu_messages
	 *
	 * List all messages currently stored in the session.
	 */
	function _psu_messages($kind, $params, &$tpl)
	{
		if($kind != 'messages' && $kind != 'errors' && $kind != 'warnings' && $kind != 'successes')
		{ 
			throw new Template\Exception(Template\Exception::UNKNOWN_MESSAGE_TYPE);
		}

		/* begin mahara message handling */
		$mahara = array();
		foreach((array) $_SESSION[$kind] as $key => $msg)
		{
			if( isset($_SESSION[$kind]) && !is_array($_SESSION[$kind]) ) {
				$_SESSION[$kind] = array('This message queue had errors and was reset.');
			}

			if(is_array($msg))
			{
				unset($_SESSION[$kind][$key]);

				switch($msg['type'])
				{
					case 'ok': $type = 'successes'; break;
					case 'error': $type = 'errors'; break;
					default: $type = 'messages'; break;
				}//end switch

				$mahara[$type] = true;

				$_SESSION[$type] = $msg['msg'];
			}//end if
		}//end foreach
		/* end mahara message handling */

		$tpl->assign('msg_messages', $_SESSION[$kind]);
		$tpl->assign('msg_class', $kind);

		if(is_file($this->template_dir . '/_messages.tpl'))
		{
			$messages_tpl = $this->template_dir . '/_messages.tpl';
		}
		else
		{
			$messages_tpl = self::$GLOBAL_STYLE . '/messages.tpl';
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
	

}//end PSUTemplate
