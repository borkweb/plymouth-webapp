<?php

require_once('PSUTools.class.php');

/**
 * CallLog API.
 */
class CallLog
{
	/**
	 * Filter the ticket list.
	 *
	 * Valid $args:
	 *
	 * @li $q \b string match text in call history
	 * @li $group \b int the assigned group
	 */
	public static function search_tickets( $args )
	{
		$args = PSU::params( $args );
		extract( $args );

		$where = array();
		$sql_args = array();

		if( $q ) {
			$where[] = 'MATCH (comments) AGAINST (?)';
			$sql_args[] = $q;
		}

		if( $group ) {
			$where[] = 'its_assigned_group = ?';
			$sql_args[] = $group;
		}

		if( $call_status ) {
			$where[] = 'call_status = ?';
			$sql_args[] = $call_status;
		}

		if( $current ) {
			$where[] = 'current = ?';
			$sql_args[] = $current;
		}

		$where_sql = implode(' AND ', $where);

		$sql = "
			SELECT *
			FROM call_history
			WHERE
				$where_sql
			ORDER BY
				date_assigned DESC,
				time_assigned DESC
		";
		
		return PSU::db('calllog')->GetAll($sql, $sql_args);
	}//end search_tickets
}//end CallLog
