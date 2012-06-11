<?php

require_once dirname(__FILE__) . '/MyPortalObject.class.php';

/**
 * Dummy interface to backend tabs.
 */
class MyTab extends MyPortalObject {
	/**
	 * Store Channels in memory to reduce database load.
	 */
	static $cache = array();
	
	/**
	 * Fetch overload.
	 */
	public static function fetch( $id, $class = __CLASS__ ) {
		return parent::fetch($id, $class);
	}//end fetch
	
	/**
	 * Fetch all tabs. Used for tabs browser or admin stuff.
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

		$tabs = array();

		foreach( $rset as $row ) {
			$id = $row['id'];

			if( ! isset(self::$cache[$id]) ) {
				self::$cache[$id] = new self($row);
			}

			$tabs[] = self::$cache[$id];
		}

		return $tabs;
	}//end fetchAll
	
public static function targets( $id, $class = __CLASS__ ) {
		return parent::targets($id, $class);
	}//end targets

}//end MyTab
