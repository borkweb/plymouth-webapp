<?php

class User
{
	var $db;
	var $people = array();

	/**
	 * Constructor.
	 */
	function __construct(&$db)
	{
		$this->db = $db;
	}//end constructor

	function callAssignedTo($tlc_users_options, $its_group_options, $tlc_assigned_to="", $its_assigned_to="")
	{
		$tpl = new XTemplate(TEMPLATE_DIR."/call_assigned_to.tpl");

		if ($_GET['call_id'] == "")
		{
			$tpl->assign('tlc_select_list', PSUHTML::getSelectOptions($tlc_users_options));
			$tpl->assign('its_select_group_list', PSUHTML::getSelectOptions($its_group_options));
		}
		else
		{
			$tpl->assign('tlc_select_list', PSUHTML::getSelectOptions($tlc_users_options, $tlc_assigned_to));
			$tpl->assign('its_select_group_list', PSUHTML::getSelectOptions($its_group_options, $its_assigned_to));
		}

		$tpl->parse('main');
		return $tpl->text('main');
	}

	function callInformation($call_status_options, $call_priority_options, $call_status, $call_priority, $building_options, $building_id, $resnet_call)
	{
		$tpl = new XTemplate(TEMPLATE_DIR.'/call_information.tpl');
		if($resnet_call == 'resnet')
		{
			$tpl->assign('is_resnet', 'checked');
		}
		$tpl->assign('call_status_select_list', PSUHTML::getSelectOptions($call_status_options, $call_status));
		$tpl->assign('call_priority_select_list', PSUHTML::getSelectOptions($call_priority_options, $call_priority));
		$tpl->assign('building_select_list', PSUHTML::getSelectOptions($building_options, $building_id));
		$tpl->parse('main');
		return $tpl->text('main');
	}

	function getCallDetails($call_id)
	{
		$call_details = Array();
		// Get call information from call_log table
		$query = "SELECT * FROM call_log WHERE call_id = '$call_id'";
		$result = $this->db->Execute($query);
		while($row = $result->FetchRow())
		{
			  $call_details['call_log'][] = $row;
		}// end while
		
		// Get call information from call_history table
		$query = "SELECT * FROM call_history WHERE call_id = '$call_id' ORDER BY date_assigned, time_assigned DESC";
		$result = $this->db->Execute($query);
		while($row = $result->FetchRow())
		{
			  $call_details['call_history'][] = $row;
		}// end while
		return $call_details;
	}// end function getCallDetails

