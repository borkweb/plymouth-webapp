<?php

$action = $_REQUEST['action'];
$type = $_REQUEST['type'];
$parent_id = (int)$_REQUEST['parent_id'];
$action_status = 'failure'; // default, overridden elsewhere

$response = array(
	'message' => '',
	'status' => 'error'
);

try
{
	$person = null;

	// try to fetch the user
	try {
		if( isset($_REQUEST['pidm']) ) {
			$person = new PSUPerson($_REQUEST['pidm']);
		} elseif( isset($_REQUEST['username']) ) {
			$person = new PSUPerson($_REQUEST['username']);
		}
	} catch( Exception $e ) {
		throw new Exception('Error fetching specified user.');
	}

	if( $person === null ) {
		throw new Exception('Error fetching specified user.');
	}

	// got a valid user, continue with life.
	
	$global_attribute_admin = IDMObject::authZ('permission','ape_attribute_admin');
	
	// if this pidm is bogus, display an error
	if(!$GLOBALS['BannerGeneral']->isValidPidm($person->pidm))
	{ 
		throw new Exception("User \"". ($person->username ? $person->username : $_REQUEST['username']) ."\" is invalid, could not $action the $type.");
	} 

	if($action == 'add')
	{
		$attributes = (array) $_REQUEST['attribute'];
		$attribute['source'] = $GLOBALS['IDM_SOURCE'];
		$attribute['grantor'] = $_SESSION['username'];


		foreach($attributes as $this_attribute)
		{
			$log_attribute = $attribute['attribute'] = $this_attribute;

			// check for allowed types. ape can add permissions and roles.
			if($type != 'permission' && $type != 'role')
			{
				throw new Exception("Sorry, APE can't add a " . htmlentities($type) .".");
			}

			// bail out totally if they can't edit this attribute
			if( !$global_attribute_admin && !IDMObject::authZ('admin', $this_attribute) )
			{
				throw new Exception("You cannot administer the $this_attribute attribute");
			}

			// validate permission adding
			if($type == 'permission')
			{
				//if no parent id was passed, attempt to find an APE granted one
				if($parent_id)
				{
					$parent_log = $GLOBALS['BannerIDM']->getLog($parent_id);
				}//end if
				else
				{
					$parent_roles = $GLOBALS['BannerIDM']->getParentRole(1, $this_attribute);
					
					if($logs = $GLOBALS['BannerIDM']->getLogs($person->pidm, "source = 'ape' AND type_id = 2 AND attribute IN('" .  implode("', '", $parent_roles['role']) . "')"))
					{
						//grab the first available parent role granted by ape
						if($parent_role = array_shift($logs['role']))
						{
							$parent_log = array_shift($parent_role);
							$parent_id = $parent_log['id'];
						}//end if
					}//end if
				}//end else
				
				if(!$parent_log)
				{
					throw new Exception('Before you can add the \'' . $this_attribute . '\' permission, that user must have one or more of the following roles: ' . implode(", ", $parent_roles['role']));
				}//end if	

				if((int) $parent_log['pidm'] != $person->pidm)
				{
					throw new Exception('Pidm of log entry did not match submitted Pidm.');
				}

				$children = $GLOBALS['BannerIDM']->getChildAttributes($parent_log['attribute'], 'permission', IDMObject::IDM_INCLUDE);

				// new child should be in the parent's child list
				if(!isset($children[$type][$attribute['attribute']]))
				{
					throw new Exception('New child is not a parent of Log ID ' . $parent_id);
				}
			}
			
			$day = date('j');
			$month = date('n');
			$year = date('Y');

			$today = mktime(0, 0, 0, $month, $day, $year);
			$start_date = preg_match('/[0-9]{4}-[0-9]{2}-[0-9]{1,2}/', $_REQUEST['start_date']) ? strtotime($_REQUEST['start_date']) : false;
			$end_date = preg_match('/[0-9]{4}-[0-9]{2}-[0-9]{1,2}/', $_REQUEST['end_date']) ? strtotime($_REQUEST['end_date']) : false;
			$max_end_date = mktime(0, 0, 0, $month, $day, $year+10);

			if($start_date && $start_date >= $today)
			{
				$attribute['start_date'] = $start_date;
			}

			if($end_date && $end_date >= $today && $end_date >= $start_date)
			{
				$attribute['end_date'] = $end_date;
			}

			if(!empty($_REQUEST['reason']))
			{
				$attribute['reason'] = urldecode($_REQUEST['reason']);
			}

			if($parent_id)
			{
				$attribute['parent_id'] = $attribute['origin_id'] = $parent_id;
			}

			$GLOBALS['BannerIDM']->addAttribute($person->pidm, $type, $attribute['attribute'], $attribute['source'], $attribute);

			// TODO: message will get overwritten on the next pass. need to let them stack.
			$response['status'] = 'success';
			$response['message'] = sprintf('%s "%s" was sucessfully added.', ucfirst($type), $attribute['attribute']);

			$name = $GLOBALS['BannerIDM']->getName($person->pidm, 'f,l');
			list($response['first_name'], $response['last_name']) = explode(',', $name);
			$response['username'] = $person->username;
			$response['source'] = $attribute['source'];
			$response['pid'] = $person->pidm;

			$GLOBALS['ape']->log($person->pidm, $action, $action_status, $type, $log_attribute);
		}
	}
	elseif($action == 'remove')
	{
		$id = $_REQUEST['id'];

		$role = $GLOBALS['BannerIDM']->getLog($id);

		if($role['source'] !== $GLOBALS['IDM_SOURCE'])
		{
			throw new Exception(sprintf('That role was added via %s, and cannot be deleted through %s.',
				$role['source'], $GLOBALS['IDM_SOURCE']));
		}

		if( !$global_attribute_admin && !IDMObject::authZ('admin', $role['attribute']) )
		{
			throw new Exception("You cannot administer the {$role['attribute']} attribute");
		}

		$GLOBALS['BannerIDM']->removeAttribute($person->pidm, $id);

		list($type_id, $type) = $GLOBALS['BannerIDM']->any2type($role['type_id']);

		$log_attribute = $role['attribute'];

		$response['status'] = 'success';
		$response['message'] = sprintf('%s "%s" has been removed.', ucfirst($type), $role['attribute']);

		$GLOBALS['ape']->log($person->pidm, $action, $action_status, $type, $log_attribute);
	}
	else
	{
		$GLOBALS['ape']->log($person->pidm, $action, $action_status, $type, $log_attribute);
	}
}
catch(Exception $e)
{
	$response['message'] = $e->GetMessage() . ($e->GetCode() ? '('.$e->GetCode().')' : '');
	$GLOBALS['ape']->log($person->pidm, $action, $action_status, $type, $log_attribute);
}

// bail here if request was javascript
if( isset($_GET['method']) && $_GET['method'] == 'js' )
{
	header('Content-type: text/javascript');

	$response['pidm'] = $person->pidm;
	$response['type'] = $type;
	$response['attribute'] = $log_attribute;

	die( json_encode($response) );
}

// pass along our message
if( $response['status'] == 'success' )
{
	$_SESSION['messages'][] = $response['message'];
}
else
{
	$_SESSION['errors'][] = $response['message'];
}

PSUHTML::redirect($GLOBALS['BASE_URL'] . '/user/' . $person->pidm);
