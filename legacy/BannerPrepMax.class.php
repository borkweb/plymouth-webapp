<?php
/**
 * BannerPrepMax.class.php
 *
 * === Modification History ===<br/>
 * 1.0.0  18-may-2006  [lmo]  original<br/>
 *
 */

/**
 * BannerPrepMax.class.php
 *
 * @version		1.0.0
 * @module		BannerPrepMax.class.php
 * @author		Laurianne Olcott <max@mail.plymouth.edu>
 * @copyright 2006, Plymouth State University, ITS
 */ 
require_once('BannerGeneral.class.php');
if(!isset($GLOBALS['PSUTools']))
{
	require_once('PSUTools.class.php');

}//end if

if(!isset($GLOBALS['BANNER']))
{
	$GLOBALS['BannerIDM']->_ADOdb=$GLOBALS['BANNER'];
}//end if

class BannerPrepMax extends BannerGeneral
{
	var $_ADOdb;
	/**
	 * BannerPrepMax
	 *
	 * BannerPrepMax constructor with db connection
	 *
	 * @since		version 1.0.0
	 * @access		public
	 * @param  		ADOdb $adodb ADOdb database connection
	 */
	function BannerPrepMax(&$adodb)
	{
		if($adodb)
		{
			$this->_ADOdb=$adodb;
		}//end if
		else
		{
			echo 'Not Connected To Database.  The BannerPrepMax class expected $GLOBALS[\'BANNER\'] variable.';
		}//end else
	}//end BannerPrepMax

	/**
	 * BannerPrepMax
	 *
	 * getOnCampusStudents retrieves pidms and info for students who live on campus
	 *
	 * @since		version 1.0.0
	 * @access		public
	 * @param  		string $pid Banner Pidm
	 * @return		array
	 */
	function getOnCampusStudents($ug_termcode,$gr_termcode)
	{
		$oncampus=array();
		$query="SELECT DISTINCT SLRRASG_PIDM,
							SPRIDEN_LAST_NAME SLRRASG_LAST_NAME,
							SPRIDEN_FIRST_NAME SLRRASG_FIRST_NAME,
							SPRIDEN_ID SLRRASG_ID
						 FROM SLRRASG,SPRIDEN
						 WHERE ((SLRRASG_TERM_CODE='$ug_termcode' AND SLRRASG_ASCD_CODE='AC')
							 OR (SLRRASG_TERM_CODE='$gr_termcode' AND SLRRASG_ASCD_CODE='AC'))
							AND SLRRASG_PIDM=SPRIDEN_PIDM
							AND SPRIDEN_CHANGE_IND IS NULL
							ORDER BY SPRIDEN_LAST_NAME,SPRIDEN_FIRST_NAME";
		$results=$this->_ADOdb->Execute($query);
		if($results)
		{
			while($row=$results->FetchRow())
			{
				$oncampus[]=PSUTools::cleanKeys('slrrasg_','r_',$row);			
			}//end while
		}//end if
		return $oncampus;
	}//end getOnCampusStudents

