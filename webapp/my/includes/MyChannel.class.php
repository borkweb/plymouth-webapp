<?php

require_once 'MyPortalObject.class.php';

/**
 * An instance of a channel. Note the important distinction between a Channel, which
 * is unique, and a UserChannel, which is an instance of a Channel in a tab.
 *
 * @sa MyUserChannel
 */
class MyChannel extends MyPortalObject {
	/**
	 * Store Channels in memory to reduce database load.
	 */
	static $cache = array();

	/**
	 * Load a channel from the database and return it as a MyChannel object.
	 * Subsequent loads will be returned from the channel cache.
	 *
	 * @param $id \b int the channel id
	 * @return MyChannel
	 */
	public static function fetch( $id ) {
		// fetch record if it's not already in the cache
		if( !isset(self::$cache[$id]) ) {
			$channel = parent::fetch( $id, __CLASS__ );

			if( $channel ) {
				self::$cache[$id] = $channel;
			} else {
				throw new Exception('Unknown channel requested: ' . $id);
			}
		}

		return self::$cache[$id];
	}//end fetch

	/**
	 * Fetch all channels. Used for channel browser or admin stuff.
	 */
	public static function fetchAll( PSUPerson $target = null ) {
		if( $target !== null ) {
			$targets = self::targetSQL( $target, __CLASS__ );
		} else {
			$targets = array('tables' => '', 'where' => '');
		}

		$sql = sprintf('
			SELECT %1$s.*
			FROM %1$s %2$s
			WHERE 1=1 %3$s
			ORDER BY %1$s.name
		', self::dbstr(__CLASS__, 'table'), $targets['tables'], $targets['where']);

		$rset = PSU::db('portal')->Execute($sql);

		$channels = array();

		foreach( $rset as $row ) {
			$id = $row['id'];

			if( ! isset(self::$cache[$id]) ) {
				self::$cache[$id] = new self($row);
			}

			$channels[$id] = self::$cache[$id];
		}

		return $channels;
	}//end fetchAll

	/**
	 * Get newest channels.
	 */
	public static function newest( PSUPerson $target = null ) {
		$channels = self::fetchAll( $target );
		uasort($channels, __CLASS__ . '::_newest_sort');
		return $channels;
	}//end newest

	/**
	 * Sorting function for getting newest channels.
	 */
	public static function _newest_sort( MyChannel $a, MyChannel $b ) {
		if( $a->create_date == $b->create_date ) {
			return 0;
		}

		$as = strtotime($a->create_date);
		$bs = strtotime($b->create_date);

		return ($as > $bs) ? -1 : 1;
	}//end _newest_sort

	/**
	 * Get popular channels.
	 */
	public static function popular( PSUPerson $target = null ) {
		$channels = self::fetchAll( $target );
		uasort($channels, __CLASS__ . '::_popular_sort');
		return $channels;
	}//end popular

	/**
	 * Sorting function for getting popular channels.
	 */
	public static function _popular_sort( MyChannel $a, MyChannel $b ) {
		if( $a->users == $b->users ) {
			// same number of users; sort by channel name
			return strnatcasecmp( $a->name, $b->name );
		}

		return ($a->users > $b->users) ? -1 : 1;
	}//end _popular_sort

	/**
	 * Pushes 1 or more default channels to all customized layouts
	 */
	public static function push_default_channels( $channels = null ) {
		$total = 0;

		if( $channels ) {
			$channels = (array) $channels;

			$where = " AND uc.channel_id IN (".implode(',', $channels).")";
		}//end if

		$sql = "
			SELECT uc.* 
			  FROM userchannels uc, usertabs ut 
			 WHERE uc.usertab_id = ut.id 
				 AND ut.wp_id = '0' {$where}
		";

		if( $results = PSU::db('portal')->Execute( $sql ) ) {
			foreach( $results as $row ) {

				$channel_id = $row['channel_id'];
				$channel_col = $row['col_num'];
				$channel_sort = $row['sort_order'];
				$channel_lock = $row['lock_state'];
				$channel_parent = $row['id'];
				$channel_parent_tab = $row['usertab_id'];

				$select = "
					SELECT 
						ut.id, 
						".$channel_id." channel_id,
						".$channel_col." channel_col,
						".$channel_sort." channel_sort,
						".$channel_lock." channel_lock,
						".$channel_parent." channel_parent,
						".$channel_parent_tab." channel_parent_tab,
						0 delete_id,
						NOW() activity_date,
						0 old_sort
					FROM usertabs ut 
								LEFT JOIN userchannels uc 
									ON ut.id = uc.usertab_id
									AND uc.channel_id = ".$channel_id."
				 WHERE ut.parent_ut_id = ".$channel_parent_tab."
					 AND uc.channel_id IS NULL
				";

				$sql = "
					INSERT INTO userchannels (
						usertab_id,
						channel_id,
						col_num,
						sort_order,
						lock_state,
						parent_uc_id,
						parent_ut_id,
						delete_id,
						activity_date,
						old_sort
					) {$select} 
				";
				$before = PSU::db('portal')->GetOne("SELECT count(*) FROM (".$select.") c");
				PSU::db('portal')->Execute( $sql );
				$after = PSU::db('portal')->GetOne("SELECT count(*) FROM (".$select.") c");

				$total += $before - $after;

			}//end foreach
		}//end if

		return $total;
	}//end push_default_channels

	/**
	 * pass our child targets function to myportalobject
	 */
	public static function targets( $id, $class = __CLASS__ ) {
		return parent::targets($id, $class);
	}//end targets

	/**
	 * pass our child targets function to myportalobject
	 */
	public static function targetNames( $id, $class = __CLASS__ ) {
		return parent::targetNames($id, $class);
	}//end targetNames

	/**
	 * Update the cached value of channel users.
	 */
	public static function update_user_count( $id = null ) {
		$where = '1=1';

		if( $id ) {
			$where = 'c.id = ' . (int)$id;
		}

		$sql = sprintf('
			UPDATE %s c
			SET users = (
				SELECT COUNT(DISTINCT ut.wp_id)
				FROM %s ut LEFT JOIN %s uc ON ut.id = uc.%s
				WHERE uc.%s = c.id
			)
			WHERE %s
			',
			self::dbstr('MyChannel', 'table'),
			self::dbstr('MyUserTab', 'table'),
			self::dbstr('MyUserChannel', 'table'),
			self::dbstr('MyUserTab', 'fk'),
			self::dbstr('MyChannel', 'fk'),
			$where
		);

		PSU::db('portal')->Execute($sql);
	}//end update_user_count
}//end class MyChannel
