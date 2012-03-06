<?php

require_once 'autoload.php';

class PSU_Model_FormFileTest extends PHPUnit_Framework_TestCase {
	function testHasFilename() {
		$pathgenerator = $this->getMock( 'PSU_Model_FormFile_PathGenerator' );
		$pathgenerator->expects( $this->any() )
			->method('get_path')
			->will( $this->returnValue( 'subdir/filename.txt' ) );

		$manager = new PSU_Model_FormFile_FileManager( $pathgenerator );
		$manager->base_url = 'http://example.com/downloads';
		$manager->upload_dir = '/path/to/uploads';

		$field = new PSU_Model_FormFile;
		$field->filemanager( $manager );

		$this->assertEquals( '/path/to/uploads/subdir/filename.txt', $field->filename(), 'full path' );
		$this->assertEquals( 'subdir/filename.txt', $field->filename( FormFile_FileManager::PATH_PARTIAL ), 'partial path' );
		$this->assertEquals( 'filename.txt', $field->filename( FormFile_FileManager::PATH_FILENAME ), 'filename' );

		$this->assertEquals( 'http://example.com/downloads/subdir/filename.txt', $field->url() );
	}

	function testNoFilename() {
		$pathgenerator = $this->getMock( 'PSU_Model_FormFile_PathGenerator' );
		$pathgenerator->expects( $this->any() )
			->method('get_path')
			->will( $this->returnValue( null ) );

		$manager = new PSU_Model_FormFile_FileManager( $pathgenerator );
		$manager->base_url = 'http://example.com/downloads';
		$manager->upload_dir = '/path/to/uploads';

		$field = new PSU_Model_FormFile;
		$field->filemanager( $manager );

		$this->assertEquals( null, $field->filename(), 'full path' );
		$this->assertEquals( null, $field->filename( PSU_Model_FormFile_FileManager::PATH_PARTIAL ), 'partial path' );
		$this->assertEquals( null, $field->filename( PSU_Model_FormFile_FileManager::PATH_FILENAME ), 'filename' );

		$this->assertEquals( null, $field->url() );
	}
}