	/**
	 * BannerPrepMax
	 *
	 * getOnCampusAddresses retrieves on campus addresses only for on campus students
	 *
	 * @since		version 1.0.0
	 * @access		public
	 * @param  		string $pid Banner Pidm
	 * @return		array
	 */
	function getOnCampusAddresses($termcode,$type)
	{
		$oncampus=array();
		$query="SELECT DISTINCT SLRRASG_PIDM,
							SPRIDEN_LAST_NAME SLRRASG_LAST_NAME,
							SPRIDEN_FIRST_NAME SLRRASG_FIRST_NAME,
							SUBSTR(SPRIDEN_MI,1,1) SLRRASG_MI,
							SPRIDEN_ID SLRRASG_ID,
							SPRADDR_STREET_LINE1 SLRRASG_STREET_LINE1,
							SPRADDR_STREET_LINE2 SLRRASG_STREET_LINE2,
							SPRADDR_STREET_LINE3 SLRRASG_STREET_LINE3,
							SPRADDR_CITY SLRRASG_CITY,
							SPRADDR_STAT_CODE SLRRASG_STATE,
							SPRADDR_ZIP SLRRASG_ZIP
						 FROM SLRRASG,SPRIDEN,SPRADDR,SGBSTDN
						 WHERE SLRRASG_TERM_CODE='$termcode' AND SLRRASG_ASCD_CODE='AC'
						  AND SGBSTDN_PIDM=SPRIDEN_PIDM
							AND SGBSTDN_STST_CODE='AS'
							AND SGBSTDN_TERM_CODE_EFF='200810'
							AND SLRRASG_PIDM=SPRIDEN_PIDM
							AND SPRADDR_PIDM=SPRIDEN_PIDM
							AND SPRADDR_ATYP_CODE='$type'
							AND SPRADDR_STATUS_IND IS NULL
							AND SPRIDEN_CHANGE_IND IS NULL
							ORDER BY SPRIDEN_LAST_NAME,SPRIDEN_FIRST_NAME";
		$results=$this->_ADOdb->Execute($query);
		if($results)
		{
			while($row=$results->FetchRow())
			{
				$oncampus[]=PSUTools::cleanKeys('slrrasg_','r_',$row);			
			}//end while
		}//end if
		return $oncampus;
	}//end getOnCampusAddresses

/**
	 * BannerPrepMax
	 *
	 * findHours This function calculates the student's hours 
	 *
	 * @since		version 1.0.0
	 * @param     string $pidm student pidm
	 * @param     string $levl_code level code UG or GR
	 * @param     string $term_code termcode
	 * @access		public
	 * @return		string 
	 */
		function findHours($pidm, $levl_code, $term_code)
		{
			$query = "SELECT NVL(SUM(SHRTGPA_HOURS_EARNED),0) TGPA_HOURS                                                                                              
								FROM   SHRTGPA                                                                                                                      
								WHERE  SHRTGPA_PIDM = $pidm                                                                                                          
								AND    SHRTGPA_LEVL_CODE = '$levl_code'                                                                                                
								AND  ((SHRTGPA_TERM_CODE <= '$term_code'                                                                                               
									 AND SHRTGPA_GPA_TYPE_IND = 'I')                                                                                                  
								OR                                                                                                                                  
											(SHRTGPA_TERM_CODE <= '$term_code'                                                                                               
									 AND SHRTGPA_GPA_TYPE_IND = 'T'))"; 
			return $this->_ADOdb->GetOne($query);
	}//end findHours

/**
	 * BannerPrepMax
	 *
	 * registeredInd This function returns the student registered indicator
	 *
	 * @since		version 1.0.0
	 * @param     string $pidm student pidm
	 * @param     string $term_code termcode
	 * @access		public
	 * @return		string 
	 */
		function registeredInd($pidm, $term_code)
		{
			$query = "SELECT 'Y' INDICATOR
								FROM DUAL
							 WHERE EXISTS
									 (SELECT 'X'
											FROM STVRSTS, SFRSTCR
										 WHERE SFRSTCR_PIDM = $pidm
											 and sfrstcr_term_code = $term_code
											 and sfrstcr_rsts_code = stvrsts_code
											 and stvrsts_incl_sect_enrl = 'Y'";
			return $this->_ADOdb->GetOne($query);
	}//end registeredInd

/**
	 * BannerPrepMax
	 *
	 * checkOnCampusIDs checks to see if an ID is in the group of on campus students
	 * (THIS MIGHT BE DEFUNCT - NEED TO CHECK WITH TELECOM)
	 *
	 * @since		version 1.0.0
	 * @param     string $ug_termcode Undergraduate Student termcode
	 * @param     string $gr_termcode Graduate Student termcode
	 * @param     string $banner_id Banner ID to test - if exists then return true otherwise return false
	 * @access		public
	 * @return		boolean 
	 */
	function checkOnCampusIDs($ug_termcode,$gr_termcode,$banner_id)
	{
		$oncampus='False';  //initialize as false
		$query="SELECT 1
						 FROM SLRRASG,SPRIDEN
						 WHERE ((SLRRASG_TERM_CODE='$ug_termcode' AND SLRRASG_ASCD_CODE='AC')
							 OR (SLRRASG_TERM_CODE='$gr_termcode' AND SLRRASG_ASCD_CODE='AC'))
							AND SLRRASG_PIDM=SPRIDEN_PIDM
							AND SPRIDEN_ID='$banner_id'
							AND SPRIDEN_CHANGE_IND IS NULL";
		$results=$this->_ADOdb->GetOne($query);
		if($results)
		{
				$oncampus='True';	
		}
		else
		{
				$oncampus='False';
		}//end if
		return $oncampus;
	}//end checkOnCampusIDs

	/**
	 * BannerPrepMax
	 *
	 * getOffCampusStudents retrieves pidms and info for students who live off campus
	 *
	 * @since		version 1.0.0
	 * @access		public
	 * @param  		string $ug_termcode undergraduate termcode
	 * @param  		string $gr_termcode graduate termcode
	 * @return		array
	 */
	function getOffCampusStudents($ug_termcode,$gr_termcode)
	{
		$offcampus=array();
		$query="SELECT SFRSTCR_PIDM,
								SPRIDEN_LAST_NAME SFRSTCR_LAST_NAME,
								SPRIDEN_FIRST_NAME SFRSTCR_FIRST_NAME,
								SPRIDEN_ID SFRSTCR_ID
							FROM SFRSTCR,SPRIDEN
							WHERE SFRSTCR_RSTS_CODE IN('RE','RW','AU')
							  AND ((SFRSTCR_TERM_CODE='$ug_termcode')
								 OR (SFRSTCR_TERM_CODE='$gr_termcode'))
								AND NOT EXISTS (SELECT 1 
							                FROM SLRRASG
															WHERE ((SLRRASG_TERM_CODE='$ug_termcode' AND SLRRASG_ASCD_CODE='AC')
																 OR (SLRRASG_TERM_CODE='$gr_termcode' AND SLRRASG_ASCD_CODE='AC'))
															  AND SLRRASG_PIDM=SFRSTCR_PIDM)
								AND SFRSTCR_PIDM=SPRIDEN_PIDM
								AND SPRIDEN_CHANGE_IND IS NULL
								GROUP BY SPRIDEN_LAST_NAME,SPRIDEN_FIRST_NAME,SPRIDEN_ID,SFRSTCR_PIDM
								ORDER BY SPRIDEN_LAST_NAME,SPRIDEN_FIRST_NAME";
		$results=$this->_ADOdb->Execute($query);
		if($results)
		{
			while($row=$results->FetchRow())
			{
				$offcampus[]=PSUTools::cleanKeys('sfrstcr_','r_',$row);			
			}//end while
		}//end if
		return $offcampus;
	}//end getOffCampusStudents

	/**
	 * BannerPrepMax
	 *
	 * getUgTerms retrieves termcodes for a set timespan
	 *
	 * @since		version 1.0.0
	 * @access		public
	 * @param  		string $starts Number of days to span back
	 * @param  		string $ends Number of days to span forward
	 * @return		array
	 */
	function getUgTerms($starts,$ends)
	{
		$terms=array();
		$query="SELECT INITCAP(TO_CHAR(STVTERM_HOUSING_START_DATE,'MON DD, YYYY')) STVTERM_STARTDATE, 
							INITCAP(TO_CHAR(STVTERM_HOUSING_END_DATE,'MON DD, YYYY')) STVTERM_ENDDATE, 
							STVTERM_DESC,
							STVTERM_CODE 
						FROM STVTERM 
						WHERE STVTERM_HOUSING_START_DATE >= (SYSDATE-$starts) 
							AND STVTERM_HOUSING_END_DATE < = (SYSDATE+$ends)
							AND SUBSTR(STVTERM_CODE,5,2) IN('10','20','30','40')
							AND STVTERM_CODE<>'220730'
						ORDER BY STVTERM_CODE DESC, 
							STVTERM_HOUSING_START_DATE, 
							STVTERM_HOUSING_END_DATE";
		$results=$this->_ADOdb->Execute($query);
		if($results)
		{
			while($row=$results->FetchRow())
			{
				$terms[]=PSUTools::cleanKeys('stvterm_','r_',$row);			
			}//end while
		}//end if
		return $terms;
	}//end getUgTerms

	/**
	 * BannerPrepMax
	 *
	 * getCurrentTerm retrieves the current term for a default
	 *
	 * @since		version 1.0.0
	 * @param     $type - undergrad (UG) or grad (GR)
	 * @access		public
	 * @return		array
	 */
	function getCurrentTerm($type)
	{
		$query="SELECT F_GET_CURRENTTERM('$type') STVTERM_CODE FROM DUAL";
		return $this->_ADOdb->GetOne($query);
	}//end getCurrentTerm

	/**
	 * BannerPrepMax
	 *
	 * getActiveStudents retrieves all active students
	 *
	 * @since		version 1.0.0
	 * @access		public
	 * @param  		string $ug_termcode  Term used to determine undergraduate students
	 * @param  		string $gr_termcode  Term used to determine graduate students
	 * @return		array
	 */
	function getActiveStudents($ug_termcode,$gr_termcode)
	{
		$students=array();
		$query="SELECT DISTINCT S1.SGBSTDN_PIDM SGBSTDN_PIDM,SPRIDEN_LAST_NAME SGBSTDN_LASTNAME,SPRIDEN_FIRST_NAME SGBSTDN_FIRSTNAME,
									 SPRIDEN_ID SGBSTDN_ID
						FROM SGBSTDN S1,SPRIDEN
						WHERE S1.SGBSTDN_STST_CODE ='AS'
							AND S1.SGBSTDN_TERM_CODE_EFF=(SELECT MAX(S2.SGBSTDN_TERM_CODE_EFF)
																											FROM SGBSTDN S2
																						WHERE S2.SGBSTDN_PIDM=S1.SGBSTDN_PIDM
																						AND ((S2.SGBSTDN_TERM_CODE_EFF <='$ug_termcode' 
																									AND S2.SGBSTDN_LEVL_CODE='UG')
																									OR (S2.SGBSTDN_TERM_CODE_EFF='$gr_termcode')))
							AND S1.SGBSTDN_PIDM=SPRIDEN_PIDM
							AND SPRIDEN_CHANGE_IND IS NULL
							ORDER BY SPRIDEN_LAST_NAME,SPRIDEN_FIRST_NAME";
		$results=$this->_ADOdb->Execute($query);
		if($results)
		{
			while($row=$results->FetchRow())
			{
				$students[]=PSUTools::cleanKeys('sgbstdn_','r_',$row);			
			}//end while
		}//end if
		return $students;
	}//end getActiveStudents

	/**
	 * BannerPrepMax
	 *
	 * getSuitesOnCampus retrieves active students suite boxes for new students who live on campus
	 *  (MAY BE DEFUNCT - GOES WITH MAILROOM ROUTINES WHICH ARE ON HOLD)
	 * @since		version 1.0.0
	 * @access		public
	 * @param  		string $ug_termcode  Term used to determine undergraduate students
	 * @param  		string $gr_termcode  Term used to determine graduate students
	 * @return		array
	 */
	function getSuitesOnCampus($ug_termcode,$gr_termcode)
	{
		$students=array();
		$query="SELECT SPRIDEN_ID,
						SPRIDEN_FIRST_NAME,
						SUBSTR(SPRIDEN_MI,1,1) SPRIDEN_INIT,
						SPRIDEN_LAST_NAME,
						A.SPRADDR_STREET_LINE1 SPRIDEN_SUITE_NUMBER
						FROM SPRIDEN
						JOIN SGBSTDN ON(SGBSTDN_PIDM = SPRIDEN_PIDM)
						JOIN SPRADDR A ON(A.SPRADDR_PIDM = SPRIDEN_PIDM)
						WHERE SPRIDEN_CHANGE_IND IS NULL
						  AND SGBSTDN_STST_CODE='AS'
							AND SGBSTDN_TERM_CODE_ADMIT IN('$ug_termcode','$gr_termcode')
						  AND A.SPRADDR_ATYP_CODE='CA'
							AND A.SPRADDR_PIDM IN (SELECT SLRRASG_PIDM
																			FROM SLRRASG
																			WHERE ((SLRRASG_TERM_CODE = '$ug_termcode' AND SLRRASG_ASCD_CODE='AC')
																			OR (SLRRASG_TERM_CODE = '$gr_termcode' AND SLRRASG_ASCD_CODE='AC')))
							AND A.SPRADDR_STATUS_IND IS NULL
							ORDER BY SPRIDEN_LAST_NAME,SPRIDEN_FIRST_NAME";
		$results=$this->_ADOdb->Execute($query);
		if($results)
		{
			while($row=$results->FetchRow())
			{
				$students[]=PSUTools::cleanKeys('spriden_','r_',$row);			
			}//end while
		}//end if
		return $students;
	}//end getSuitesOnCampus
 
	/**
	 * BannerPrepMax
	 *
	 * getNewSubscriberAddress retrieves active students suite boxes for new students who live on campus
	 *
	 * @since		version 1.0.0
	 * @access		public
	 * @param     string $bannerid - banner ID
	 * @param     string $type - type of mailing address to retrieve
	 * @return		array
	 */
	function getNewSubscriberAddress($bannerid,$type)
	{
		$students=array();
		$query="SELECT SPRIDEN_ID,
					A.SPRADDR_STREET_LINE1 SPRIDEN_LINE1,
					A.SPRADDR_STREET_LINE2 SPRIDEN_LINE2,
					A.SPRADDR_STREET_LINE3 SPRIDEN_LINE3,
					A.SPRADDR_CITY SPRIDEN_CITY,
					A.SPRADDR_STAT_CODE SPRIDEN_STATE,
					A.SPRADDR_ZIP SPRIDEN_ZIP
					FROM SPRIDEN, SPRADDR A
					WHERE SPRIDEN_CHANGE_IND IS NULL
					  AND SPRIDEN_ID='$bannerid'
						AND A.SPRADDR_PIDM = SPRIDEN_PIDM
						AND A.SPRADDR_ATYP_CODE = '$type'
						AND A.SPRADDR_STATUS_IND IS NULL";
		$results=$this->_ADOdb->Execute($query);
		while($row = $results->FetchRow())
		{
			$students[]=PSUTools::cleanKeys('spriden_','r_',$row);
		}//end while 
		return $students;
	}//end getSuitesOnCampus

	/**
	 * BannerPrepMax
	 *
	 * getAllAdmits retrieves addresses for all admits - number should be checked with Admissions
	 *
	 * @since		version 1.0.0
	 * @access		public
	 * @param  		string $ug_termcode  Term used to determine undergraduate students
	 * @param  		string $gr_termcode  Term used to determine graduate students
	 * @return		array
	 */
	function getAllAdmits($ug_termcode,$gr_termcode)
	{
		$students=array();
		$query="SELECT ID,
									FIRST_NAME,
									MIDDLE_INITIAL,
									LAST_NAME,
									A.SPRADDR_STREET_LINE1 STREET1,
									A.SPRADDR_STREET_LINE2 STREET2,
									A.SPRADDR_STREET_LINE3 STREET3,
									A.SPRADDR_CITY CITY,
									A.SPRADDR_STAT_CODE STATE,
									A.SPRADDR_ZIP ZIP
						FROM	AS_ADMISSIONS_APPLICANT 
						JOIN SPRADDR A ON(A.SPRADDR_PIDM = PIDM_KEY)
						WHERE APDC_CODE1 IN('PD',   'PW')
						  AND TERM_CODE_KEY IN('$ug_termcode','$gr_termcode')
						  AND ADMT_CODE IN('ST',   'FS',   'IN')
						  AND ADMT_CODE IS NOT NULL
						  AND FULL_PART_TIME_IND IN('F',   'P')
						  AND STYP_CODE IN('N',   'T',   'I',   'R')
						  AND A.SPRADDR_ATYP_CODE = 'MA'
						  AND A.SPRADDR_STATUS_IND IS NULL
						  AND A.SPRADDR_SEQNO = (SELECT MAX(B.SPRADDR_SEQNO)
																		 FROM SPRADDR B
																		 WHERE B.SPRADDR_PIDM = A.SPRADDR_PIDM
																			 AND B.SPRADDR_STATUS_IND IS NULL
																			 AND B.SPRADDR_ATYP_CODE='MA')
						ORDER BY LAST_NAME,
							FIRST_NAME";
		$results=$this->_ADOdb->Execute($query);
		if($results)
		{
			while($row=$results->FetchRow())
			{
				$students[]=PSUTools::cleanKeys('','',$row);			
			}//end while
		}//end if
		return $students;
	}//end getSuitesOnCampus
  
	/**
	 * BannerPrepMax
	 *
	 * getAllNewStudents retrieves email addresses and names for all new students for Pinnacle mailing lists
	 *
	 * @since		version 1.0.0
	 * @access		public
	 * @param  		string $ug_termcode  Term used to determine undergraduate students
	 * @param  		string $gr_termcode  Term used to determine graduate students
	 * @return		array
	 */
	function getAllNewStudents($ug_termcode,$gr_termcode)
	{
		$students=array();
		$query="SELECT SPRIDEN_FIRST_NAME FIRST_NAME, 
									SUBSTR(SPRIDEN_MI,1,1)MIDDLE_INIT, 
									SPRIDEN_LAST_NAME LAST_NAME, 
									SPRIDEN_ID ID, 
									GOBTPAC_EXTERNAL_USER USERNAME
						FROM SPRIDEN
						RIGHT JOIN SGBSTDN ON (SGBSTDN_PIDM=SPRIDEN_PIDM)
						LEFT JOIN GOBTPAC ON(GOBTPAC_PIDM=SPRIDEN_PIDM)
						WHERE SGBSTDN_TERM_CODE_ADMIT IN('$ug_termcode','$gr_termcode')
							 AND SPRIDEN_CHANGE_IND IS NULL
						ORDER BY SPRIDEN_LAST_NAME, SPRIDEN_FIRST_NAME";
		$results=$this->_ADOdb->Execute($query);
		if($results)
		{
			while($row=$results->FetchRow())
			{
				$students[]=PSUTools::cleanKeys('','',$row);			
			}//end while
		}//end if
		return $students;
	}//end getAllNewStudents

	/**
	 * BannerPrepMax
	 *
	 * getNewSubscribers retrieves the initial information for the Pinnacle Subscriber Load
	 *
	 * @since		version 1.0.0
	 * @access		public
	 * @param  		string $ug_termcode  Term used to determine undergraduate students
	 * @param  		string $gr_termcode  Term used to determine graduate students
	 * @return		array
	 */
	function getNewSubscribers($ug_termcode,$gr_termcode)
	{
		$students=array();
		$query="SELECT DISTINCT SPRIDEN_LAST_NAME, SPRIDEN_FIRST_NAME, SPRIDEN_MI,  SPRIDEN_ID, 
									SGBSTDN_TERM_CODE_ADMIT SPRIDEN_TERM_CODE_ADMIT, GOREMAL_EMAIL_ADDRESS SPRIDEN_EMAIL_ADDRESS, 
									SPBPERS_CONFID_IND SPRIDEN_CONFID_IND
						FROM GOREMAL
						RIGHT JOIN SGBSTDN ON (SGBSTDN_PIDM=GOREMAL_PIDM)
						INNER JOIN SPRIDEN ON (SGBSTDN_PIDM=SPRIDEN_PIDM)
						LEFT JOIN SPBPERS ON (SPRIDEN_PIDM=SPBPERS_PIDM)
						WHERE SGBSTDN_TERM_CODE_ADMIT IN('$ug_termcode','$gr_termcode')
							 AND SPRIDEN_CHANGE_IND IS NULL
							 AND (GOREMAL_EMAL_CODE='CA' OR GOREMAL_EMAL_CODE IS NULL)
						ORDER BY SPRIDEN_LAST_NAME, SPRIDEN_FIRST_NAME";
		$results=$this->_ADOdb->Execute($query);
		if($results)
		{
			while($row=$results->FetchRow())
			{
				$students[]=PSUTools::cleanKeys('spriden_','r_',$row);			
			}//end while
		}//end if
		return $students;
	}//end getNewSubscribers

	/**
	 * BannerPrepMax
	 *
	 * getAllNewAddresses retrieves the address info needed for the Pinnacle feed file
	 *
	 * @since		version 1.0.0
	 * @access		public
	 * @param  		string $banner_id  Banner student identifier
	 * @return		array
	 */
	function getAllNewAddresses($banner_id)
	{
		$addresses=array();
		$query="SELECT 'T' SPRIDEN_IS_MATCH,
						SPRIDEN_ID SPRIDEN_ID,
						SPRADDR_ATYP_CODE SPRIDEN_ATYP_CODE,
						SPRADDR_STREET_LINE1 SPRIDEN_LINE1,
						SPRADDR_STREET_LINE2 SPRIDEN_LINE2,
						SPRADDR_STREET_LINE3 SPRIDEN_LINE3,
						SPRADDR_CITY SPRIDEN_CITY,
						SPRADDR_STAT_CODE SPRIDEN_STATE,
						SPRADDR_PHONE_AREA SPRIDEN_PHONE_AREA,
						SPRADDR_PHONE_NUMBER SPRIDEN_PHONE_NUMBER,
						SPRADDR_ZIP SPRIDEN_ZIP
					FROM SPRIDEN
					INNER JOIN SPRADDR ON (SPRADDR_PIDM=SPRIDEN_PIDM)
					WHERE SPRIDEN_CHANGE_IND IS NULL
					  AND SPRADDR_ATYP_CODE IN('RH','CA','MA','BI','PA','P2','OF','LO')
						AND SPRADDR_STATUS_IND IS NULL
						AND SPRIDEN_ID = '$banner_id'";
		$results=$this->_ADOdb->Execute($query);
		if($results)
		{
			while($row=$results->FetchRow())
			{
				$addresses[]=PSUTools::cleanKeys('spriden_','r_',$row);			
			}//end while
		}//end if
		return $addresses;
	}//end getAllNewAddresses

	/**
	 * BannerPrepMax
	 *
	 * getAllRHAddresses retrieves the RH (residents only) address info needed for the Pinnacle feed file
	 *
	 * @since		version 1.0.0
	 * @access		public
	 * @param  		string $pidm  student pidm
	 * @return		array
	 */
	function getAllRHAddresses($pidm)
	{
		$addresses=array();
		$query="SELECT 'T' SPRADDR_IS_MATCH,
						SPRADDR_ATYP_CODE,
						SPRADDR_STREET_LINE1,
						SPRADDR_STREET_LINE2,
						SPRADDR_STREET_LINE3,
						SPRADDR_CITY,
						SPRADDR_STAT_CODE,
						SPRADDR_PHONE_AREA,
						SPRADDR_PHONE_NUMBER,
						SPRADDR_ZIP
					FROM SPRADDR
					WHERE SPRADDR_ATYP_CODE = 'RH'
						AND SPRADDR_STATUS_IND IS NULL
						AND SPRADDR_PIDM = '$pidm'";
		$results=$this->_ADOdb->Execute($query);
		if($results)
		{
			while($row=$results->FetchRow())
			{
				$addresses[]=PSUTools::cleanKeys('spraddr_','r_',$row);			
			}//end while
		}//end if
		return $addresses;
	}//end getAllRHAddresses

	/**
	 * BannerPrepMax
	 *
	 * getSpridenInfo gets the student's information found in spriden using banner id.
	 *
	 * @since		version 1.0.0
	 * @access		public
	 * @param  		string $bannerid  Student's Banner Identifier
	 * @return		array
	 */
	function getSpridenInfo($bannerid)
	{
		//str_replace was used incase a last name was passed - it tests both.
		$lastname=str_replace("'","''",$bannerid);
		$data=array();
		$query="SELECT *
						FROM SPRIDEN
						WHERE (SPRIDEN_ID='$bannerid' OR SPRIDEN_LAST_NAME='$lastname')
							AND ROWNUM = 1
							AND SPRIDEN_CHANGE_IND IS NULL";
		$results=$this->_ADOdb->Execute($query);
		if($results)
		{
			while($row=$results->FetchRow())
			{
				$data[]=PSUTools::cleanKeys('spriden_','r_',$row);	
			}// end while
		}// end if
		return $data;
	}//end getSpridenInfo

	/**
	 * BannerPrepMax
	 *
	 * getSzrpregAddr retrieves the person's existing contact information found in SZRPREG using bannerid.
	 *
	 * @since		version 1.0.0
	 * @access		public
	 * @param  		string $pidm   Personal Identifier
	 * @return		array
	 */
	function getSzrpregAddr($pidm)
	{
		$data=array();
		$query="SELECT *
						FROM SZRPREG
						WHERE SZRPREG_PIDM='$pidm'
							AND SZRPREG_STREET1 IS NOT NULL
							AND ROWNUM = 1";
		$results=$this->_ADOdb->Execute($query);
		if($results)
		{
			while($row=$results->FetchRow())
			{
				$data[]=PSUTools::cleanKeys('szrpreg_','r_',$row);	
			}// end while
		}// end if
		return $data;
	}//end getSzrpregAddr

	/**
	 * BannerPrepMax
	 *
	 * getAllSzrpregItems retrieves all SZRPREG property items registered.
	 *
	 * @since		version 1.0.0
	 * @access		public
	 * @param  		array $itemarray   array of items to search on
	 * @return		array
	 */
	function getAllSzrpregItems($itemarray)
	{
		$data=array();
		if($itemarray['serialno']!="")
		{
		$query="SELECT *
						FROM SZRPREG
						WHERE UPPER(SZRPREG_SERIALNO)=UPPER('".$itemarray['serialno']."')
						ORDER BY SZRPREG_PROPERTY_TYPE,SZRPREG_OTHERSPEC";
		}
		else
		{
			if($itemarray['property_type'] !="")
			{
				$property_type=" SZRPREG_PROPERTY_TYPE='".$itemarray['property_type']."'";
				$line=" WHERE".$property_type;
			}
			if($itemarray['otherspec'] !="")
			{
				$otherspec=" UPPER(SZRPREG_OTHERSPEC)=UPPER('".str_replace("'","''",$itemarray['otherspec'])."')";
				if($line !="")
				{
					$line .=$line." AND".$otherspec;
				}
				if($line=="")
				{
					$line= " WHERE".$otherspec;
				}
			}
			if($itemarray['brand'] !="")
			{
				$brand="UPPER(SZRPREG_BRAND)=UPPER('".str_replace("'","''",$itemarray['brand'])."')";
				if($line !="")
				{
					$line .=$line." AND".$brand;
				}
				if($line=="")
				{
					$line= " WHERE".$brand;
				}
			}
			if($itemarray['color'] !="")
			{
				$color="UPPER(SZRPREG_COLOR)=UPPER('".str_replace("'","''",$itemarray['color'])."')";
				if($line !="")
				{
					$line .=$line." AND".$color;
				}
				if($line=="")
				{
					$line= " WHERE".$color;
				}
			}
			if($line !="")
			{
				$query="SELECT * FROM SZRPREG".$line."ORDER BY SZRPREG_PROPERTY_TYPE,SZRPREG_OTHERSPEC";
			}
			else
			{
				$query="SELECT * FROM SZRPREG ORDER BY SZRPREG_PROPERTY_TYPE,SZRPREG_OTHERSPEC";
			}
		}
		$results=$this->_ADOdb->Execute($query);
		if($results)
		{
			while($row=$results->FetchRow())
			{
				$data[]=PSUTools::cleanKeys('szrpreg_','r_',$row);	
			}// end while
		}// end if
		return $data;
	}//end getAllSzrpregItems

	/**
	 * BannerPrepMax
	 *
	 * getSzrpregExistingRecs retrieves a list of existing records for a person using pidm.
	 *
	 * @since		version 1.0.0
	 * @access		public
	 * @param  		string $pidm   Personal Identifier
	 * @return		array
	 */
	function getSzrpregExistingRecs($pidm)
	{
		$data=array();
		$query="SELECT *
						FROM SZRPREG
						WHERE SZRPREG_PIDM='$pidm'
						ORDER BY SZRPREG_PROPERTY_TYPE,SZRPREG_OTHERSPEC";
		$results=$this->_ADOdb->Execute($query);
		if($results)
		{
			while($row=$results->FetchRow())
			{
				$data[]=PSUTools::cleanKeys('szrpreg_','r_',$row);	
			}// end while
		}// end if
		return $data;
	}//end getSzrpregExistingRecs

	/**
	 * BannerPrepMax
	 *
	 * getMissingItemsSzrpreg retrieves a list of items reported stolen and not recovered.
	 *
	 * @since		version 1.0.0
	 * @access		public
	 * @return		array
	 */
	function getMissingItemsSzrpreg()
	{
		$data=array();
		$query="SELECT *
						FROM SZRPREG
						WHERE SZRPREG_MISSING_DATE IS NOT NULL
						  AND SZRPREG_FOUND_DATE IS NULL
						ORDER BY SZRPREG_LASTNAME,SZRPREG_FIRSTNAME,SZRPREG_PROPERTY_TYPE,SZRPREG_OTHERSPEC";
		$results=$this->_ADOdb->Execute($query);
		if($results)
		{
			while($row=$results->FetchRow())
			{
				$data[]=PSUTools::cleanKeys('szrpreg_','r_',$row);	
			}// end while
		}// end if
		return $data;
	}//end getMissingItemsSzrpreg

	/**
	 * BannerPrepMax
	 *
	 * getSzrpregSpecific retrieves the person's existing information found in SZRPREG using key.
	 *
	 * @since		version 1.0.0
	 * @access		public
	 * @param  		string $key   Record to retrieve
	 * @return		array
	 */
	function getSzrpregSpecific($key)
	{
		$data=array();
		$query="SELECT *
						FROM SZRPREG
						WHERE SZRPREG_KEY='$key'";
		$results=$this->_ADOdb->Execute($query);
		if($results)
		{
			while($row=$results->FetchRow())
			{
				$data[]=PSUTools::cleanKeys('szrpreg_','r_',$row);	
			}// end while
		}// end if
		return $data;
	}//end getSzrpregSpecific

	/**
	 * BannerPrepMax
	 *
	 * getGobtpacPidm retrieves the person's pidm via username
	 *
	 * @since		version 1.0.0
	 * @access		public
	 * @param  		string $username - username from auth
	 * @return		string
	 */
	function getGobtpacPidm($username)
	{
		$query="SELECT DISTINCT GOBTPAC_PIDM
						FROM GOBTPAC
						WHERE LOWER(GOBTPAC_EXTERNAL_USER)=LOWER('$username')";
		return $this->_ADOdb->GetOne($query);
	}//end getGobtpacPidm

	/**
	 * BannerPrepMax
	 *
	 * getGobtpacUsername retrieves the person's username for cross checking
	 *
	 * @since		version 1.0.0
	 * @access		public
	 * @param  		string $pidm - personal identifier
	 * @return		string
	 */
	function getGobtpacUsername($pidm)
	{
		$query="SELECT GOBTPAC_EXTERNAL_USER
						FROM GOBTPAC
						WHERE GOBTPAC_PIDM='$pidm'";
		return $this->_ADOdb->GetOne($query);
	}//end getGobtpacUsername

	/**
	 * BannerPrepMax
	 *
	 * getSaAdvisors gets the list of SA Advisors from the pzvadvr table.  returns r_name.
	 *
	 * @since		version 1.0.0
	 * @access		public
	 * @return		array
	 */
	function getSaAdvisors()
	{
		$data=array();
		$query="SELECT * FROM PZVADVR ORDER BY PZVADVR_CODE";
		$results=$this->_ADOdb->Execute($query);
		if($results)
		{
			while($row=$results->FetchRow())
			{
				$data[]=PSUTools::cleanKeys('pzvadvr_','r_',$row);			
			}//end while
		}//end if
		return $data;
	}//end getSaAdvisors

	/**
	 * BannerPrepMax
	 *
	 * getSchools gets the list of schools available.
	 *
	 * @since		version 1.0.0
	 * @access		public
	 * @return		array
	 */
	function getSchools()
	{
		$data=array();
		$query="SELECT * FROM STVSBGI
						WHERE STVSBGI_TYPE_IND='C'
						  AND STVSBGI_ADMR_CODE='CLT1'
						ORDER BY STVSBGI_DESC";
		$results=$this->_ADOdb->Execute($query);
		if($results)
		{
			while($row=$results->FetchRow())
			{
				$data[]=PSUTools::cleanKeys('stvsbgi_','r_',$row);			
			}//end while
		}//end if
		return $data;
	}//end getSchools

	/**
	 * BannerPrepMax
	 *
	 * getStates gets the list of states available.
	 *
	 * @since		version 1.0.0
	 * @access		public
	 * @return		array
	 */
	function getStates()
	{
		$data=array();
		$query="SELECT DISTINCT STVSTAT_DESC,STVSTAT_CODE
						FROM SOBSBGI,STVSTAT
						WHERE SOBSBGI_STAT_CODE=STVSTAT_CODE";
		$results=$this->_ADOdb->Execute($query);
		if($results)
		{
			while($row=$results->FetchRow())
			{
				$data[]=PSUTools::cleanKeys('stvstat_','r_',$row);			
			}//end while
		}//end if
		return $data;
	}//end getStates

	/**
	 * BannerPrepMax
	 *
	 * getTrCourses gets the list of transfer courses from that school we've seen to-date
	 *
	 * @since		version 1.0.0
	 * @access		public
	 * @param			$ceeb string College Entrance Exam Board number
	 * @param     $gened string General Education code
	 * @param			$state string State where colleges are located
	 * @return		array
	 */
	function getTrCourses($ceeb,$gened,$state)
	{
		$data=array();
		if($ceeb !='')
		{
			$query="SELECT STVSBGI_DESC SHBTATC_DESC,
								SHBTATC_CRSE_NUMB_TRNS,
								SHBTATC_TRNS_TITLE,
								SHBTATC_TRNS_LOW_HRS,
								SHBTATC_TRNS_REVIEW_IND,
								SHRTATC_SUBJ_CODE_INST SHBTATC_SUBJ_CODE_INST,
								SHRTATC_CRSE_NUMB_INST SHBTATC_CRSE_NUMB_INST,
								SHRTATC_ACTIVITY_DATE SHBTATC_EVAL_DATE,
								SHRTRAT_ATTR_CODE SHBTATC_ATTR_CODE
								FROM SHBTATC,SHRTATC,SHRTRAT,STVSBGI
							WHERE SHBTATC_SBGI_CODE='$ceeb'
								AND STVSBGI_CODE=SHBTATC_SBGI_CODE
								AND SHBTATC_SBGI_CODE=SHRTATC_SBGI_CODE
								AND SHBTATC_SUBJ_CODE_TRNS=SHRTATC_SUBJ_CODE_TRNS
								AND SHBTATC_CRSE_NUMB_TRNS=SHRTATC_CRSE_NUMB_TRNS
								AND SHBTATC_TERM_CODE_EFF_TRNS=SHRTATC_TERM_CODE_EFF_TRNS
								AND SHRTRAT_SHRTATC_SEQNO(+)=SHRTATC_SEQNO
								AND SHRTRAT_SBGI_CODE(+)=SHRTATC_SBGI_CODE
								AND SHRTRAT_SUBJ_CODE_INST(+)=SHRTATC_SUBJ_CODE_INST
								AND SHRTRAT_CRSE_NUMB_INST(+)=SHRTATC_CRSE_NUMB_INST
							GROUP BY STVSBGI_DESC,SHBTATC_CRSE_NUMB_TRNS,SHBTATC_TRNS_TITLE,SHBTATC_TRNS_LOW_HRS,SHBTATC_TRNS_REVIEW_IND,
								SHRTATC_SUBJ_CODE_INST,SHRTATC_CRSE_NUMB_INST,SHRTATC_ACTIVITY_DATE,SHRTRAT_ATTR_CODE
							ORDER BY SHBTATC_CRSE_NUMB_TRNS,SHBTATC_TRNS_TITLE";
		}
		if($gened !='')
		{
			$query="SELECT STVSBGI_DESC SHBTATC_DESC,
								SHBTATC_CRSE_NUMB_TRNS,
								SHBTATC_TRNS_TITLE,
								SHBTATC_TRNS_LOW_HRS,
								SHBTATC_TRNS_REVIEW_IND,
								SHRTATC_SUBJ_CODE_INST SHBTATC_SUBJ_CODE_INST,
								SHRTATC_CRSE_NUMB_INST SHBTATC_CRSE_NUMB_INST,
								SHRTATC_ACTIVITY_DATE SHBTATC_EVAL_DATE,
								SHRTRAT_ATTR_CODE SHBTATC_ATTR_CODE
								FROM SHBTATC,SHRTATC,SHRTRAT,STVSBGI,SOBSBGI
							WHERE SHRTRAT_ATTR_CODE='$gened'
							  AND SOBSBGI_SBGI_CODE=SHBTATC_SBGI_CODE
								AND SOBSBGI_STAT_CODE='$state'
								AND STVSBGI_CODE=SHBTATC_SBGI_CODE
								AND SHBTATC_SBGI_CODE=SHRTATC_SBGI_CODE
								AND SHBTATC_SUBJ_CODE_TRNS=SHRTATC_SUBJ_CODE_TRNS
								AND SHBTATC_CRSE_NUMB_TRNS=SHRTATC_CRSE_NUMB_TRNS
								AND SHBTATC_TERM_CODE_EFF_TRNS=SHRTATC_TERM_CODE_EFF_TRNS
								AND SHRTRAT_SHRTATC_SEQNO(+)=SHRTATC_SEQNO
								AND SHRTRAT_SBGI_CODE(+)=SHRTATC_SBGI_CODE
								AND SHRTRAT_SUBJ_CODE_INST(+)=SHRTATC_SUBJ_CODE_INST
								AND SHRTRAT_CRSE_NUMB_INST(+)=SHRTATC_CRSE_NUMB_INST
							GROUP BY STVSBGI_DESC,SHBTATC_CRSE_NUMB_TRNS,SHBTATC_TRNS_TITLE,SHBTATC_TRNS_LOW_HRS,SHBTATC_TRNS_REVIEW_IND,
								SHRTATC_SUBJ_CODE_INST,SHRTATC_CRSE_NUMB_INST,SHRTATC_ACTIVITY_DATE,SHRTRAT_ATTR_CODE
							ORDER BY STVSBGI_DESC,SHBTATC_CRSE_NUMB_TRNS,SHBTATC_TRNS_TITLE";
		}
		$results=$this->_ADOdb->Execute($query);
		if($results)
		{
			while($row=$results->FetchRow())
			{
				$data[]=PSUTools::cleanKeys('shbtatc_','r_',$row);			
			}//end while
		}//end if
		return $data;
	}//end getTrCourses

	/**
	 * BannerPrepMax
	 *
	 * getTrCourses gets the list of colleges and addresses by state
	 *
	 * @since		version 1.0.0
	 * @param  		string $state  state to use for lookup
	 * @return		array
	 */
	function getTrColleges($state)
	{
		$data=array();
		$query="SELECT SOBSBGI_SBGI_CODE,
		          STVSBGI_DESC SOBSBGI_NAME,
							SOBSBGI_STREET_LINE1,
							SOBSBGI_STREET_LINE2,
							SOBSBGI_STREET_LINE3,
							SOBSBGI_CITY,
							SOBSBGI_STAT_CODE,
							SOBSBGI_ZIP
						FROM SOBSBGI,STVSBGI
						WHERE SOBSBGI_SBGI_CODE=STVSBGI_CODE
						 AND STVSBGI_ADMR_CODE='CLT1'
						 AND SOBSBGI_STAT_CODE='$state'
						 ORDER BY STVSBGI_DESC";
		$results=$this->_ADOdb->Execute($query);
		if($results)
		{
			while($row=$results->FetchRow())
			{
				$data[]=PSUTools::cleanKeys('sobsbgi_','r_',$row);			
			}//end while
		}//end if
		return $data;
	}//end getTrCourses


	/**
	 * BannerPrepMax
	 *
	 * insertSzrpregNew inserts the key, id and pidm
	 *
	 * @since		version 1.0.0
	 * @access		public
	 *
	 * @param  		pidm - personal identifier
	 * @param     key - which record to update
	 * @param			username - enter username 
	 * @return		boolean
	 */
		
	function insertSzrpregNew($pidm,$key,$username)
	{
		$query="INSERT INTO SZRPREG(
							SZRPREG_KEY,
							SZRPREG_PIDM,
							SZRPREG_USERNAME)
						VALUES(
							'$key',
							'$pidm',
							'$username')";
		$row=$this->_ADOdb->Execute($query);
				return true;			
		return false;
	}//end insertSzrpregNew

