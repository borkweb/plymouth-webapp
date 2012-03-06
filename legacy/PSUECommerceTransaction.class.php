<?php

/**
* PSUECommerceTransaction.class.php
*
* Class based code to handle PSU ECommerce 
*
* @since			version 2.0.0
* @access			public
* @author			Nathan Porter <nrporter@plymouth.edu>, Matthew Batchelder <mtbatchelder@plymouth.edu>
* @copyright 2008, Plymouth State University, ITS
* @package    ECommerce
*/

require_once 'autoload.php';

class PSUECommerceTransaction extends PSUECommerce
{
	var $prod;

	/**
	* generateSQL
	* 
	* generate oracle insert statements
	* 
	* @since      version 2.0.0
	* @access     public
	*/
	function generateSQL($table, $array)
	{
		$sql = "INSERT INTO ".$table." (";
		foreach($array as $key => $data)
		{
			$sql_fields .= $key.', ';
			$sql_values .= $data.', ';
		}//end foreach	
		
		$sql .= trim($sql_fields,', ').') VALUES ('.trim($sql_values, ', ').')';
		
		return $sql;
	}//end generateSQL

	/**
	* getPendingFiles
	* 
	* retrieves the pending files from ecommerce_pending_eod
	* 
	* @since      version 2.0.0
	* @access     public
	*/
	function getPendingFiles()
	{
		$data = array();
		
		$sql = "SELECT * FROM ecommerce_pending_eod WHERE file_name not like 'psc_%'";
		if($results = $this->db->Execute($sql))
		{
			while($row = $results->FetchRow())
			{
				$data[$row['file_name']] = $row['contents'];
			}//end while
		}//end if
		
		return $data;
	}//end getPendingFiles

