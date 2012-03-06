<?php

/**
 * zimbra.class.php
 *
 * === Modification History ===<br/>
 * ALPHA  25-Sep-2007  [zbt]  original<br/>
 * 1.0    10-Oct-2007  [zbt]  Added calendar, mail, and admin functionailty<br/>
 * 1.1    30-Oct-2007  [zbt]  Added on vacation, and split admin functionality out into child class<br/>
 * 1.2    23-Oct-2008  [zbt]  Added stuff for tasks
 * 1.3    24-Oct-2008  [nrp]  Addded phpDoc goodness
 * 1.4    02-Feb-2010  [zbt/djb/amb]  Converted email channel to Smarty, improved functions for appointment notifications, extended data in task functions
 *
 */

/**
 * zimbra.class.php
 *
 * Zimbra API
 *
 * @version		1.3
 * @module		zimbra.class.php
 * @author		Zachary Tirrell <zbtirrell@plymouth.edu>
 * @GPL 2007, Plymouth State University, ITS
 */ 

class Zimbra
{
	public $debug=false;
	public $error;
	public $error_code;

	protected $_connected = false; // boolean to determine if the connect function has been called
	protected static $_num_soap_calls = 0; // the number of times a SOAP call has been made
	
	protected $_preAuthKey; // key for doing pre-authentication
	
	protected $_lcached_assets = array(); // an array to hold assets that have been cached
	
	protected $_preauth_expiration=0; // 0 indicates using the default preauth expiration as defined on the server
	protected $_dev; // boolean indicating whether this is development or not
	protected $_protocol; // which protocol to use when building the URL
	protected $_server; // hostname of zimbra server
	protected $_path = '/service/soap';
	protected $_timestamp;

	protected $_account_info;

	protected $_admin = false; // operating as an admin

	protected $_curl;
	protected $_auth_token; // used for repeat calls to zimbra through soap
	protected $_session_id; // used for repeat calls to zimbra through soap

	protected $_idm;  // IDMObject

	protected $_username; // the user we are operating as

	/**
	* __construct
	*
	* constructor sets up connectivity to servers
	*
	* @since		version 1.0
	* @acess	public
	* @param string $username username
	* @param string $which defaults to prod
	*/
	public function __construct($username, $which = 'prod')
	{
		if($which=='dev')
		{
			$which = 'zimbra_dev';
			$this->_dev = true;
		}
		else
		{
			$which = 'zimbra';
		}

		// load the following configuration settings via PSU's credential storage mechanism
		require_once 'PSUDatabase.class.php';
		$conf = PSUDatabase::connect('other/'.$which,'return');
		$this->_preAuthKey = PSUSecurity::password_decode($conf['key']);
		$this->_protocol = $conf['protocol'];
		$this->_server = $conf['server'];
		// end of PSU proprietary configuration load
	
		/* **** if not PSU, do something similar to the following:
		$this->_preAuthKey = '<insert key string acquired from Zimbra server>';
		$this->_protocol = 'https://'; // could also be http://
		$this->_server = 'zimbra.hostname.edu';
		*** */

		$this->_username = $username;

		$this->_timestamp = time().'000';
	} // end __construct

	/**
	* sso
	*
	* sso to Zimbra
	*
	* @since		version 1.0
	* @param	string $options options for sso
	* @return	boolean
	*/
	public function sso($options='')
	{
		if($this->_username)
		{
			setcookie('ZM_SKIN','plymouth',time()+60*60*24*30,'/','.plymouth.edu');

			$pre_auth = $this->getPreAuth($this->_username);
			
			$url = $this->_protocol.$this->_server.'/service/preauth?account='.$this->_username.'@'.$this->_server.'&expires='. $this->_preauth_expiration.'&timestamp='.$this->_timestamp.'&preauth='.$pre_auth.'&'.$options;

			header("Location: $url");
			exit;
		}
		else
		{
			return false;
		}
	} // end sso

	/**
	* getPreAuth
	*
	* get the preauth key needed for single-sign on
	*
	* @since		version1.0
	* @param	string $username username
	* @return	string preauthentication key in hmacsha1 format
	*/
	private function getPreAuth($username)
	{
		$account_identifier = $username.'@'.$this->_server;
		$by_value = 'name';
		$expires = $this->_preauth_expiration;
		$timestamp = $this->_timestamp;

		$string = $account_identifier.'|'.$by_value.'|'.$expires.'|'.$timestamp;
		
		return $this->hmacsha1($this->_preAuthKey,$string);
	} // end getPreAuth