	/**
	 * BannerPrepMax
	 *
	 * getMaxKeySzrpreg Get the max key number+1 to insert a new record into SZRPREG
	 *
	 * @since		version 1.0.0
	 * @access		public
	 * @return		number
	 */
	function getMaxKeySzrpreg()
	{
		$query="SELECT MAX(NVL(SZRPREG_KEY,0))+1 MAXKEY FROM SZRPREG";
		return $this->_ADOdb->GetOne($query);
	}//end getMaxKeySzrpreg

	/**
	 * BannerPrepMax
	 *
	 * checkForInactivity checks to see if the student is still here or not
	 *
	 * @since		version 1.0.0
	 * @access		public
	 * @param     string $pidm student's Banner pidm number
	 * @param     string $termcode term to query against
	 * @return		array
	 */
	function checkForInactivity($pidm,$termcode)
	{
		$data=array();
		$query="SELECT SFRWDRL_TERM_CODE,
							SFRWDRL_COMMENT,
							SPRIDEN_ID SFRWDRL_ID,
							SPRIDEN_LAST_NAME SFRWDRL_LAST_NAME,
							SPRIDEN_FIRST_NAME SFRWDRL_FIRST_NAME
						FROM SFRWDRL,SPRIDEN
						WHERE SFRWDRL_PIDM='$pidm' 
						AND SFRWDRL_TERM_CODE=(SELECT MAX(SGBSTDN_TERM_CODE_EFF)
						                       FROM SGBSTDN
																	 WHERE SGBSTDN_TERM_CODE_EFF >='$termcode'
																	 AND SGBSTDN_PIDM='$pidm')
						AND SPRIDEN_PIDM=SFRWDRL_PIDM
						AND SPRIDEN_CHANGE_IND IS NULL";
		$results=$this->_ADOdb->Execute($query);
		if($results)
		{
			while($row=$results->FetchRow())
			{
				$data[]=PSUTools::cleanKeys('sfrwdrl_','r_',$row);			
			}//end while
		}//end if
		return $data;
	}//end checkForInactivity

	/**
	 * BannerPrepMax
	 *
	 * studentsInMajor finds all students in a major and associated concentration (if provided) - uses new curriculum tables 
	 * This also incorporates sfbetrm to check for withdrawals and shrttrm for checking severences.  
	 *
	 * @since		version 1.0.0
	 * @access		public
	 * @param     string $termcode  termcode we are currently in
	 * @param     string $catyear   effective catalog year to check
	 * @param     string $major_code  student's major
	 * @param     string $concen_code student's concentration
	 * @param     string $degree  degree student is studying
	 * @return		array
	 */
	function studentsInMajor($termcode, $catyear, $major_code, $concen_code, $degree)
	{
		$data=array();
		if($concen_code == '')
		{
				$query="SELECT DISTINCT A.SORLFOS_PIDM SORLFOS_PIDM
								FROM SORLFOS A,
									SORLFOS B,
									SORLCUR,
									SFBETRM
								WHERE A.SORLFOS_TERM_CODE= '$termcode'
								 AND A.SORLFOS_MAJR_CODE = '$major_code'
								 AND SFBETRM_PIDM(+) = A.SORLFOS_PIDM
								 AND SFBETRM_TERM_CODE(+) = '$termcode'
								 AND B.SORLFOS_TERM_CODE=A.SORLFOS_TERM_CODE
								 AND SORLCUR_TERM_CODE=A.SORLFOS_TERM_CODE
								 AND SFBETRM_ESTS_CODE(+) = 'EL'
								 AND A.SORLFOS_LCUR_SEQNO = B.SORLFOS_LCUR_SEQNO
								 AND SORLCUR_PIDM = A.SORLFOS_PIDM
								 AND SORLCUR_SEQNO = A.SORLFOS_LCUR_SEQNO
								 AND A.SORLFOS_PIDM = B.SORLFOS_PIDM
								 AND SUBSTR(A.SORLFOS_TERM_CODE_CTLG,1,4) = '$catyear'
								 AND A.SORLFOS_LFST_CODE = 'MAJOR'
								 AND B.SORLFOS_LFST_CODE = 'CONCENTRATION'
								 AND SORLCUR_LMOD_CODE = 'LEARNER'
								 AND SORLCUR_DEGC_CODE = '$degree'
								 AND SORLCUR_CACT_CODE = 'ACTIVE'";
		}
		else
		{
				$query="SELECT DISTINCT A.SORLFOS_PIDM SORLFOS_PIDM
								FROM SORLFOS A,
									SORLFOS B,
									SORLCUR,
									SFBETRM
								WHERE A.SORLFOS_TERM_CODE= '$termcode'
								 AND A.SORLFOS_MAJR_CODE = '$major_code'
								 AND B.SORLFOS_MAJR_CODE = '$concen_code'
								 AND SFBETRM_PIDM(+) = A.SORLFOS_PIDM
								 AND SFBETRM_TERM_CODE(+) = '$termcode'
								 AND B.SORLFOS_TERM_CODE=A.SORLFOS_TERM_CODE
								 AND SORLCUR_TERM_CODE=A.SORLFOS_TERM_CODE
								 AND SFBETRM_ESTS_CODE(+) = 'EL'
								 AND A.SORLFOS_LCUR_SEQNO = B.SORLFOS_LCUR_SEQNO
								 AND SORLCUR_PIDM = A.SORLFOS_PIDM
								 AND SORLCUR_SEQNO = A.SORLFOS_LCUR_SEQNO
								 AND A.SORLFOS_PIDM = B.SORLFOS_PIDM
								 AND SUBSTR(A.SORLFOS_TERM_CODE_CTLG,1,4) = '$catyear'
								 AND A.SORLFOS_LFST_CODE = 'MAJOR'
								 AND B.SORLFOS_LFST_CODE = 'CONCENTRATION'
								 AND SORLCUR_LMOD_CODE = 'LEARNER'
								 AND SORLCUR_DEGC_CODE = '$degree'
								 AND SORLCUR_CACT_CODE = 'ACTIVE'";
		}//end if
		$results=$this->_ADOdb->Execute($query);
		if($results)
		{
			while($row=$results->FetchRow())
			{
				$data[]=PSUTools::cleanKeys('sorlfos_','r_',$row);			
			}//end while
		}//end if
		return $data;
	}//end studentsInMajor

