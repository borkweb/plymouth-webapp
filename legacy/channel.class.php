<?php

/*
 * channel.class.php
 *
 * === Modification History ===<br/>
 * ALPHA 03-may-2006  [zbt]  original<br/>
 * 0.6   28-feb-2007  [mtb]  added callback functions
 * 1.0   17-jan-2008  [zbt]  made object oriented
 * 2.0   11-jun-2008  [mtb]  cleaned up and renamed functions
 */

require_once 'PSUTools.class.php';

/**
 * Code for simplifying channel creation.
 *
 * @version		2.0
 * @module		channel.class.php
 * @author		Zachary Tirrell <zbtirrell@plymouth.edu>, Matthew Batchelder <mtbatchelder@plymouth.edu>
 * @copyright 2008, Plymouth State University, ITS
 */ 
class Channel
{
	/**
	 * Generate a callback function for the given text
	 *
	 * @since version 2.0.0
	 * @param $text \b string Text to output
	 * @param $callback \b string Callback function
	 * @param $params \b mixed Parameters to be appended to the JS callback
	 */
	public function callback($text, $callback, $params)
	{
		//callback is being passed in separately...ensure that its corresponding params entry is unset
		unset($params['callback']);
		
		$find = array(
			'\\',
			"'",
			"\n",
			"\r",
			"\t",
			"document.write('');\n",
		);
		
		$replace = array(
			'\\\\',
			"\'",
			"'+\n'",
			'',
			'',
			'',
		);

		//create a variable to put the page content into
		$text="var the_text_to_output='".str_replace($find,$replace,$text)."';\n";
		
		//begin the callback
		$output = $text.strip_tags($callback).'(the_text_to_output';
		
		//are there parameters set?
		if(is_array($params) && !empty($params))
		{
			//yup!  implode those puppies and append to the output
			$output .= ',"'.implode('","',$params).'"';
		}//end if
		
		//finish off the output
		$output .= ');';
		
		return $output;
	}//end callback

	/**
	 * Retrieves the state of a given parameter
	 *
	 * @since		version 2.0.0
	 * @param   $wpid \b int Person identifier
	 * @param   $meta \b string Meta tag
	 * @param   $callback_when_default \b string Callback when defaulting to base state
	 */
	public function getState($wpid, $meta, $callback_when_default = '') {
		$state = PSU::db('go')->GetOne("SELECT value FROM user_meta WHERE wp_id=? AND name=?", array( $wpid, $meta ));
		if($state === false) {
			if($callback_when_default) {
				$state = $callback_when_default($meta);
			} else {
				$state = PSU::db('go')->GetOne("SELECT value FROM user_meta WHERE wp_id=0 AND name=?", array( $meta ));
			}//end else
		}//end if
		return $state;
	}//end getState

	/**
	 * html
	 *
	 * Outputs the channel in the default channel html
	 *
	 * @since		version 2.0.0
	 * @param  	string $text Text to output
	 * @param   mixed $params Parameters to be appended to the JS callback
	 */
	public function html($text, $params = false)
	{
		include_once('PSUTemplate.class.php');
		$GLOBALS['NEW_STYLE'] = true;
		$tpl = new PSUTemplate();
		$tpl->channel_container = true;

		foreach( (array) $params['template_vars'] as $key => $value ) {
			$tpl->assign( $key, $value );
		}//end foreach

		if($params['title']) $tpl->assign('app_title',$params['title']);

		$tpl->assign('output',$text);
		$tpl->assign('js_init',stripslashes($params['js_callback']));
		$tpl->addCSS('/webapp/my/templates/override.css');
		$tpl->addCSS('/webapp/my/templates/channels.css');
		ob_start();
		$tpl->display('/web/pscpages/webapp/portal/channel/templates/main.tpl');
		$text = ob_get_clean();
		return $text;
	}//end html

	/**
	 * out
	 *
	 * A utility function that outputs the channel content returned by Channel::text
	 *
	 * @since		version 2.0.0
	 * @param  	string $type Type of output (callback or write)
	 * @param   mixed $params Parameters to be appended to the JS callback
	 */
	public function out($type = 'write', $params = false)
	{
		echo self::text($type, $params);		
	}//end out

	/**
	 * outState
	 *
	 * Outputs the JS state
	 *
	 * @since		version 2.0.0
	 * @param   string $js_call JavaScript function for echoing state
	 * @param   array $params
	 */
	public function outState($js_call, $params)
	{
		$state = $js_call.'(\''.implode("','",$params).'\');';
		echo $state;
	}//end outState

