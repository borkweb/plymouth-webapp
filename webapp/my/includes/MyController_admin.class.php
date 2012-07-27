<?php

require_once 'PSUController.class.php';

class MyController_admin extends MyController
{
	/**
	 *
	 */
	public function __construct() {
		parent::__construct();
	}//end __construct

	/**
	 * allows user to add a channel through a form
	 */
	public function channel( $id = null ) {
		$this->_force_admin();

		if($_SERVER['REQUEST_METHOD'] == 'POST') {
			if($_POST['newchannel']) {
				unset($_POST['id']);
				unset($id);
			} else {
				$postdata = $_POST;
				if(get_magic_quotes_gpc()){
					foreach($postdata as $k=>$v) {
						$postdata[$k] = stripslashes($v);
					}
				}

				$postdata['slug'] = str_replace(' ', '-', strtolower($postdata['name']));
				$postdata['create_date'] = date('Y-m-d H:i:s');
				$channel = new MyChannel( $postdata );
				$id = $channel->id;
				if( $_POST['targets'] != null ) {
					$channel->_save();
					MyPortalObject::save_targets( $_POST['targets'], $channel->id, 'MyChannel' );
					echo 'Your channel has been saved.';
				} else {
					echo 'You have selected no targets and your channel has not been saved. Did you mean to select public targeting?';
				}
			}	
		}
		
		if( ($channel->id == null) && ($id == null) ) {
			$channel = new ChannelForm;
		}else {
			if ($channel->id != null)
				$id = $channel->id;
			$channel = MyChannel::fetch($id);
			$this->tpl->assign('custom_authz', ChannelAuthZ::_has_authz($channel->slug));
			$channel = MyPortalObject::fetchRow($channel->id, 'MyChannel');
			$channel = array_map('stripslashes', $channel);
			$channel = new ChannelForm($channel);
		}
		
		$this->tpl->assign('channel', $channel);
		$this->display('admin-channel.tpl');
	}//end channel

	/**
	 * Force an update of the user count for all channels.
	 * @todo may do other things in the future? update for now.
	 */
	public function channel_user_count( $action = 'update', $id = null ) {
		MyChannel::update_user_count( $id );
		$_SESSION['messages'][] = 'User counts updated.';
		PSU::redirect( $GLOBALS['BASE_URL'] . '/admin/' );
	}//end channel_user_counts_update

	/**
	 * Tab function, allows a user to administer tabs
	 */
	public function tab( $id = null ) {
		$this->_force_admin();

		if($_SERVER['REQUEST_METHOD'] == 'POST') {
			if($_POST['newtab']) {
				unset($_POST['id']);
				unset($id);
			}else {
				$postdata = $_POST;
				foreach($postdata['lock_state'] as $key => $value)
					$lock_state += $value;
				$postdata['lock_state'] = $lock_state;
				$postdata['slug'] = str_replace(' ', '-', strtolower($postdata['name']));
				$tab_obj = new MyTab( $postdata );
				$id = $tab_obj->id;
				if( isset($_POST['targets']) ) {
					$tab_obj->_save();
					MyPortalObject::save_targets( $_POST['targets'], $tab_obj->id, 'MyTab' );
					echo 'Your tab has been saved.';
				}else {
					echo 'You have selected no targets and your tab has not been saved. Did you mean to select public targeting?';
				}	
			}
		}

		if( ($tab_obj->id == null) && ($id == null) ) {
			$tabform = new TabForm;
		}else {
			if ($tab_obj->id != null)
				$id = $tab_obj->id;
			
			$tabform = MyPortalObject::fetchRow($id, 'MyTab');
			$tabform = new TabForm($tabform, true);
		
		}
		
		$this->tpl->assign('tabform', $tabform);
		$this->display('admin-tab.tpl');		
	}//end tab
		
	/**
	 * delegates page rendering
	 *
	 * @param $path \b path stuff
	 */
	public static function delegate( $path = null, $class = __CLASS__ ) {
		parent::delegate($path, $class);
	}//end delegate

	/**
	 *
	 */
	public function index() {
		$this->_force_admin();

		$tabs = MyTab::fetchAll();
		$channels = MyChannel::fetchAll();
		foreach($channels as $channel) {
			$targets = MyChannel::targetNames($channel->id);
			if(ChannelAuthZ::_has_authz($channel->slug)) {
				$targets[] = '<strong>custom</strong>';
			}
			if(count($targets)>0) {
				$channel->target_names = implode(', ', $targets);
			}
		}
		$this->tpl->assign_by_ref('tabs', $tabs);
		$this->tpl->assign_by_ref('channels', $channels);
		$this->display('admin-index.tpl');
	}//end index