	/**
	 * BannerPrepMax
	 *
	 * getStudentDegree gets a student's degree code which the minor is under
	 *
	 * @since		version 1.0.0
	 * @access		public
	 * @param     string $pidm  student's identifier
	 * @param			string $termyear  current year (if september it's year+1...)
	 * @param     string $catyear   effective catalog year to check
	 * @param     string $minor_code  student's minor
	 * @return		string
	 */
	function getStudentDegree($pidm, $termyear, $catyear, $minor_code)
	{
		$query="SELECT DISTINCT A2.SORLCUR_DEGC_CODE DEGC_CODE
										FROM SORLFOS A1,SORLCUR A2
										WHERE A1.SORLFOS_PIDM='$pidm'
											AND A1.SORLFOS_MAJR_CODE='$minor_code'
											AND A2.SORLCUR_PIDM=A1.SORLFOS_PIDM
											AND A2.SORLCUR_SEQNO=A1.SORLFOS_LCUR_SEQNO
											AND A2.SORLCUR_TERM_CODE=A1.SORLFOS_TERM_CODE
											AND A2.SORLCUR_LMOD_CODE='LEARNER'
											AND SUBSTR(A1.SORLFOS_TERM_CODE_CTLG,1,4) = '$catyear'
											AND A1.SORLFOS_LFST_CODE='MINOR'
											AND A1.SORLFOS_CSTS_CODE='INPROGRESS'
											AND A1.SORLFOS_CACT_CODE='ACTIVE'";	
		return $this->_ADOdb->GetOne($query);
	}//end getStudentDegree

	/**
	 * BannerPrepMax
	 *
	 * studentsInMinor gets all students (per defined minor) and returns an array of pidms - uses the new curriculum tables
	 *
	 * @since		version 1.0.0
	 * @access		public
	 * @param     string $termcode   termcode we are currently in
v	 * @param     string $catyear   effective catalog year to check
	 * @param     string $minor_code  student's minor
	 * @return		array
	 */
	function studentsInMinor($termcode, $catyear, $minor_code)
	{
		$data=array();
		$query="SELECT DISTINCT(SORLFOS_PIDM) SORLFOS_PIDM
						FROM SORLFOS,
							SORLCUR,
							SFBETRM
						WHERE SORLFOS_PIDM = SORLCUR_PIDM
						 AND SORLFOS_TERM_CODE = '$termcode'
						 AND SORLCUR_TERM_CODE=SORLFOS_TERM_CODE
						 AND SFBETRM_PIDM(+) = SORLFOS_PIDM
						 AND SFBETRM_TERM_CODE(+) = '$termcode'
						 AND SFBETRM_ESTS_CODE(+) = 'EL'
						 AND SORLFOS_MAJR_CODE = '$minor_code'
						 AND SUBSTR(SORLFOS_TERM_CODE_CTLG,1,4) = '$catyear'
						 AND SORLCUR_LMOD_CODE = 'LEARNER'
						 AND SORLFOS_LCUR_SEQNO = SORLCUR_SEQNO
						 AND SORLCUR_CACT_CODE = 'ACTIVE'
						 AND SORLFOS_LFST_CODE = 'MINOR'";
		$results=$this->_ADOdb->Execute($query);
		if($results)
		{
			while($row=$results->FetchRow())
			{
				$data[]=PSUTools::cleanKeys('sorlfos_','r_',$row);			
			}//end while
		}//end if
		return $data;
	}//end studentsInMinor

	/**
	 * BannerPrepMax
	 *
	 * getCurrentSchedule gets the student's current schedule.
	 *
	 * @since		version 1.0.0
	 * @access		public
	 * @param     string $pidm  student's Banner pidm
	 * @param     string $term undergraduate term passed
	 * @return		array
	 */
	function getCurrentSchedule($pidm, $term)
	{
		$data=array();
		$query="SELECT 
								TERM_CODE_KEY, CRN_KEY, SUBJ_CODE, CRSE_NUMBER, CREDIT_HOURS_LOW, 
								TITLE, BEGIN_TIME1, END_TIME1, MONDAY_IND1, TUESDAY_IND1, WEDNESDAY_IND1, THURSDAY_IND1, 
								FRIDAY_IND1, SATURDAY_IND1, SUNDAY_IND1, BEGIN_TIME2, END_TIME2, MONDAY_IND2, TUESDAY_IND2, WEDNESDAY_IND2, 
								THURSDAY_IND2, FRIDAY_IND2, SATURDAY_IND2, SUNDAY_IND2, BEGIN_TIME3, END_TIME3, MONDAY_IND3, TUESDAY_IND3, WEDNESDAY_IND3,
								THURSDAY_IND3, FRIDAY_IND3, SATURDAY_IND3, SUNDAY_IND3, BEGIN_TIME4, END_TIME4, MONDAY_IND4, TUESDAY_IND4, 
								WEDNESDAY_IND4, THURSDAY_IND4, FRIDAY_IND4, SATURDAY_IND4, SUNDAY_IND4, BEGIN_TIME5, END_TIME5, 
								MONDAY_IND5, TUESDAY_IND5, WEDNESDAY_IND5, THURSDAY_IND5, FRIDAY_IND5, SATURDAY_IND5, SUNDAY_IND5
						FROM SFRSTCR,DATAMART.PS_AS_CATALOG_SCHEDULE
						WHERE SFRSTCR_PIDM=$pidm
							AND SFRSTCR_CRN=CRN_KEY
							AND SFRSTCR_TERM_CODE=TERM_CODE_KEY	
							AND TERM_CODE_KEY='$term'";
		$results=$this->_ADOdb->Execute($query);
		if($results)
		{
			while($row=$results->FetchRow())
			{
				$data[]=PSUTools::cleanKeys('','',$row);			
			}// end while
		}// end if
		return $data;
	}//end getCurrentSchedule

	/**
	 * BannerPrepMax
	 *
	 * getBannerDirectory Opens the directory to see if the file exists or not
	 *
	 * @since		version 1.0.0
	 * @access		public
	 * @param  		string $filename File to check
	 * @param  		string $directory to check
	 * @return		boolean
	 */
	function getBannerDirectory($filename,$directory)
	{
		$query="DECLARE INFILE UTL_FILE.FILE_TYPE; BEGIN  INFILE := UTL_FILE.FOPEN('$directory','$filename', 'R'); IF UTL_FILE.IS_OPEN(INFILE) THEN UTL_FILE.FREMOVE('$directory','$filename'); END IF; UTL_FILE.FCLOSE(INFILE); END;";
		$stmt=$this->_ADOdb->PrepareSP($query);
		$this->_ADOdb->Execute($stmt);
				return true;			
		return false;
	}//end getBannerDirectory

	/**
	 * BannerPrepMax
	 *
	 * populateFile Opens the Banner directory and appends the data to the file
	 *
	 * @since		version 1.0.0
	 * @access		public
	 * @param  		string $filename File to be updated
	 * @param  		string $newline Feed information for the file
	 * @param			string $directory - directory object name
	 * @return		boolean
	 */
	function populateFile($filename,$newline,$directory)
	{
		ini_set("max_execution_time","3600");
		$query="DECLARE F1 UTL_FILE.FILE_TYPE; BEGIN F1 := UTL_FILE.FOPEN('$directory','$filename', 'A', 8192); BEGIN UTL_FILE.PUT_LINE(F1, '".str_replace("'","''",$newline)."'); END; UTL_FILE.FCLOSE(F1); END;";
		$stmt=$this->_ADOdb->PrepareSP($query);
		$this->_ADOdb->Execute($stmt);
				return true;			
		return false;
	}//end populateFile

	/**
	 * BannerPrepMax
	 *
	 * openRemoteFile Opens the Banner directory and reads the data from the file
	 *
	 * @since		version 1.0.0
	 * @access		public
	 * @param  		string $filename File to be updated
	 * @param			string $directory - directory object name
	 * @return		array $data()
	 */
	function openRemoteFile($filename,$directory)
	{
		$data=array();
		ini_set("max_execution_time","3600");
		$query="DECLARE output_table DBMS_OUTPUT.CHARARR;  a_line VARCHAR2(4000); status INTEGER; f1 utl_file.file_type; ex BOOLEAN; flen NUMBER; bsize NUMBER; BEGIN DBMS_OUTPUT.ENABLE(1000000); f1 := utl_file.fopen('DIR_BURSAR','myfile.txt','R'); utl_file.fgetattr('DIR_BURSAR', 'myfile.txt', ex, flen, bsize); output_table (0) := 'ABC'; --output_table (12) := 'DEF'; FOR linenum in 0..flen-1 LOOP UTL_FILE.GET_LINE(f1,a_line); IF a_line is null THEN a_line := ' '; END IF; DBMS_OUTPUT.PUT_LINE (a_line); status := linenum; END LOOP; utl_file.fclose(f1); FOR linenum in 0..status LOOP BEGIN DBMS_OUTPUT.PUT_LINE (output_table(linenum)); EXCEPTION WHEN OTHERS THEN NULL; END; END LOOP; EXCEPTION WHEN OTHERS THEN NULL; END;";
		$stmt=$this->_ADOdb->PrepareSP($query);
		$results=$this->_ADOdb->Execute($stmt);
		if($results)
		{
			while($row=$results->FetchRow())
			{
				$data=$row;			
			}// end while
		}// end if
	return $data;
	}//end openRemoteFile

	/**
	 * BannerPrepMax
	 *
	 * getMatchingGrade gets all institution coursework.
	 *
	 * @since		version 1.0.0
	 * @access		public
	 * @param     string $pidm  student's Banner pidm
	 * @param     string $termcode  termcode used
	 * @param     string $crn  course crn number
	 * @return		array
	 */
function getMatchingGrade($pidm,$termcode,$crn)
{
	$data = array();
	$query = "SELECT SHRTCKG_GRDE_CODE_FINAL
						FROM SHRTCKN, SHRTCKG
						WHERE SHRTCKN_PIDM = $pidm
							AND SHRTCKN_PIDM = SHRTCKG_PIDM
							AND SHRTCKN_SEQ_NO = SHRTCKG_TCKN_SEQ_NO
							AND SHRTCKN_TERM_CODE = SHRTCKG_TERM_CODE
							AND SHRTCKG_TERM_CODE = '$termcode'
							AND SHRTCKN_CRN = '$crn'";
	$results=$this->_ADOdb->Execute($query);
	if($results)
	{
		while($row=$results->FetchRow())
		{
			$data[]=PSUTools::cleanKeys('shrtckg_','r_',$row);			
		}// end while
	}// end if
	return $data;
}//end getMatchingGrade

	/**
	 * BannerPrepMax
	 *
	 * getAllTerms gets all terms initially as ug, (changing when levl=gr once entered)
	 *
	 * @since		version 1.0.0
	 * @access		public
	 * @return		array
	 */
function getAllTerms()
{
	$data=array();
	//Query to get the current term and 1 year behind
	$query="SELECT INITCAP(TO_CHAR(STVTERM_HOUSING_START_DATE,'MON DD, YYYY')) STVTERM_STARTDATE, 
					INITCAP(TO_CHAR(STVTERM_HOUSING_END_DATE,'MON DD, YYYY')) STVTERM_ENDDATE, 
					STVTERM_DESC,
					STVTERM_CODE
					FROM STVTERM 
					WHERE STVTERM_HOUSING_START_DATE >= (SYSDATE-360) 
						AND STVTERM_HOUSING_END_DATE < = (SYSDATE+90)
						AND SUBSTR(STVTERM_CODE,5,2) IN('10','20','30','40','50')
					ORDER BY STVTERM_CODE, STVTERM_HOUSING_START_DATE, STVTERM_HOUSING_END_DATE";
	$results=$this->_ADOdb->Execute($query);
	if($results)
	{
		while($row=$results->FetchRow())
		{
			$data[]=PSUTools::cleanKeys('stvterm_','r_',$row);			
		}// end while
	}// end if
	return $data;
}// end getAllTerms

	/**
	 * BannerPrepMax
	 *
	 * insertSxbpark inserts records into the SXBPARK table 
	 *
	 * @since		version 1.0.0 for Parking Citations
	 * @access		public
	 * @param  		string $id student Banner ID
	 * @param  		string $citationid Citation ID
	 * @param  		string $location where the car was parked
	 * @param  		string $violation what the violation was 
	 * @param  		string $first_name student first name 
	 * @param  		string $last_name student last name 
	 * @param  		string $issuedate  when the ticket was issued 
	 * @param  		string $permitno  permit number 
	 * @param  		string $licenseno license number 
	 * @param  		string $state state vehicle is from  
	 * @param  		string $duedate  date the ticket amount is due 
	 * @param  		string $parkamt  parking amount billed 
	 * @param  		string $billamt  the actual bill 
	 * @param  		string $term_code term that this takes place (defaults to current) 
	 * @param  		string $username  who is doing this data entry 
	 * @param  		string $citstat  citation status 
	 * @return		string
	 */
function insertSxbpark($id, $citationid, $citationno, $location, $violation,$first_name, $last_name, 
	$issuedate, $permitno,$licenseno, $state, $duedate, $parkamt,$billamt, $term_code, $username, $citstat)
{
	$entered = "F";
	$query="INSERT INTO SXBPARK( SXBPARK_BANNER_ID, SXBPARK_DMV_ID, SXBPARK_CITATION_ID, 
							SXBPARK_CITATION_NUMBER, SXBPARK_LOCATION, SXBPARK_VIOLATION,
							SXBPARK_FIRST_NAME, SXBPARK_LAST_NAME, SXBPARK_ISSUE_DATE, 
							SXBPARK_PERMIT_NUMBER, SXBPARK_LICENSE_NUMBER, SXBPARK_DMV_STATE, 
							SXBPARK_DUE_DATE, SXBPARK_PARK_AMOUNT, SXBPARK_BILL_AMOUNT,
							SXBPARK_TERM_CODE, SXBPARK_USER, SXBPARK_CITATION_STATUS, SXBPARK_ACTIVITY_DATE)
						VALUES( '$id', '$id', '$citationid', '$citationno', '$location', '$violation',
							'$first_name', '$last_name', TO_DATE('$issuedate','MM/DD/YYYY'), '$permitno',
							'$licenseno', '$state', TO_DATE('$duedate','MM/DD/YYYY'), '$parkamt',
							'$billamt', '$term_code', '$username', '$citstat', SYSDATE)";
	$results=$this->_ADOdb->Execute($query);
	$query = "SELECT 1 FROM SXBPARK WHERE SXBPARK_CITATION_NUMBER='$citationno'
							AND SXBPARK_ACTIVITY_DATE LIKE SYSDATE
							AND SXBPARK_USER = '$username'";
	$results=$this->_ADOdb->GetOne($query);
	if($results)
	{
			$entered='T';	
	}
	else
	{
			$entered='F';
	}//end if
	return $entered;
}//end insertSxbpark

	/**
	 * BannerPrepMax
	 *
	 * checkSsnNames double checks names and ssns to make sure we have the right person 
	 *
	 * @since		version 1.0.0 for Parking Citations
	 * @access		public
	 * @param  		string $id student Banner ID
	 * @param  		string $last_name Student Last Name
	 * @return		boolean
	 */
function checkSsnNames($id,$last_name)
{
	$query="UPDATE SXBPARK
				SET SXBPARK_PIDM = (SELECT SPBPERS_PIDM
														FROM SPBPERS,SPRIDEN
														WHERE SPBPERS_SSN = SXBPARK_BANNER_ID
														AND SPBPERS_SSN='$id'
														AND SPBPERS_PIDM=SPRIDEN_PIDM
														AND UPPER(SUBSTR(SPRIDEN_FIRST_NAME,1,3))=UPPER(SUBSTR('$first_name',1,3))
														AND UPPER(SPRIDEN_LAST_NAME)=UPPER('$last_name')
														AND SPRIDEN_CHANGE_IND IS NULL)
				WHERE SXBPARK_PIDM IS NULL 
				AND SXBPARK_UNBILLABLE IS NULL
				AND SXBPARK_BANNER_ID='$id'";
	$row=$this->_ADOdb->Execute($query);
			return true;			
	return false;
}//end checkSsnNames

	/**
	 * BannerPrepMax
	 *
	 * populateNullPidms populates any pidms that are null based on the banner ID 
	 *
	 * @since		version 1.0.0 for Parking Citations
	 * @access		public
	 * @return		boolean
	 */
