<?php

namespace Rave\REST;

class SiteAdmin extends \Rave\REST {
	protected static $rest_endpoint = 'https://www.getrave.com/remoteservices/rest/siteadmin/';	

	/**
	 *  Creates a new user list with the given list of member email id’s. 
	 *  If any of the members are not found in Rave, operation will return a list of rejected id’s; otherwise the rejected list will be empty. Even if there are some rejected, the list will always be created with the valid members.
	 *  @param name
	 *  @param members list
	 */
	public static function createUserList( $name, $list ) {
		// TODO: for now, this is just a stub function
	} // end createUserList

	/** 
	 * Creates a new group
	 * @param group XML payload
	 */
	public static function createGroup( $group_xml ) {
		// TODO: for now, this is just a stub function
	} // end createGroup

	/**
	 * Deletes the user list with the given listId
	 * @param listId to delete
	 */
	public static function deleteUserList( $listId ) {
		// TODO: this function is untested
		return static::delete( 'userlists/' . $listId );
	} // end deleteUserList

	/**
	 * Deletes the group with given group ID.
	 * @param group id to delete
	 */
	public static function deleteGroup( $groupId ) {
		// TODO: this function is untested
		return static::delete( 'groups/' . $groupId );
	} // end deleteGroup

	/**
	 * Finds a group given a group ID.
	 * @param id of group to find
	 */
	public static function findGroupById( $groupId ) {
		// TODO: this function is untested
		return static::get( 'groups/' . $groupId );
	} // end findGroupById

	/**
	 * Finds a group given a group name.
	 * @param name of the group to find
	 */
	public static function findGroupByName( $groupName ) {
		// TODO: this function is untested
		return static::get( 'groups/findbyname?groupName=' . $groupName );
	} // end findGroupByName

	/**
	 * Returns a list of mobile carriers supported by Rave for SMS/Text message delivery.
	 */
	public static function getAllMobileCarriers() {
		return static::get( 'mobilecarriers' );
	} // end function getMobileCarriers

	/**
	 * Returns all active groups.
	 * The list always includes “Rave Broadcast Alerts” group; the settings for this group are used for Broadcast Alerts and this group cannot be deleted.
	 */
	public static function getGroups() {
		// TODO: this function is untested
		return static::get( 'groups' );
	} // end getGroups

	/**
	 * Returns all user lists. The returned list contains the name and ID of the list.
	 * 	Note that the “ALL USERS” list seen in the Rave Management Console is a virtual rather than actual list; it will not be returned in the result set. All active subscribers in your site domain are included in this construct automatically. 
	 */
	public static function getUserLists() {
		return static::get( 'userlists' );
	} // end getUserLists

	/**
	 * Returns user list detailing all member IDs for a given List ID
	 * @param id number of the list you are getting details for
	 */
	public static function getUserListDetails( $listid ) {
		return static::get( 'userlists/' . $listid );
	} // end getUserListDetails
	/**
	 * Returns mobile carrier for a given mobile phone number, if carrier not found returns an error.
	 * @param $phone_number 10 digit phone number, no dashes, spaces, or parens
	 */
	public static function lookupCarrier( $phone_number ) {
		// TODO: data validation
		return static::get( 'mobilecarriers/phonecarrier/'.$phone_number );
	} // end function lookupCarrier

	/**
	 * Updates an existing group.
	 * 	Note: Group post permission and visibility can’t be updated after group creation. 
	 * 	@param group XML payload
	 */
	public static function updateGroup( $group_xml ) {
		// TODO: for now, this is just a stub function
	} // end updateGroup

} // end class SiteAdmin

