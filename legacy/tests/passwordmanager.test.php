<?php

require_once('simpletest/autorun.php');
require_once('PasswordManager.class.php');

/**
 * Test suite for PasswordManager.
 * @author Adam Backstrom <ambackstrom@plymouth.edu>
 */
class PasswordFormatTest extends UnitTestCase {
	const disallowedChars = '@:;$';
	const knownGoodPassword = 'ABcd1234';
	
	public function testDisallowedCharacters() {
		// a good password
		$this->assertTrue( PasswordManager::validPassword( self::knownGoodPassword ) );

		// bad characters
		$disallowed = str_split(self::disallowedChars, 1);
		foreach($disallowed as $char)
		{
			$this->assertFalse( PasswordManager::validPassword(self::knownGoodPassword . $char), 'bad character: ' . $char );
		}

		$this->assertFalse( PasswordManager::validPassword( substr(self::knownGoodPassword, 0, -1) ), 'short password' );

		// no uppercase
		$this->assertFalse( PasswordManager::validPassword( strtolower(self::knownGoodPassword) ), 'no uppercase' );
	}

	public function testOracle() {
		$this->assertTrue( PasswordManager::validateOracleUsername( 'ambackstrom' ) );
		$this->assertTrue( PasswordManager::validateOracleUsername( 'j_thibeault' ) );
		$this->assertTrue( PasswordManager::validateOracleUsername( 'j_thibeault1' ) );

		$this->assertFalse( PasswordManager::validateOracleUsername( '_thibeault1' ) );
		$this->assertFalse( PasswordManager::validateOracleUsername( '1thibeault1' ) );

		$password = '';
		for( $i = 0; $i < 128; $i++ ) {
			if( $i != 34 ) {
				$password .= chr($i);
			}
		}
		$this->assertTrue( PasswordManager::validateOraclePassword( $password ) );
		$this->assertFalse( PasswordManager::validateOraclePassword( $password . '"' ) );
	}
}