function populateNullPidms()
{
	$query="UPDATE SXBPARK
				SET SXBPARK_PIDM = (SELECT SPRIDEN_PIDM
														FROM SPRIDEN
														WHERE SPRIDEN_ID = SXBPARK_BANNER_ID
														AND SPRIDEN_CHANGE_IND IS NULL)
				WHERE SXBPARK_PIDM IS NULL 
				AND SXBPARK_UNBILLABLE IS NULL
				AND SXBPARK_BANNER_ID <>'000000000' 
				AND SXBPARK_BANNER_ID IS NOT NULL";
	$row=$this->_ADOdb->Execute($query);
			return true;			
	return false;
}//end populateNullPidms

	/**
	 * BannerPrepMax
	 *
	 * padIds makes sure all IDs are padded
	 *
	 * @since		version 1.0.0 for Parking Citations
	 * @access		public
	 * @return		boolean
	 */
function padIds()
{
	$query="UPDATE SXBPARK
					SET SXBPARK_BANNER_ID=LPAD(SXBPARK_BANNER_ID,9,'0')";
	$row=$this->_ADOdb->Execute($query);
			return true;			
	return false;
}//end padIds

	/**
	 * BannerPrepMax
	 *
	 * flagDeceased Flags all persons who are found deceased in SPBPERS
	 *
	 * @since		version 1.0.0 for Parking Citations
	 * @access		public
	 * @return		boolean
	 */
function flagDeceased()
{
	$query="UPDATE SXBPARK
					SET SXBPARK_PROCESSED = 'Y',
					SXBPARK_PROCESS_DATE = SYSDATE
					WHERE SXBPARK_PIDM IN(SELECT SPBPERS_PIDM FROM SPBPERS WHERE SPBPERS_DEAD_IND='Y')
					  AND SXBPARK_UNBILLABLE IS NULL";
	$row=$this->_ADOdb->Execute($query);
			return true;			
	return false;
}//end flagDeceased

	/**
	 * BannerPrepMax
	 *
	 * getSxbparkNoDate Pulls up all unbillable records or records to fix
	 *
	 * @since		version 1.0.0 for Parking Citations
	 * @access		public
	 * @return		array
	 */
function getSxbparkNoDate()
{
	$data = array();
	$query="SELECT SXBPARK_PIDM,
								SXBPARK_DMV_ID,
								SXBPARK_BANNER_ID,
								SXBPARK_LAST_NAME,
								SXBPARK_FIRST_NAME,
								SXBPARK_MI,
								SXBPARK_TERM_CODE,
								SXBPARK_TRANS_DATE,
								SXBPARK_CITATION_NUMBER,
								SXBPARK_TERM_CODE,
								SXBPARK_ISSUE_DATE,
								SXBPARK_BILL_AMOUNT,
								SXBPARK_DMV_ID,
								SXBPARK_LICENSE_NUMBER,
								SXBPARK_LOCATION,
								SXBPARK_VIOLATION,
								SXBPARK_USER
						FROM SXBPARK 
						WHERE SXBPARK_PROCESS_DATE IS NULL
							AND SXBPARK_UNBILLABLE IS NULL
						ORDER BY SXBPARK_LAST_NAME,SXBPARK_FIRST_NAME,SXBPARK_MI";
	$results=$this->_ADOdb->Execute($query);
	if($results)
	{
		while($row=$results->FetchRow())
		{
			$data[]=PSUTools::cleanKeys('sxbpark_','r_',$row);			
		}// end while
	}// end if
	return $data;
}//end getSxbparkNoDate

	/**
	 * BannerPrepMax
	 *
	 * getSxbparkDate Pulls up all unbillable records or records to fix using a date span
	 *
	 * @since		version 1.0.0 for Parking Citations
	 * @params    $from_date  starting date
	 * @params    $to_date ending date
	 * @access		public
	 * @return		array
	 */
function getSxbparkDate($from_date,$to_date)
{
	$data = array();
	$query="SELECT SXBPARK_PIDM,
								SXBPARK_DMV_ID,
								SXBPARK_BANNER_ID,
								SXBPARK_LAST_NAME,
								SXBPARK_FIRST_NAME,
								SXBPARK_MI,
								SXBPARK_TERM_CODE,
								SXBPARK_TRANS_DATE,
								SXBPARK_CITATION_NUMBER,
								SXBPARK_ISSUE_DATE,
								SXBPARK_BILL_AMOUNT,
								SXBPARK_LICENSE_NUMBER,
								SXBPARK_LOCATION,
								SXBPARK_VIOLATION,
								SXBPARK_USER
						FROM SXBPARK 
						WHERE SXBPARK_PROCESS_DATE IS NULL
							AND SXBPARK_UNBILLABLE IS NULL
							AND SXBPARK_ISSUE_DATE >= '$from_date'
							AND SXBPARK_ISSUE_DATE <= '$to_date'
						ORDER BY SXBPARK_LAST_NAME,SXBPARK_FIRST_NAME,SXBPARK_MI";
	$results=$this->_ADOdb->Execute($query);
	if($results)
	{
		while($row=$results->FetchRow())
		{
			$data[]=PSUTools::cleanKeys('sxbpark_','r_',$row);			
		}// end while
	}// end if
	return $data;
}//end getSxbparkDate

	/**
	 * BannerPrepMax
	 *
	 * updateSxbparkReason Updates SXBPARK reasons ticket is unbillable
	 *
	 * @since		version 1.0.0 for Parking Citations
	 * @access		public
	 * @params    $reason_rec reason it is unbillable
	 * @params    $citation_number  citation number of the ticket
	 * @return		boolean
	 */
function updateSxbparkReason($reason_rec,$citation_number)
{
	$query="UPDATE SXBPARK
						SET SXBPARK_UNBILLABLE = SYSDATE,
						SXBPARK_UNBILLABLE_REASON = '$reason_rec'
						WHERE SXBPARK_CITATION_NUMBER = '$citation_number'";
	$row=$this->_ADOdb->Execute($query);
			return true;			
	return false;
}//end updateSxbparkReason

	/**
	 * BannerPrepMax
	 *
	 * updateSxbparkPidm Update SXBPARK pidms
	 *
	 * @since		version 1.0.0 for Parking Citations
	 * @access		public
	 * @params    $pidm  personal identifier number of person ticketed
	 * @params    $citation_number  citation number of the ticket
	 * @return		boolean
	 */
function updateSxbparkPidm($pidm,$citation_number)
{
	$query="UPDATE SXBPARK
			SET SXBPARK_PIDM = '$pidm'
			WHERE SXBPARK_CITATION_NUMBER = '$citation_number'";
	$row=$this->_ADOdb->Execute($query);
			return true;			
	return false;
}//end updateSxbparkPidm

	/**
	 * BannerPrepMax
	 *
	 * updateSxbparkId updates SXBPARK Banner IDs using PIDMs
	 *
	 * @since		version 1.0.0 for Parking Citations
	 * @access		public
	 * @params    $pidm  personal identifier number of person ticketed
	 * @params    $citation_number  citation number of the ticket
	 * @return		boolean
	 */
function updateSxbparkId($pidm,$citation_number)
{
	$query="UPDATE SXBPARK
			SET SXBPARK_BANNER_ID = (SELECT SPRIDEN_ID FROM SPRIDEN WHERE SPRIDEN_PIDM='$pidm'
													AND SPRIDEN_CHANGE_IND IS NULL)
			WHERE SXBPARK_CITATION_NUMBER = '$citation_number'";
	$row=$this->_ADOdb->Execute($query);
			return true;			
	return false;
}//end updateSxbparkId

	/**
	 * BannerPrepMax
	 *
	 * updatePidmUsingId updates SXBPARK PIDMs using Banner IDs
	 *
	 * @since		version 1.0.0 for Parking Citations
	 * @access		public
	 * @params    $banner_id  Banner ID of the person ticketed
	 * @params    $citation_number  citation number of the ticket
	 * @return		boolean
	 */
function updatePidmUsingId($banner_id,$citation_number)
{
	$query="UPDATE SXBPARK
			SET SXBPARK_PIDM = (SELECT SPRIDEN_PIDM FROM SPRIDEN WHERE SPRIDEN_ID='$banner_id'
													AND SPRIDEN_CHANGE_IND IS NULL)
			WHERE SXBPARK_CITATION_NUMBER = '$citation_number'";
	$row=$this->_ADOdb->Execute($query);
			return true;			
	return false;
}//end updatePidmUsingId

	/**
	 * BannerPrepMax
	 *
	 * updateSxbparkFirstName updates SXBPARK first name
	 *
	 * @since		version 1.0.0 for Parking Citations
	 * @access		public
	 * @params    $first_name  first name of person ticketed
	 * @params    $citation_number  citation number of the ticket
	 * @return		boolean
	 */
function updateSxbparkFirstName($first_name,$citation_number)
{
	$query="UPDATE SXBPARK
			SET SXBPARK_FIRST_NAME = '$first_name'
			WHERE SXBPARK_CITATION_NUMBER = '$citation_number'";
	$row=$this->_ADOdb->Execute($query);
			return true;			
	return false;
}//end updateSxbparkFirstName

	/**
	 * BannerPrepMax
	 *
	 * updateSxbparkLastName updates SXBPARK last name
	 *
	 * @since		version 1.0.0 for Parking Citations
	 * @access		public
	 * @params    $last_name  last name of person ticketed
	 * @params    $citation_number  citation number of the ticket
	 * @return		boolean
	 */
function updateSxbparkLastName($last_name,$citation_number)
{
	$query="UPDATE SXBPARK
			SET SXBPARK_LAST_NAME = '$last_name'
			WHERE SXBPARK_CITATION_NUMBER = '$citation_number'";
	$row=$this->_ADOdb->Execute($query);
			return true;			
	return false;
}//end updateSxbparkLastName

	/**
	 * BannerPrepMax
	 *
	 * updateSxbparkMi updates SXBPARK middle name
	 *
	 * @since		version 1.0.0 for Parking Citations
	 * @access		public
	 * @params    $mi  middle name of person ticketed
	 * @params    $citation_number  citation number of the ticket
	 * @return		boolean
	 */
function updateSxbparkMi($mi,$citation_number)
{
	$query="UPDATE SXBPARK
			SET SXBPARK_MI = '$mi'
			WHERE SXBPARK_CITATION_NUMBER = '$citation_number'";
	$row=$this->_ADOdb->Execute($query);
			return true;			
	return false;
}//end updateSxbparkMi

	/**
	 * BannerPrepMax
	 *
	 * updateSxbparkUser updates SXBPARK user
	 *
	 * @since		version 1.0.0 for Parking Citations
	 * @access		public
	 * @params    $user  username of the person doing the data entry
	 * @params    $citation_number  citation number of the ticket
	 * @return		boolean
	 */
function updateSxbparkUser($user,$citation_number)
{
	$query="UPDATE SXBPARK
			SET SXBPARK_USER = '$user'
			WHERE SXBPARK_CITATION_NUMBER = '$citation_number'";
	$row=$this->_ADOdb->Execute($query);
			return true;			
	return false;
}//end updateSxbparkUser

	/**
	 * BannerPrepMax
	 *
	 * searchSxbparkIds Search to see if there is a match via names 
	 *
	 * @since		version 1.0.0 for Parking Citations
	 * @params    $first  first name
	 * @params    $last last name
	 * @access		public
	 * @return		array
	 */
function searchSxbparkIds($first,$last)
{
	$data = array();
	$query="SELECT A.SPRIDEN_PIDM SPRIDEN_PIDM, 
										A.SPRIDEN_ID SPRIDEN_ID, 
										SPBPERS_SSN SPRIDEN_SSN, 
										SPBPERS_BIRTH_DATE SPRIDEN_DOB, 
										A.SPRIDEN_FIRST_NAME||' '||A.SPRIDEN_MI||' '||A.SPRIDEN_LAST_NAME SPRIDEN_FULLNAME, 
										B.SPRIDEN_ID SPRIDEN_ALTERNATE_ID, 
										SPRADDR_STREET_LINE1 SPRIDEN_STREET_LINE1
						FROM SPRIDEN A
						LEFT JOIN SPRIDEN B ON (A.SPRIDEN_PIDM = B.SPRIDEN_PIDM AND B.SPRIDEN_CHANGE_IND IS NOT NULL) 
						LEFT OUTER JOIN SPBPERS ON(A.SPRIDEN_PIDM = SPBPERS_PIDM)
						LEFT OUTER JOIN SPRADDR ON(A.SPRIDEN_PIDM = SPRADDR_PIDM)
						WHERE SUBSTR(UPPER(A.SPRIDEN_FIRST_NAME),1,3) = '$first' 
									AND UPPER(A.SPRIDEN_LAST_NAME) = '$last'
									AND SPRADDR_STATUS_IND IS NULL AND SPRADDR_ATYP_CODE='MA'
									AND A.SPRIDEN_CHANGE_IND IS NULL
						ORDER BY A.SPRIDEN_LAST_NAME, A.SPRIDEN_FIRST_NAME, A.SPRIDEN_MI";
	$results=$this->_ADOdb->Execute($query);
	if($results)
	{
		while($row=$results->FetchRow())
		{
			$data[]=PSUTools::cleanKeys('spriden_','r_',$row);			
		}// end while
	}// end if
	return $data;
}//end searchSxbparkIds

	/**
	 * BannerPrepMax
	 *
	 * searchSxbparkSsns Search to see if there is a match via dmv_ids (ssns) if name fails
	 *
	 * @since		version 1.0.0 for Parking Citations
	 * @params    $dmv_id ssn or id
	 * @access		public
	 * @return		array
	 */
function searchSxbparkSsns($dmv_id)
{
	$data = array();
	$query="SELECT A.SPRIDEN_PIDM SPRIDEN_PIDM, 
										A.SPRIDEN_ID SPRIDEN_ID, 
										SPBPERS_SSN SPRIDEN_SSN, 
										SPBPERS_BIRTH_DATE SPRIDEN_DOB, 
										A.SPRIDEN_FIRST_NAME||' '||A.SPRIDEN_MI||' '||A.SPRIDEN_LAST_NAME SPRIDEN_FULLNAME, 
										B.SPRIDEN_ID SPRIDEN_ALTERNATE_ID, 
										SPRADDR_STREET_LINE1 SPRIDEN_STREET_LINE1
						FROM SPRIDEN A
						LEFT JOIN SPRIDEN B ON (A.SPRIDEN_PIDM = B.SPRIDEN_PIDM AND B.SPRIDEN_CHANGE_IND IS NOT NULL) 
						LEFT OUTER JOIN SPBPERS ON(A.SPRIDEN_PIDM = SPBPERS_PIDM)
						LEFT OUTER JOIN SPRADDR ON(A.SPRIDEN_PIDM = SPRADDR_PIDM)
						WHERE SPBPERS_SSN = '$dmv_id'
						ORDER BY A.SPRIDEN_LAST_NAME, A.SPRIDEN_FIRST_NAME, A.SPRIDEN_MI";
	$results=$this->_ADOdb->Execute($query);
	if($results)
	{
		while($row=$results->FetchRow())
		{
			$data[]=PSUTools::cleanKeys('spriden_','r_',$row);			
		}// end while
	}// end if
	return $data;
}//end searchSxbparkSsns

	/**
	 * BannerPrepMax
	 *
	 * searchLastName Search to see if there is a match via just lastnames
	 *
	 * @since		version 1.0.0 for Parking Citations
	 * @params    $lastname  last name to search on
	 * @access		public
	 * @return		array
	 */
