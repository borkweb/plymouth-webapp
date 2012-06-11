<?php

require_once 'MyPortalObject.class.php';
require_once 'MyChannelAuthZ.class.php';

/**
 * An instance of a user channel. Note the important distinction between a Channel, which
 * is unique, and a UserChannel, which is an instance of a Channel in a tab.
 *
 * @sa MyChannel
 */
class MyUserChannel extends MyPortalObject {
	/**
	 * Fetch overload.
	 */
	public static function fetch( $id, $class = __CLASS__ ) {
		return parent::fetch($id, $class);
	}//end fetch

	/**
	 * Return the child userchannel id for a given user and parent channel id.
	 *
	 * @param $person \b PSUPerson the owner
	 * @param $parent_id \b int the parent userchannel id
	 */
	public static function get_child_id( PSUPerson $person, $parent_id ) {
		$sql = sprintf("
			SELECT uc.id
			FROM %s uc LEFT JOIN %s ut ON ut.id = uc.%s
			WHERE
				ut.wp_id = ? AND
				uc.parent_uc_id = ?
		",
			self::dbstr(__CLASS__, 'table'),
			self::dbstr('MyUserTab', 'table'),
			self::dbstr('MyUserTab', 'fk')
		);
		
		$args = array($person->wp_id, $parent_id);

		$child_id = PSU::db('portal')->GetOne($sql, $args);

		return $child_id;
	}//end get_child_id

	/**
	 * Fetch user channels for a specific tab. Also loads the "real" channel into
	 * $this->base, and clones the channel's meta into $this.
	 *
	 * @param $usertab_id \b int
	 * @todo move some stuff into a new function so loadUserChannel() can take advantage of channel load logic
	 */
	public static function getUserChannels( $person, $usertab_id ) {
		$targeting = self::targetSQL($person, 'MyChannel');

		//
		// userchannels -> channels -> targets
		//
		$sql = sprintf('
			SELECT %1$s.*
			FROM %1$s 
           LEFT JOIN %2$s ON %2$s.id = %1$s.%3$s %5$s
				WHERE %4$s = ? %6$s
			  AND delete_id = 0
			ORDER BY col_num, sort_order
			', 
			self::dbstr(__CLASS__, 'table'), 
			self::dbstr('MyChannel', 'table'), 
			self::dbstr('MyChannel', 'fk'), 
			self::dbstr('MyUserTab', 'fk'), 
			$targeting['tables'], 
			$targeting['where']
		);

		$rset = PSU::db('portal')->Execute($sql, array($usertab_id));

		$channels = array();

		foreach($rset as $row) {
			// do not instantiate the channel if it's already in our channel list
			if( isset($channels[$row['id']]) ) {
				continue;
			}

			$channel = new self($row);

			if( self::use_targeting() == false || ChannelAuthZ::_authz( $channel->slug )) {
				$channels[$channel->id] = $channel;
				unset($channel);
			} 
		}

		return $channels;
	}//end getUserChannels

	/**
	 * Return the parent usertab id, given a userchannel id.
	 *
	 * @param $userchannel_id
	 */
	public static function get_parent_usertab( $userchannel_id ) {
		$sql = sprintf("
			SELECT %s
			FROM %s uc
			WHERE uc.id = ?
		",
			self::dbstr('MyUserTab', 'fk'), self::dbstr(__CLASS__, 'table')
		);

		$usertab_id = PSU::db('portal')->GetOne($sql, array($userchannel_id));

		return $usertab_id;
	}//end get_parent_usertab

	/**
	 * Load a user channel from the database by id.
	 */
	public static function loadUserChannel( $id ) {
		$sql = "
			SELECT *
			FROM userchannels
			WHERE id = ?
		";

		$row = PSU::db('portal')->GetRow($sql, array($id));

		return new self($row);
	}//end loadUserChannel

	/**
	 * Sets the sort order and column of the channel
	 * @param $col_num 
	 * @param $order
	 * @param $tab_id
	 * @param $direction
	 */
	public function setLocation($col_num, $order, $tab_id = null, $direction = 'up') {
		
		//use direction of move to either increase the sort order of all
		//channels with a sort order higher than the moved channel(up) or
		//decrease the sort order of all channels with a lower order (down) 
		$sort_inc =  ($direction == 'up') ? '+ 1' : '- 1';
		$sort_gt_or_lt =  ($direction == 'up') ? '>=' : '<';
		$sort_opposite = ($sort_gt_or_lt == '>=') ? '<=' : '>=';
		$sql = sprintf('
			UPDATE %1$s
		     SET sort_order = sort_order %3$s
		   WHERE col_num = ?
		     AND sort_order %4$s ?
				 AND sort_order %5$s ? 
				 AND %2$s = ?
				 AND lock_state not in(1, 3, 5, 7)
				 AND id <> ?
		', self::dbstr(__CLASS__, 'table'), self::dbstr('MyUserTab', 'fk'), $sort_inc, $sort_gt_or_lt, $sort_opposite);
	 
		$dump = PSU::db('portal')->Execute($sql, array($col_num, $order, $this->sort_order, $this->usertab_id, $this->id));
		
		$new_location = array(
			'col_num' => $col_num,
			'sort_order' => ($direction == 'up') ? $order : $order - 1,
			'usertab_id' => $tab_id ? $tab_id : $this->usertab_id,
			'id' => $this->id
		);
	
		// relocate the channel to the new column
		$sql = sprintf('
			UPDATE %1$s
				 SET col_num = ?,
				     sort_order = ?,
			       %2$s = ?
		   WHERE id = ?
		', self::dbstr(__CLASS__, 'table'), self::dbstr('MyUserTab', 'fk'));
		
		PSU::db('portal')->Execute($sql, $new_location);
	 
	}//end setLocation

	/**
	 * Custom constructor to load base meta in addition to userchannel meta.
	 */
	public function __construct( $params = array() ) {
		if( !$params['channel_id'] ) {
			throw new Exception("You cannot create a MyUserChannel without an underlying channel id");
		}

		// load meta so we get a proper overlay of:
		//
		//  1. Channel, replaced by...
		//  2. UserChannel, replaced by...
		//  3. $params['meta']
		//
		// TODO: this is slightly inefficient, since we will also load the meta
		// below.
		
		$this->meta()->load( $params['channel_id'], 'MyChannel' );

		parent::__construct($params);

		$this->base = MyChannel::fetch($this->channel_id);
		$this->base->parent = $this;
		$this->data['name'] =& $this->base->name;
		$this->data['slug'] =& $this->base->slug;
		$this->data['content_url'] =& $this->base->content_url;
		$this->data['content_text'] =& $this->base->content_text;
	}//end __construct
}//end class MyUserChannel