	/**
	 * outAllStates
	 *
	 * Outputs the JS state for all meta
	 *
	 * @since		version 2.0.0
	 * @param   int $wpid Person identifier
	 * @param   mixed $meta List of meta tags
	 * @param   array $params list of parameters
	 */
	public function outAllStates($wpid, $meta, $params) {
		if(!is_array($meta)) {
			$meta = array($meta);
		}//end if
		
		$states = '';
		foreach($meta as $m) {
			$state = Channel::getState($wpid,$m, $params['default_callback']);
			
			$js_call = ($state)?$params['active_state']:$params['fail_state'];
		
			$js_params = array($m);
			if(!is_array($params['js_params'])) {
				$params['js_params'] = array($params['js_params']);
			}//end if
			$js_params = array_merge($js_params, $params['js_params']);
		
			$states .= Channel::outState($js_call, $js_params);
		}//end foreach
		return $states;
	}//end outAllStates

	/**
	 * start
	 *
	 * Prepares the page for channel output
	 *
	 * @since		version 2.0.0
	 */
	public function start()
	{
		ob_start();
	}//end start

	/**
	 * Return the channel in a specified format
	 *
	 * @param  	string $type Type of output (callback or write)
	 * @param   mixed $params Parameters to be appended to the JS callback
	 */
	public function text($type = 'write', $params = false)
	{
		$text = ob_get_contents();
		ob_end_clean();

		$non_js_types = array(
			'html',
			'raw',
		);
		
		if( ! in_array( $type, $non_js_types )  ) {
			header('Content-type: text/javascript');
		}
		
		$params = PSU::params( $params );

		$params['callback'] = $params['callback'] ? $params['callback'] : '$.my.channelLoad';
		$params['channel_id'] = $params['channel_id'] ? $params['channel_id'] : $_GET['channel_id'];

		if( $_SERVER['HTTPS'] == 'on') {
			$text = str_replace('src="http://www.plymouth', 'src="https://www.plymouth', $text);	
		}//end if
		
		if($type == 'callback')
		{
			$text = self::callback(psu::closeOpenTags($text), $params['callback'],$params);
		}//end if
		elseif($type == 'html')
		{
			$text = self::html($text, $params);
		}//end elseif
		elseif( $type == 'raw' ) {
			// do not modify $text!
		}//end elseif
		else
		{
			$text = self::write($text);
		}//end else
		return $text;
	}//end text

	/**
	 * Formats the given text as a series of document.writes
	 *
	 * @since		version 2.0.0
	 * @param  	string $text Text to output
	 */
	public function write($text)
	{
		$find = array("'","\n","\r","\t","document.write('');\n");
		$replace = array("\'","');\ndocument.write('",'','','');
		$text="document.write('".str_replace($find,$replace,$text)."');";
		return $text;
	}//end write

	/**
	 * Legacy function that probably did something great a long time ago.
	 *
	 * @since		version 1.0.0
	 * @deprecated 
	 */
	public function pukeBufferedJSFriendly()
	{
		$page = ob_get_contents();
		ob_end_clean();
		if($_GET['z']!='inline')
		{
			$page = Channel::makeJSFriendy($page);
			
			if($_GET['z']=='load')
			{
				print '
				<html><head>
				<script type="text/javascript" src="http://www.plymouth.edu/includes/js/jquery-latest.pack.js"></script>';
				print "\n<script type=\"text/javascript\">\n".$page."\n</script></head></html>";
			}
			else
			{
				print $page;
			}
		}
		else
		{
			print '<script type="text/javascript" src="http://www.plymouth.edu/includes/js/jquery-latest.pack.js"></script>';
			print $page;
		}
	}  // end pukeBufferedJSFriendly

	/**
	 * @since		version 1.0.0
	 * @deprecated
	 */
	public function pukeCallbackJS($page,$callback,$vars='')
	{
		$page = ob_get_contents();
		ob_end_clean();
		header('Content-type: text/javascript');
		if( $_SERVER['HTTPS'] == 'on') {
			$page = str_replace('src="http://www.plymouth', 'src="https://www.plymouth', $page);	
		}//end if
		echo Channel::makeJSFriendy($page,$callback,$vars);
	}//end pukeCallbackJS

	/**
	 * @since		version 1.0.0
	 */
	public function makeJSFriendy($text,$callback='',$vars='')
	{
		if($callback)
		{
			$find = array('\\',"'","\n","\r","\t","document.write('');\n");
			$replace = array("&#92;","\'","'+\n'",'','','');
			$text="var the_text_to_output='".str_replace($find,$replace,$text)."';";
			return ''.$text."\n".$callback.'(the_text_to_output'.$vars.');';
		}
		else
		{
			$find = array("'","\n","\r","\t","document.write('');\n");
			$replace = array("\'","');\ndocument.write('",'','','');
			$text="document.write('".str_replace($find,$replace,$text)."');";
			return $text;
		}//end else
	}//end makeJSFriendly
}//end class Channel