function searchLastName($lastname)
{
	$data = array();
		$query="SELECT A.SPRIDEN_PIDM SPRIDEN_PIDM, 
						A.SPRIDEN_ID SPRIDEN_ID, 
						SPBPERS_SSN SPRIDEN_SSN, 
						A.SPRIDEN_FIRST_NAME||' '||A.SPRIDEN_MI||' '||A.SPRIDEN_LAST_NAME SPRIDEN_FULLNAME,
						A.SPRIDEN_CHANGE_IND SPRIDEN_CHANGE_IND,
						SPBPERS_BIRTH_DATE SPRIDEN_BIRTH_DATE, 
						B.SPRIDEN_ID SPRIDEN_ALTERNATE_ID, 
						SPRADDR_STREET_LINE1 SPRIDEN_STREET_LINE1
					FROM SPRIDEN A
						LEFT JOIN SPRIDEN B ON (A.SPRIDEN_PIDM = B.SPRIDEN_PIDM AND B.SPRIDEN_CHANGE_IND IS NOT NULL) 
						LEFT OUTER JOIN SPBPERS ON(A.SPRIDEN_PIDM = SPBPERS_PIDM)
						LEFT OUTER JOIN SPRADDR ON(A.SPRIDEN_PIDM = SPRADDR_PIDM)
					WHERE UPPER(A.SPRIDEN_LAST_NAME) = '$lastname'
						AND A.SPRIDEN_CHANGE_IND IS NULL
					ORDER BY A.SPRIDEN_LAST_NAME, A.SPRIDEN_FIRST_NAME, A.SPRIDEN_MI";
	$results=$this->_ADOdb->Execute($query);
	if($results)
	{
		while($row=$results->FetchRow())
		{
			$data[]=PSUTools::cleanKeys('spriden_','r_',$row);			
		}// end while
	}// end if
	return $data;
}//end searchLastName

	/**
	 * BannerPrepMax
	 *
	 * searchAdjRecords Can search by lastname, id or citation numbers
	 *
	 * @since		version 1.0.0 for Parking Citations
	 * @params    $searchitem  what we want to search for
	 * @access		public
	 * @return		array
	 */
function searchAdjRecords($searchitem)
{
	$data = array();
		$query="SELECT SXBPARK_PIDM, 
						SXBPARK_BANNER_ID, 
						SXBPARK_FIRST_NAME, 
						SXBPARK_LAST_NAME, 
						SXBPARK_TRANS_DATE, 
						SXBPARK_BILL_AMOUNT,
						SXBPARK_CITATION_NUMBER, 
						SXBPARK_ADJUSTMENT_USER, 
						SXBPARK_ADJUSTMENTS, 
						SXBPARK_ADJUSTMENT_DATE,
						SXBPARK_ADJUSTMENT_USER2, 
						SXBPARK_ADJUSTMENTS2, 
						SXBPARK_ADJUSTMENT_DATE2,
						SXBPARK_PROCESS_DATE
        FROM SXBPARK 
				WHERE (SXBPARK_BANNER_ID LIKE '%$searchitem%' OR UPPER(SXBPARK_LAST_NAME)
        LIKE '%$searchitem%' OR SXBPARK_CITATION_NUMBER LIKE '%$searchitem%') 
					AND SXBPARK_PROCESS_DATE IS NOT NULL AND SXBPARK_UNBILLABLE IS NULL
	ORDER BY SXBPARK_LAST_NAME, SXBPARK_FIRST_NAME";
	$results=$this->_ADOdb->Execute($query);
	if($results)
	{
		while($row=$results->FetchRow())
		{
			$data[]=PSUTools::cleanKeys('sxbpark_','r_',$row);			
		}// end while
	}// end if
	return $data;
}//end searchAdjRecords

	/**
	 * BannerPrepMax
	 *
	 * editAdjRecords Pulls up the person for editing or adding adjustments
	 *
	 * @since		version 1.0.0 for Parking Citations
	 * @params    $citationno  identifier for each record (citation number) 
	 * @access		public
	 * @return		array
	 */
function editAdjRecords($citationno)
{
	$data = array();
		$query="SELECT SXBPARK_BANNER_ID, 
							SXBPARK_FIRST_NAME, 
							SXBPARK_LAST_NAME, 
							SXBPARK_TERM_CODE, 
							SXBPARK_CITATION_ID,
							SXBPARK_CITATION_NUMBER, 
							SXBPARK_ISSUE_DATE, 
							SXBPARK_DUE_DATE, 
							SXBPARK_PERMIT_NUMBER,
							SXBPARK_LICENSE_NUMBER, 
							SXBPARK_DMV_STATE, 
							SXBPARK_LICENSE_PLATE, 
							SXBPARK_VIOLATION, 
							SXBPARK_LOCATION,
							SXBPARK_CITATION_STATUS, 
							SXBPARK_TRANS_DATE, 
							SXBPARK_BILL_AMOUNT, 
							SXBPARK_PARK_AMOUNT, 
							SXBPARK_ADJUSTMENTS, 
							SXBPARK_ADJUSTMENTS2, 
							SXBPARK_ADJUSTMENT_DATE, 
							SXBPARK_ADJUSTMENT_DATE2,
							SXBPARK_PROCESSED, 
							SXBPARK_USER, 
							SXBPARK_ADJUSTMENT_USER, 
							SXBPARK_ADJUSTMENT_USER2, 
							SXBPARK_BILLING_ERR,
							SXBPARK_BILLING_ERR2
					FROM SXBPARK WHERE SXBPARK_CITATION_NUMBER = '$citationno'
						AND SXBPARK_PROCESS_DATE IS NOT NULL AND SXBPARK_UNBILLABLE IS NULL";
	$results=$this->_ADOdb->Execute($query);
	if($results)
	{
		while($row=$results->FetchRow())
		{
			$data[]=PSUTools::cleanKeys('sxbpark_','r_',$row);			
		}// end while
	}// end if
	return $data;
}//end editAdjRecords

	/**
	 * BannerPrepMax
	 *
	 * updateAdjustment Adds or adjusts adjustments to the bill
	 *
	 * @since		version 1.0.0 for Parking Citations
	 * @access		public
	 * @params    $credits  amount of the adjustment
	 * @params    $adjust_user  username of the person doing the data entry
	 * @params    $billing_err  what the billing error is
	 * @params    $citation_number  citation number of the ticket
	 * @return		boolean
	 */
function updateAdjustment($credits,$adjust_user,$billing_err,$citationno)
{
	$query="UPDATE SXBPARK 
						SET SXBPARK_ADJUSTMENTS = '$credits',
						SXBPARK_ADJUSTMENT_USER = '$adjust_user',
						SXBPARK_BILLING_ERR = '$billing_err'
					WHERE SXBPARK_CITATION_NUMBER = '$citationno'";
	$row=$this->_ADOdb->Execute($query);
			return true;			
	return false;
}//end updateAdjustment

	/**
	 * BannerPrepMax
	 *
	 * updateAdjustment2 Add or adjusts second adjustments to the bill
	 *
	 * @since		version 1.0.0 for Parking Citations
	 * @access		public
	 * @params    $credits2  amount of the adjustment
	 * @params    $adjust_user2  username of the person doing the data entry
	 * @params    $billing_err2  what the billing error is
	 * @params    $citation_number  citation number of the ticket
	 * @return		boolean
	 */
function updateAdjustment2($credits2,$adjust_user2,$billing_err2,$citationno)
{
	$query="UPDATE SXBPARK 
						SET SXBPARK_ADJUSTMENTS2 = '$credits2',
						SXBPARK_ADJUSTMENT_USER2 = '$adjust_user2',
						SXBPARK_BILLING_ERR2 = '$billing_err2'
					WHERE SXBPARK_CITATION_NUMBER = '$citationno'";
	$row=$this->_ADOdb->Execute($query);
			return true;			
	return false;
}//end updateAdjustment2

	/**
	 * BannerPrepMax
	 *
	 * getBillingErrors Pulls up all billing errors in SXBPARK
	 *
	 * @since		version 1.0.0 for Parking Citations
	 * @access		public
	 * @params    $from_date  Start date of date span to look for records
	 * @params    $to_date    End date of date span to look for records
	 * @return		array
	 */
function getBillingErrors($from_date,$to_date)
{
	$data = array();
	if($from_date != '' && $to_date !='')
	{
		$query="SELECT SXBPARK_CITATION_NUMBER,
						SXBPARK_BILLING_ERR,
						SXBPARK_BILLING_ERR2,
						SXBPARK_FIRST_NAME,
						SXBPARK_MI,
						SXBPARK_LAST_NAME,
						SXBPARK_BANNER_ID,
						SXBPARK_TERM_CODE,
						SXBPARK_PARK_AMOUNT,
						SXBPARK_BILL_AMOUNT,
						SXBPARK_TRANS_DATE,
						SXBPARK_ADJUSTMENTS,
						SXBPARK_ADJUSTMENT_USER,
						SXBPARK_ADJUSTMENTS2
						SXBPARK_ADJUSTMENT_USER2,
						SXBPARK_ISSUE_DATE,
						SXBPARK_PROCESSED,
						SXBPARK_PROCESS_DATE,
						SXBPARK_CITATION_STATUS
						FROM SXBPARK WHERE (SXBPARK_BILLING_ERR IS NOT NULL OR SXBPARK_BILLING_ERR2 IS NOT NULL)
						  AND SXBPARK_ACTIVITY_DATE >= '$from_date'
							AND SXBPARK_ACTIVITY_DATE <= '$to_date'
						ORDER BY SXBPARK_ADJUSTMENT_USER, SXBPARK_LAST_NAME, SXBPARK_FIRST_NAME, SXBPARK_CITATION_NUMBER";
	}
	else
	{
		$query="SELECT SXBPARK_CITATION_NUMBER,
						SXBPARK_BILLING_ERR,
						SXBPARK_BILLING_ERR2,
						SXBPARK_FIRST_NAME,
						SXBPARK_MI,
						SXBPARK_LAST_NAME,
						SXBPARK_BANNER_ID,
						SXBPARK_TERM_CODE,
						SXBPARK_PARK_AMOUNT,
						SXBPARK_BILL_AMOUNT,
						SXBPARK_TRANS_DATE,
						SXBPARK_ADJUSTMENTS,
						SXBPARK_ADJUSTMENT_USER,
						SXBPARK_ADJUSTMENTS2
						SXBPARK_ADJUSTMENT_USER2,
						SXBPARK_ISSUE_DATE,
						SXBPARK_PROCESSED,
						SXBPARK_PROCESS_DATE,
						SXBPARK_CITATION_STATUS
						FROM SXBPARK WHERE SXBPARK_BILLING_ERR IS NOT NULL OR SXBPARK_BILLING_ERR2 IS NOT NULL
						ORDER BY SXBPARK_ADJUSTMENT_USER, SXBPARK_LAST_NAME, SXBPARK_FIRST_NAME, SXBPARK_CITATION_NUMBER";
	}
	$results=$this->_ADOdb->Execute($query);
	if($results)
	{
		while($row=$results->FetchRow())
		{
			$data[]=PSUTools::cleanKeys('sxbpark_','r_',$row);			
		}// end while
	}// end if
	return $data;
}//end getBillingErrors


	/**
	 * BannerPrepMax
	 *
	 * getNewEntries Pulls up all records that are ready for processing
	 *
	 * @since		version 1.0.0 for Parking Citations
	 * @access		public
	 * @return		array
	 */
function getNewEntries()
{
	$data = array();
		$query="SELECT SXBPARK_PIDM,
							 SXBPARK_LAST_NAME,
							 SXBPARK_FIRST_NAME,
							 SXBPARK_MI,
							 SXBPARK_TERM_CODE,
							 SXBPARK_BANNER_ID,
							 SXBPARK_CITATION_ID,
							 SXBPARK_CITATION_NUMBER,
							 SXBPARK_ISSUE_DATE,
							 SXBPARK_DUE_DATE,
							 SXBPARK_CITATION_STATUS,
							 SXBPARK_PARK_AMOUNT,
							 SXBPARK_BILL_AMOUNT,
							 SXBPARK_DETAIL_CODE,
							 SXBPARK_DETAIL_CODE_DESC,
							 SXBPARK_USER,
							 SXBPARK_TRANS_DATE,
							 SXBPARK_LICENSE_NUMBER,
							 SXBPARK_LICENSE_PLATE,
							 SXBPARK_LOCATION,
							 SXBPARK_VIOLATION,
							 SXBPARK_PERMIT_NUMBER,
							 SXBPARK_DMV_ID,
							 SXBPARK_DMV_STREET,
							 SXBPARK_DMV_STATE,
							 SXBPARK_PROCESS_DATE,
							 SXBPARK_PROCESSED,
							 SXBPARK_ADJUSTMENT_DATE,
							 SXBPARK_ADJUSTMENTS,
							 SXBPARK_WD,
							 SXBPARK_BILLING_ERR,
							 SXBPARK_BILLING_ERR2,
							 SXBPARK_ADJUSTMENTS2,
							 SXBPARK_ADJUSTMENT_DATE2,
							 SXBPARK_ADJUSTMENT_USER,
							 SXBPARK_ADJUSTMENT_USER2,
							 SXBPARK_ACTIVITY_DATE
						FROM SXBPARK 
						WHERE SXBPARK_PROCESS_DATE IS NULL
							AND SXBPARK_UNBILLABLE IS NULL
							AND SXBPARK_PIDM IS NOT NULL
						ORDER BY SXBPARK_TERM_CODE DESC, SXBPARK_LAST_NAME, SXBPARK_FIRST_NAME";
	$results=$this->_ADOdb->Execute($query);
	if($results)
	{
		while($row=$results->FetchRow())
		{
			$data[]=PSUTools::cleanKeys('sxbpark_','r_',$row);			
		}// end while
	}// end if
	return $data;
}//end getNewEntries

	/**
	 * BannerPrepMax
	 *
	 * getUnbillableRecs Pulls up all records that were found to be unbillable for various reasons
	 *
	 * @since		version 1.0.0 for Parking Citations
	 * @access		public
	 * @return		array
	 */
function getUnbillableRecs($from_date,$to_date)
{
	$data = array();
	if($from_date != '' && $to_date != '')
	{
		$query="SELECT SXBPARK_PIDM,
							 SXBPARK_UNBILLABLE,
							 SXBPARK_UNBILLABLE_REASON,
							 SXBPARK_LAST_NAME,
							 SXBPARK_FIRST_NAME,
							 SXBPARK_MI,
							 SXBPARK_TERM_CODE,
							 SXBPARK_BANNER_ID,
							 SXBPARK_CITATION_ID,
							 SXBPARK_CITATION_NUMBER,
							 SXBPARK_ISSUE_DATE,
							 SXBPARK_DUE_DATE,
							 SXBPARK_CITATION_STATUS,
							 SXBPARK_PARK_AMOUNT,
							 SXBPARK_BILL_AMOUNT,
							 SXBPARK_DETAIL_CODE,
							 SXBPARK_DETAIL_CODE_DESC,
							 SXBPARK_USER,
							 SXBPARK_TRANS_DATE,
							 SXBPARK_LICENSE_NUMBER,
							 SXBPARK_LICENSE_PLATE,
							 SXBPARK_LOCATION,
							 SXBPARK_VIOLATION,
							 SXBPARK_PERMIT_NUMBER,
							 SXBPARK_DMV_ID,
							 SXBPARK_DMV_STREET,
							 SXBPARK_DMV_STATE,
							 SXBPARK_PROCESS_DATE,
							 SXBPARK_PROCESSED,
							 SXBPARK_ADJUSTMENT_DATE,
							 SXBPARK_ADJUSTMENTS,
							 SXBPARK_WD,
							 SXBPARK_BILLING_ERR,
							 SXBPARK_BILLING_ERR2,
							 SXBPARK_ADJUSTMENTS2,
							 SXBPARK_ADJUSTMENT_DATE2,
							 SXBPARK_ADJUSTMENT_USER,
							 SXBPARK_ADJUSTMENT_USER2
						FROM SXBPARK 
						WHERE SXBPARK_UNBILLABLE IS NOT NULL
						  AND SXBPARK_ACTIVITY_DATE >= '$from_date'
							AND SXBPARK_ACTIVITY_DATE <= '$to_date'
						ORDER BY SXBPARK_TERM_CODE DESC, SXBPARK_LAST_NAME, SXBPARK_FIRST_NAME";
	}
	else
	{
		$query="SELECT SXBPARK_PIDM,
							 SXBPARK_UNBILLABLE,
							 SXBPARK_UNBILLABLE_REASON,
							 SXBPARK_LAST_NAME,
							 SXBPARK_FIRST_NAME,
							 SXBPARK_MI,
							 SXBPARK_TERM_CODE,
							 SXBPARK_BANNER_ID,
							 SXBPARK_CITATION_ID,
							 SXBPARK_CITATION_NUMBER,
							 SXBPARK_ISSUE_DATE,
							 SXBPARK_DUE_DATE,
							 SXBPARK_CITATION_STATUS,
							 SXBPARK_PARK_AMOUNT,
							 SXBPARK_BILL_AMOUNT,
							 SXBPARK_DETAIL_CODE,
							 SXBPARK_DETAIL_CODE_DESC,
							 SXBPARK_USER,
							 SXBPARK_TRANS_DATE,
							 SXBPARK_LICENSE_NUMBER,
							 SXBPARK_LICENSE_PLATE,
							 SXBPARK_LOCATION,
							 SXBPARK_VIOLATION,
							 SXBPARK_PERMIT_NUMBER,
							 SXBPARK_DMV_ID,
							 SXBPARK_DMV_STREET,
							 SXBPARK_DMV_STATE,
							 SXBPARK_PROCESS_DATE,
							 SXBPARK_PROCESSED,
							 SXBPARK_ADJUSTMENT_DATE,
							 SXBPARK_ADJUSTMENTS,
							 SXBPARK_WD,
							 SXBPARK_BILLING_ERR,
							 SXBPARK_BILLING_ERR2,
							 SXBPARK_ADJUSTMENTS2,
							 SXBPARK_ADJUSTMENT_DATE2,
							 SXBPARK_ADJUSTMENT_USER,
							 SXBPARK_ADJUSTMENT_USER2
						FROM SXBPARK 
						WHERE SXBPARK_UNBILLABLE IS NOT NULL
						ORDER BY SXBPARK_TERM_CODE DESC, SXBPARK_LAST_NAME, SXBPARK_FIRST_NAME";
	}
	$results=$this->_ADOdb->Execute($query);
	if($results)
	{
		while($row=$results->FetchRow())
		{
			$data[]=PSUTools::cleanKeys('sxbpark_','r_',$row);			
		}// end while
	}// end if
	return $data;
}//end getUnbillableRecs

	/**
	 * BannerPrepMax
	 *
	 * fixSxbparkNames Fixes missing names from SXBPARK
	 *
	 * @since		version 1.0.0 for Parking Citations
	 * @access		public
	 * @return		boolean
	 */
