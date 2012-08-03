<?php
/**
 * This class - whose methods should be called statically - handles
 * authorizations for channels using their slugs as method names
 */
class ChannelAuthZ {
	static $person;
	static $grants = array();

	public static function advising() {
		return !IDMObject::authZ( 'banner', 'student_grad' ) && !IDMObject::authZ( 'banner', 'student_expected' );
	}//end advising

	public static function bursar_information() {
		return ( IDMObject::authZ( 'banner', 'student_account_active' ) || IDMObject::authZ('banner', 'student_active') ) && IDMObject::authZ( 'banner', 'ug_app_accept' );
	}//end call_log
	
	public static function call_log() {
		return IDMObject::authZ( 'role', 'calllog' ) || IDMObject::authZ( 'department', 'Information Technology Svcs.' );
	}//end call_log

	/**
	 * @todo Family access should be limited to family of a ug_app
	 */
	public static function financial_aid_information() {
		//If this is a grantee user, see if the granter is still a ug_app
		if( self::$grants['finaid'] ) {
			foreach( self::$person->myrelationships->relationships as $identifier => $value ) {
				$granter = new PSUPerson( $identifier );
				if( array_key_exists( 'ug_app', $granter->banner_roles ) ) {
					return true;
				}//end if
			}//end foreach
		}//end if
		return IDMObject::authZ( 'banner', 'ug_app' ) || IDMObject::authZ('role', 'finaid');
	}//end financial_aid_information

	public static function important_phone_numbers() {
		return !self::$person->pidm || self::$person->myrelationships;
	}//end important_phone_numbers

	public static function myfinances() {
		return IDMObject::authZ( 'banner', 'student_account_active' ) 
						|| IDMObject::authZ('banner', 'student_active') 
						|| IDMObject::authZ( 'banner', 'student_former' ) 
						|| IDMObject::authZ( 'banner', 'ug_app_accept' ) 
						|| self::$grants['bill_view'] 
						|| self::$grants['finaid'] 
						|| self::$grants['flexcash'] 
						|| IDMObject::authZ('role', 'finaid') 
						|| IDMObject::authZ('role', 'bursar');
	}//end myfinances

	public static function myhousing() {
		return IDMObject::authZ( 'banner', 'student_account_active' ) || IDMObject::authZ('banner', 'student_active') || self::$grants['housing_view'];
	}//end myhousing

	public static function off_campus_email() {
		return !(IDMObject::authZ('banner', 'personal_email_collected') || substr($person->wp_email, -12) != 'plymouth.edu' || !$person->wp_email_alt);
	}//end off_campus_email

	public static function orientation() {
		return IDMObject::authZ( 'banner', 'student_account_active' ) && IDMObject::authZ( 'banner', 'ug_app_accept' );
	}//end orientation

	public static function visit_plymouth_state() {
		return IDMObject::authZ( 'banner', 'ug_app' ) && !IDMObject::authZ( 'banner', 'ug_app_denied' );
	}//end visit_plymouth_state

	public static function _has_authz($slug) {
		$slug = str_replace('-', '_', $slug);
		return method_exists( 'ChannelAuthZ', $slug ); 
	}

	/**
	 * Returns whether or not the user is authorized to 
	 * view the channel
	 */
	public static function _authz($slug) {
		if( !self::$person ) {
			self::$person = new PSUPerson( $GLOBALS['identifier'] );
			self::$person->_load_myrelationship_grants();
			self::$grants = self::$person->myrelationship_grants;
		}//end if

		$slug = str_replace('-', '_', $slug);
			
		if( method_exists( 'ChannelAuthZ', $slug )) {
			return call_user_func( 'ChannelAuthZ::' . $slug );
		}
		return true; //default to have privs for a given channel
	}//end _authz
}//end class ChannelAuthZ
