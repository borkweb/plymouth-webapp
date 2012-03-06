<?php

include 'simpletest/autorun.php';
include '/web/connect.plymouth.edu/wp-content/mu-plugins/login-hacks/link-accounts.php';
include '/web/connect.plymouth.edu/wp-includes/classes.php';

class LinkTest extends UnitTestCase
{
	function testConfirmPassword()
	{
		global $person;

		$result = sl_linkacct_checkconfirmpost( 'pw', $person, array('confirmpw' => 'testpassword') );
		$this->assertIdentical( $result, true, 'correct password' );

		$result = sl_linkacct_checkconfirmpost( 'pw', $person, array('confirmpw' => 'badpw') );
		$this->assertIsA( $result, 'WP_Error', 'bad password #1' );
	}

	function testMissingPersonFields1()
	{
		global $person;

		$post = array('confirmdob' => '1/1/1980', 'confirmssn' => '123456789');
		$result = sl_linkacct_checkconfirmpost( 'ssn', $person, $post );
		$this->assertIdentical( $result, true, 'missing fields: success' );
	}

	function testMissingPersonFields2() {
		global $person;

		unset($person->ssn);
		$post = array('confirmdob' => '1/1/1980', 'confirmssn' => '123456789');
		$result = sl_linkacct_checkconfirmpost( 'ssn', $person, $post );
		$this->assertIsA( $result, 'WP_Error', 'missing fields: ssn' );
	}

	function testMissingPersonFields3() {
		global $person;

		unset($person->foreign_ssn);
		$post = array('confirmdob' => '1/1/1980', 'confirmfssn' => '987654321');
		$result = sl_linkacct_checkconfirmpost( 'fssn', $person, $post );
		$this->assertIsA( $result, 'WP_Error', 'missing fields: fssn' );
	}

	function testMissingPersonFields4() {
		global $person;

		unset($person->certification_number);
		$post = array('confirmdob' => '1/1/1980', 'confirmcn' => 'N1234567890');
		$result = sl_linkacct_checkconfirmpost( 'cn', $person, $post );
		$this->assertIsA( $result, 'WP_Error', 'missing fields: cn' );
	}

	function testConfirmSSN1()
	{
		global $person;

		$post = array('confirmdob' => '1/1/1980', 'confirmssn' => '123456789');
		$result = sl_linkacct_checkconfirmpost( 'ssn', $person, $post );
		$this->assertIdentical( $result, true, 'ssn check: good dob, good ssn' );
	}

	function testConfirmSSN2()
	{
		global $person;

		$post = array('confirmdob' => '1/1/1980', 'confirmssn' => '123-45-6789');
		$result = sl_linkacct_checkconfirmpost( 'ssn', $person, $post );
		$this->assertIdentical( $result, true, 'ssn check: good dob, good ssn (dashes)' );
	}

	function testConfirmSSN3()
	{
		global $person;

		$post = array('confirmdob' => '1/1/1981', 'confirmssn' => '123456789');
		$result = sl_linkacct_checkconfirmpost( 'ssn', $person, $post );
		$this->assertIsA( $result, 'WP_Error', 'ssn check: bad dob, good ssn' );
	}

	function testConfirmSSN4()
	{
		global $person;

		$post = array('confirmdob' => '1/1/1980', 'confirmssn' => '12345678');
		$result = sl_linkacct_checkconfirmpost( 'ssn', $person, $post );
		$this->assertIsA( $result, 'WP_Error', 'ssn check: good dob, bad ssn' );
	}

	function testConfirmSSN5()
	{
		global $person;

		$post = array('confirmdob' => '1/1/1981', 'confirmssn' => '12345678');
		$result = sl_linkacct_checkconfirmpost( 'ssn', $person, $post );
		$this->assertIsA( $result, 'WP_Error', 'ssn check: bad dob, bad ssn' );
	}

	function testConfirmFSSN()
	{
		global $person;

		$post = array('confirmdob' => '1/1/1980', 'confirmfssn' => '987654321');
		$result = sl_linkacct_checkconfirmpost( 'fssn', $person, $post );
		$this->assertIdentical( $result, true, 'fssn check: good dob, good fssn' );

		$post = array('confirmdob' => '1/1/1980', 'confirmfssn' => '098765432');
		$result = sl_linkacct_checkconfirmpost( 'fssn', $person, $post );
		$this->assertIsA( $result, 'WP_Error', 'fssn check: good dob, bad fssn' );
	}

	function testConfirmCN()
	{
		global $person;

		$post = array('confirmdob' => '1/1/1980', 'confirmcn' => 'N1234567890');
		$result = sl_linkacct_checkconfirmpost( 'cn', $person, $post );
		$this->assertIdentical( $result, true, 'cn check: good dob, good cn' );

		$post = array('confirmdob' => '1/1/1980', 'confirmfssn' => '098765432');
		$result = sl_linkacct_checkconfirmpost( 'cn', $person, $post );
		$this->assertIsA( $result, 'WP_Error', 'cn check: good dob, bad cn' );
	}

	function setUp()
	{
		global $person;
		$person = new PSUPerson(200443);
	}
}

//
// Support stuff
//

class PSUPerson {
	function __construct($id) {
		$this->ssn = '123456789';
		$this->foreign_ssn = '987654321';
		$this->certification_number = 'N1234567890';

		$this->birth_date = strtotime('January 1 1980');
	}

	function _load_ssn() {
	}
}

function get_userdatabylogin() {
	return (object)array(
		'ID' => 1,
		'user_pass' => 'testpassword'
	);
}

function wp_check_password( $p1, $p2, $id ) {
	return $p1 == $p2;
}

function add_action() {
}
