<?php

require_once 'PSUTools.class.php';

/**
 * @backupGlobals disabled
 */
class IncludeTest extends PHPUnit_Framework_TestCase
{
	public $safe_include_dir = '/tmp';
	public $safe_include_basefile = 'includetest_dummyfile';
	public $safe_include_file; // basefile + count

	public $includecount = 0;

	function testFileNotFound()
	{
		try
		{
			PSU::safe_include($this->safe_include_dir, 'includetest_notafile', PSU::I_INCLUDE);
		}
		catch(PSUToolsException $e)
		{
			if($e->GetCode() === PSUToolsException::FILE_NOT_FOUND)
			{
				return;
			}
		}

		return $this->fail();
	}

	function testCanIncludeKnownSafe()
	{
		$v = PSU::safe_include($this->safe_include_dir, $this->safe_include_file, PSU::I_INCLUDE);
		$this->assertSame($v, 1337);
		$this->assertSame($GLOBALS['dummyfile_was_included'], 1);

		$v = PSU::safe_include($this->safe_include_dir, $this->safe_include_file, PSU::I_INCLUDE);
		$this->assertSame($v, 1337);
		$this->assertSame($GLOBALS['dummyfile_was_included'], 2);
	}

	function testAbsoluteFileFails()
	{
		$this->setExpectedException('PSUToolsException');
		PSU::safe_include($this->safe_include_dir, '/etc/passwd');
	}

	function testParentBackToSafeDir()
	{
		$v = PSU::safe_include($this->safe_include_dir, '../tmp/' . $this->safe_include_file);
		$this->assertSame($v, 1337);
		$this->assertSame($GLOBALS['dummyfile_was_included'], 1);
	}

	function testRelativeFileOutsideFails()
	{
		$this->setExpectedException('PSUToolsException');
		PSU::safe_include($this->safe_include_dir, '../etc/passwd');
	}

	function testCanRequireOnceKnownSafe()
	{
		$v = PSU::safe_include($this->safe_include_dir, $this->safe_include_file, PSU::I_REQUIRE_ONCE);
		$this->assertSame($v, 1337);
		$this->assertSame($GLOBALS['dummyfile_was_included'], 1);

		$v = PSU::safe_include($this->safe_include_dir, $this->safe_include_file, PSU::I_REQUIRE_ONCE);
		$this->assertSame($v, true);
		$this->assertSame($GLOBALS['dummyfile_was_included'], 1);
	}

	function testCanRequireKnownSafe()
	{
		$v = PSU::safe_include($this->safe_include_dir, $this->safe_include_file, PSU::I_REQUIRE);
		$this->assertSame($v, 1337);
		$this->assertSame($GLOBALS['dummyfile_was_included'], 1);

		$v = PSU::safe_include($this->safe_include_dir, $this->safe_include_file, PSU::I_REQUIRE);
		$this->assertSame($v, 1337);
		$this->assertSame($GLOBALS['dummyfile_was_included'], 2);
	}

	function testCanIncludeOnceKnownSafe()
	{
		$v = PSU::safe_include($this->safe_include_dir, $this->safe_include_file, PSU::I_INCLUDE_ONCE);
		$this->assertSame($v, 1337);
		$this->assertSame($GLOBALS['dummyfile_was_included'], 1);

		$v = PSU::safe_include($this->safe_include_dir, $this->safe_include_file, PSU::I_INCLUDE_ONCE);
		$this->assertSame($v, true);
		$this->assertSame($GLOBALS['dummyfile_was_included'], 1);
	}

	protected function setUp()
	{
		$this->safe_include_file = $this->safe_include_basefile . microtime(true) . uniqid();

		@unlink($this->safe_include_dir . '/dummyfile_notafile');

		$GLOBALS['dummyfile_was_included'] = 0;
		$f = $this->safe_include_dir . '/' . $this->safe_include_file;
		@unlink($f);

		$f = fopen($f, 'w');

		$opentag = '<'.'?php';

		fwrite($f, <<<EOF
$opentag
\$GLOBALS['dummyfile_was_included']++;
return 1337;
EOF
);
	}

	function tearDown()
	{
		$f = $this->safe_include_dir . '/' . $this->safe_include_file;
		@unlink($f);
	}
}
