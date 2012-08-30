<?php

require_once dirname(__FILE__) . '/MyUserTab.class.php';
require_once dirname(__FILE__) . '/MyTab.class.php';
require_once dirname(__FILE__) . '/MyUserChannel.class.php';
require_once dirname(__FILE__) . '/MyChannel.class.php';
require_once dirname(__FILE__) . '/MyMagicGetters.class.php';
require_once ('PSUPerson.class.php');

/**
 * The base portal object.
 *
 * @section properties Properties
 *
 * \li $this->tabs -- all tabs as an array
 * \li $this->tabs($id) -- a single tab, specified by usertab_id or slug
 */
class MyPortal extends MyMagicGetters {
	/**
	 * The wp_id of the user that owns this portal.
	 */
	public $wp_id;

	/**
	 * The tabs within this portal.
	 */
	private $tabs = null;

	/**
	 * Channels within tabs in the portal.
	 */
	private $channels = null;

	/**
	 * True if the portal object was cloned during the current execution
	 * cycle. Significant because when you move a channel, you must first
	 * clone the layout, invalidating the previous channel ids.
	 */
	public $cloned = false;

	/// The wp_id of the default layout.
	const DEFAULT_LAYOUT_WPID = 0;
	
	/**
	 * Constructor.
	 */
	public function __construct( $identifier = 0 ) {
		$this->setUser($identifier);
	}//end __construct

	/**
	 * Shortcut this channel back into the portal object, so it can be
	 * accessed by $this->channels($id).
	 *
	 * @param $channel \b MyUserChannel the channel object
	 */
	public function add_channel_shortcut( $channel ) {
		$this->channels[ $channel->id ] = $channel;
	}//end add_channel_shortcut

	/**
	 * All channels, or a single channel specified by its ID.
	 * @param $id int
	 * @return Array|MyChannel
	 */
	public function channels( $id = null ) {
		if( $id == null ) {
			// we need all channels
			$this->load_everything();

			return $this->channels;
		}

		// if we don't know this channel. load its tab
		if( ! isset($this->channels[$id]) ) {
			$usertab_id = MyUserChannel::get_parent_usertab( $id );

			if( ! $this->tabs( $usertab_id )->channels( $id ) ) {
				return false;
			}
		}

		return $this->channels[$id];
	}//end channels

	/**
	 * Clone the current layout to the logged in portal user. (Used to copy the
	 * default layout into a user layout.)
	 */
	public function cloneLayout() {
		// This is imperfect, but there's too much static stuff going on elsewhere
		// to do this any other way right now.
		MyPortalObject::use_targeting( false );

		$this->reset();

		// iterate over all portal tabs
		foreach($this->tabs() as $tab) {
			$tab->channels(); // touch channels so it loads before we change the usertab id

			$original_tabid = $tab->id;

			$tab->id = null;
			$tab->wp_id = $this->person->wp_id;
			$tab->parent_ut_id = $original_tabid;

			// blank out meta ids
			foreach($tab->meta()->get() as $meta) {
				$meta->id = null;
			}

			// iterate over all this tab's channels
			foreach($tab->channels() as $channel) {
				$original_channelid = $channel->id;

				// remove the shortcut to this channel; this will be added
				// back in by MyUserTab::save()
				unset($this->channels[ $original_channelid ]);

				$channel->id = null;
				$channel->parent_uc_id = $original_channelid;
				$channel->parent_ut_id = $original_tabid;

				// we won't set $channel->usertab_id here, because we don't know the new
				// usertab_id yet (it hasn't been saved). MyUserTab::save() will pass along
				// the new id to the channel when it has saved.

				$channel_class = get_class($channel);

				// blank out meta ids. do a class check so all class defined in userchannels_meta
				// is cloned, but we don't unnecessarily clone channel_meta
				foreach($channel->meta()->get() as $meta) {
					if( $meta->class != $channel_class ) {
						continue;
					}

					// mark as changed so a save is forced
					$meta->id = null;
					$meta->changed = true;
				}
			}
		}

		$this->save();

		$this->cloned = true;
	}//end cloneLayout

