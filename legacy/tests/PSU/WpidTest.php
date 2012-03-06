<?php

require_once('PSUTools.class.php');

class WpidTests extends PHPUnit_Framework_TestCase {
	function testWpid() {
		$this->assertTrue(  PSU::is_wpid('p0intless'), 'good wpid (default flags)' );
		$this->assertFalse( PSU::is_wpid('t0intless'), 'bad initial char (default flags)' );
		$this->assertFalse( PSU::is_wpid('p0intlss'), 'short wpid (default flags)' );
		$this->assertFalse( PSU::is_wpid('p0intlessss'), 'long wpid (default flags)' );
		$this->assertFalse( PSU::is_wpid('paintless'), 'missing number (default flags)' );

		$this->assertTrue(  PSU::is_wpid('p0intless', PSU::MATCH_WPID), 'good wpid (wpid flag)' );
		$this->assertFalse( PSU::is_wpid('t0intless', PSU::MATCH_WPID), 'bad initial char (wpid flag)' );
		$this->assertFalse( PSU::is_wpid('p0intlss', PSU::MATCH_WPID), 'short wpid (wpid flag)' );
		$this->assertFalse( PSU::is_wpid('p0intlessss', PSU::MATCH_WPID), 'long wpid (wpid flag)' );
		$this->assertFalse( PSU::is_wpid('paintless', PSU::MATCH_WPID), 'missing number (wpid flag)' );

		$this->assertTrue(  PSU::is_wpid('t0intless', PSU::MATCH_TEMPID), 'good tempid (tempid flag)' );
		$this->assertFalse( PSU::is_wpid('p0intless', PSU::MATCH_TEMPID), 'bad initial char (tempid flag)' );
		$this->assertFalse( PSU::is_wpid('t0intlss', PSU::MATCH_TEMPID), 'short wpid (tempid flag)' );
		$this->assertFalse( PSU::is_wpid('t0intlessss', PSU::MATCH_TEMPID), 'long wpid (tempid flag)' );
		$this->assertFalse( PSU::is_wpid('taintless', PSU::MATCH_TEMPID), 'missing number (tempid flag)' );

		$this->assertTrue(  PSU::is_wpid('p0intless', PSU::MATCH_BOTH), 'good wpid (both flag)' );
		$this->assertTrue(  PSU::is_wpid('t0intless', PSU::MATCH_BOTH), 'good tempid (both flag)' );
		$this->assertFalse( PSU::is_wpid('z0intless', PSU::MATCH_BOTH), 'bad wpid (both flag)' );
		$this->assertFalse( PSU::is_wpid('p0intlss', PSU::MATCH_BOTH), 'short wpid (both flag)' );
		$this->assertFalse( PSU::is_wpid('t0intlss', PSU::MATCH_BOTH), 'short tempid (both flag)' );
		$this->assertFalse( PSU::is_wpid('p0intlessss', PSU::MATCH_BOTH), 'long wpid (both flag)' );
		$this->assertFalse( PSU::is_wpid('t0intlessss', PSU::MATCH_BOTH), 'long tempid (both flag)' );
		$this->assertFalse( PSU::is_wpid('paintless', PSU::MATCH_BOTH), 'missing wpid number (both flag)' );
		$this->assertFalse( PSU::is_wpid('taintless', PSU::MATCH_BOTH), 'missing tempid number (both flag)' );
	}
}
