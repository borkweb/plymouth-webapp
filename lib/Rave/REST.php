<?php

namespace Rave;

class REST {

	protected static $rest_endpoint = 'https://www.getrave.com/remoteservices/rest/';
	// XML Schema definition: http://www.getrave.com/restinfo/serviceSchema.xsd

	protected static $headers = array(
		'Content-type: application/xml'
	);

	public static $last_xml_response = '';

	/**
	 * general wrapper for handling Rave REST calls
	 *
	 */
	protected static function callFunction( $function, $method = 'get', $params = false ) {
		// we need to be able to set curl options, so intentionally not using PSU::curl

		$url = static::$rest_endpoint . $function;

		$config = \PSU\Config\Factory::get_config();
		$username = $config->get( 'rave', 'rest_user' );
		$password = $config->get( 'rave', 'rest_passwd' );

		$options=array(
			CURLOPT_USERPWD => ( $username . ':' . $password ),
			CURLOPT_HEADER => false,
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_URL => $url,
		);

		switch( $method ){
			case 'head':
				$options[CURLOPT_CUSTOMREQUEST] = 'HEAD';
				break;
			case 'post':
				$options[CURLOPT_CUSTOMREQUEST] = 'POST';
				// explicitly setting the CURLOPT_POST is neither positive, nor negative, with CUSTOMREQUEST set, so... not doing it
				//$options[CURLOPT_POST] = true;
				break;
			case 'put':
				// DO NOT use the following curlopt, it will cause a "premature end of file error"
				//$options[CURLOPT_PUT] = true;
				$options[CURLOPT_CUSTOMREQUEST] = 'PUT';
				break;
			case 'delete':
				$options[CURLOPT_CUSTOMREQUEST] = 'DELETE';
				break;
		} // end switch 
		
		$headers = static::$headers;
		$headers[] = 'Content-Length: ' . strlen( $params );
		$options[CURLOPT_HTTPHEADER] = $headers;

		if( $params ) {
			$options[CURLOPT_POSTFIELDS] = $params;
		} // end if

		$ch = curl_init( $url );
		curl_setopt_array( $ch, $options);
		static::$last_xml_response = curl_exec($ch);
		curl_close($ch);

		$obj = simplexml_load_string( static::$last_xml_response );
		if( $obj->errorMessage ) {
			Error::handle($obj->errorMessage);
		} // end if

		// if the call is a delete and we don't get anything back, it is successful
		if( $method == 'delete' ) {
			return true;
		}//end if
		
		return $obj;
	} // end callFunction

	/**
	 * Confirms a pending primary phone
	 * @param email
	 * @param confirmationCode
	 */
	public static function confirmPhone( $email, $confirmationCode ) {
		return static::post( 'user/'.urlencode($email).'/confirmphone', $confirmationCode );
	} // end confirmPhone

	/**
	 * wrapper function for delete functions
	 */
	protected static function delete( $function, $params = false ) {
		return static::callFunction( $function, 'delete', $params );
	} // end put

	/** 
	 * Deletes a user
	 * @param email of user to delete
	 */
	public static function deleteUser( $email ) {
		$res = static::delete( 'user/'.urlencode( $email ) );
		return $res;
	} // end deleteUser

	/** 
	 * Looks up user by given Email, if not found error message will be returned.
	 * @param email address to do the lookup with
	 */
	public static function findUserByEmail( $email ) {
		return static::get( 'user/'.urlencode( $email ) );
	} // end findUserByEmail 

	/** 
	 * Finds user by given sisId, if not found error message will be returned.
	 * @param sisId to do the lookup on 
	 */
	public static function findUserBySisId( $sisId ) {
		// note: documentation is wrong on this calling path, it is not unlikely that this might change to match docs someday...
		// we have included the documented way (July 2011) in the comment
		//$user = static::get( 'user/findbysisid/'.$sisId );
		$user = static::get( 'user/findbysisid?sisid='.$sisId );

		return $user;
	} // end findUserBySisId 

	/** 
	 * wrapper function for get functions
	 */
	protected static function get( $function, $params = false ) {
		return static::callFunction( $function, 'get', $params );
	} // end get