	/**
	* hmacsha1
	*
	* generate an HMAC using SHA1, required for preauth
	* 
	* @since		version 1.0
	* @param	int $key encryption key
	* @param	string $data data to encrypt
	* @return	string converted to hmac sha1 format
	*/
	private function hmacsha1($key,$data)
	{
		$blocksize=64;
		$hashfunc='sha1';
		if (strlen($key)>$blocksize)
			$key=pack('H*', $hashfunc($key));
		$key=str_pad($key,$blocksize,chr(0x00));
		$ipad=str_repeat(chr(0x36),$blocksize);
		$opad=str_repeat(chr(0x5c),$blocksize);
		$hmac = pack(
					'H*',$hashfunc(
						($key^$opad).pack(
							'H*',$hashfunc(
								($key^$ipad).$data
							)
						)
					)
				);
		return bin2hex($hmac);
	} // end hmacsha1

	/**
	* connect
	*
	* connect to the Zimbra SOAP service
	*
	* @since	version 1.0
	* @return	array associative array of account information
	*/
	public function connect()
	{
		if($this->_connected)
		{
			return $this->_account_info;
		}

        $this->_curl = curl_init();
        curl_setopt($this->_curl, CURLOPT_URL,$this->_protocol.$this->_server.$this->_path);
        curl_setopt($this->_curl, CURLOPT_POST,           true);
        curl_setopt($this->_curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($this->_curl, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($this->_curl, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($this->_curl, CURLOPT_TIMEOUT, 15);

		$preauth =	$this->getPreAuth($this->_username);
        $header  = '<context xmlns="urn:zimbra'.(($this->_admin)?'Admin':'').'"/>';

		if($this->_admin)
		{
			$body = '<AuthRequest xmlns="urn:zimbraAdmin">
						<name>'.$this->_admin_username.'</name>
						<password>'.$this->_admin_password.'</password>
					</AuthRequest>';
		}
		else
		{
			$body = '<AuthRequest xmlns="urn:zimbraAccount">
						<account by="name">'.$this->_username.'@'.$this->_server.'</account>
						<preauth timestamp="'.$this->_timestamp.'" expires="'.$this->_preauth_expiration.'">'.$preauth.'</preauth>
					</AuthRequest>';
		}

		try {
			$response = $this->soapRequest($body,$header,true);
		} catch(Exception $e) {
			$this->_connected = false;
			return false;
		}

		if($response)
		{
			$tmp = $this->makeXMLTree($response);
			$this->_account_info = $tmp['soap:Envelope'][0]['soap:Header'][0]['context'][0]['refresh'][0]['folder'][0];

			$this->session_id = $this->extractSessionID($response);
			$this->auth_token = $this->extractAuthToken($response);
			
			$this->_connected = true;
			
			return $this->_account_info;
		}
		else
		{
			$this->_connected = false;
			return false;
		}
	}  // end connect

	/**
	* administerUser
	*
	* set the user you are administering (experimental)
	*
	* @since		version 1.0
	* @param	string $username username to administer
	* @return	boolean
	*/
	public function administerUser($username)
	{
		if(!$this->_admin)
		{
			return false;
		}

		$this->_username = $username;

		$body = '<DelegateAuthRequest xmlns="urn:zimbraAdmin">
			<account by="name">'.$this->_username.'@'.$this->_server.'</account> 
		</DelegateAuthRequest>';

		$response = $this->soapRequest($body,$header);

		global $firephp;
		$firephp->log($response);
		if($response)
		{
			$tmp = $this->makeXMLTree($response);
			$this->_account_info = $tmp['soap:Envelope'][0]['soap:Header'][0]['context'][0]['refresh'][0]['folder'][0];

			$this->session_id = $this->extractSessionID($response);
			$this->auth_token = $this->extractAuthToken($response);

			return true;
		}
		else
		{
			return false;
		}
	} // end administerUser
 
	/**
	* getInfo
	*
	* generic function to get information on mailbox, preferences, attributes, properties, and more!
	*
	* @since		version 1.0
	* @param	string $options options for info retrieval, defaults to null
	* @return	array information
	*/
	public function getInfo($options='')
	{
		// valid sections: mbox,prefs,attrs,zimlets,props,idents,sigs,dsrcs,children
		$option_string = $this->buildOptionString($options);

		$soap ='<GetInfoRequest xmlns="urn:zimbraAccount"'.$option_string.'></GetInfoRequest>';
		$response = $this->soapRequest($soap);
		if($response)
		{
			$array = $this->makeXMLTree($response);
			return $array['soap:Envelope'][0]['soap:Body'][0]['GetInfoResponse'][0];
		}
		else
		{
			return false;
		}
	} // end getInfo

	/**
	* getMessages
	*
	* get the messages in folder, deafults to inbox
	*
	* @since		version 1.0
	* @param	string $search folder to retrieve from, defaults to in:inbox
	* @param $options options to apply to retrieval
	* @return	array array of messages
	*/
	public function getMessages($search='in:inbox', $options=array('limit'=>5, 'fetch'=>'none'))
	{
		$option_string = $this->buildOptionString($options);

		$soap ='<SearchRequest xmlns="urn:zimbraMail" types="message"'.$option_string.'>
					<query>'.$search.'</query>
				</SearchRequest>';
		$response = $this->soapRequest($soap);
		if($response)
		{
			$array = $this->makeXMLTree($response);
			return $array['soap:Envelope'][0]['soap:Body'][0]['SearchResponse'][0];
		}
		else
		{
			return false;
		}
	} // end getMessages

	/**
	* getAppointments
	*
	* get appointments in a calendar
	*
	* @since		version 1.0
	* @param $options array of options to apply to retrieval from calendar
	* @return	array associative array of appointments
	*/
	public function getAppointments($options=array())
	{
		$option_string = $this->buildOptionString($options);

		$soap ='<BatchRequest xmlns="urn:zimbra" onerror="continue"><GetApptSummariesRequest xmlns="urn:zimbraMail" '.$option_string.'/></BatchRequest>';

		$response = $this->soapRequest($soap);
		if($response)
		{
			$array = $this->makeXMLTree($response);

			return $array['soap:Envelope'][0]['soap:Body'][0]['BatchResponse'][0]['GetApptSummariesResponse'][0]['appt'];
		}
		else
		{
			return false;
		}
	} // end getAppointments


	/**
	* searchAppointment
	*
	* search for appointments
	*
	* @param	string $search item for searching
	* @param $options special search options
	* @return	mixed
	*/
	public function searchAppointments($search,$options=array())
	{
		$option_string = $this->buildOptionString($options);

		$soap ='<SearchRequest xmlns="urn:zimbraMail" types="appointment"'.$option_string.'>
					<query>in:"'.$search.'"</query>
				</SearchRequest>';

		$response = $this->soapRequest($soap);
		if($response)
		{
			$array = $this->makeXMLTree($response);

			return $array['soap:Envelope'][0]['soap:Body'][0]['SearchResponse'][0]['appt'];
		}
		else
		{
			return false;
		}
	} // end searchAppointments


	/**
	* getAppointment
	*
	* get appointment detail
	*
	* @param	int $id of appointment
	* @return	mixed
	*/
	public function getAppointment($id)
	{
		$soap ='<GetMsgRequest xmlns="urn:zimbraMail">
					<m id="'.$id.'" />
				</GetMsgRequest>';

		$response = $this->soapRequest($soap);
		if($response)
		{
			$array = $this->makeXMLTree($response);
			return $array['soap:Envelope'][0]['soap:Body'][0]['GetMsgResponse'][0]['m'][0];
		}
		else
		{
			return false;
		}
	} // end getAppointment


	/**
	* getTasks
	*
	* get tasks in a task list
	*
	* @since		version 1.0
	* @param	string $search search paramaters, defaults to *
	* @param $options options to control retrieval
	* @return	array associative array of tasks
	*/
	public function getTasks($search='*', $options=array('limit'=>1000))
	{
		$option_string = $this->buildOptionString($options);

		$soap ='<SearchRequest xmlns="urn:zimbraMail" types="task"'.$option_string.'>
					<query>in:"'.$search.'"</query>
				</SearchRequest>';
		$response = $this->soapRequest($soap);

		if($response)
		{
			$array = $this->makeXMLTree($response);

			$tasks = $array['soap:Envelope'][0]['soap:Body'][0]['SearchResponse'][0]['task'];

			$task_list = array();
			$task_list['INPR'] = array();
			$task_list['WAITING'] = array();
			$task_list['DEFERRED'] = array();
			$task_list['NEED'] = array();
			$task_list['COMP'] = array();
			$task_list['CONFIG'] = array('ti'=>$search,'in'=>'In Progress','wt'=>'Waiting','df'=>'Deferred','nd'=>'Not Started','cp'=>'Completed');

			foreach($tasks as $task)
			{
				if($task['name']!='META_CONFIG_DO_NOT_DELETE')
				{
					$task['start'] = ($task['dur'])?date('n/j/y',($task['dueDate']-$task['dur'])/1000):'[not scheduled]';
					$task['end'] = ($task['dueDate'])?date('n/j/y',$task['dueDate']/1000):'[no date]';
					$task['t_percent_complete'] = (int)$task['percentComplete'].'%';
					$task['end_ts'] = strtotime($task['end']);

					// begin code for gantt chart
					global $gantt_months;
					if(isset($gantt_months))
					{
						$start_ym = date('Ym',strtotime($task['start']));
						$end_ym = date('Ym',strtotime($task['end']));
						$this_ym = date('Ym');
		
						foreach($gantt_months as $k=>$month)
						{
							$ym = date('Ym',$month);
		
							$task['gantt']['month'][$k]='';
							if($ym>$start_ym && $ym<$end_ym) // show the middle of the line portion of the graphic
							{
								$task['gantt']['month'][$k] = 'month-mid';
							}
							elseif($ym==$end_ym) // show the end of the line portion of the graphic, an arrow
							{
								$task['gantt']['month'][$k] = 'month-end';
							}
							elseif($ym==$start_ym) // show the start of the line portion of the graphic, square ended
							{
								$task['gantt']['month'][$k] = 'month-start';
							}
							else // event isn't in this month, so show nothing
							{
								$task['gantt']['month'][$k]='month-none';
							}
		
							// highlight vertically the current month
							if($this_ym==$ym)
							{
								$task['gantt']['month'][$k] .= ' month-current';
							}
						}
						
						$task['start_my'] = date('M/Y',strtotime($task['start']));

					}
					// end codes for gantt chart
					
					$task_list[$task['status']][] = $task;
				}
				else
				{
					$temp = explode('|',$task['fr'][0]);
					foreach($temp as $tmp)
					{
						list($k,$v) = explode('::',$tmp);
						$task_list['CONFIG'][$k]=$v;
					}
				}
			}

			usort($task_list['INPR'], 'zimbra_dueSort');
			usort($task_list['WAITING'], 'zimbra_startSort');
			usort($task_list['DEFERRED'], 'zimbra_startSort');
			usort($task_list['NEED'], 'zimbra_nameSort');
			usort($task_list['COMP'], 'zimbra_dueSort');

			return $task_list;
		}
		else
		{
			return false;
		}
	} // end getTasks

	/**
	* getMessageContent
	*
	* get the content from a message
	*
	* @since		version 1.0
	* @param	int $id id number of message to retrieve content of
	* @return	array associative array with message content, valid for tasks, calendar entries, and email messages.
	*/	
	public function getMessageContent($id)
	{	
		$soap ='<GetMsgRequest xmlns="urn:zimbraMail">
					<m id="'.$id.'" html="1">*</m>
				</GetMsgRequest>';
		$response = $this->soapRequest($soap);

		if($response)
		{
			$array = $this->makeXMLTree($response);
			$temp = $array['soap:Envelope'][0]['soap:Body'][0]['GetMsgResponse'][0]['m'][0];

			$message = $temp['inv'][0]['comp'][0];

			// content with no attachment
			$message['content'] = $message['descHtml'][0];
			
			// content with attachment
			$message['content'] .= $temp['mp'][0]['mp'][0]['mp'][1]['content'][0]; 

			return $message;
		}
		else
		{
			return false;
		}
	}

	/**
	* getSubscribedCalendars
	*
	* get the calendars the user is subscribed to
	*
	* @since		version 1.0
	* @return	array $subscribed
	*/
	public function getSubscribedCalendars()
	{
		$subscribed = array();
		if(is_array($this->_account_info['link_attribute_name']))
		{
			foreach($this->_account_info['link_attribute_name'] as $i=>$name)
			{
				if($this->_account_info['link_attribute_view'][$i]=='appointment')
				$subscribed[$this->_account_info['link_attribute_id'][$i]] = $name;
			}
		}
		return $subscribed;
	} // end getSubscribedCalendars


	/**
	* getSubscribedTaskLists
	*
	* get the task lists the user is subscribed to
	*
	* @since		version 1.0
	* @access       public
	* @return       array $subscribed or false
	 */
	public function getSubscribedTaskLists()
	{
		$subscribed = array();
		if(is_array($this->_account_info['link_attribute_name']))
		{
			foreach($this->_account_info['link_attribute_name'] as $i=>$name)
			{
				if($this->_account_info['link_attribute_view'][$i]=='task')
					$subscribed[$this->_account_info['link_attribute_id'][$i]] = $name;
			}
		}
		return $subscribed;
	} // end getSubscribedCalendars
	
	/**
	* getFolder
	*
	* get a folder (experimental)
	*
	* @since                version 1.0
	* @param	string $folder_options options for folder retrieval
	* @return	array $folder or false
	*/
	public function getFolder($folder_options='')
	{
		$folder_option_string = $this->buildOptionString($folder_options);

		$soap ='<GetFolderRequest xmlns="urn:zimbraMail" visible="1">
					<folder path="Inbox"/>
				</GetFolderRequest>';
		$response = $this->soapRequest($soap);
		if($response)
		{
			$array = $this->makeXMLTree($response);

			$folder = (is_array($array['soap:Envelope'][0]['soap:Body'][0]['GetFolderResponse'][0]['folder'][0]))?$array['soap:Envelope'][0]['soap:Body'][0]['GetFolderResponse'][0]['folder'][0]:$array['soap:Envelope'][0]['soap:Body'][0]['GetFolderResponse'][0];

			$folder['u'] = (!isset($folder['u']))?$folder['folder_attribute_u'][0]:$folder['u'];
			$folder['n'] = (!isset($folder['n']))?$folder['folder_attribute_n'][0]:$folder['n'];

			return $folder;
		}
		else
		{
			return false;
		}
	} // end getFolder

	/**
	* Get preferences. Use the following format: <pre><code>&lt;GetPrefsRequest> &lt;!-- get only the specified prefs --> [&lt;pref name="{name1}"/> &lt;pref name="{name2}"/>] &lt;/GetPrefsRequest></code></pre>
	*
	* @since		version 1.0
	* @return	array $prefs or false
	*/
	public function getPreferences()
	{
		$soap ='<GetPrefsRequest xmlns="urn:zimbraAccount" />';
		$response = $this->soapRequest($soap);
		if($response)
		{
			$prefs = array();
			$array = $this->makeXMLTree($response);
			foreach($array['soap:Envelope'][0]['soap:Body'][0]['GetPrefsResponse'][0]['pref'] as $k=>$value)
			{
				$prefs[$array['soap:Envelope'][0]['soap:Body'][0]['GetPrefsResponse'][0]['pref_attribute_name'][$k]] = $value;
			}
			return $prefs;
		}
		else
		{
			return false;
		}
	} // end getPreferences

	/**
	 * Modify preferences. Use the following format: <pre><code>&lt;ModifyPrefsRequest> [&lt;pref name="{name}">{value}&lt;/pref>...]+ &lt;/ModifyPrefsRequest></code></pre>
	 *
	 * @since		version 1.0
	 * @param	string $options options to set the preferences
	 * @return	boolean
	 */
	public function setPreferences($options='')
	{
		$option_string = '';
		foreach($options as $name=>$value)
		{
			$option_string .= '<pref name="'.$name.'">'.$value.'</pref>';
		}

		$soap ='<ModifyPrefsRequest xmlns="urn:zimbraAccount">
					'.$option_string.'
				</ModifyPrefsRequest>';
		$response = $this->soapRequest($soap);
		if($response)
		{
			return true;
		}
		else
		{
			return false;
		}
	} // end setPreferences

	/**
	* build the email channel
	*
	* @since		version 1.0
	*/
	public function emailChannel()
	{
		require_once('PSUSmarty.class.php');
		$tpl = new PSUSmarty;

		$total_messages = 0;
		$unread_messages = 0;

		$clean_messages = array();
		$messages = $this->getMessages('in:inbox');

		if( $messages === false )
		{
			return sprintf('Sorry, your mailbox could not be fetched (%s).', $this->error_code);
		}

		if(is_array($messages))
		{
			$more = $messages['more'];
			foreach($messages['m'] as $message)
			{
				$clean_message = array();

				$clean_message['subject'] = (isset($message['su'][0])&&$message['su'][0]!='')?htmlentities($message['su'][0]):'[None]';
				$clean_message['subject'] = (strlen($clean_message['subject'])>50)?substr($clean_message['subject'],0,47).'...':$clean_message['subject'];
				
				$clean_message['body_fragment'] = $message['fr'][0];
				$clean_message['from_email'] = $message['e_attribute_a'][0];
				$clean_message['from'] = ($message['e_attribute_p'][0])?htmlspecialchars($message['e_attribute_p'][0]):$clean_message['from_email'];
				$clean_message['size'] = $this->makeBytesPretty($message['s'],40*1024*1024);
				$timestamp = $message['d'] / 1000;
				$clean_message['date'] = date('n/j/y') == date('n/j/y', $timestamp) ? date('g:ia', $timestamp) : date('M j', $timestamp);
				$clean_message['id'] = $message['id'];
				$clean_message['url'] = 'http://go.plymouth.edu/mymail/msg/' . $clean_message['id'];
				
				$clean_message['attachment'] = false;
				$clean_message['status'] = 'read';
				$clean_message['deleted'] = false;
				$clean_message['flagged'] = false;
				if(isset($message['f']))
				{
					$clean_message['attachment'] = (strpos($message['f'],'a')!==false)?true:false;
					$clean_message['status'] = (strpos($message['f'],'u')!==false)?'unread':'read';;
					$clean_message['deleted'] = (strpos($message['f'],'2')!==false)?true:false;
					$clean_message['flagged'] = (strpos($message['f'],'f')!==false)?true:false;
				}

				$clean_messages[] = $clean_message;
			}
			$tpl->assign('messages', $clean_messages);

			$inbox = $this->getFolder(array('l'=>2));
			
			$total_messages = (int)$inbox['n'];
			$unread_messages = (int)$inbox['u'];
		}

		$tpl->assign('total_messages', $total_messages);
		$tpl->assign('unread_messages', $unread_messages);

		$info = $this->getInfo(array('sections'=>'mbox'));
		if(is_array($info['attrs'][0]['attr_attribute_name']))
		{
			$quota = $info['attrs'][0]['attr'][array_search('zimbraMailQuota',$info['attrs'][0]['attr_attribute_name'])];
			$size_text = $this->makeBytesPretty($info['used'][0],($quota*0.75)).' out of '.$this->makeBytesPretty($quota);
			$tpl->assign('size', $size_text);
		}

		/*include_once 'portal_functions.php';
		$roles = getRoles($this->_username);

		if(in_array('faculty', $roles) || in_array('employee', $roles))
		{
			$tpl->parse('main.away_message');
		}*/
		
		return $tpl->fetch('/web/pscpages/webapp/portal/channel/email/templates/index.tpl');
	} // end emailChannel
	

	
	/**
	* builOptionString
	*
	* make an option string that will be placed as attributes inside an XML tag
	*
	* @since		version 1.0
	* @param $options array of options to be parsed into a string
	* @return	string $options_string
	*/
	protected function buildOptionString($options)
	{
		$options_string = '';
		foreach($options as $k=>$v)
		{
			$options_string .= ' '.$k.'="'.$v.'"';
		}
		return $options_string;
	} // end buildOptionString

	/**
	*	extractAuthToken
	*
	* get the Auth Token out of the XML
	*
	* @since		version 1.0
	* @param string $xml xml to have the auth token pulled from
	* @return string $auth_token
	*/
	private function extractAuthToken($xml)
	{
		$auth_token = strstr($xml, "<authToken");
        $auth_token = strstr($auth_token, ">");
        $auth_token = substr($auth_token, 1, strpos($auth_token, "<") - 1);
		return $auth_token;
	}

	/**
	* extractSessionID
	*
	* get the Session ID out of the XML
	*
	* @since		version 1.0
	* @param	string $xml xml to have the session id pulled from
	* @return int $session_id
	*/
	private function extractSessionID($xml)
	{
		$session_id = strstr($xml, "<sessionId");
        $session_id = strstr($session_id, ">");
        $session_id = substr($session_id, 1, strpos($session_id, "<") - 1);
		return $session_id;
	} // end extractSessionID

	/**
	* extractErrorCode
	*
	* get the error code out of the XML
	*
	* @since		version 1.0
	* @param	string $xml xml to have the error code pulled from
	* @return int $session_id
	*/
	private function extractErrorCode($xml)
	{
		$session_id = strstr($xml, "<Code");
        $session_id = strstr($session_id, ">");
        $session_id = substr($session_id, 1, strpos($session_id, "<") - 1);
		return $session_id;
	} // end extractErrorCode


	/**
	* makeBytesPretty
	*
	* turns byte numbers into a more readable format with KB or MB
	*
	* @since		version 1.0
	* @param	int $bytes bytes to be worked with
	* @param	boolean $redlevel
	* @return int $size
	*/
	private function makeBytesPretty($bytes, $redlevel=false)
	{
		if($bytes<1024)
			$size = $bytes.' B';
		elseif($bytes<1024*1024)
			$size = round($bytes/1024,1).' KB';
		else
			$size = round(($bytes/1024)/1024,1).' MB';

		if($redlevel && $bytes>$redlevel)
		{
			$size = '<span style="color:red">'.$size.'</span>';
		}

		return $size;
	} // end makeBytesPretty
	
	/**
	* message
	*
	* if debug is on, show a message
	*
	* @since		version 1.0
	* @param	string $message message for debug
	*/
	protected function message($message)
	{
		if($this->debug)
		{
			echo $message;
		}
	} // end message

	/**
	* modifyAppointment
	*
	* modify appoint
	*
	* @since		version 1.0
	* @param $appt Zimbra appt to modify
	* @param	boolean $needexceptID for whether this modification requires one or not
	* @return	boolean success/failure
	*/
	public function modifyAppointment($appt,$needexceptID=false)
	{
		$exceptIdstr = '';
		if($needexceptID)
		{
			$exceptIdstr='		<exceptId
									d="'.$appt['inv'][0]['comp'][0]['s_attribute_d'][0].'" 
									tz="'.htmlentities($appt['inv'][0]['comp'][0]['s_attribute_tz'][0]).'"/>';
		}
		$soap ='
			<ModifyAppointmentRequest id="'.$appt['id'].'" comp="0" xmlns="urn:zimbraMail">
				<m>
					<inv>
						<comp 
							fb="'.$appt['inv'][0]['comp'][0]['fb'].'" 
							fba="'.$appt['inv'][0]['comp'][0]['fba'].'" 
							name="'.$appt['inv'][0]['comp'][0]['name'].'" 
							loc="'.$appt['inv'][0]['comp'][0]['loc'].'">
								<s 
									d="'.$appt['inv'][0]['comp'][0]['s_attribute_d'][0].'" 
									tz="'.htmlentities($appt['inv'][0]['comp'][0]['s_attribute_tz'][0]).'"/>
								<e 
									d="'.$appt['inv'][0]['comp'][0]['e_attribute_d'][0].'" 
									tz="'.htmlentities($appt['inv'][0]['comp'][0]['s_attribute_tz'][0]).'"/>
								'.$exceptIdstr.'
						</comp>
					</inv>
					<mp>
						<content>'.$appt['inv'][0]['comp'][0]['desc'][0].'</content>
					</mp>
				</m>
			</ModifyAppointmentRequest>';
		//echo $soap;
		//$this->debug=true;
		$response = $this->soapRequest($soap);

		if($response)
		{
			return true;
		}
		else
		{
			return false;
		}
	
		
	} // end modifyAppointment


	/**
	* createAppointmentException
	*
	* modify recurring appointment instance
	*
	* @since		version 1.0
	* @param $appt Zimbra appt to modify
	* @return	boolean success/failure
	*/
	public function CreateAppointmentException($appt)
	{
		if (preg_match('/T/',$appt['inv'][0]['comp'][0]['s_attribute_d'][0]))
		{
			$start_ts = $appt['inv'][0]['comp'][0]['s_attribute_d'][0];
			$end_ts = $appt['inv'][0]['comp'][0]['e_attribute_d'][0];
			$exceptid_ts = $appt['inv'][0]['comp'][0]['s_attribute_d'][0];
		}
		else
		{
			$start_ts = date('Ymd\THis',($appt['inv'][0]['comp'][0]['s_attribute_d'][0])/1000);
			$end_ts = date('Ymd\THis',($appt['inv'][0]['comp'][0]['e_attribute_d'][0])/1000);
			$exceptid_ts = date('Ymd\THis',($appt['inv'][0]['comp'][0]['s_attribute_d'][0])/1000);
		}
		$soap ='
			<CreateAppointmentExceptionRequest id="'.$appt['id'].'" comp="0" xmlns="urn:zimbraMail">
				<m>
					<inv>
						<comp 
							fb="'.$appt['inv'][0]['comp'][0]['fb'].'" 
							fba="'.$appt['inv'][0]['comp'][0]['fba'].'" 
							name="'.$appt['inv'][0]['comp'][0]['name'].'" 
							loc="'.$appt['inv'][0]['comp'][0]['loc'].'">
								<s 
									d="'.$start_ts.'" 
									tz="'.htmlentities($appt['inv'][0]['comp'][0]['s_attribute_tz'][0]).'"/>
								<e 
									d="'.$end_ts.'" 
									tz="'.htmlentities($appt['inv'][0]['comp'][0]['s_attribute_tz'][0]).'"/>
								<exceptId
									d="'.$exceptid_ts.'" 
									tz="'.htmlentities($appt['inv'][0]['comp'][0]['s_attribute_tz'][0]).'"/>
						</comp>

					</inv>
					<mp>
						<content>'.$appt['inv'][0]['comp'][0]['desc'][0].'</content>
					</mp>
				</m>
			</CreateAppointmentExceptionRequest>';
		//echo $soap;
		//$this->debug=true;
		$response = $this->soapRequest($soap);
		if($response)
		{
			return true;
		}
		else
		{
			return false;
		}
	
		
	} // end createAppointmentException


	/**
	* soapRequest
	*
	* make a SOAP request to Zimbra server, returns the XML
	*
	* @since		version 1.0
	* @param	string $body body of page
	* @param	boolean $header
	* @param	boolean $footer
	* @return	string $response
	*/
	protected function soapRequest($body, $header=false,$connecting=false)
	{
		$this->error = $this->error_code = null;

		if(!$connecting && !$this->_connected)
		{
			$this->error = 'soapRequest called without a connection to Zimbra server';
			throw new Exception($this->error); 
		}

		if($header==false)
		{
			$header = '<context xmlns="urn:zimbra">
	<authToken>'.$this->auth_token.'</authToken>
	<sessionId id="'.$this->session_id.'">'.$this->session_id.'</sessionId>
</context>';
		}

		$soap_message = '<soap:Envelope xmlns:soap="http://www.w3.org/2003/05/soap-envelope">
	<soap:Header>'.$header.'</soap:Header>
	<soap:Body>'.$body.'</soap:Body>
</soap:Envelope>';
		$this->message( sprintf('%s SOAP message: %s', get_class($this), PSU::xmlpp($soap_message, true)) );
		
		curl_setopt($this->_curl, CURLOPT_POSTFIELDS, $soap_message);
        
		if(!($response = curl_exec($this->_curl)))
		{
			$this->error = sprintf('%s ERROR: curl_exec - (%s) %s', get_class($this), curl_errno($this->_curl), curl_error($this->_curl));
			if( $this->debug ) echo '<hr>';
			return false;
		}
		elseif(strpos($response,'<soap:Body><soap:Fault>')!==false)
		{
			$this->error_code = $this->extractErrorCode($response);
			$this->error = sprintf('%s ERROR: %s: %s', get_class($this), $this->error_code, PSU::xmlpp($response, true));
			$this->message($this->error);
			if( $this->debug ) echo '<hr>';
			return false;
		}
		$this->message( sprintf('%s SOAP response: %s', get_class($this), PSU::xmlpp($response, true)) );

		if( $this->debug ) echo '<hr>';
		
		$this->_num_soap_calls++;
		return $response;
	} // end soapRequest

	/**
	* getNumSOAPCalls
	*
	* get the number of SOAP calls that have been made.  This is for debugging and performancing
	*
	* @since		version 1.0
	* @return int $this->_num_soap_calls
	*/
	public function getNumSOAPCalls()
	{
		return $this->_num_soap_calls;
	} // end getNumSOAPCalls

	/**
	* makeXMLTree
	*
	* turns XML into an array
	*
	* @since		version 1.0
	* @param	string $data data to be built into an array
	* @return 	array $ret
	*/
	protected function makeXMLTree($data) 
	{
		// create parser
		$parser = xml_parser_create();
		xml_parser_set_option($parser,XML_OPTION_CASE_FOLDING,0);
		xml_parser_set_option($parser,XML_OPTION_SKIP_WHITE,1);
		xml_parse_into_struct($parser,$data,$values,$tags);
		xml_parser_free($parser);

		// we store our path here
		$hash_stack = array();

		// this is our target
		$ret = array();
		foreach ($values as $key => $val) {

			switch ($val['type']) {
				case 'open':
					array_push($hash_stack, $val['tag']);
					if (isset($val['attributes']))
						$ret = $this->composeArray($ret, $hash_stack, $val['attributes']);
					else
						$ret = $this->composeArray($ret, $hash_stack);
				break;

				case 'close':
					array_pop($hash_stack);
				break;

				case 'complete':
					array_push($hash_stack, $val['tag']);
					$ret = $this->composeArray($ret, $hash_stack, $val['value']);
					array_pop($hash_stack);

					// handle attributes
					if (isset($val['attributes']))
					{
						foreach($val['attributes'] as $a_k=>$a_v)
						{
							$hash_stack[] = $val['tag'].'_attribute_'.$a_k;
							$ret = $this->composeArray($ret, $hash_stack, $a_v);
							array_pop($hash_stack);
						}
					}

				break;
			}
		}

		return $ret;
	} // end makeXMLTree

	/**
	* function used exclusively by makeXMLTree to help turn XML into an array
	*
	* @since		version 1.0
	* @param $array
	* @param $elements
	* @param $value
	* @return	array $array
	*/
	private function &composeArray($array, $elements, $value=array())
	{
		global $XML_LIST_ELEMENTS;

		// get current element
		$element = array_shift($elements);

		// does the current element refer to a list
		if(sizeof($elements) > 0)
		{
			$array[$element][sizeof($array[$element])-1] = &$this->composeArray($array[$element][sizeof($array[$element])-1], $elements, $value);
		}
		else // if (is_array($value))
		{
			$array[$element][sizeof($array[$element])] = $value;
		}

		return $array;
	} // end composeArray

	/**
	* noop
	*
	* keeps users session alive
	*
	* @since		version 1.0
	* @return	string xml response from the noop
	*/	
	public function noop()
	{
		return $this->soapRequest('<NoOpRequest xmlns="urn:zimbraMail"/>');
	}

} // end Zimbra class


// annoying sorting functions for getTasks... 
// I don't know how to make usort calls to internal OO functions
// if someone knows how, please fix this :)

/**
* zimbra_startSort
*
* sort of zimbra elements
*
* @since		version 1.0
* @param $task_a
* @param $task_b
* @return	int (($task_a['dueDate']-$task_a['dur']) < ($task_b['dueDate']-$task_b['dur'])) ? -1 : 1
*/	
function zimbra_startSort($task_a, $task_b)
{
    if (($task_a['dueDate']-$task_a['dur']) == ($task_b['dueDate']-$task_b['dur'])) {
		return ($task_a['name'] < $task_b['name']) ? -1 : 1;
    }
    return (($task_a['dueDate']-$task_a['dur']) < ($task_b['dueDate']-$task_b['dur'])) ? -1 : 1;
}
/**
* zimbra_dueSort
*
* sort by dueDate
*
* @since		version 1.0
* @param $task_a
* @param $task_b
* @return	int ($task_a['dueDate'] < $task_b['dueDate']) ? -1 : 1
*/	

function zimbra_dueSort($task_a, $task_b)
{
    if ($task_a['dueDate'] == $task_b['dueDate']) {
		return ($task_a['name'] < $task_b['name']) ? -1 : 1;
    }
    return ($task_a['dueDate'] < $task_b['dueDate']) ? -1 : 1;
}
/**
* zimbra_nameSort
*
* sort by name
*
* @since		version 1.0
* @param $task_a
* @param $task_b
* @return	int ($task_a['name'] < $task_b['name']) ? -1 : 1
*/	

function zimbra_nameSort($task_a, $task_b)
{
    if ($task_a['name'] == $task_b['name']) {
		return 0;
    }
    return ($task_a['name'] < $task_b['name']) ? -1 : 1;
}

?>
