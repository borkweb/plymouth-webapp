<?php
/**
 * MyController_channel.class.php
 *
 * Portal Controller controls the channel delegation page loads within the portal
 *
 * @version		1.0.0
 * @author		Adam Backstrom <ambackstrom@plymouth.edu>
 * @author		Matthew Batchelder <mtbatchelder@plymouth.edu>
 * @author		Vasken Hauri <vkhauri@plymouth.edu>
 * @copyright 2010, Plymouth State University, ITS
 */ 
require_once 'PSUController.class.php';

class MyController_channel extends PSUController
{
	/**
	 * constructor
	 */
	public function __construct()
	{
		parent::__construct();
		$this->portal = new MyPortal($GLOBALS['identifier']);

		MyController::_detect_disabled_chat( $this->portal, $this->tpl );
	}//end __construct

	/**
	 * handles the adding of a channel to a layout
	 *
	 * @param $channel \b id of channel to add
	 * @param $target \b id of tab to add channel to
	 */
	public function add($channel, $target = null) {
		$default_layout = false;
		
		ignore_user_abort();
		
		if($_SESSION['generic_user_type'] == 'portalord'){
			$default_layout = true;
		}

		MyPortal::force_clone( $this->portal );

		$channel_id = str_replace('channel-', '', $channel);

		$target = str_replace('tab-', '', $target);

		//
		// if portal was just cloned, channel ids must be updated.
		// they referred to the default layout in the ui, but we will actually update
		// the custom user layout.
		//
		// @todo account for admins updating the default layout (GET param?)
		//
			
		//if we just cloned the layout, set the target as the child tab
		if( $this->portal->cloned ) {
			$target = MyUserTab::get_child_id( $this->portal->person, $target );
		}
		
		if(!$this->portal->tabs($target))
		{
			$default_portal = new MyPortal(0);
			$default_tab_slug = $default_portal->tabs($target)->slug;
			$target = $this->portal->tabs($default_tab_slug)->id;

			$default_channel = $default_portal->tabs($default_tab_slug)->channels($channel_id);

			$sql = "SELECT id FROM userchannels WHERE channel_id = ? AND usertab_id = ? AND col_num = ? AND sort_order = ?";
			$channel_id = PSU::db('portal')->GetOne($sql, array($channel_id, $this->portal->tabs($default_tab_slug)->id, $default_channel->col_num, $default_channel->sort_order));
		}//end if

		//if we're spoofing the portalord role, edit the default layout rather than the cloned one
		if($default_layout) {
			if(!$default_portal) {
				$default_portal = new MyPortal(0);
			}	
			$target = $default_portal->tabs($target)->base->id;
		}

		$sql = "SELECT MAX(sort_order) FROM userchannels WHERE usertab_id = ? AND col_num = 2";

		$sort_order = psu::db('portal')->GetOne($sql, $target);

		$channel = new MyUserChannel(array('channel_id' => $channel_id));
		
		$channel->col_num = 2;
		$channel->sort_order = $sort_order + 1;
		$channel->usertab_id = $target ? $target : 1;

		$channel->save();

		echo $this->portal->is_default_layout() ? 'default' : 'user';
	}//end add
	
	/**
	 * delegates page rendering
	 *
	 * @param $path \b path stuff
	 */
	public static function delegate( $path = null, $class = __CLASS__ ) {
		parent::delegate($path, $class);
	}//end delegate
	
	/**
	 * handles the deleting of a channel from a layout
	 *
	 * @param $channel \b id of channel to remove from layout
	 */
	public function delete($channel) {
		ignore_user_abort();

		MyPortal::force_clone( $this->portal );

		$channel_id = str_replace('channel-', '', $channel);

		// if the portal was just cloned, the channel id must be updated to be the new
		// child channel
		if( $this->portal->cloned ) {
			$channel_id = MyUserChannel::get_child_id( $this->portal->person, $channel_id );
		}//end if

		$channel = MyUserChannel::fetch(array('channel_id' => $channel_id));
		$delete_id = $channel->delete($channel_id, 'MyUserChannel');

		$return = array(
			'action' => 'delete_channel',
			'data' => array(
				'delete_id' => $delete_id
			)
		);

		header('Content-Type: text/javascript');
		echo '['.json_encode($return).']';
	}//end delete

	/**
	 * logic to display a given tab and portal structure for the user
	 *
	 * @param $id \b id of the channel to load
	 */
	public function index( $id ) {
		$channel = MyUserChannel::fetch($id);
		$this->tpl->assign('channel', $channel);
		$this->tpl->assign('portal', $this->portal);

		$this->display('channel.tpl');
	}//end index

	/**
	 * handles the moving of a channel
	 * @param $channel \b int the channel id
	 * @param $location \b string destination: tab, before, after, col
	 * @param $target \b id of the target element
	 */
	public function move($channel, $location, $target = null) {
		ignore_user_abort();

		MyPortal::force_clone( $this->portal );
		
		$channel_id = str_replace('channel-', '', $channel);
		$target = str_replace('channel-', '', $target);
		
		//
		// if portal was just cloned, the incoming tab and channel ids must be updated.
		// they referred to the default layout in the ui, but we will actually update
		// the custom user layout.
		//
		// @todo account for admins updating the default layout (GET param?)
		//

		if( $this->portal->cloned ) {
			$channel_id = MyUserChannel::get_child_id( $this->portal->person, $channel_id );

			if( $location == 'tab' ) {
				$target = MyUserTab::get_child_id( $this->portal->person, $target );
			} elseif( $location == 'before' || $location == 'after' ) {
				$target = MyUserChannel::get_child_id( $this->portal->person, $target );
			}
		}

		//
		// perform the channel moving
		//

		$channel = MyUserChannel::fetch($channel_id);
		if($location == 'before')
		{
			$target = MyUserChannel::fetch($target);
			$direction = ($target->sort_order <= $channel->sort_order) ? 'up' : 'down';
			$channel->setLocation($target->col_num, $target->sort_order, null, $direction);
		}//end if
		elseif($location == 'after')
		{
			$target = MyUserChannel::fetch($target);
			$channel->setLocation($target->col_num, $target->sort_order + 1, null, 'down');
		}//end if
		//the following location cannot occur @TODO determine if we need this
		elseif($location == 'col')
		{
			$channel->setLocation($target, 1);
		}//end if
		elseif($location == 'tab')
		{
			$channel->setLocation(1, 1, $target);
		}//end if

		echo $is_default_layout ? 'default' : 'user';
	}//end move
	
	/**
	 * handler for displaying a channel all by itself
	 *
	 * @param $id \b id of the channel to load
	 */
	public function view( $id = null ) {
		$this->index($channel);
	}// end view
}//end MyController
