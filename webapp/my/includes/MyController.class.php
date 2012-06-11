<?php

require_once 'PSUController.class.php';

/**
 * MyController.class.php
 *
 * Portal Controller controls the delegation page loads within the portal
 *
 * @version		1.0.0
 * @author		Adam Backstrom <ambackstrom@plymouth.edu>
 * @author		Matthew Batchelder <mtbatchelder@plymouth.edu>
 * @author		Vasken Hauri <vkhauri@plymouth.edu>
 * @copyright 2010, Plymouth State University, ITS
 */ 
class MyController extends PSUController
{
	/**
	 * constructor
	 */
	public function __construct()
	{
		parent::__construct();
		$this->portal = new MyPortal($GLOBALS['identifier']);
		$this->tpl->assign('portal', $this->portal);

		$this->tpl->body_style_classes[] = 'myplymouth';

		if( $this->portal->is_chat_disabled() ) {
			$this->tpl->body_style_classes[] = 'chat-disabled';
		}//end if
	}//end __construct

	/**
	 * Portal Content browser
	 */
	public function channels( $view = null, $page = 1 ) {
		if( $view == 'newest' || $view == 'popular' ) {
			$channels = MyChannel::$view( $this->portal->person );
		} else {
			$channels = MyChannel::fetchAll( $this->portal->person );
		}

		$data = array();

		foreach( $channels as $channel ) {
			if( ChannelAuthZ::_authz( $channel->slug )) {
				$data[] = $channel;
			} 
		}//end foreach

		$per_page = 20;

		$num_records = count($data);
		$data = array_slice( $data, ($page - 1) * $per_page, $per_page );
		
		$overrides = array(
			'last_page' => ceil($num_records / $per_page),
			'total_records' => $num_records,
			'num_rows' => count($data),
			'rows_per_page' => $per_page,
			'current_page' => $page,
		);

		$pagination = PSU::paginationResults( PSU::paginationInfo( $_GET, $results, $overrides ), $data );
		$this->tpl->assign('channels', $pagination['items']);
		$this->tpl->assign('pages', $pagination);
		$this->tpl->assign('user_channels', MyChannel::fetchAll($this->person));
		$this->tpl->display('channels.tpl');
	}//end channels

	/**
	 * clones a user's layout
	 */
	public function clone_layout(){
		if($this->portal->tabs('welcome')->wp_id != $GLOBALS['identifier'])
		{
			$this->portal->cloneLayout();
		}//end if
	}//end clone_layout

	/**
	 * delegates page rendering
	 *
	 * @param $path \b path stuff
	 */
	public static function delegate( $path = null, $class = __CLASS__ ) {
		parent::delegate($path, $class);
	}//end delegate

	/**
	 * Debug output of the portal contents for this user.
	 */
	private function dbug() {
		//$this->portal->tabs('welcome')->channels(1)->meta()->rss = rand(1,10);
		//$this->portal->save();
		
		$this->tpl->assign_by_ref('portal', $this->portal);
		$this->tpl->display('debug.tpl');
	}//end debug

	/**
	 * @todo delete
	 */
	public function fetch( $id, $obj_or_class ) {
		MyPortalObject::fetch( $id, $obj_or_class );
	}//end fetch

	/**
	 * Default handler to redirect to /tab/welcome
	 */
	public function index(){
		PSUHTML::redirect( $GLOBALS['BASE_URL'] . '/tab/welcome' );
	}//end index

	/**
	 * Restore a session stashed by an admin emulating another user's layout.
	 */
	public function restore_layout() {
		if( ! isset($_SESSION['portal']['session_stashed']) ) {
			$_SESSION['errors'][] = 'Could not find a stashed session to restore.';
			PSU::redirect( $GLOBALS['BASE_URL'] );
		}

		$session_stashed = $_SESSION['portal']['session_stashed'];
		unset($_SESSION['portal']['session_stashed']);

		$_SESSION = $session_stashed;

		$_SESSION['messages'][] = "Your session has been restored.";
		PSU::redirect( $GLOBALS['BASE_URL'] . '/admin' );
	}//end restore_layout

	/**
	 * Save tester.
	 */
	private function save() {
		$c = MyChannel::fetch(1);
		$c->save();
	}//end save

	/**
	 * searches PSU web and help
	 */
	public function search() {
		$this->tpl->assign('portal', $this->portal);
		$this->tpl->display('search.tpl');
	}//end search

	/**
	 * if chat is disabled, set it in the template
	 */
	public static function _detect_disabled_chat( &$portal, &$tpl ) {
		if( $portal->is_chat_disabled() ) {
			$tpl->body_style_classes[] = 'chat-disabled';
		}//end if
	}//end _disabled_chat
}//end MyController