	/**
	 * Returns list of the group membership preferences to which a user subscribed.
	 * @param email
	 */
	public static function getSubscribedGroupsForUser( $email ) {
		return static::get('user/'.urlencode( $email ) . '/groups');
	} // end getSubscribedGroupsForUser

	/**
	 * Lists all UserLists to which a user is subscribed.
	 * @param email
	 */
	public static function getSubscribedListsForUser( $email ) {
		return static::get( 'user/' . urlencode( $email ) . '/userlists' );
	} // end getSubscribedListsForUser

	/** 
	 * wrapper function for head functions
	 */
	protected static function head( $function, $params = false ) {
		return static::callFunction( $function, 'head', $params );
	} // end head

	/**
	 * wrapper function for post functions
	 */
	protected static function post( $function, $params = false ) {
		return static::callFunction( $function, 'post', $params );
	} // end post

	/**
	 * wrapper function for put functions
	 */
	protected static function put( $function, $params = false ) {
		return static::callFunction( $function, 'put', $params );
	} // end put
	
	/**
	 *  Register a new User.
	 *  @param user_xml is a user XML payload with at least firstName, lastName, and email
	 */
	public static function registerUser( $user_xml ) {
		return static::post( 'user', $user_xml );
	} // end registerUser

	/**
	 * Sends confirmation code to the user primary phone if there is a pending 
	 * primary phone for the given user, otherwise error message will be returned.
	 * @param $email the email of the person to send the confirmation code to
	 */
	public static function sendConfCode( $email ) {
		return static::post( 'user/'.urlencode($email).'/sendconfcode' );
	} // end sendConfCode
	
	/**
	 * Subscribe user to a group with given messaging preferences.
	 * @param email
	 * @param group_xml is the XML payload for groups
	 */
	public static function subscribeToGroup( $email , $group_xml ) {	
		return static::post( 'user/' . urlencode( $email ) . '/groups', $group_xml );
	} // end subscribeToGroup

	/**
	 * Subscribe the user to a user list.
	 * @param email
	 * @param listid
	 */
	public static function subscribeToList( $email , $listid ) {
		return static::post( 'user/' . urlencode($email) . '/userlists/' . $listid );
	} // end subscribeToList

	/**
	 * Unsubscribe user from group.
	 * @param email
	 * @param groupid
	 */
	public static function unsubscribeToGroup( $email , $groupid ) {	
		return static::delete( 'user/' . urlencode( $email ) . '/groups/'. $groupid );
	} // end unsubscribeToGroup

	/**
	 * Unsubscribe the user from a Broadcast alert user list.
	 * @param email
	 * @param listid
	 */
	public static function unsubscribeToList( $email , $listid ) {
		return static::delete( 'user/' . urlencode($email) . '/userlists/' . $listid );
	} // end unsubscribeToList

	/**
	 * Update existing group membership preferences.
	 * @param email
	 * @param group_xml is the XML payload for groups
	 */
	public static function updateGroupSubscription( $email , $group_xml ) {	
		return static::put( 'user/' . urlencode( $email ) . '/groups', $group_xml );
	} // end updateGroupSubscription

	/**
	 * Updates user, user is looked up by email for update. 
	 * 	If users primary email changes you need to delete and reregister the user. But if you use sisId, you can update email by sisId using update primaryEmail method and issue update after that.
	 * @param user_xml is the XML payload
	 */
	public static function updateUser( $user_xml ) {
		return static::put( 'user', $user_xml );
	} // end updateUser

	/**
	 * Update primary email given a sisId
	 * @param sisId for lookup
	 * @param newEmail to change it to
	 */
	public static function updatePrimaryEmail( $sisId, $newEmail ) {
		// TODO: test this once the sisId stuff is working
		$result = static::post( 'user/updateprimaryemail?sisid='.$sisId.'&email='.$newEmail );

		return $result;
	} // end updatePrimaryEmail

	/** 
	 * Update user password.
	 * @param email
	 * @param password
	 */
	public function updatePassword( $email, $password ) {
		$result = static::post( 'user/'.urlencode( $email ) . '/resetpassword', $password );
		return $result;
	} // end updatePassword
	

} // end class REST