	/**
	 * getCallerData
	 *
	 * returns caller data.  wewt.
	 *
	 * @param string $caller Caller username or pidm or wp_id
	 * @param array $person Person record
	 * @return array
	 */
	function getCallerData($caller, $person = false)
	{
		$found_via = null;

		if( $person ) {
			$found_via = 'function-args';
		}

		if( ! $caller ) {
			return array();
		}

		$config = \PSU\Config\Factory::get_config();
		$overrides = $config->get_json( 'psuperson', 'overrides' );

		if( isset( $overrides->$caller ) ) {
			$caller = $overrides->$caller;
		}

		//has the caller data already been queried?
		if(is_scalar($caller) && $this->people[$caller]) 
		{
			//aye!  return it
			return $this->people[$caller];
		}//end if

		// are we trying to query getCallerData based on an already-populated $caller?
		if(is_array($caller) && isset($caller['wp_id']) && isset($this->people[$caller['wp_id']]) ) {
			return $this->people[$caller['wp_id']];
		} elseif(is_array($caller) && isset($caller['pidm']) && isset($this->people[$caller['pidm']]) ) {
			return $this->people[$caller['pidm']];
		}//end elseif

		//
		// populate generic data
		//

		$caller_data = array(
			'pidm' => 0,
			'wp_id' => null,
			'identifier' => 'generic',
			'email' => 'generic',
			'name_first' => 'Generic Caller',
			'name_last' => 'Help Desk'
		);
		
		switch($caller)
		{
			case 'generic':
				$found_via = 'fake-user';
				break;
			case 'kiosk':
				$caller_data['email'] = 'kiosk';
				$caller_data['name_first'] = 'Kiosk';
				$found_via = 'fake-user';
				break;
			case 'clusteradm':
				$caller_data['email'] = 'clusteradm';
				$caller_data['name_first'] = 'Cluster Call';
				$found_via = 'fake-user';
				break;
		}//end switch

		$caller_data['name_full'] = $caller_data['name_first'].' - '.$caller_data['name_last'];

		//
		// done with generic user setup; try to populate real user
		//

		if(!$person && !$this->isFakeUser($caller))
		{
			// looks like a real user. try and find him.

			$caller_person = new PSUPerson( $caller );

			if( $caller_person->is_valid() ) {
				$person = array();
				$person['name_full'] = $caller_person->formatName('f m l');
				$person['wp_id'] = $caller_person->wp_id;
				$person['email'] = $caller_person->username ? $caller_person->username : $caller_person->wp_id;
				$person['pidm'] = $caller_person->pidm ? $caller_person->pidm : null;
				$person['identifier'] = $caller_person->wp_email ? $caller_person->wp_id : $caller_person->pidm;

				$found_via = 'psuperson';
			}
		}//end if

		//was a person record found?
		if(!empty($person))
		{
			//Do some data cleansing
			$person['phone_number'] = ($person['phone_of']) ? $person['phone_of'] : $person['phone_vm'];
	
			if( $person['pidm'] ) {
				$person['role'] = @implode(', ',$GLOBALS['portal']->getRoles($person['email']));
			} else {
				$person['role'] = 'No Roles: Family Portal Only';
			}//end else
			
			if ($person['class'] == 'Alumni')
			{
				$person['class'] = strtolower($person['class']).'.';
			}//end if

			if( $person['pidm'] ) {
				//get address for location
				if($addresses = current($GLOBALS['BannerGeneral']->getAddress($person['pidm'],'RH')))
				{
					$person['location'] = $addresses['r_street_line1'].' / '.$person['msc'];
				}//end if
				elseif($addresses = current($GLOBALS['BannerGeneral']->getAddress($person['pidm'],'OF')))
				{
					$person['location'] = $addresses['r_street_line2'].' / '.$person['msc'];
				}//end elseif

				$psu_person = new PSUPerson( $person['pidm'] );
				$person['phone_number'] = $this->getCallerPhone( $psu_person );
			}//end if
			//set the caller data to the person record
			$caller_data = $person;
		}//end if( !empty($person) )

		// person was not found via phonebook; try PSUPerson (again, if user isn't fake)
		elseif( !$this->isFakeUser($caller) )
		{
			$person = new PSUPerson($caller);

			$caller_data = array(
				'pidm' => $person->pidm,
				'wp_id' => $person->wp_id,
				'psu_id' => $person->id,
				'username' => $person->username,
				'identifier' => $person->username ? $person->username : $person->wp_id,
				'email' => $person->wp_email ? $person->wp_email : ($person->email ? $person->email['CA'][0] : ''),
				'name_last' => $person->last_name,
				'name_first' => $person->first_name,
				'name_full' => "{$person->first_name} {$person->last_name}",
				'phone_number' => $this->getCallerPhone( $person ),
			);

			if( isset($caller_data['email']['CA']) && strpos($caller_data['email']['CA'],'@')!==false ) {
				$caller_data['email'] = $caller_data['email']['CA'][0];
			} elseif( count($caller_data['email']) ) {
				if(is_array($caller_data['email'])) {
					$caller_data['email'] = array_shift(array_shift($caller_data['email']));
				}
				else {
					$caller_data['email'] = $caller_data['email'];
				}
			} else {
				$caller_data['email'] = null;
			}
		}
		
		$caller_data['username'] = $caller_data['email'];

		if( $found_via == null ) {
			return false;
		}
		
		//store the caller data so it isn't requeried a crap ton of times
		$this->people[$caller] = $caller_data;
		return $caller_data;
	}//end getCallerData