function fixSxbparkNames()
{
	$query="UPDATE SXBPARK
						SET SXBPARK_FIRST_NAME = (SELECT SPRIDEN_FIRST_NAME FROM SPRIDEN WHERE SPRIDEN_PIDM = SXBPARK_PIDM
							AND SPRIDEN_CHANGE_IND IS NULL),
						SXBPARK_LAST_NAME = (SELECT SPRIDEN_LAST_NAME FROM SPRIDEN WHERE SPRIDEN_PIDM = SXBPARK_PIDM
							AND SPRIDEN_CHANGE_IND IS NULL),
						SXBPARK_MI = (SELECT SPRIDEN_MI FROM SPRIDEN WHERE SPRIDEN_PIDM = SXBPARK_PIDM 
							AND SPRIDEN_CHANGE_IND IS NULL)
						WHERE SXBPARK_FIRST_NAME IS NULL AND SXBPARK_LAST_NAME IS NULL";
	$row=$this->_ADOdb->Execute($query);
			return true;			
	return false;
}//end fixSxbparkNames

	/**
	 * BannerPrepMax
	 *
	 * deleteSzblostLoad Deletes all records from the last load in the temporary file based on activity dates. 
	 *
	 * @since		version 1.0.0 for Parking Citations
	 * @access		public
	 * @params    undodate  Activity date of the records that need to be removed.
	 * @return		boolean
	 */
function deleteSzblostLoad($undodate)
{
	$query="DELETE FROM SZBLOST WHERE TRUNC(SZBLOST_ACTIVITY_DATE) = '$undodate'";
	$row=$this->_ADOdb->Execute($query);
			return true;			
	return false;
}//end deleteSzblostLoad

	/**
	 * BannerPrepMax
	 *
	 * deleteSxbparkLoad Deletes all records from the last load based on activity dates. (does 2 tables)
	 *
	 * @since		version 1.0.0 for Parking Citations
	 * @access		public
	 * @params    undodate  Activity date of the records that need to be removed.
	 * @return		boolean
	 */
function deleteSxbparkLoad($undodate)
{
	$query="DELETE FROM SXBPARK WHERE TRUNC(SXBPARK_ACTIVITY_DATE) = '$undodate'";
	$row=$this->_ADOdb->Execute($query);
			return true;			
	return false;
}//end deleteSxbparkLoad

	/**
	 * BannerPrepMax
	 *
	 * findSxbparkHowmany Pulls up all records that were found to be unbillable for various reasons
	 *
	 * @since		version 1.0.0 for Parking Citations
	 * @access		public
	 * @params    $undodate - SXBPARK activity date, will return count of how many will delete.
	 * @return		number
	 */
function findSxbparkHowmany($undodate)
{
		$query="SELECT COUNT(*) HOWMANY FROM SXBPARK WHERE TRUNC(SXBPARK_ACTIVITY_DATE)='$undodate'";
	return $this->_ADOdb->GetOne($query);
}//end findSxbparkHowmany

	/**
	 * BannerPrepMax
	 *
	 * insertSzblost Inserts all records into the temporary holding table SZBLOST for accounting purposes.
	 *
	 * @since		version 1.0.0 for Parking Citations
	 * @access		public
	 * @params    $fullrecord Record that stores all the information that was in the feedfile uploaded to SXBPARK.
	 * @params    $citationno Citation number
	 * @params    $id  Person ID
	 * @params    $bill_amount  The Bill Amount
	 * @params    $last_name  Person Last Name
	 * @params    $first_name Person First Name
	 * @params    $username Person who uploaded the feedfile.
	 * @return		boolean
	 */
function insertSzblost($fullrecord,$citationno,$id,$bill_amount,$last_name,$first_name,$username)
{
	$query="INSERT INTO SZBLOST
					(
						SZBLOST_RECORD_DATE,
						SZBLOST_CITATION_NUMBER,
						SZBLOST_ID,
						SZBLOST_FULL_RECORD,
						SZBLOST_BILL_AMOUNT,
						SZBLOST_LAST_NAME,
						SZBLOST_FIRST_NAME,
						SZBLOST_USER,
						SZBLOST_ACTIVITY_DATE
					)
					VALUES
					(
						SYSDATE,
						'$citationno',
						'$id',
						'$fullrecord',
						'$bill_amount',
						'$last_name',
						'$first_name',
						'$username',
						SYSDATE
					)";
	$row=$this->_ADOdb->Execute($query);
			return true;			
	return false;
}//end insertSzblost

	/**
	 * BannerPrepMax
	 *
	 * testCitationno tests to see if a citation number already exists
	 *
	 * @since		version 1.0.0 for Parking Citations
	 * @access		public
	 * @param  		string $citationid Citation ID
	 * @return		string
	 */
function testCitationno($citationno)
{
	$entered = "F";
	$query = "SELECT 'T' FROM SXBPARK WHERE SXBPARK_CITATION_NUMBER='$citationno'";
	$entered=$this->_ADOdb->GetOne($query);
	return $entered;
}//end testCitationno

	/**
	 * BannerPrepMax
	 *
	 * updateSzblostPosted Enters a "Y" into field SZBLOST_POSTED for auditing purposes
	 *
	 * @since		version 1.0.0 for Parking Citations
	 * @access		public
	 * @params    $citationno  Citation number and ID to identify which record to mark.
	 * @params    $id   Secondary identifier if it exists.  To always work in conjunction with Citation Number.
	 * @return		boolean
	 */
function updateSzblostPosted($citationno)
{
	$query="UPDATE SZBLOST
	          SET SZBLOST_POSTED = 'Y'
					WHERE SZBLOST_CITATION_NUMBER='$citationno'
						AND SZBLOST_ALREADY_POSTED IS NULL
						AND SZBLOST_NOT_POSTED IS NULL
						AND SZBLOST_POSTED IS NULL
						AND SZBLOST_ACTIVITY_DATE LIKE SYSDATE";
	$row=$this->_ADOdb->Execute($query);
			return true;			
	return false;
}//end updateSzblostPosted

	/**
	 * BannerPrepMax
	 *
	 * updateSzblostNotPosted Enters a "Y" into field SZBLOST_NOT_POSTED for auditing purposes
	 *
	 * @since		version 1.0.0 for Parking Citations
	 * @access		public
	 * @params    $citationno  Citation number and ID to identify which record to mark.
	 * @params    $id   Secondary identifier if it exists.  To always work in conjunction with Citation Number.
	 * @return		boolean
	 */
function updateSzblostNotPosted($citationno)
{
	$query="UPDATE SZBLOST
	          SET SZBLOST_NOT_POSTED = 'Y'
					WHERE SZBLOST_CITATION_NUMBER='$citationno'
					  AND SZBLOST_POSTED IS NULL
						AND SZBLOST_NOT_POSTED IS NULL
						AND SZBLOST_ALREADY_POSTED IS NULL
						AND SZBLOST_ACTIVITY_DATE LIKE SYSDATE";
	$row=$this->_ADOdb->Execute($query);
			return true;			
	return false;
}//end updateSzblostNotPosted

	/**
	 * BannerPrepMax
	 *
	 * updateSzblostAlreadyPosted Enters a "Y" into field SZBLOST_ALREADY_POSTED for auditing purposes
	 *
	 * @since		version 1.0.0 for Parking Citations
	 * @access		public
	 * @params    $citationno  Citation number and ID to identify which record to mark.
	 * @return		boolean
	 */