	/**
	 * pushes default channels to users
	 */
	public function push() {
		$this->_force_admin();

		$pushed = MyChannel::push_default_channels();

		$_SESSION['successes'][] = 'Default channel push has completed ('.$pushed.' channels pushed)!';
		PSU::redirect( $GLOBALS['BASE_URL'].'/admin' );
	}//end push

	/**
	 * Remove custom layout for the logged-in user.
	 */
	public function reset() {
		if( !$this->portal->person->wp_id ) {
			$_SESSION['errors'][] = "No wp_id, refusing to reset layout.";
			PSUHTML::redirect( $GLOBALS['BASE_URL'] );
		}

		$wp_id = array($this->portal->person->wp_id);

		$sql = "
			DELETE c, m
			FROM
				usertabs t LEFT JOIN
				userchannels c ON t.id = c.usertab_id LEFT JOIN
				userchannels_meta m ON c.id = m.userchannel_id
			WHERE t.wp_id = ?
		";

		PSU::db('portal')->Execute($sql, $wp_id);

		$sql = "
			DELETE t, m
			FROM usertabs t LEFT JOIN usertabs_meta m ON t.id = m.usertab_id
			WHERE t.wp_id = ?
		";

		PSU::db('portal')->Execute($sql, $wp_id);

		PSUHTML::redirect( $GLOBALS['BASE_URL'] );
	}//end reset

	/**
	 * Allow a user to view another person's layout.
	 */
	public function set_layout( $wp_id = null ) {
		$this->_force_admin();
		
		if( $wp_id === null ) {
			$wp_id = $_REQUEST['wp_id'];
		}

		//
		// is this user too privileged to be simulated?
		//

		$p = new PSUPerson($wp_id);

		if( PSU::get('idmobject')->hasAttribute($person->pidm, 'role', 'myplymouth') ) {
			$_SESSION['errors'][] = "You cannot login as a portal administrator.";
			PSU::redirect( $GLOBALS['BASE_URL'] . '/admin' );
		}

		$session_stashed = $_SESSION;

		$_SESSION['wp_id'] = $wp_id;
		// if editing the default layout (i.e. $wp_id = 0), make sure the username and pidm are both 0
		$_SESSION['username'] = $p->username ?: 0;
		$_SESSION['pidm'] = $p->pidm ?: 0;
		$_SESSION['portal']['session_stashed'] = $session_stashed;

		$_SESSION['messages'][] = "You are now logged in as {$wp_id}.";

		PSU::redirect( $GLOBALS['BASE_URL'] );
	}//end set_layout

	/**
	 * Allows a user to view a definied type of layout, i.e.
	 * generic alumnus, family member, etc.
	 */
	public function set_type ( $user_type = null ) {
		$this->_force_admin();
		
		if( $user_type === null ) {
			$user_type = $_REQUEST['generic_role'];
		}

		if( !$user_type ) {
			PSU::redirect( $GLOBALS['BASE_URL'] . '/admin' );
		}else {
			$_SESSION['generic_user_type'] = $user_type;
			$_SESSION['messages'][] = "You are now logged in as {$user_type}. Remember: 1) With great power comes great responsibility and 2) MIS has ninja skillz";
			PSU::redirect( $GLOBALS['BASE_URL'] );
		}	
	}//end set_type

	public function test() {
		$this->_force_admin();
		
		PSU::db('portal')->debug = true;

		var_dump( MyUserTab::get_child_id( $this->portal->person, 1 ) );
		var_dump( MyUserChannel::get_child_id( $this->portal->person, 1 ) );
	}

	/**
	 * targets tester
	 * @todo delete this when testing is done
	 */
	public static function targets( $id, $class = __CLASS__ ) {
		$this->_force_admin();
		
		MyPortalObject::targets( $id, $class );	
	}//end targets

	public function unset_type() {
		$this->_force_admin();
		
		unset($_SESSION['generic_user_type']);
		PSU::redirect( $GLOBALS['BASE_URL'] . '/admin' );
	}

	/**
	 * redirects if not admin
	 */
	private function _force_admin() {
		if( !IDMObject::authZ('role', 'myplymouth') ) {
			$_SESSION['errors'][] = 'You are not allowed to view the MyPlymouth administration interface.';
			PSU::redirect( $GLOBALS['BASE_URL'] );
		}//end if
	}//end _force_admin
}//end MyController_admin