	/**
	 * getCallerInformation
	 */
	function getCallerInformation($caller, $call_id = '', $person = false)
	{
		$tpl = new XTemplate(TEMPLATE_DIR.'/user_information.tpl');

		$caller_data = $this->getCallerData($caller, $person);

		$tpl->assign('call_log_username', $_SESSION['username']);		
		$tpl->assign('caller', $caller_data);

		if($caller_data['pidm'] || $caller_data['wp_id'])
		{
			$tpl->parse('main.ape');	
		}//end if	
		else
		{
			$tpl->parse('main.no_ape');
		}//end else
		
		if($caller_data['phone_number']) $tpl->parse('main.phone');
		if($caller_data['title']) $tpl->parse('main.title');
		if($caller_data['dept']) $tpl->parse('main.dept');
		if($caller_data['location']) $tpl->parse('main.location');
		if($caller_data['phone_number']) $tpl->parse('main.phone_number');
		if($caller_data['role']) $tpl->parse('main.role');

		if( ! $this->isFakeUser( $caller_data['username'] ) && 'app.' != substr($caller_data['username'], 0, 4) && $caller_data['email'] ) {
			$tpl->parse('main.send_mail');
		}
		
		if($call_id){
			$tpl->assign('call_id', $call_id);
			$tpl->parse('main.edit_call_id');
			$tpl->assign('call_id_email', $call_id);
		}

		$tpl->parse('main');
		return $tpl->text('main');
	}// end getCallerInformation

	function getCallerPhone(PSUPerson $person) {
		$person->phone;

		if( isset( $person->phone['OF'][0] ) ) {
			return $person->phone['OF'][0]->phone_area .'-'.$person->phone['OF'][0]->phone_number_formatted;
		} elseif( isset( $person->phone['VM'][0] ) ) {
			return $person->phone['VM'][0]->phone_area .'-'.$person->phone['VM'][0]->phone_number_formatted;
		}//end else
		return null;
	}//end getCallerPhone

	function getSearchSetting($username = '')
	{
		$username = ($username) ? $username : $_SESSION['username'];
		return $this->db->GetOne("SELECT search_type FROM call_log_employee WHERE user_name = '$username'");
	}//end getSearchSetting

	function isFakeUser($username)
	{
		$fake_users = array('generic', 'clusteradm', 'kiosk', 'helpdesk');
		return in_array($username, $fake_users);
	}//end isFakeUser

	function setSearchSetting($setting)
	{
		$settings = array('full','split');
		if(in_array($setting, $settings))
		{
			$this->db->Execute("UPDATE call_log_employee SET search_type = '$setting' WHERE user_name = '{$_SESSION['username']}'");
		}//end if
		
	}//end getSearchSetting

	function userCallHistory($caller)
	{
		$tpl = new XTemplate(TEMPLATE_DIR.'/user_call_history.tpl');
		
		$person = $this->getCallerData($caller);

		if( $person['pidm'] || $person['username'] || $person['wp_id'] ) {
			$where = array();
			$args = array();

			if( $person['pidm'] ) {
				$where[] = "call_log.pidm = ?";
				$args[] = $person['pidm'];
			}//end if

			if( $person['username'] ) {
				$where[] = "call_log.caller_username = ?";
				$args[] = $person['username'];
			}//end if

			if( $person['wp_id'] ) {
				$where[] = "call_log.wp_id = ?";
				$args[] = $person['wp_id'];
			}//end if

			$where = implode(" OR ", $where);
		}//end if
		
		$sql = "SELECT * 
		          FROM call_history, 
		               call_log 
		         WHERE call_log.call_id = call_history.call_id 
		           AND ({$where})
		         GROUP BY call_history.call_id 
		         ORDER BY call_history.date_assigned DESC, 
		                  call_history.time_assigned DESC 
		         LIMIT 100";

		$result = $this->db->GetAll($sql, $args);
		if (count($result) > 0){
			$i=0;
			foreach ($result as $row){
				$i++;
				$tpl->assign('i',$i);
				if(empty($row['comments'])) {
					$row['comments'] = '<em>no update details</em>';
				} else {
					$row['comments'] = strip_tags( $row['comments'] );
				}
				$tpl->assign('call',$row);
				$tpl->parse('main.user_call_history.call');
				$tpl->parse('main.user_call_history');
			}
		}else{
			$tpl->parse('main.no_results_message');
		}
		$tpl->parse('main');
		return $tpl->text('main');
	}

	function userEmailFunction($caller, $call_id){
		$tpl = new XTemplate(TEMPLATE_DIR.'/user_email.tpl');

		if( ! $caller ) {
			return false;
		}
		
		$person = $this->getCallerData($caller);
		
		$tpl->assign('email_call_id', ($call_id) ? $call_id : 0);
		$tpl->assign('caller', $person);
		
		$tpl->parse('main');
		return $tpl->text('main');
	}
}