function updateSzblostAlreadyPosted($citationno)
{
	$query="UPDATE SZBLOST
	          SET SZBLOST_ALREADY_POSTED = 'Y'
					WHERE SZBLOST_CITATION_NUMBER='$citationno'
					  AND SZBLOST_POSTED IS NULL
						AND SZBLOST_NOT_POSTED IS NULL
						AND SZBLOST_ALREADY_POSTED IS NULL
						AND SZBLOST_ACTIVITY_DATE LIKE SYSDATE";
	$row=$this->_ADOdb->Execute($query);
			return true;			
	return false;
}//end updateSzblostAlreadyPosted

	/**
	 * BannerPrepMax
	 *
	 * getRevenueSxbpark Pulls up all records that were found to be unbillable for various reasons
	 *
	 * @since		version 1.0.0 for Parking Citations
	 * @access		public
	 * @return		array
	 */
	function getRevenueSxbpark($from_date,$to_date)
	{
		$data = array();
		if($from_date != '' && $to_date != '')
		{
			$query="SELECT SXBPARK_TERM_CODE,
								 SXBPARK_DETAIL_CODE_DESC,
								 SUM(NVL(SXBPARK_PARK_AMOUNT,0)) SXBPARK_PARK_TOTALS,
								 SUM(NVL(SXBPARK_BILL_AMOUNT,0)) SXBPARK_BILL_TOTALS,
								 SUM(NVL(SXBPARK_ADJUSTMENTS,0)) SXBPARK_ADJUST_TOTALS,
								 SUM(NVL(SXBPARK_ADJUSTMENTS2,0)) SXBPARK_ADJUST2_TOTALS,
								 SUM(NVL(SXBPARK_BILL_AMOUNT,0))+SUM(NVL(SXBPARK_ADJUSTMENTS,0))+SUM(NVL(SXBPARK_ADJUSTMENTS2,0)) SXBPARK_GRAND_TOTALS
							FROM SXBPARK 
							WHERE SXBPARK_ACTIVITY_DATE >= '$from_date'
								AND SXBPARK_ACTIVITY_DATE <= '$to_date'
							GROUP BY SXBPARK_TERM_CODE,SXBPARK_DETAIL_CODE_DESC";
		}
		else
		{
			$query="SELECT SXBPARK_TERM_CODE,
								 SXBPARK_DETAIL_CODE_DESC,
								 SUM(NVL(SXBPARK_PARK_AMOUNT,0)) SXBPARK_PARK_TOTALS,
								 SUM(NVL(SXBPARK_BILL_AMOUNT,0)) SXBPARK_BILL_TOTALS,
								 SUM(NVL(SXBPARK_ADJUSTMENTS,0)) SXBPARK_ADJUST_TOTALS,
								 SUM(NVL(SXBPARK_ADJUSTMENTS2,0)) SXBPARK_ADJUST2_TOTALS,
								 SUM(NVL(SXBPARK_BILL_AMOUNT,0))+SUM(NVL(SXBPARK_ADJUSTMENTS,0))+SUM(NVL(SXBPARK_ADJUSTMENTS2,0)) SXBPARK_GRAND_TOTALS
							FROM SXBPARK
							GROUP BY SXBPARK_TERM_CODE,SXBPARK_DETAIL_CODE_DESC";
		}
		$results=$this->_ADOdb->Execute($query);
		if($results)
		{
			while($row=$results->FetchRow())
			{
				$data[]=PSUTools::cleanKeys('sxbpark_','r_',$row);			
			}// end while
		}// end if
		return $data;
	}//end getRevenueSxbpark


	/**
	 * BannerPrepMax
	 *
	 * getRevSxbparkDetails Pulls up all records that were found to be unbillable for various reasons
	 *
	 * @since		version 1.0.0 for Parking Citations
	 * @access		public
	 * @return		array
	 */
	function getRevSxbparkDetails($from_date,$to_date,$termcode)
	{
		$data = array();
		if($from_date != '' && $to_date != '')
		{
			$query="SELECT *
							FROM SXBPARK
							WHERE SXBPARK_TERM_CODE='$termcode'
								AND SXBPARK_ACTIVITY_DATE >= '$from_date'
								AND SXBPARK_ACTIVITY_DATE <= '$to_date'";
		}
		else
		{
			$query="SELECT *
							FROM SXBPARK
							WHERE SXBPARK_TERM_CODE='$termcode'";
		}
		$results=$this->_ADOdb->Execute($query);
		if($results)
		{
			while($row=$results->FetchRow())
			{
				$data[]=PSUTools::cleanKeys('sxbpark_','r_',$row);			
			}// end while
		}// end if
		return $data;
	}//end getRevSxbparkDetails

	/**
	 * BannerPrepMax
	 *
	 * updateSzrpregInfo gets the student's information found in spriden using banner id.
	 *
	 * @since		version 1.0.0
	 * @access		public
	 * @params	  $itemarray
	 * @return		boolean
	 */
	function updateSzrpregInfo($itemarray)
	{
		$query="UPDATE SZRPREG SET
							 SZRPREG_ID='".$itemarray['bannerid']."',
							 SZRPREG_FIRSTNAME='".str_replace("'","''",$itemarray['firstname'])."',
							 SZRPREG_LASTNAME='".str_replace("'","''",$itemarray['lastname'])."',
							 SZRPREG_INITIAL='".$itemarray['initial']."',
							 SZRPREG_DOB='".$itemarray['dob']."',
							 SZRPREG_ROOM='".$itemarray['room']."',
							 SZRPREG_STREET1='".str_replace("'","''",$itemarray['street1'])."',
							 SZRPREG_STREET2='".str_replace("'","''",$itemarray['street2'])."',
							 SZRPREG_STREET3='".str_replace("'","''",$itemarray['street3'])."',
							 SZRPREG_CITY='".str_replace("'","''",$itemarray['city'])."',
							 SZRPREG_STATE='".$itemarray['state']."',
							 SZRPREG_ZIP='".$itemarray['zip']."',
							 SZRPREG_HUBSUITE='".$itemarray['hubsuite']."',
							 SZRPREG_WORKPHONE='".$itemarray['workphone']."',
							 SZRPREG_HOMEPHONE='".$itemarray['homephone']."',
							 SZRPREG_CELLPHONE='".$itemarray['cellphone']."',
							 SZRPREG_EMAIL='".$itemarray['email']."',
							 SZRPREG_PROPERTY_TYPE='".$itemarray['property_type']."',
							 SZRPREG_VALUE='".$itemarray['value']."',
							 SZRPREG_OTHERSPEC='".str_replace("'","''",$itemarray['otherspec'])."',
							 SZRPREG_BRAND='".str_replace("'","''",$itemarray['brand'])."',
							 SZRPREG_MODEL='".str_replace("'","''",$itemarray['model'])."',
							 SZRPREG_SERIALNO='".$itemarray['serialno']."',
							 SZRPREG_COLOR='".str_replace("'","''",$itemarray['color'])."',
							 SZRPREG_GEARS='".$itemarray['gears']."',
							 SZRPREG_BIKESIZE='".$itemarray['bikesize']."',
							 SZRPREG_MF='".$itemarray['mf']."',
							 SZRPREG_MARKS='".str_replace("'","''",$itemarray['marks'])."',
							 SZRPREG_OTHERINF='".str_replace("'","''",$itemarray['otherinf'])."',
							 SZRPREG_ACTIVITY_DATE=SYSDATE
							WHERE SZRPREG_KEY='".$itemarray['key']."'";
		$row=$this->_ADOdb->Execute($query);
				return true;			
		return false;
	}//end updateSzrpregInfo

	/**
	 * BannerPrepMax
	 *
	 * updPoliceRecSzrpreg gets the student's information found in spriden using banner id.
	 *
	 * @since		version 1.0.0
	 * @access		public
	 * @params	  $itemarray
	 * @return		boolean
	 */
	function updPoliceRecSzrpreg($itemarray)
	{
		$query="UPDATE SZRPREG SET
							 SZRPREG_STATUS='".str_replace("'","''",$itemarray['status'])."',
							 SZRPREG_MISSING_DATE=UPPER('".$itemarray['missing_date']."'),
							 SZRPREG_FOUND_DATE=UPPER('".$itemarray['found_date']."'),
							 SZRPREG_COMMENTS='".str_replace("'","''",$itemarray['comments'])."',
							 SZRPREG_OFFICER='".str_replace("'","''",$itemarray['officer'])."',
							 SZRPREG_ITEM_RETURNED_TO='".str_replace("'","''",$itemarray['item_returned_to'])."'
						WHERE SZRPREG_KEY='".$itemarray['key']."'";
		$row=$this->_ADOdb->Execute($query);
				return true;			
		return false;
	}//end updPoliceRecSzrpreg

	/**
	 * BannerPrepMax
	 *
	 * purgeSzrpreg purges all records from the database.
	 *
	 * @since		version 1.0.0
	 * @access		public
	 * @params	  $itemarray
	 * @return		boolean
	 */
	function purgeSzrpreg()
	{
		$query="DELETE FROM SZRPREG";
		$row=$this->_ADOdb->Execute($query);
				return true;			
		return false;
	}//end purgeSzrpreg

	/**
	 * BannerPrepMax
	 *
	 * getAwardInfo Pull up award information for a student
	 *
	 * @since		version 1.0.0 for Financial Aid Perkins Loans
	 * @params    array user - array to store information to key against.
	 * @access		public
	 * @return		array
	 */
	function getAwardInfo($user)
	{
		$data = array();
		$query="SELECT A.AWARD_ACCEPT_AMOUNT AWARD_ACCEPT_AMOUNT,
								 B.AID_PERIOD_DESC AID_PERIOD_DESC
						FROM AR_AWARD_DETAIL_BY_YEAR A,
								 AR_BUDGET_COMPONENTS B
					 WHERE A.PIDM_KEY='".$user['pidm']."'
						 AND B.PIDM_KEY='".$user['pidm']."'
						 AND A.FUND_CODE_KEY='PERKIN'
						 AND A.AID_YEAR_KEY='".$user['aidycode']."'
						 AND B.AID_YEAR_KEY='".$user['aidycode']."'";
		$results=$this->_ADOdb->Execute($query);
		if($results)
		{
			while($row=$results->FetchRow())
			{
				$data[]=PSUTools::cleanKeys('','',$row);			
			}// end while
		}// end if
		return $data;
	}//end getAwardInfo

	/**
	 * BannerPrepMax
	 *
	 * getRcrapp1Info Pull up RCRAPP1 demog information for the student
	 *
	 * @since		version 1.0.0 for Financial Aid Perkins Loans
	 * @params    array user - array to store information to key against.
	 * @access		public
	 * @return		array
	 */
	function getRcrapp1Info($user)
	{
		$data = array();
		$query="SELECT A.RCRAPP1_PHONE_AREA RCRAPP1_PHONE_AREA,
									 A.RCRAPP1_PHONE_NO RCRAPP1_PHONE_NO,
									 A.RCRAPP1_STAT_CODE_LIC RCRAPP1_STAT_CODE_LIC,
									 A.RCRAPP1_DRIVER_LIC_NO RCRAPP1_DRIVER_LIC_NO,
									 SUM(DISTINCT B.RCRLDS4_PERK_CUMULATIVE_AMT) RCRAPP1_PERK_CUM_AMT
							FROM RCRAPP1 A,RCRLDS4 B
						 WHERE A.RCRAPP1_PIDM=".$user['pidm']."
							 AND B.RCRLDS4_PIDM=".$user['pidm']."
							 AND A.RCRAPP1_AIDY_CODE='".$user['aidycode']."'
							 AND B.RCRLDS4_AIDY_CODE='".$user['aidycode']."'
						 GROUP BY A.RCRAPP1_PHONE_AREA,
											A.RCRAPP1_PHONE_NO,
											A.RCRAPP1_STAT_CODE_LIC,
											A.RCRAPP1_DRIVER_LIC_NO";
		$results=$this->_ADOdb->Execute($query);
		if($results)
		{
			while($row=$results->FetchRow())
			{
				$data[]=PSUTools::cleanKeys('rcrapp1_','r_',$row);			
			}// end while
		}// end if
		return $data;
	}//end getRcrapp1Info

	/**
	 * BannerPrepMax
	 *
	 * getBdayMDY Created to make getting birthdates in format mm/dd/yyyy  easier
	 *
	 * @since		version 1.0.0 for Financial Aid Perkins Loans
	 * @params    pidm - banner personal identifier
	 * @access		public
	 * @return		string
	 */
	function getBdayMDY($pidm)
	{
		$query="SELECT TO_CHAR(SPBPERS_BIRTH_DATE,'MM/DD/YYYY') BIRTHDATE FROM SPBPERS WHERE SPBPERS_PIDM='$pidm'";
		return $this->_ADOdb->GetOne($query);
	}//end getBdayMDY

	/**
	 * BannerPrepMax
	 *
	 * getMajorInfo Pulls up all current majors for a student for a term
	 *
	 * @since		version 1.0.0 using the new currculum tables for Banner 8
	 * @params    string pidm - student's pidm.
	 * @params    string termcode - which termcode.
	 * @access		public
	 * @return		array
	 */
	function getMajorInfo($pidm,$termcode)
	{
		$data = array();
		$query="SELECT distinct sovlfos_majr_code, sovlfos_lcur_seqno,
								 f_student_get_desc('STVMAJR',sovlfos_majr_code,30) sovlfos_majr_desc, 
								 sovlfos_term_code_ctlg
						FROM sovlfos,sovlcur a
					 WHERE a.sovlcur_pidm='$pidm' 
						 AND (a.sovlcur_term_code=(select max(b.sovlcur_term_code) from sovlcur b
                                       where b.sovlcur_pidm=a.sovlcur_pidm)
								  or a.sovlcur_term_code_ctlg='$termcode')
						 AND a.sovlcur_lmod_code in ('LEARNER')
						 AND a.sovlcur_term_code=sovlfos_term_code
						 AND a.sovlcur_active_ind='Y' 
						 AND a.sovlcur_current_ind='Y'
						 AND sovlfos_pidm=a.sovlcur_pidm
						 AND sovlfos_lcur_seqno=a.sovlcur_seqno
						 AND sovlfos_active_ind='Y' 
						 AND sovlfos_current_ind='Y'						 
						 AND sovlfos_lfst_code='MAJOR'
						ORDER BY sovlfos_lcur_seqno";
		$results=$this->_ADOdb->Execute($query);
		if($results)
		{
			while($row=$results->FetchRow())
			{
				$data[]=PSUTools::cleanKeys('sovlfos_','r_',$row);	
			}// end while
		}
		return $data;
	}//end getMajorInfo

	/**
	 * BannerPrepMax
	 *
	 * getOrientationTransfers Gets PSU transfer students for any orientation session
	 *
	 * @since		version 1.0.0 using the new currculum tables for Banner 8
	 * @params    string orient_sess - which orientation session
	 * @params    string termcode - which term code
	 * @access		public
	 * @return		array
	 */
	function getOrientationTransfers($orient_sess,$termcode)
	{
		$data = array();
		$query="SELECT SPRIDEN_FIRST_NAME,
										SPRIDEN_MI,
										SPRIDEN_LAST_NAME,
										SPRIDEN_ID,
										SPRIDEN_PIDM
						FROM SPRIDEN,SGBSTDN A
						WHERE SPRIDEN_CHANGE_IND IS NULL
						AND SPRIDEN_PIDM=A.SGBSTDN_PIDM
						AND A.SGBSTDN_ORSN_CODE='$orient_sess'
						AND A.SGBSTDN_TERM_CODE_EFF=(SELECT MAX(B.SGBSTDN_TERM_CODE_EFF)FROM SGBSTDN B
																					WHERE B.SGBSTDN_PIDM=A.SGBSTDN_PIDM
																					  AND B.SGBSTDN_TERM_CODE_EFF <='$termcode')
						AND A.SGBSTDN_STYP_CODE IN('I','T')
						AND A.SGBSTDN_STST_CODE IN('IS','AS')
						AND A.SGBSTDN_PIDM IN (SELECT SHRTRCR_PIDM FROM SHRTRCR WHERE SHRTRCR_TERM_CODE='$termcode')
						ORDER BY SPRIDEN_LAST_NAME,SPRIDEN_FIRST_NAME";
		$results=$this->_ADOdb->Execute($query);
		if($results)
		{
			while($row=$results->FetchRow())
			{
				$data[]=PSUTools::cleanKeys('spriden_','r_',$row);			
			}// end while
		}// end if
		return $data;
	}//end getOrientationTransfers

	/**
	 * BannerPrepMax
	 *
	 * getAllTransfers Returns All transfer students for a specific term.
	 *
	 * @since		version 1.0.0 using the new currculum tables for Banner 8 (except some sgbstdn for status)
	 * @params    string termcode - which term code
	 * @access		public
	 * @return		array
	 */
	function getAllTransfers($termcode)
	{
		$data = array();
		$query="SELECT SPRIDEN_FIRST_NAME,
										SPRIDEN_MI,
										SPRIDEN_LAST_NAME,
										SPRIDEN_ID,
										SPRIDEN_PIDM
						FROM SPRIDEN,SGBSTDN A
						WHERE SPRIDEN_CHANGE_IND IS NULL
						AND SPRIDEN_PIDM=SGBSTDN_PIDM
						AND EXISTS(SELECT 1 FROM SHRTRAM WHERE SHRTRAM_PIDM=SPRIDEN_PIDM AND SHRTRAM_TERM_CODE_ENTERED='$termcode')
						AND A.SGBSTDN_TERM_CODE_EFF=(SELECT MAX(B.SGBSTDN_TERM_CODE_EFF)FROM SGBSTDN B
																					WHERE B.SGBSTDN_PIDM=A.SGBSTDN_PIDM
																					  AND B.SGBSTDN_TERM_CODE_EFF <='$termcode')
						AND A.SGBSTDN_STST_CODE='AS'
						ORDER BY SPRIDEN_LAST_NAME,SPRIDEN_FIRST_NAME";
		$results=$this->_ADOdb->Execute($query);
		if($results)
		{
			while($row=$results->FetchRow())
			{
				$data[]=PSUTools::cleanKeys('spriden_','r_',$row);			
			}// end while
		}// end if
		return $data;
	}//end getAllTransfers

	/**
	 * BannerPrepMax
	 *
	 * getTransfersPerSchool Returns All transfer students for a specific term.
	 *
	 * @since		version 1.0.0 using the new currculum tables for Banner 8 (except some sgbstdn for status)
	 * @params    string ceeb - which ceeb number
	 * @params    string termcode - which term code
	 * @access		public
	 * @return		array
	 */
	function getTransfersPerSchool($ceeb,$termcode)
	{
		$data = array();
		$query="SELECT SPRIDEN_FIRST_NAME,
										SPRIDEN_MI,
										SPRIDEN_LAST_NAME,
										SPRIDEN_ID,
										SPRIDEN_PIDM
						FROM SPRIDEN,SGBSTDN A,SHRTRIT,SHRTRAM
						WHERE SPRIDEN_CHANGE_IND IS NULL
						AND SPRIDEN_PIDM=SGBSTDN_PIDM
						AND SHRTRIT_SBGI_CODE='$ceeb' AND SHRTRIT_PIDM=SPRIDEN_PIDM
						AND SHRTRAM_PIDM=SPRIDEN_PIDM 
												AND SHRTRAM_TERM_CODE_ENTERED='$termcode'
												AND SHRTRAM_TRIT_SEQ_NO=SHRTRIT_SEQ_NO
						AND A.SGBSTDN_TERM_CODE_EFF=(SELECT MAX(B.SGBSTDN_TERM_CODE_EFF)FROM SGBSTDN B
																					WHERE B.SGBSTDN_PIDM=A.SGBSTDN_PIDM
																					  AND B.SGBSTDN_TERM_CODE_EFF <='$termcode')
						AND A.SGBSTDN_STST_CODE='AS'
						GROUP BY SPRIDEN_LAST_NAME,SPRIDEN_PIDM,SPRIDEN_FIRST_NAME,SPRIDEN_MI,SPRIDEN_ID
						ORDER BY SPRIDEN_LAST_NAME,SPRIDEN_FIRST_NAME";
		$results=$this->_ADOdb->Execute($query);
		if($results)
		{
			while($row=$results->FetchRow())
			{
				$data[]=PSUTools::cleanKeys('spriden_','r_',$row);			
			}// end while
		}// end if
		return $data;
	}//end getTransfersPerSchool

	/**
	 * BannerPrepMax
	 *
	 * translateCeeb gets the school name using ceeb
	 *
	 * @since		version 1.0.0
	 * @access		public
	 * @params    string ceeb college entrance examination board number
	 * @return		string
	 */
	function translateCeeb($ceeb)
	{
		$query="SELECT STVSBGI_DESC FROM STVSBGI
						WHERE STVSBGI_CODE='$ceeb'";
		return $this->_ADOdb->GetOne($query);
	}//end translateCeeb

	/**
	 * BannerPrepMax
	 *
	 * getAllCurrentTRCourses Returns all transfer courses seen to date per institution using ceeb number.
	 *
	 * @since		version 1.0.0 
	 * @params    string ceeb - which ceeb number
	 * @access		public
	 * @return		array
	 */
	function getAllCurrentTRCourses($ceeb)
	{
		$data = array();
			$query = "SELECT SHBTATC_SBGI_CODE, 
										SHBTATC_PROGRAM, 
										SHBTATC_TLVL_CODE, 
										SHBTATC_SUBJ_CODE_TRNS, 
										SHBTATC_CRSE_NUMB_TRNS, 
										SHBTATC_TERM_CODE_EFF_TRNS, 
										SHBTATC_ACTIVITY_DATE, 
										SHBTATC_TRNS_TITLE, 
										SHBTATC_TRNS_LOW_HRS, 
										SHBTATC_TRNS_HIGH_HRS, 
										SHBTATC_TRNS_REVIEW_IND, 
										SHBTATC_TAST_CODE, 
										SHBTATC_TRNS_CATALOG, 
										SHBTATC_TGRD_CODE_MIN, 
										SHBTATC_GROUP, 
										SHBTATC_GROUP_PRIMARY_IND, 
										SHRTATC_SBGI_CODE SHBTATC_RSBGI_CODE, 
										SHRTATC_PROGRAM SHBTATC_RPROGRAM, 
										SHRTATC_TLVL_CODE SHBTATC_RTLVL_CODE, 
										SHRTATC_SUBJ_CODE_TRNS SHBTATC_RSUBJ_CODE_TRNS, 
										SHRTATC_CRSE_NUMB_TRNS SHBTATC_RCRSE_NUMB_TRNS, 
										SHRTATC_TERM_CODE_EFF_TRNS SHBTATC_RTERM_CODE_EFF_TRNS, 
										SHRTATC_SEQNO SHBTATC_RSEQNO, 
										SHRTATC_ACTIVITY_DATE SHBTATC_RACTIVITY_DATE, 
										SHRTATC_CONNECTOR SHBTATC_R_CONNECTOR, 
										SHRTATC_INST_LPAREN_CONN SHBTATC_R_INST_LPAREN_CONN, 
										SHRTATC_SUBJ_CODE_INST SHBTATC_RSUBJ_CODE_INST, 
										SHRTATC_CRSE_NUMB_INST SHBTATC_RCRSE_NUMB_INST, 
										SHRTATC_INST_TITLE SHBTATC_RINST_TITLE, 
										SHRTATC_INST_CREDITS_USED SHBTATC_RINST_CREDITS_USED, 
										SHRTATC_INST_RPAREN SHBTATC_RINST_RPAREN, 
										SHRTATC_GROUP SHBTATC_RGROUP
									FROM SHBTATC, SHRTATC 
									WHERE SHBTATC_TERM_CODE_EFF_TRNS = SHRTATC_TERM_CODE_EFF_TRNS 
										AND SHBTATC_CRSE_NUMB_TRNS = SHRTATC_CRSE_NUMB_TRNS
										AND SHBTATC_TLVL_CODE = SHRTATC_TLVL_CODE
										AND SHBTATC_SBGI_CODE = SHRTATC_SBGI_CODE
										AND ((SHBTATC_GROUP = SHRTATC_GROUP)OR(SHBTATC_GROUP IS NULL AND SHRTATC_GROUP IS NULL))
										AND SHBTATC_SBGI_CODE='$ceeb'
									ORDER BY SHBTATC_TRNS_TITLE, SHBTATC_SUBJ_CODE_TRNS, SHBTATC_CRSE_NUMB_TRNS";
		$results=$this->_ADOdb->Execute($query);
		if($results)
		{
			while($row=$results->FetchRow())
			{
				$data[]=PSUTools::cleanKeys('shbtatc_','r_',$row);			
			}// end while
		}// end if
		return $data;
	}//end getAllCurrentTRCourses

}//end bannerPrepMax
?>