<?php

require_once 'MyPortalObject.class.php';

/**
 * An instance of a UserTab.
 *
 * @section methods Methods
 *
 * \li $tab->channels() -- lazy loads channel list as array of MyUserChannel objects
 *
 * @section properties Properties
 *
 * \li $tab->base -- the base MyTab for this MyUserTab
 *
 * @todo make read/write rather than read-only
 */
class MyUserTab extends MyPortalObject {
	/**
	 * UserChannels in this tab.
	 */
	private $channels;

	/**
	 * Constructor for MyUserTab.
	 */
	public function __construct( array $params = array() ) {
		if( ! $params['tab_id'] ) {
			throw new Exception("You cannot create a MyUserTab without an underlying tab id");
		}

		parent::__construct($params);

		$this->base = MyTab::fetch($this->tab_id);
		$this->base->parent($this);

		// override usertab slug with tab slug if it was missing
		if( ! $this->slug ) {
			$this->slug = $this->base->slug;
		}
	}//end __construct

	/**
	 * Return all tabs for this user.
	 * @return array of MyTab objects
	 */
	public static function getUserTabs( PSUPerson $person, $slug = null ) {
		$tabs = array();

		$targeting = self::targetSQL($person, 'MyTab');

		//
		// usertabs -> tabs -> targets
		//
		$sql = sprintf('
			SELECT %1$s.*
			FROM %1$s LEFT JOIN %4$s ON %1$s.%5$s = %4$s.id %2$s
			WHERE
				wp_id = (SELECT MAX(wp_id) FROM %1$s WHERE wp_id IN ("0", ?))
				%3$s
			ORDER BY sort_order
		', self::dbstr(__CLASS__, 'table'), $targeting['tables'], $targeting['where'], self::dbstr('MyTab', 'table'), self::dbstr('MyTab', 'fk'));

		$rset = PSU::db('portal')->Execute($sql, array($person->wp_id));
	
		// load all tabs into the array
		foreach($rset as $row) {
			$tab = new self($row);
			$tabs[$tab->id] = $tab;
			unset($tab);
		}

		return $tabs;
	}//end getUserTabs

	/**
	 * Fetch overload.
	 */
	public static function fetch( $id, $class = __CLASS__ ) {
		return parent::fetch($id, $class);
	}//end fetch

	/**
	 * Return the child usertab id for a given user and parent usertab id.
	 *
	 * @param $person \b PSUPerson the owner
	 * @param $parent_id \b int the parent usertab id
	 */
	public static function get_child_id( PSUPerson $person, $parent_id ) {
		$sql = sprintf("
			SELECT ut.id
			FROM %s ut
			WHERE
				ut.wp_id = ? AND
				ut.parent_ut_id = ?
		",
			self::dbstr('MyUserTab', 'table'),
			self::dbstr(__CLASS__, 'table'),
			self::dbstr('MyUserTab', 'fk')
		);

		$args = array($person->wp_id, $parent_id);

		$child_id = PSU::db('portal')->GetOne($sql, $args);

		return $child_id;
	}//end get_child_id

	/**
	 * Log hits to this usertab.
	 */
	public function log_hit() {
		$args = array($this->parent()->wp_id, $this->id, $this->parent_ut_id);

		$sql = "
			INSERT DELAYED INTO hits
				(wp_id, hit_date, hit_time, hit_usertab, hit_ut_parent)
			VALUES
				(?, NOW(), NOW(), ?, ?)
		";

		PSU::db('portal')->Execute( $sql, $args );
	}//end log_hit

	/**
	 * Fetch channels for this tab.
	 * @return void
	 */
	public function channels( $id = null ) {
		if( $this->channels === null ) {
			$this->channels = MyUserChannel::getUserChannels($this->parent()->person, $this->id);

			foreach($this->channels as $channel) {
				$channel->tab = $this;

				$this->parent()->add_channel_shortcut($channel);
			}
		}

		if( $id !== null ) {
			return $this->channels[$id];
		} else {
			return $this->channels;
		}
	}//end channels

	/**
	 * Save the tab and all channels within.
	 */
	public function save() {
		// save yourself!
		parent::save();

		// save your children!
		if( $this->channels ) {
			// don't bother with save if we never loaded any sub-channels (if we didn't
			// load them, we didn't update fields, so there are no changes)
			foreach($this->channels() as $channel) {
				// make sure channel knows what usertab it's part of
				$channel->usertab_id = $this->id;

				$channel->save();

				$this->parent()->add_channel_shortcut($channel);
			}
		}
	}//end save

	/**
	 * Magic getter.
	 */
	public function &__get($k) {
		if( $k == 'num_cols' ) {
			$num_cols = 0;
			foreach($this->channels() as $channel) {
				$num_cols = $num_cols > $channel->col_num ? $num_cols : $channel->col_num;
			}
			$this->data['num_cols'] = $num_cols;
		}

		return parent::__get($k);
	}//end __get
}//end MyUserTab