	/**
	 * Ensure that a portal object is custom. Modifies the
	 * incoming portal object.
	 */
	public static function force_clone( MyPortal $portal ) {
		$identifier = $portal->wp_id;
		
		// do not force a clone if the wp_id is 0 (manipulating the default layout)
		//   or if the user ALREADY has a cloned layout
		if( 0 === $identifier || ! $portal->is_default_layout() ) {
			return;
		}

		$portal->cloneLayout();
	}//end force_clone

	/**
	 * Return true if current user is a portal admin, false if not
	 */
	public function is_admin() {
		return IDMObject::authZ('role', 'myplymouth');
	}//end is_admin

	/**
	 * Returns true if the user has specified that they want chat disabled
	 */
	public function is_chat_disabled() {
		static $chat_disabled;

		if( is_bool( $chat_disabled ) ) {
			return $chat_disabled;
		}//end if

		$chat_disabled = (boolean) PSU::db('go')->GetOne("SELECT 1 FROM user_meta WHERE wp_id = ? AND name = 'disabled_chat' AND value = '1'", array( $this->wp_id ));

		return $chat_disabled;
	}//end is_chat_disabled

	/**
	 * Return true if the current layout is a custom layout, rather
	 * than the default layout.
	 */
	public function is_default_layout() {
		// typecast as string because "foo" == 0, which isn't what we want to see
		return $this->tabs('welcome')->wp_id == (string)self::DEFAULT_LAYOUT_WPID;
	}//end is_custom_layout

	/**
	 * Returns true if the user has specified that they want a fluid layout
	 */
	public function is_fluid() {
		static $fluid;

		if( is_bool( $fluid ) ) {
			return $fluid;
		}//end if

		$theme_data = PSUTheme::getUserTheme( $this->wp_id, '*', 'GetRow' );

		if( $theme_data['allow_fluid'] === '0' ) {
			$fluid = false;
		} else {
			$fluid = (boolean) PSU::db('go')->GetOne("SELECT 1 FROM user_meta WHERE wp_id = ? AND name = 'fluid' AND value = '1'", array( $this->wp_id ));
		}//end else

		return $fluid;
	}//end is_fluid

	/**
	 * Force every object to load.
	 */
	public function load_everything() {
		foreach($this->tabs() as $tab) {
			$tab->channels();
		}
	}//end load_everything

	/**
	 * Reset data in this object. Does not affect the database.
	 */
	public function reset() {
		$this->tabs = null;
	}//end reset

	/**
	 * Load tabs for the logged-in user.
	 *
	 * @param $id \b id of tab
	 * @param $load_all \b true loads all user tabs, false only loads requested tab
	 */
	public function tabs( $id = null, $load_all = true ) {
		if( $this->tabs === null ) {
			//use the default tabset if user is PortaLord
		/*	if( $_SESSION['generic_user_type'] == 'portalord' ) {
				// @todo this shouldn't overwrite $this->tabs every time, it should add to the array if we're not loading all
				$this->tabs = !$load_all && $id ? array(MyUserTab::fetch($id)) : MyUserTab::getUserTabs( 0 );
		}else { */
				// @todo this shouldn't overwrite $this->tabs every time, it should add to the array if we're not loading all
				$this->tabs = !$load_all && $id ? array(MyUserTab::fetch($id)) : MyUserTab::getUserTabs( $this->person );
		//	}

			foreach($this->tabs as $tab) {
				$tab->parent($this);
			}
		}

		if( $id !== null ) {
			foreach($this->tabs as $tab) {
				if( $tab->id == $id || $tab->slug == $id ) {
					return $tab;
				}
			}

			return false;
		}

		return $this->tabs;
	}//end tabs

	/**
	 * Save the portal and everything within.
	 */
	public function save() {
		foreach($this->tabs() as $tab) {
			$tab->save();
		}
	}//end save

	/**
	 * Set the portal user.
	 */
	public function setUser( $identifier ) {
		$this->wp_id = $identifier;
		$this->person = new PSUPerson($_SESSION['username']);
	}//end setUser
}//end class MyPortal
