<?php

require_once 'PSUTools.class.php';
require_once 'CommonAppRecord.class.php';

class CommonApp
{

	/**
	 * Array of errors that happen during processing.
	 */
	var $errors;

	public function __construct($file = null)
	{
		if($file) 
		{
			$this->retrieveFile($file);
			$this->parseFeed();
		}//end if

		$this->errors = array();
	}//end constructor

	/**
	 * Return number of imported records.
	 */
	public static function countImportedRecords( $where = '1=1' ) {
		$sql = "SELECT COUNT(1) FROM common_app_feed WHERE $where";
		return (int)PSU::db('banner')->GetOne($sql);
	}//end countImportedRecords

	/**
	 * Return all records in the Common App table.
	 * @param $where \b string the where clause (will not be escaped)
	 */
	public static function importedRecords( $where = null, $sort = null, $order = 'ASC', $offset = -1, $limit = -1) {
		if( $sort == null ) {
			$sort = 'feed_date';
		}

		if( $where == null ) {
			$where = "1=1";
		}

		$order = strtoupper($order);
		if( $order != 'DESC' && $order != 'ASC' ) {
			$order = 'DESC';
		}

		$sql = "SELECT * FROM common_app_feed f WHERE $where ORDER BY $sort $order";

		$rset = PSU::db('banner')->SelectLimit($sql, $limit, $offset);
		return $rset;
	}//end importedRecords

 /**
  * Import data into Banner the data for all records that 
  * have not yet been loaded.
  */
	public function import()
	{
		$sql = "SELECT *
		          FROM psu.common_app_feed
		         WHERE load_date IS NULL";

		if($results = PSU::db('banner')->Execute($sql))
		{
			foreach($results as $row)
			{
				$application = new CommonAppRecord($row['id'], $row['application_xml']);
				$success = $application->import();

				// if we had a failure, no sense continuing on
				if( !$success ) {
					$this->errors[] = sprintf("Error in row %d", $row['id']);
				}
			}//end foreach
		}//end if

		return true;
	}//end import

 /**
  * Parses the CommonApp feed file into an array
  */
  public function parseFeed()
  {
  	if($this->file === false) throw new CommonAppException(CommonAppException::INVALID_FILE);
  
		//parse xml
		$dom = new DOMDocument();
		$dom->preserveWhiteSpace = false;
		$dom->loadXML($this->file);
		$applications = $dom->getElementsByTagName('applications')->item(0);
		
		if($applications->hasChildNodes())
		{
			foreach($applications->childNodes as $app_node)
			{
				$app_client_id = null;
				$app_term_id = null;
				$app_xml = '';

				$owner_document = $app_node->ownerDocument;
				
				foreach($app_node->childNodes as $app_element)
				{
					switch($app_element->nodeName)
					{
						case 'commonapplicantClientID':
							$app_client_id = $app_element->nodeValue;
						break;
						case 'termID':
							$app_term_id = $app_element->nodeValue;
						break;
					}//end switch
					
					$app_xml .= $owner_document->saveXML($app_element);
				}//end foreach
				
				if(!$app_client_id) 
					throw new CommonAppException(CommonAppException::NULL_CLIENT_ID, '(file = '.$this->file_name.')');
					
				if(!$app_term_id) 
					throw new CommonAppException(CommonAppException::NULL_TERM_ID, '(client_id = '. $client_id .' :: file = ' . $this->file_name .')');
				
				$this->storeApplcationXML($app_client_id, $app_term_id, '<application>'.$app_xml.'</application>');
			}//end foreach
		}//end if
  }//end parseFeed
  
 /**
  * Stores application XML into database
  */
  public function storeApplcationXML($client_id, $term_id, $xml)
  {
		$params = array(
		'client_id' => $client_id
  	);

  	$sql = "SELECT 1 FROM psu.common_app_feed 
  	         WHERE common_app_client_id = :client_id";

		if(PSU::db('banner')->GetOne($sql, $params))
	  {
			$sql = "UPDATE psu.common_app_feed 
			           SET application_xml = :contents, 
										 file_name = :file_name,
										 feed_date = sysdate
			         WHERE common_app_client_id = :client_id";
			$content_params = $params;
			$content_params['file_name'] = $this->file_name;
			$content_params['contents'] = $xml;
			
			if(PSU::db('banner')->Execute($sql, $content_params))
			{
				return true;
			}//end if
			else
			{
				throw new CommonAppException(CommonAppException::APPLICATION_INSERT_XML_CLOB_FAIL, '(client_id = '. $client_id .' :: file = ' . $this->file_name .')');
			}//end if
		}//end if
		else
		{
			$params = array(
			'client_id' => $client_id, 
			'term_id' => $term_id, 
			'contents' => $xml,
			'file_name' => $this->file_name
			);

			$sql = "INSERT INTO psu.common_app_feed (
								common_app_client_id,
								application_xml,
								file_name,
								term_id,
								feed_date
							) VALUES (
								:client_id,
								:contents,
								:file_name,
								:term_id,
								sysdate
							)";
			if(PSU::db('banner')->Execute($sql, $params))
			{
				return true;
			}
			else
			{
				throw new CommonAppException(CommonAppException::APPLICATION_INSERT_XML_FAIL, '(client_id = '. $client_id .' :: file = ' . $this->file_name .')');
			}//end else
		}
		
  }//end storeApplicationXML

 /**
	* Retrieves a file from Common App servers
	*
	* @param file
	*/
	public function retrieveFile($file = null)
	{
		if($file)
		{
			$this->file_name = basename($file);
			
			return ($this->file = file_get_contents($file)) ? true : false;
		}//end if
		
		//
		// @todo sftp retrieve feed file from server
		//
		
		if(!$this->file === false) throw new CommonAppException(CommonAppException::INVALID_FILE);

		return $this->file ? true : false;
	}//end retrieveFile
}//end class CommonApp

class CommonAppException extends PSUException
{
	const INVALID_FILE = 1;
	const APPLICATION_INSERT_XML_FAIL = 2;
	const NULL_CLIENT_ID = 3;
	const NULL_TERM_ID = 4;
	const APPLICATION_INSERT_XML_CLOB_FAIL = 5;

	private static $_msgs = array(
		self::INVALID_FILE => 'Invalid File.',
		self::APPLICATION_INSERT_XML_FAIL => 'common_app_feed record failed to insert',
		self::NULL_CLIENT_ID => 'Null Client ID in XML',
		self::NULL_TERM_ID => 'Null Term ID in XML',
		self::APPLICATION_INSERT_XML_CLOB_FAIL => 'common_app_feed XML contents failed to insert'
	);

	/**
	 * Wrapper construct so PSUException gets our message array.
	 */
	function __construct($code, $append=null)
	{
		parent::__construct($code, $append, self::$_msgs);
	}
}//end class CommonAppException
