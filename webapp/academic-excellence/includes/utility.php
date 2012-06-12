<?php

/**
 * utility.php
 *
 * Non-display utility functions for the AE app.
 */

/**
 * initializeSession
 *
 * Set up necessary session variables.
 */
function initializeSession()
{
	if( !isset($_SESSION['errors']) ) {
		$_SESSION['errors'] = array();
	}

	if( !isset($_SESSION['messages']) ) {
		$_SESSION['messages'] = array();
	}

	$_SESSION['student'] = array();
	$_SESSION['user_type'] = null;
	$_SESSION['editing'] = true; // first time through means we're editing
	$_SESSION['ae_init'] = true;

	$_SESSION['pidm'] = $GLOBALS['BannerIDM']->getIdentifier($_SESSION['username'], 'username', 'pidm');

	if(IDMObject::authZ('permission', 'academic_excellence_admin'))
	{   
		$_SESSION['user_type'] = 'admin';
	}
	else
	{   
		$gpa = $GLOBALS['BannerStudent']->getOverallGPA($_SESSION['pidm']);
		$_SESSION['gpa'] = $gpa['r_gpa'];
		unset($gpa);

		if($_SESSION['username'] == 'ambackstrom')
		{
			$_SESSION['gpa'] = 3.5; // DEBUG: always let student through
		}

		// they're 'aestudent' only if their gpa qualifies
		if($_SESSION['gpa'] < 3.5)
		{
			return;
		}

		$_SESSION['user_type'] = 'aestudent';

		$name = $GLOBALS['BannerStudent']->getName($_SESSION['pidm']);
		$_SESSION['student']['full_name'] = sprintf('%s %s %s', $name['r_first_name'], $name['r_mi'], $name['r_last_name']);
		$_SESSION['student']['first_name'] = $name['r_first_name'];
		$_SESSION['student']['middle_name'] = $name['r_mi'];
		$_SESSION['student']['last_name'] = $name['r_last_name'];
		unset($name);

		$student = AEStudent::getStudentData($_SESSION['pidm'], $GLOBALS['TERM']);
		$_SESSION['student'] = array_merge($_SESSION['student'], $student);

		// (confirmed != -1) means that they have already submitted the form in a previous session
		if($student['confirmed'] > -1)
		{
			$_SESSION['editing'] = false;
		}
	}
}

/**
 * Academic Excellence helper API.
 */
class AEAPI {
	static $options = array();

	static function option( $key ) {
		return self::$options[$key];
	}//end option

	static function _init_options() {
		$rset = PSU::db('myplymouth')->Execute("SELECT * FROM academic_excellence_options");
		foreach($rset as $row) {
			$option = new AEOption($row);
			self::$options[ $option->key ] =& $option;
			unset($option);
		}
	}//end _init_options
}//end class AEAPI

AEAPI::_init_options();

class AEOption {
	var $ID;
	var $key;
	var $value;

	var $_orig_value;

	function __construct($row) {
		$row = (object)$row;

		if( isset($row->ID) ) {
			$this->ID = $row->ID;
		}

		$this->key = $row->key;
		$this->value = $this->_orig_value = $row->value;

		$this->changed = false;
	}//end __construct

	function save() {
		if( $this->value === $this->_orig_value ) {
			return;
		}

		$sql = "
			INSERT INTO academic_excellence_options (`key`, `value`) VALUES (?, ?)
			ON DUPLICATE KEY UPDATE `value` = ?
		";

		PSU::db('myplymouth')->Execute($sql, array(&$this->key, &$this->value, &$this->value));
	}//end save

	function value( $value ) {
		$this->value = $value;
		return $this;
	}//end value

	function __toString() {
		return $this->value;
	}//end __toString
}
