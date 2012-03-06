<?php

/**
 * zimbraAdmin.class.php
 *
 * === Modification History ===<br/>
 * 1.0    05-Dec-2007  [zbt]  original<br/>
 *
 */

require_once('zimbra.class.php');
require_once('PSUDatabase.class.php');

/**
 * zimbraAdmin.class.php
 *
 * ZimbraAdmin API
 *
 * @version		1.0
 * @module		zimbraAdmin.class.php
 * @author		Zachary Tirrell <zbtirrell@plymouth.edu>
 * @GPL 2007, Plymouth State University, ITS
 */ 

class ZimbraAdmin extends Zimbra
{
	protected $_admin = true; // operating as an admin
	protected $_path = ':7071/service/admin/soap';
	protected $_admin_password;
	protected $_admin_username;
	
	/**
	* __construct
	*
	* constructor for ZimbraAdmin API
	*
	* @since		version 1.0
	* @access	public
	* @param	string $username username to use in the construction of object
	* @param	string $which type to construct, defaults to prod
	*/
	public function __construct($username = 'not_a_real_user', $which = 'prod')
	{
		$file = "other/zimbra" . ($which == 'prod' ? '' : '_dev');
		$credentials = PSUDatabase::connect($file, 'return');

		$this->_admin_username = $credentials['username'];
		$this->_admin_password = $credentials['password'];

		parent::__construct($username, $which);
		
		$this->_protocol = 'https://';
	}

	/**
	 * accountExists
	 *
	 * Returns true if the specified account exists.
	 *
	 * @param    string    $username
	 */
	public function accountExists($username)
	{
		try
		{
			return (bool)$this->getAccountInfo($username);
		}
		catch(Exception $e)
		{
			if( $e->getMessage() == 'account.NO_SUCH_ACCOUNT' )
			{
				// false if no account
				return false;
			}

			// otherwise, some other error
			throw $e;
		}
	}//end accountExists

	/**
	 * Factory to return a connected zimbraAdmin object.
	 */
	public static function factory( $which = 'prod' ) {
		static $instance = null;

		if( $instance === null ) {
			$instance = array();
		}

		if( !isset($instance[$which]) ) {
			$instance[$which] = new self( 'not_a_real_user', $which );
			$instance[$which]->connect();
		}

		return $instance[$which];
	}//end factory

	/**
	 * getAccountInfo
	 *
	 * Return the Zimbra ID and mail host for this account.
	 */
	function getAccountInfo($username)
	{
		$soap = <<<EOT
<GetAccountInfoRequest xmlns="urn:zimbraAdmin">
	<account by="name">{$username}@{$this->_server}</account>
</GetAccountInfoRequest>
EOT;

		$response = $this->soapRequest($soap);

		if($response)
		{
			$array = $this->makeXMLTree($response);

			$response =& $array['soap:Envelope'][0]['soap:Body'][0]['GetAccountInfoResponse'][0];

			$keys = $response['a_attribute_n'];
			$values = $response['a'];

			return array_combine($keys, $values);
		}

		throw new Exception( $this->error_code ); 
	}//end getAccountInfo

	/**
	* getAccountOptions
	*
	* get account options, admin connection required
	*
	* @since		version 1.0
	* @access	public
	* @param	string $username username of account
	* @return	mixed $array['soap:Envelope'][0]['soap:Body'][0]['GetAccountResponse'][0]['account'][0] or false
	*/
	public function getAccountOptions($username)
	{
		$soap = '<GetAccountRequest xmlns="urn:zimbraAdmin">
				  <account by="name">'.$username.'@'.$this->_server.'</account>
				</GetAccountRequest>';

		$response = $this->soapRequest($soap);
		if($response)
		{
			$array = $this->makeXMLTree($response);

			return $array['soap:Envelope'][0]['soap:Body'][0]['GetAccountResponse'][0]['account'][0];
		}
		else
		{
			return false;
		}
	}//end getAccountOptions

	/**
	* setAccountOptions
	*
	* set options for an account, admin connection required
	*
	* @since		version 1.0
	* @access	public
	* @param	int $id id of account to set options in
	* @param	array $options options to be set
	* @return	boolean
	*/
	public function setAccountOptions($id, $options)
	{
		$option_string = '';
		foreach($options as $name=>$value)
		{
			$option_string .= '<a n="'.$name.'">'.$value.'</a>';
		}
		$soap ='<ModifyAccountRequest xmlns="urn:zimbraAdmin">
					<id>'.$id.'</id>
					'.$option_string.'
				</ModifyAccountRequest>';
		$response = $this->soapRequest($soap);
		if($response)
		{
			return true;
		}
		else
		{
			return false;
		}
	} // end setAccountOptions

	/**
	* createAccount
	*
	* create an account, assuming that an admin connection is required
	*
	* @since     version 1.0
	* @access    public
	* @param     string $username username of account to be created
	* @param     array $options options to be set
	* @return    boolean
	*/
	public function createAccount($username, $options=array())
	{
		$option_string = '';
		foreach($options as $key=>$value)
		{
			$option_string .= sprintf('<a n="%s">%s</a>', $key, $value);
		}

		$soap ='<CreateAccountRequest xmlns="urn:zimbraAdmin">
					<name>'.$username.'@'.$this->_server.'</name>
					<password>'.md5(rand().$username).'</password>
					'.$option_string.'
				</CreateAccountRequest>';
		$response = $this->soapRequest($soap);

		return $response ? true : false;
	}
	
	/*
	 * search
	 *
	 * search with the given query using the gien attributes 
	 *
	 * @since		version 1.0
	 * @access	public
	 * @param	string $query query to run
	 * @param	boolean $attributes
	 * @example	<SearchDirectoryRequest [limit="..."] [offset="..."] [domain="{domain-name}"] [applyCos="{apply-cos}"] [maxResults="..."] [attrs="a1,a2,a3"] [sortBy="{sort-by}"] [sortAscending="{sort-ascending}"] [types="{type}"]> <query>...</query> </SearchDirectoryRequest>
	 * @return	mixed $array['soap:Envelope'][0]['soap:Body'][0]['SearchDirectoryResponse'][0]['account'] or false
	 */
	public function search($query, $attributes=false)
	{
		$soap ='<SearchDirectoryRequest xmlns="urn:zimbraAdmin" '.(($attributes)?' attrs="'.$attributes.'"':'').'>
					<query>'.$query.'</query>
				</SearchDirectoryRequest>';
		$response = $this->soapRequest($soap);
		if($response)
		{
			$array = $this->makeXMLTree($response);
			return $array['soap:Envelope'][0]['soap:Body'][0]['SearchDirectoryResponse'][0]['account'];
		}
		else
		{
			return false;
		}
	}
	
}