	/**
	* parsePendingEOD
	* 
	* parses out pending EOD Files
	* 
	* @since      version 2.0.0
	* @access     public
	*/
	function parsePendingEOD()
	{
		//retrieve pending files
		$files = $this->getPendingFiles();
		
		$fields = array();
		
		//get field information for ecommerce_transaction
		$results = $this->db->Execute("SELECT * FROM ecommerce_transaction WHERE 1=0");
		foreach($results->_fieldobjs as $field)
		{
			$fields['transaction'][$field->name] = array(
				'name' => $field->name,
				'max_length' => $field->max_length,
				'type' => $field->type
			);
		}//end foreach
		
		//loop over pending EOD files
		foreach($files as $file_name => $file)
		{
			//parse eod xml
			$dom = new DOMDocument();
			$dom->preserveWhiteSpace = false;
			$dom->loadXML($file);
			
			//find the transactionNotification element
			$eod = $dom->getElementsByTagName('transactionNotification')->item(0);
			//set up eod data
			$eod_data = array(
				'fileId' =>                 $this->db->qstr($eod->getAttribute('fileId')),
				'timeStamp' =>              $this->formatDate($eod->getAttribute('timeStamp')),
				'fileSource' =>             $this->db->qstr($eod->getAttribute('fileSource')),
				'sourceDescription' =>      $this->db->qstr($eod->getAttribute('sourceDestination')),
				'fileDestination' =>        $this->db->qstr($eod->getAttribute('fileDestination')),
				'destinationDescription' => $this->db->qstr($eod->getAttribute('destinationDescription')),
				'file_name' => $this->db->qstr($file_name),
				'activity_date' => 'sysdate'
			);

			//insert pending eod into eod table
			if(!$this->db->Execute($this->generateSQL('ecommerce_eod', $eod_data)))
			{
				throw new PSUECommerceException(PSUECommerceException::INSERTING_EOD, ': (fileId: '.$eod_data['fileId'].') - '.$this->db->ErrorMsg()."\n");
			}//end if
			
			//update clob
			if(!$this->db->Execute("UPDATE ecommerce_eod e SET e.contents = (SELECT p.contents FROM ecommerce_pending_eod p WHERE p.file_name = e.file_name) WHERE e.file_name = ".$eod_data['file_name']))
			{
				throw new PSUECommerceException(PSUECommerceException::INSERTING_EOD_CLOB, ': (fileId: '.$eod_data['fileId'].') - '.$this->db->ErrorMsg()."\n");
			}//end if
			
			//are there transactions in the eod?
			if($eod->hasChildNodes())
			{
				//loop over children
				for($i = 0; $i < $eod->childNodes->length; $i++)
				{
					$transaction = $eod->childNodes->item($i);
					$trans_data = array();
					if($transaction->attributes->getNamedItem('originalTransactionId')->nodeValue)
					{
						$trans_data['originalTransactionId'] = $transaction->attributes->getNamedItem('originalTransactionId')->nodeValue;
					}//end if
					$trans_data['transactionType'] = $transaction->attributes->getNamedItem('transactionType')->nodeValue;
					$trans_data['transactionId'] = $transaction->attributes->getNamedItem('transactionId')->nodeValue;
					$trans_data['transactionStatus'] = $transaction->attributes->getNamedItem('transactionStatus')->nodeValue;
					$trans_data['activity_date'] = 'sysdate';
					$trans_data['fileId'] = $eod_data['fileId'];
					$trans_data['psu_status'] = "'eod'";
		
					foreach($transaction->childNodes as $transaction_children)
					{
						foreach($transaction_children->childNodes as $transaction_data)
						{
							if($transaction_data->nodeName == 'contactInfo')
							{
								foreach($transaction_data->childNodes as $contact_info)
								{
									if($contact_info->nodeValue)
									{
										switch($fields['transaction'][strtolower($contact_info->nodeName)]['type'])
										{
											case 'DATE':
												$trans_data[$contact_info->nodeName] = $this->formatDate($contact_info->nodeValue);
												break;
											case 'NUMBER':
												$trans_data[$contact_info->nodeName] = $contact_info->nodeValue;
												break;
											default:
												$trans_data[$contact_info->nodeName] = $this->db->qstr($contact_info->nodeValue);
												break;
										}//end switch
									}//end if
								}//end foreach
							}//end if
							elseif($transaction_data->nodeValue)
							{
								switch($fields['transaction'][strtolower($transaction_data->nodeName)]['type'])
								{
									case 'DATE':
										$trans_data[$transaction_data->nodeName] = $this->formatDate($transaction_data->nodeValue);
										break;
									case 'NUMBER':
										$trans_data[$transaction_data->nodeName] = $transaction_data->nodeValue;
										break;
									default:
										$trans_data[$transaction_data->nodeName] = $this->db->qstr($transaction_data->nodeValue);
										break;
								}//end switch
							}//end if
						}//end foreach
					}//end foreach
					
					if($row = $this->db->GetRow("SELECT * FROM ecommerce_transaction WHERE transactionid=".$trans_data['transactionId']." AND fileid='receipt'"))
					{
						if($row['transactiontype'] != $trans_data['transactionType'] ||
						   $row['transactionstatus'] != $trans_data['transactionStatus'] ||
						   $row['orderamount'] != $trans_data['orderAmount'])
						{
							throw new PSUECommerceException(PSUECommerceException::INSERTING_TRANSACTION, ': (transactionId:'. $trans_data['originalTransactionId'].') - '.$this->db->ErrorMsg()."\n");
						}//end if
						else
						{
							$this->db->Execute("UPDATE ecommerce_transaction SET fileid=".$trans_data['fileId']." WHERE transactionid = ".$trans_data['transactionId']." AND fileid = 'receipt'");
						}//end else
					}//end if
					elseif(!$this->db->GetOne("SELECT * FROM ecommerce_transaction WHERE transactionid=".$trans_data['transactionId']." AND fileid=".$trans_data['fileId'].""))
					{					
						//insert transaction information
						if(!$this->db->Execute($this->generateSQL('ecommerce_transaction', $trans_data)))
						{
							print_r($trans_data);
							throw new PSUECommerceException(PSUECommerceException::INSERTING_TRANSACTION, ': (transactionId:'. $trans_data['originalTransactionId'].') - '.$this->db->ErrorMsg()."\n");
						}//end if
					}//end else
				}//end for
			}//end if	
			
			if($this->db->GetOne("SELECT count(*) FROM ecommerce_eod WHERE file_name = ".$eod_data['file_name']))
			{
				if(!$this->db->Execute("DELETE FROM ecommerce_pending_eod WHERE file_name = ".$eod_data['file_name']))
				{
					throw new PSUECommerceException(PSUECommerceException::DELETING_PENDING_EOD, ': (fileId: '.$eod_data['fileId'].') - '.$this->db->ErrorMsg()."\n");
				}//end if
			}//end if	
		}//end foreach
	}//end parsePendingEOD

	/**
	* parseXML
	*
	* parses EOD XML into an array
	*
	* @since      version 2.0.0
	* @access     public
	* @param      string $xml XML
	* @return     array
	*/
	function parseXML($xml)
	{
		$dom = new DOMDocument();
		$dom->preserveWhiteSpace = false;
		$dom->loadXML($xml);
		
		return PSUTools::xml2Array($xml);
	}//end parseXML

	function __construct(&$db, $prod = false)
	{
		parent::__construct($db);
		$this->activity_date = date('d-M-Y H:i:s');
		$this->entry_date = date('d-M-Y H:i:s');
		$this->effective_date = date('d-M-Y');
		$this->trans_date = date('d-M-Y');
		
		$this->prod = $prod;
		$this->pass_phrase = ($this->prod) ? $this->_prod_pass_phrase : $this->_test_pass_phrase;
		$this->directory = $this->base_dir.'/'.(( $this->prod ) ? 'prod' : 'test');
		
		$this->user = ($_SESSION['username']) ? $_SESSION['username'] : 'script';
	}//end constructor
	
	function _money($value)
	{
		return ($value) ? $value : 0;
	}//end _money
}//end class
