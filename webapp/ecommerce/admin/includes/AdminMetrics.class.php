<?php

/**
 * AdminMetrics.class.php
 * 
 * ===Modification History===
 * ALPHA 16-june-2009  [nrp]  original<br/>
 */
 
 /**
  * AdminMetrics.class.php
  *
  * Class for handleing the retrieveing of metrics for the ecommerce dashboard
  *
  * @version	1.0
  * @module	AdminMetrics.class.php
  * @author	Nathan Porter <nrporter@plymouth.edu>
  * @copyright 	2009, Plymouth State University, ITS
  */

class AdminMetrics
{
	
	public $db;	

	/**
	 * __construct
	 *
	 * constructor for AdminMetrics. Connects to db
	 * 
	 * @since	version 1.0
	 * @access	public
	 */
	public function __construct(){
	
		$this->db=&$GLOBALS['BANNER'];
		//$this->db=PSUDatabase::connect('oracle/test_psu/fixcase');

	}	

	/**
	 * getAllAdminMetrics
	 *
	 * get all of the ecommerce admin metrics
	 *
	 * @since	version 1.0
	 * @access	public
	 * @return	array $metric returns an associative array of counts from the ecommerce tables
	 */
	public function getAllAdminMetrics(){
		
		$metric = $this->getTransactionMetrics;
		$metric['End of Day Files Processed']['metric'] = $this->getEODCount();
		$metric['Pending End of Day Files']['metric'] = $this->getPendingEODCount();

		$metric = array_merge($metric, $this->getTransactionMetrics());

		return $metric;
		
	}

	/**
	 * getEODCount
	 *
	 * get a count of the records in the ecommerce_eod table
	 *
	 * @since	version 1.0
	 * @access	public
	 * @return	integer $metric returns the count of records in the EOD table
	 */
	public function getEODCount(){
		
		$query = "SELECT COUNT(*) FROM PSU.ECOMMERCE_EOD";
		$metric = $this->db->GetOne($query);
		return $metric;

	}

	/**
	 * getPendingEODCount
	 *
	 * get a count of the records in the pending_ecommerce_eod table
	 *
	 * @since	version 1.0
	 * @access	public
	 * @return	integer $metric returns the count of records in the pending EOD table
	 */
	public function getPendingEODCount(){
		
		$query = "SELECT COUNT(*) FROM PSU.ECOMMERCE_PENDING_EOD";
		$metric = $this->db->GetOne($query);
		return $metric;

	}
	
	/**
	 * getTransactionMetrics
	 *
	 * get the total count of the transaction table as well as counts of each ordertype
	 *
	 * @since	version 1.0
	 * @access	public
	 * @return	array $metric returns an array of counts taken from the transaction table
	 */
	public function getTransactionMetrics(){
		
		$query1 = "SELECT ordertype, COUNT(*) AS metric, SUM(orderamount)/10 AS value FROM PSU.ECOMMERCE_TRANSACTION GROUP BY ordertype";
		$query2 = "SELECT COUNT(*) AS metric, SUM(orderamount)/10 AS value FROM PSU.ECOMMERCE_TRANSACTION";

		$temp = $this->db->GetAll($query1);

		foreach($temp as $val){
			$metric[$val['ordertype']] = array('metric'=>$val['metric'], 'value'=>number_format($val['value'], 2));
		}

		$metric['Total Transactions'] = $this->db->GetRow($query2);
		$metric['Total Transactions']['value'] = number_format($metric['Total Transactions']['value'], 2);

		return $metric;
	}

}

?>
