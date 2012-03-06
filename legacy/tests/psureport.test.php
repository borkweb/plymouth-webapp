<?php

require_once('simpletest/autorun.php');
require_once 'PSUTools.class.php';
require_once('/web/pscpages/webapp/analytics/includes/PSUGraph.class.php');
require_once('/web/pscpages/webapp/analytics/includes/PSUSQL.class.php');
require_once('/web/pscpages/webapp/analytics/includes/PSUReport.class.php');

class AddRange extends UnitTestCase
{
	public $report;
	public static $bind = 1;

	function testAddRangeNotBetween()
	{
		$this->assertEqual($this->report->addRangeWhere('field1', '>'), 'AND field1 > :range_bind_'.(self::$bind++), 'addRangeWhere(field1, >)'); 
		$this->assertEqual($this->report->addRangeWhere('field1', '>', 5), 'AND field1 > :range_bind_'.(self::$bind++), 'addRangeWhere(field1, >, 5)'); 
		$this->assertEqual($this->report->addRangeWhere('field1', '>', null, 'field2'), 'AND field1 > field2', 'addRangeWhere(field1, >, null, field2)'); 
	}

	function testAddRangeBetween()
	{
		$where = "AND %s BETWEEN %s AND %s";

		$p1 = 'field1';
		$p2 = $this->bind();
		$p3 = $this->bind();
		$this->assertEqual($this->report->addRangeWhere($p1, 'between', null, null, null), sprintf($where, $p1, $p2, $p3), "addRangeWhere({$p1}, between, null, null, null)"); 

		$p1 = 'field1';
		$p2 = $this->bind();
		$p3 = $this->bind();
		$this->assertEqual($this->report->addRangeWhere($p1, 'between', 1, null, null), sprintf($where, $p1, $p2, $p3), "addRangeWhere({$p1}, between, 1, null, null)"); 

		$p1 = 'field1';
		$p2 = $this->bind();
		$p3 = $this->bind();
		$this->assertEqual($this->report->addRangeWhere($p1, 'between', 1, null, 3), sprintf($where, $p1, $p2, $p3), "addRangeWhere({$p1}, between, 1, null, 3)"); 

		$p1 = 'field1';
		$p2 = $this->bind();
		$p3 = $this->bind();
		$this->assertEqual($this->report->addRangeWhere($p1, 'between', null, null, 3), sprintf($where, $p1, $p2, $p3), "addRangeWhere({$p1}, between, null, null, 3)"); 

		$p1 = 'field1';
		$p2 = $this->bind();
		$p3 = 'field2';
		$this->assertEqual($this->report->addRangeWhere($p1, 'between', 1, $p3, null), sprintf($where, $p1, $p2, $p3), "addRangeWhere({$p1}, between, 1, {$p3}, null)"); 

		$p1 = 'field1';
		$p2 = $this->bind();
		$p3 = 'field2';
		$this->assertEqual($this->report->addRangeWhere($p1, 'between', null, $p3, null), sprintf($where, $p1, $p2, $p3), "addRangeWhere({$p1}, between, null, {$p3}, null)"); 

		$p1 = 'field1';
		$p2 = $this->bind();
		$p3 = 'field2';
		$this->assertEqual($this->report->addRangeWhere($p1, 'between', 1, $p3, 5), sprintf($where, $p1, $p2, $p3), "addRangeWhere({$p1}, between, 1, {$p3}, 5)"); 

		$p1 = 'field1';
		$p2 = 'field2';
		$p3 = $this->bind();
		$this->assertEqual($this->report->addRangeWhere($p1, 'between', null, $p2, 3), sprintf($where, $p1, $p2, $p3), "addRangeWhere({$p1}, between, null, {$p2}, 3)"); 

		$p1 = $this->bind();
		$p2 = $this->bind();
		$p3 = $this->bind();
		$this->assertEqual($this->report->addRangeWhere(null, 'between', 1, null, null), sprintf($where, $p1, $p2, $p3), "addRangeWhere(null, between, 1, null, null)"); 

		$p1 = $this->bind();
		$p2 = $this->bind();
		$p3 = $this->bind();
		$this->assertEqual($this->report->addRangeWhere(null, 'between', null, null, null), sprintf($where, $p1, $p2, $p3), "addRangeWhere(null, between, null, null, null)"); 
		
		/** we don't care about these combos **
		$p1 = $this->bind();
		$p2 = 'field2';
		$p3 = $this->bind();
		$this->assertEqual($this->report->addRangeWhere(null, 'between', null, null, null), sprintf($where, $p1, $p2, $p3), "addRangeWhere(null, between, null, field2, null)"); 
		
		$p2 = $this->bind();
		$p1 = $this->bind();
		$p3 = $this->bind();
		$this->assertEqual($this->report->addRangeWhere(null, 'between', null, null, 3), sprintf($where, $p1, $p2, $p3), "addRangeWhere(null, between, null, null, 3)"); 
		 */
	}//end testaddRangeWhereBetween

	function bind()
	{
		return ':range_bind_'.(self::$bind++);
	}//end bind

	function setUp()
	{
		$sql = "SELECT sysdate FROM dual";
		$this->report = new PSUReport('test', $sql);
	}

	function tearDown()
	{
		unset($this->report);
	}
}
