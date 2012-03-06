<?php

/**
 * BannerAdmissions.class.php
 *
 * @package 		PSUBannerAPI
 */

/**
 * BannerAdmissions.class.php
 *
 * Banner API for Admissions
 *
 * @module		BannerAdmissions.class.php
 * @author		Matthew Batchelder <mtbatchelder@plymouth.edu>
 * @copyright 2005, Plymouth State University, ITS
 */ 

require_once('BannerGeneral.class.php');

class BannerAdmissions extends BannerGeneral
{
	var $db;
	/**
	 * __construct
	 *
	 * BannerAdmissions constructor with db connection. 
	 *
	 * @access		public
	 * @param  		ADOdb $db ADOdb database connection
	 */
	function __construct(&$db)
	{
		$this->db = $db;
	}//end __construct
}//end class BannerAdmissions

?>
