<?php

/**
 * Pegasys Card Access System API
 *
 * @version             1.0.0
 * @author              Zach Tirrell <zbtirrell@plymouth.edu>
 * @copyright 2010, Plymouth State University, ITS
 */

require_once 'PSUTools.class.php';

class CardAccess
{
	// no known validation table, all types can be retrieved like this:
	// select x_hist_type from xaction group by x_hist_type;
	// however, definitions have been determined only through experimentation
	public static $types = array(
		17 => '',
		18 => '',
		19 => 'lock',
		20 => 'unlock',
		35 => 'no access',
		39 => 'unknown card',
		68 => 'success',
		20576 => 'alarm',
		20577 => 'alarm clear'
	);
	
	
	/**
	 * get all card access alarms
	 * @param $args \b can include filter, start_date, end_date
	 * @return array 
	 */
	public static function alarms($args)
	{
		$args = PSU::params($args);

		$bind = array();

		$where = '';
		if($args['start_date'])
		{
			$where .= " AND ah_condition_timestamp >= ?";
			$bind['start_date'] = PSU::db('pegasys')->BindTimeStamp(CardAccess::timeStamp($args['start_date']));
		} // end if

		if($args['end_date'])
		{
			$where .= " AND ah_condition_timestamp <= ?";
			$bind['end_date'] = PSU::db('pegasys')->BindTimeStamp(CardAccess::timeStamp($args['end_date'],false));
		} // end if

		if($args['filter'])
		{
			$where .= " AND ah_description_string LIKE '%'+?+'%'";
			$bind['filter'] = $args['filter'];
		} // end if

		$sql = "SELECT ah_condition_timestamp, ah_alarm_state, ah_description_string FROM alarms_hist WHERE 1=1 $where ORDER BY ah_condition_timestamp DESC";
		return PSU::db('pegasys')->GetAll($sql, $bind);
	} // end function alarms


	/**
	 * get all card access actions
	 * @param $args \b can include filter, start_date, end_date, id
	 * @return array 
	 */
	public static function actions($args='')
	{
		$args = PSU::params($args);

                $where = '';
                if($args['start_date'])
                {
                        $where .= " AND x_timestamp >= ?";
                        $bind['start_date'] = PSU::db('pegasys')->BindTimeStamp(CardAccess::timeStamp($args['start_date']));
                } // end if

                if($args['end_date'])
                {
                        $where .= " AND x_timestamp <= ?";
                        $bind['end_date'] = PSU::db('pegasys')->BindTimeStamp(CardAccess::timeStamp($args['end_date'],false));
                } // end if

                if($args['filter'])
                {
                        $where .= " AND x_term_name LIKE '%'+?+'%'";
                        $bind['filter'] = $args['filter'];
                } // end if

		if($args['id'])
                {
			$args['id'] = (int)$args['id'];
			$where .= " AND x_badge_number={$args['id']}";
		} // end if
	
		$sql = "SELECT x_timestamp, x_badge_number, x_fname, x_lname, x_hist_type, x_item_name, x_term_name FROM xaction WHERE 1=1 $where ORDER BY x_timestamp DESC";
		return PSU::db('pegasys')->GetAll($sql, $bind);
	} // end function actions


	/**
	 * get the issue number for a given badge for a person id
	 * @param $id
	 * @return string 
	*/
	public static function badgeIssueNumber($id)
	{
		$id = (int)$id;
		$sql = "SELECT b_issue FROM badge WHERE b_number_str=?";
		return PSU::db('pegasys')->GetOne($sql, array($id));
	} // end function badgeIssueNumber


	/**
	 * returns an array of doors for a given person id
	 * @param $id
	 * @return array 
	 */
	public static function doorAccess($id)
	{
		$id = (int)$id;
		$sql = "SELECT DoorName FROM viewReportAllCardholderToDoor WHERE BadgeNumber=?";
		return PSU::db('pegasys')->GetCol($sql, array($id));
	} // end function doorAccess


	/**
	* Return the unix timestamp from various formats, properly appending time for end dates
	* @param $date
	* @param $start \b boolean flag for indicating start date, if false appends 23:59:59
	* @return int 
	*/
	private static function timeStamp($date, $start=true)
	{
		if(is_numeric($date))
		{
			return $date;
		} // end if
		elseif(!$start && strpos($date,':')===false)
		{
			return strtotime(date('Y-m-d 23:59:59',strtotime($date)));
		} // end else if
		return strtotime($date);
	} // end function timeStamp

} // end class CardAccess


