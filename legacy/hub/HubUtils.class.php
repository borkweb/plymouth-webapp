<?php

/**
 * Hub.class.php.
 *
 * Coordinate common data & functionality across all HUB apps in 1 location
 *
 * @copy; 2010 Plymouth State University 
 *
 * @author	Betsy Coleman <bscoleman@plymouth.edu>
 */ 


class HubUtils 
{
/**
 */

	public $hub_config_id_msgofday = 'message_of_the_day';
	public $hub_config_id_monthlystaffmtgsched = 'monthly_staff_meeting_schedule';
	public $hub_config_id_email_who = 'portal_email_names';
	public $hub_config_id_email_addresses = 'portal_email_addresses';
	public $hub_config_id_ir_admin = 'ir_notify_admin';
	public $hub_config_id_ir_rec = 'ir_notify_rec';
	public $hub_config_id_ar_admin = 'ar_notify_admin';
	public $hub_config_id_ar_rec = 'ar_notify_rec';
	public $hub_config_id_bmr_notify = 'bmr_notify';

	public function __construct()
	{
	}


/**
 *
 * @param string $config_id
 * @access public
 * @return value of the passed config value
 * 
 */
  function getConfigValue($config_id)
  {
    $sql = "SELECT config_value
              FROM `hub_config`
              WHERE config_id=?";

    return PSU::db('hub')->GetOne($sql, array($config_id));
  }

/**
 *
 * @param string $config_id
 * @access public
 * @return value of the passed config value
 * 
 */
  function setConfigValue($config_id, $data)
	{
		$sql = "REPLACE INTO hub_config 
							( 
							config_id,
							config_value
							)
						VALUES 
							(
							  ?,
								?
							)";

		$rs = PSU::db('hub')->Execute($sql,
						array(
								$config_id,
								$data,
								));

		return $rs;
	}
}

