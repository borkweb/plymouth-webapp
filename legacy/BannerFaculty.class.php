<?php
/**
 * BannerFaculty.class.php
 *
 * === Modification History ===<br/>
 * 1.0.0  18-sep-2006  [mtb]  original<br/>
 *
 * @package 		PSUBannerAPI
 */

/**
 * BannerFaculty.class.php
 *
 * Banner API
 *
 * @version		1.0.0
 * @module		BannerFaculty.class.php
 * @author		Matthew Batchelder <mtbatchelder@plymouth.edu>
 * @copyright 2005, Plymouth State University, ITS
 */ 

require_once('BannerGeneral.class.php');

if(!isset($GLOBALS['BannerCourse']))
{
	require_once('BannerCourse.class.php');
	$GLOBALS['BannerCourse']=new BannerCourse($GLOBALS['BANNER']);
}//end if

class BannerFaculty extends BannerGeneral
{
	var $termcode;
	var $levl;

	/**
	 * BannerFaculty
	 *
	 * BannerFaculty constructor with db connection. 
	 *
	 * @since		version 1.0.0
	 * @access		public
	 * @param  		ADOdb $adodb ADOdb database connection
	 * @param  		string $termcode Termcode.  If not set, defaults global termcode to the current Undergraduate Term
	 */
	function BannerFaculty(&$adodb,$termcode='')
	{
		parent::__construct($adodb);
		if($termcode)
			$this->termcode=$termcode;
		else
			$this->termcode=$this->_ADOdb->GetOne("SELECT f_get_currentterm('UG') FROM dual");
	}//end BannerFaculty

	/**
	 * BannerFaculty
	 *
	 * getSchedule returns the faculty's schedule as an array of CRNs 
	 *
	 * @since		version 1.0.0
	 * @access	public
	 * @param  	string $pid Banner pidm
	 * @param  	string $termcode Termcode to check (default is the globally defined termcode)
	 * @return	array
	 */
	function getSchedule($pid,$termcode='')
	{
		$termcode=($termcode)?$termcode:$this->termcode;

		if(!is_array($termcode))
			$termcode=array($termcode);
		$schedule=array();
		foreach($termcode as $term)
		{
			$data=array();
			$query="SELECT *
								FROM sirasgn
							 WHERE sirasgn_pidm=$pid
								 AND sirasgn_term_code='$term'";
			if($results=$this->_ADOdb->Execute($query))
			{
				while($row=$results->FetchRow())
				{
					$row=$this->cleanKeys('sirasgn_','r_',$row);
					$data[]=$row;
				}//end while
			}//end if
			$schedule[$term]=$data;
		}//end foreach
		return $schedule;
	}//end getSchedule
}//end general_person

?>