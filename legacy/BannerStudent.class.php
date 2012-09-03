<?php

/*
 * BannerStudent.class.php
 *
 * === Modification History ===<br/>
 * 1.0.0  18-may-2005  [mtb]  original<br/>
 * 1.1.0  13-sep-2007  [zbt]  added studentSighted and removed references to COMMON_INCLUDES<br/>
 * 1.1.1  04-feb-2008  [mtb]  added getActiveTerms<br/>
 */

/**
 * Banner API
 *
 * @version		1.0.0
 * @module		BannerStudent.class.php
 * @author		Matthew Batchelder <mtbatchelder@plymouth.edu>
 * @copyright 2005, Plymouth State University, ITS
 * @class BannerStudent
 */ 

require_once('BannerGeneral.class.php');

if(!isset($GLOBALS['BannerCourse']))
{
	require_once('BannerCourse.class.php');
	$GLOBALS['BannerCourse']=new BannerCourse($GLOBALS['BANNER']);
}//end if

class BannerStudent extends BannerGeneral
{
	var $termcode;
	var $levl;

	/**
	 * BannerStudent constructor with db connection. 
	 *
	 * @since		version 1.0.0
	 * @param  		ADOdb $adodb ADOdb database connection
	 * @param  		string $termcode Termcode.  If not set, defaults global termcode to the current Undergraduate Term
	 */
	function BannerStudent(&$adodb,$termcode='')
	{
		parent::__construct($adodb);
		if($termcode)
			$this->termcode=$termcode;
		else
			$this->termcode=$this->_ADOdb->GetOne("SELECT f_get_currentterm('UG') FROM dual");
			
		$this->levl = 'UG';
	}//end BannerStudent

	/**
	 * getAdvisors returns an array of active advisor records for the given pidm
	 * replaced with $person->student->ug/gr/etc->advisors
	 *
	 * @since		version 1.0.0
	 * @param  	string $pid Banner pidm
	 * @param  	string $termcode Termcode to check (default is the globally defined termcode)
	 * @return	array
	 */
	function getAdvisors($pid,$termcode='')
	{
		$termcode=($termcode)?$termcode:$this->termcode;

		$advisors=array();

		$query="SELECT *
							 FROM sgradvr a
							WHERE a.sgradvr_pidm = $pid
								AND a.sgradvr_term_code_eff =
											(SELECT MAX (b.sgradvr_term_code_eff)
												FROM sgradvr b
											 WHERE b.sgradvr_pidm = a.sgradvr_pidm
												 AND b.sgradvr_advr_code = 'ACAD'
												 AND b.sgradvr_term_code_eff <= '$termcode')
								AND a.sgradvr_advr_pidm in
											(SELECT c.sgradvr_advr_pidm
												 FROM sgradvr c
												WHERE c.sgradvr_term_code_eff=a.sgradvr_term_code_eff
													AND c.sgradvr_pidm=a.sgradvr_pidm)
							 AND EXISTS (SELECT 1
														 FROM sgbstdn,sobcurr
														WHERE sgbstdn_pidm = $pid
															AND sgbstdn_stst_code = 'AS'
															AND sgbstdn_styp_code <> 'X'
															AND sgbstdn_levl_code=sobcurr_levl_code
															AND sgbstdn_degc_code_1=sobcurr_degc_code
															AND sgbstdn_program_1=sobcurr_program
															AND sobcurr_secd_roll_ind='Y'
															AND sobcurr_term_code_init<='$termcode'
															AND sgbstdn_term_code_eff =
																	 (SELECT MAX (sg.sgbstdn_term_code_eff)
																			FROM sgbstdn sg
																		 WHERE sg.sgbstdn_pidm = $pid))";
		if($results=$this->_ADOdb->Execute($query))
		{
			while($row=$results->FetchRow())
			{
				$row=$this->cleanKeys('sgradvr_','r_',$row);
				$advisors[]=$row;
			}//end while
		}//end if
		return $advisors;
	}//end getAdvisors

	/**
	 * returns the number of years student has been an athlete
	 * replaced with $person->student->ug->athletic_year
	 *
	 * @since		version 1.0.0
	 * @param  	string $pid Banner pidm
	 * @return	int
	 */
	function getAthleticYear($pid)
	{
		if(is_array($pid))
		{
			$data = array();
			$sql="SELECT count(DISTINCT SUBSTR(sgrsprt_term_code, 1, 4)) sgrsprt_years, sgrsprt_pidm FROM sgrsprt WHERE sgrsprt_pidm IN (".implode(',',$pid).") GROUP BY sgrsprt_pidm";
			if($results = $this->_ADOdb->Execute($sql))
			{
				while($row = $results->FetchRow())
				{
					$row = PSUTools::cleanKeys('sgrsprt','r',$row);
					$data[$row['r_pidm']] = $row;
				}//end while
			}//end if
			return $data;
		}//end if
		else
		{
			return $this->_ADOdb->GetOne("SELECT count(DISTINCT SUBSTR(sgrsprt_term_code, 1, 4)) FROM sgrsprt WHERE sgrsprt_pidm=$pid");
		}//end else
	}//end getAthleticYear

	/**
	 * returns student's class
	 * replaced with $person->student->ug/gr/etc->class and $person->student->ug/gr/etc->class_code
	 *
	 * @since		version 1.0.0
	 * @param  	int $pid Banner pidm
	 * @param   string $termcode Term Code
	 * @param   string $level Level Code
	 * @return	array
	 */
	function getClass($pidm, $termcode ='', $level ='')
	{
		$student_data = $this->getStudentData($pidm, $termcode, $level);

		$data = $this->getValidation('stvclas',"stvclas_code='".$student_data['r_clas_code']."'");
		return $data;
	}//end getClass

	/**
	 * returns student demographic data for the given student(s)
	 * replaced with $person->inDemographics()
	 *
	 * @since		version 1.0.0
	 * @param  	mixed $pid Banner pidm
	 * @return	array
	 */
	function getDemographics($pid)
	{
		if(is_array($pid))
		{
			$data = array();
			$sql="SELECT * FROM ps_as_student_demographics WHERE pidm IN(".implode(',',$pid).")";
			if($results = $this->_ADOdb->Execute($sql))
			{
				while($row = $results->FetchRow())
				{
					$row = PSUTools::cleanKeys('','',$row);
					$data[$row['pidm']] = $row;
				}//end while
			}//end if
			return $data;
		}//end if
		else
		{
			return PSUTools::cleanKeys('','',$this->_ADOdb->GetRow("SELECT * FROM ps_as_student_demographics WHERE pidm=$pid"));
		}//end else
	}//end getDemographics

	/**
	 * getHolds returns an array of holds on a student's account
	 * replaced with $person->student->holds
	 *
	 * @since		version 1.0.0
	 * @param  	string $pid Banner pidm
	 * @param  	string $date Date to check
	 * @param		string $date_format Format of date
	 * @return	array
	 */
	function getHolds($pid,$params='')
	{
		if(!is_array($params))
		{
			parse_str($params,$params);
		}//end if
	
		$data = array();
		$sql = "BEGIN :cursorvar := gb_hold.f_query_all(p_pidm => ".$pid;
		
		if(is_array($params))
		{
			foreach($params as $key=>$param)
			{
				$sql .= ",p_".$key."=>";
				if(preg_match('/\_date/',$key))
				{
					$sql .= (strtolower($param)=='sysdate' || strtolower($param)=='null')?$param:"TO_DATE('".$param."','DD-MON-YY')";
				}//end if
				else
				{
					$sql .= "'".$param."'";
				}//end else
			}//end foreach
		}//end if
		
		$sql .= "); END;";

		if($results = $this->_ADOdb->ExecuteCursor($sql, 'cursorvar'))
		{
			while($row = $results->FetchRow())
			{
				$row = PSUTools::cleanKeys('sprhold_','r_',$row);
				
				$data[] = $row;
			}//end while
		}//end if
		
		return $data;

/*
		$date=($date && $date!='sysdate')?"TO_DATE('$date','$date_format')":'sysdate';

		$data=array();
		$query="SELECT *
							FROM sprhold
						 WHERE sprhold_pidm=$pid
							 AND $date BETWEEN sprhold_from_date AND sprhold_to_date";
		if($results=$this->_ADOdb->Execute($query))
		{
			while($row=$results->FetchRow())
			{
				$row=$this->cleanKeys('sprhold_','r_',$row);
				$data[]=$row;
			}//end while
		}//end if
		return $data;*/
	}//end getHolds

	/**
	 * getMajors returns an array of student majors
	 * replaced with $person->student->ug/gr/etc->curriculum
	 *
	 * @since		version 1.0.0
	 * @param  	string $pid Banner pidm
	 * @param  	string $termcode Termcode to check (default is the globally defined termcode)
	 * @return	array
	 */
	function getMajors($pid,$termcode='')
	{
		$termcode=($termcode)?$termcode:$this->termcode;
		$majors=array();
		$query="SELECT s1.sgbstdn_majr_code_1,
		               f_student_get_desc('STVMAJR',s1.sgbstdn_majr_code_1,30) sgbstdn_majr_desc_1,
									 s1.sgbstdn_majr_code_2,
		               f_student_get_desc('STVMAJR',s1.sgbstdn_majr_code_2,30) sgbstdn_majr_desc_2,
									 s1.sgbstdn_majr_code_2,
		               f_student_get_desc('STVMAJR',s1.sgbstdn_majr_code_1_2,30) sgbstdn_majr_desc_1_2,
									 s1.sgbstdn_majr_code_2,
		               f_student_get_desc('STVMAJR',s1.sgbstdn_majr_code_2_2,30) sgbstdn_majr_desc_2_2 
							FROM sgbstdn s1
						 WHERE s1.sgbstdn_pidm=$pid
							 AND s1.sgbstdn_levl_code='{$this->levl}'
							 AND s1.sgbstdn_term_code_eff=
											(SELECT max(s2.sgbstdn_term_code_eff) 
												 FROM sgbstdn s2 
												WHERE s2.sgbstdn_pidm=s1.sgbstdn_pidm 
													AND s2.sgbstdn_term_code_eff<='$termcode' 
													AND s2.sgbstdn_levl_code=s1.sgbstdn_levl_code)";
		if($results=$this->_ADOdb->Execute($query))
		{
			while($row=$results->FetchRow())
			{
				$row=$this->cleanKeys('sgbstdn_','r_',$row);
				if($row['r_majr_code_1'])
				{
					$majors[]=array('code'=>$row['r_majr_code_1'],'desc'=>$row['r_majr_desc_1']);
				}//end if
				if($row['r_majr_code_2'])
				{
					$majors[]=array('code'=>$row['r_majr_code_2'],'desc'=>$row['r_majr_desc_2']);
				}//end if
				if($row['r_majr_code_1_2'])
				{
					$majors[]=array('code'=>$row['r_majr_code_1_2'],'desc'=>$row['r_majr_desc_1_2']);
				}//end if
				if($row['r_majr_code_2_2'])
				{
					$majors[]=array('code'=>$row['r_majr_code_2_2'],'desc'=>$row['r_majr_desc_2_2']);
				}//end if
			}//end while
		}//end if
		return $majors;
	}//end getMajors

	/**
	 * getMinors returns an array of student minors
	 * replaced with $person->student->ug/gr/etc->curriculum
	 *
	 * @since		version 1.0.0
	 * @param  	string $pid Banner pidm
	 * @param  	string $termcode Termcode to check (default is the globally defined termcode)
	 * @return	array
	 */
	function getMinors($pid,$termcode='')
	{
		$termcode=($termcode)?$termcode:$this->termcode;
		$minors=array();
		$query="SELECT s1.sgbstdn_majr_code_minr_1,
		               f_student_get_desc('STVMAJR',s1.sgbstdn_majr_code_minr_1,30) sgbstdn_majr_desc_minr_1,
									 s1.sgbstdn_majr_code_minr_1_2,
		               f_student_get_desc('STVMAJR',s1.sgbstdn_majr_code_minr_1_2,30) sgbstdn_majr_desc_minr_1_2,
									 s1.sgbstdn_majr_code_minr_2,
		               f_student_get_desc('STVMAJR',s1.sgbstdn_majr_code_minr_2,30) sgbstdn_majr_desc_minr_2,
									 s1.sgbstdn_majr_code_minr_2_2,
		               f_student_get_desc('STVMAJR',s1.sgbstdn_majr_code_minr_2_2,30) sgbstdn_majr_desc_minr_2_2 
							FROM sgbstdn s1
						 WHERE s1.sgbstdn_pidm=$pid
							 AND s1.sgbstdn_levl_code='{$this->levl}'
							 AND s1.sgbstdn_term_code_eff=
											(SELECT max(s2.sgbstdn_term_code_eff) 
												 FROM sgbstdn s2 
												WHERE s2.sgbstdn_pidm=s1.sgbstdn_pidm 
													AND s2.sgbstdn_term_code_eff<='$termcode' 
													AND s2.sgbstdn_levl_code=s1.sgbstdn_levl_code)";
		if($results=$this->_ADOdb->Execute($query))
		{
			while($row=$results->FetchRow())
			{
				$row=$this->cleanKeys('sgbstdn_','r_',$row);
				if($row['r_majr_code_minr_1'])
				{
					$minors[]=array('code'=>$row['r_majr_code_minr_1'],'desc'=>$row['r_majr_desc_minr_1']);
				}//end if
				if($row['r_majr_code_minr_1_2'])
				{
					$minors[]=array('code'=>$row['r_majr_code_minr_1_2'],'desc'=>$row['r_majr_desc_minr_1_2']);
				}//end if
				if($row['r_majr_code_minr_2'])
				{
					$minors[]=array('code'=>$row['r_majr_code_minr_2'],'desc'=>$row['r_majr_desc_minr_2']);
				}//end if
				if($row['r_majr_code_minr_2_2'])
				{
					$minors[]=array('code'=>$row['r_majr_code_minr_2_2'],'desc'=>$row['r_majr_desc_minr_2_2']);
				}//end if
			}//end while
		}//end if
		return $minors;
	}//end getMinors

	/**
	 * getOptions returns an array of student options
	 * replaced with $person->student->ug/gr/etc->curriculum
	 *
	 * @since		version 1.0.0
	 * @param  	string $pid Banner pidm
	 * @param  	string $termcode Termcode to check (default is the globally defined termcode)
	 * @return	array
	 */
	function getOptions($pid,$termcode='')
	{
		$termcode=($termcode)?$termcode:$this->termcode;
		$options=array();
		$query="SELECT s1.sgbstdn_majr_code_conc_1,
		               f_student_get_desc('STVMAJR',s1.sgbstdn_majr_code_conc_1,30) sgbstdn_majr_desc_conc_1,
									 s1.sgbstdn_majr_code_conc_1_2,
		               f_student_get_desc('STVMAJR',s1.sgbstdn_majr_code_conc_1_2,30) sgbstdn_majr_desc_conc_1_2,
									 s1.sgbstdn_majr_code_conc_1_3,
		               f_student_get_desc('STVMAJR',s1.sgbstdn_majr_code_conc_1_3,30) sgbstdn_majr_desc_conc_1_3,
									 s1.sgbstdn_majr_code_conc_2,
		               f_student_get_desc('STVMAJR',s1.sgbstdn_majr_code_conc_2,30) sgbstdn_majr_desc_conc_2,
									 s1.sgbstdn_majr_code_conc_2_2,
		               f_student_get_desc('STVMAJR',s1.sgbstdn_majr_code_conc_2_2,30) sgbstdn_majr_desc_conc_2_2,
									 s1.sgbstdn_majr_code_conc_2_3,
		               f_student_get_desc('STVMAJR',s1.sgbstdn_majr_code_conc_2_2,30) sgbstdn_majr_desc_conc_2_3
							FROM sgbstdn s1
						 WHERE s1.sgbstdn_pidm=$pid
							 AND s1.sgbstdn_levl_code='{$this->levl}'
							 AND s1.sgbstdn_term_code_eff=
											(SELECT max(s2.sgbstdn_term_code_eff) 
												 FROM sgbstdn s2 
												WHERE s2.sgbstdn_pidm=s1.sgbstdn_pidm 
													AND s2.sgbstdn_term_code_eff<='$termcode' 
													AND s2.sgbstdn_levl_code=s1.sgbstdn_levl_code)";
		if($results=$this->_ADOdb->Execute($query))
		{
			while($row=$results->FetchRow())
			{
				$row=$this->cleanKeys('sgbstdn_','r_',$row);
				if($row['r_majr_code_conc_1'])
				{
					$options[]=array('code'=>$row['r_majr_code_conc_1'],'desc'=>$row['r_majr_desc_conc_1']);
				}//end if
				if($row['r_majr_code_conc_1_2'])
				{
					$options[]=array('code'=>$row['r_majr_code_conc_1_2'],'desc'=>$row['r_majr_desc_conc_1_2']);
				}//end if
				if($row['r_majr_code_conc_1_3'])
				{
					$options[]=array('code'=>$row['r_majr_code_conc_1_3'],'desc'=>$row['r_majr_desc_conc_1_3']);
				}//end if
				if($row['r_majr_code_conc_2'])
				{
					$options[]=array('code'=>$row['r_majr_code_conc_2'],'desc'=>$row['r_majr_desc_conc_2']);
				}//end if
				if($row['r_majr_code_conc_2_2'])
				{
					$options[]=array('code'=>$row['r_majr_code_minr_2_2'],'desc'=>$row['r_majr_desc_conc_2_2']);
				}//end if
				if($row['r_majr_code_conc_2_3'])
				{
					$options[]=array('code'=>$row['r_majr_code_minr_2_3'],'desc'=>$row['r_majr_desc_conc_2_3']);
				}//end if
			}//end while
		}//end if
		return $options;
	}//end getOptions

	/**
	 * returns Credit information for a student
	 * @deprecated  replaced with $person->student->ug/gr/etc->hours_earned
	 *
	 * @since		version 1.0.0
	 * @param  	string $pid Banner pidm
	 * @param  	string $levl Student Level e.g. UG, GR, etc.
	 * @param  	string $type Credit Type: I, O, or T
	 * @return	array
	 */
	function getOverallCredits($pid,$termcode='',$level='')
	{
		$student_data = $this->getStudentData($pid,$termcode,$level);

		return $student_data['r_hours_earned'];
	}//end getOverallCredits

	/**
	 * getOverallGPA returns GPA information for a student
	 * @deprecated  replaced with $person->student->ug/gr/etc->gpa
	 *
	 * @since		version 1.0.0
	 * @param  	string $pid Banner pidm
	 * @param  	string $levl Student Level e.g. UG, GR, etc.
	 * @param  	string $type GPA Type: I (institutional course gpa), O (overall course gpa), or T (transfer course gpa)
	 * @return	array
	 */
	function getOverallGPA($pid,$levl='',$type='O')
	{
		$levl=($levl)?$levl:$this->levl;

		$data=array();
		$query="SELECT *
							FROM shrlgpa
						 WHERE shrlgpa_pidm=$pid
							 AND shrlgpa_levl_code='$levl'
							 AND shrlgpa_gpa_type_ind='$type'";

		if($row=$this->_ADOdb->GetRow($query))
		{
				$row=$this->cleanKeys('shrlgpa_','r_',$row);
				$data=$row;
		}//end if

		return $data;
	}//end getOverallGPA

	/**
	 * getRegistrationEligibility returns a student's SFBETRM (registration eligibility) record
	 *
	 * @since		version 1.0.0
	 * @param  	string $pid Banner pidm
	 * @param  	string $termcode Termcode to check (default is the globally defined termcode)
	 * @return	array
	 */
	function getRegistrationEligibility($pid,$termcode='')
	{
		$termcode=($termcode)?$termcode:$this->termcode;
		$data=array();
		$query="SELECT *
							FROM sfbetrm
						 WHERE sfbetrm_pidm=$pid
							 AND sfbetrm_term_code='$termcode'";
		if($row=$this->_ADOdb->GetRow($query))
		{
				$row=$this->cleanKeys('sfbetrm_','r_',$row);
				$data=$row;
		}//end if

		return $data;
	}//end getRegistrationEligibility

	/**
	 * getSchedule returns the student's schedule as an array of CRNs 
	 * @deprecated replaced by $person->student->schedule( $term );
	 *
	 * @since		version 1.0.0
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
								FROM sfrstcr
							 WHERE sfrstcr_pidm=$pid
								 AND sfrstcr_rsts_code IN('RE','RW','AU')
								 AND sfrstcr_term_code='$term'";
			if($results=$this->_ADOdb->Execute($query))
			{
				while($row=$results->FetchRow())
				{
					$row=$this->cleanKeys('sfrstcr_','r_',$row);
					$data[]=$row;
				}//end while
			}//end if
			$schedule[$term]=$data;
		}//end foreach
		return $schedule;
	}//end getSchedule

	/**
	 * getResidentialData
	 *
	 * returns an array of student room assignments
	 *
	 * @since		version 1.0.0
	 * @param  	string $pid Banner pidm
	 * @param  	string $termcode Termcode to check (default is the globally defined termcode)
	 * @return	array
	 */
	function getResidentialData($pidm,$termcode='')
	{
		$data = array();
		
		$termcode=($termcode)?$termcode:$this->termcode;
		
		if(!is_array($termcode)) $terms = array($termcode);
		else $terms = $termcode;
		
		$sql="SELECT stvbldg_desc, slrrasg_room_number, slrrasg_bldg_code, slrrasg_term_code
						FROM slrrasg, stvbldg
					WHERE slrrasg_pidm = ".$pidm."
		 and slrrasg_bldg_code = stvbldg_code
		 and slrrasg_ascd_code = 'AC'
		 and slrrasg_term_code in ('".implode("','",$terms)."')";
		
		if($results=$this->_ADOdb->Execute($sql)) 
		{
			for($num=0;$row=$results->FetchRow();$num++) 
			{
				$row = PSUTools::cleanKeys(array('stvbldg_','slrrasg_'),'r_',$row);
				$data[$row['r_term_code']] = $row;
			}//end for
		}//end if
		return $data;
	}//end getResidentialData

	/**
	 * getSportComments returns comments on a student's sport participation
	 *
	 * @since		version 1.0.0
	 * @param  	string $pid Banner pidm
	 * @param  	string $sport Banner sport code
	 * @param  	string $termcode Termcode to check (default is the globally defined termcode)
	 * @return	array
	 */
	function getSportComments($pid,$sport,$termcode='')
	{
		$termcode=($termcode)?$termcode:$this->termcode;
		$data=array();
		$query="SELECT *
							FROM sgrcmnt
						 WHERE sgrcmnt_actc_code='$sport'
							 AND sgrcmnt_pidm=$pid
							 AND sgrcmnt_term_code='$termcode'
						 ORDER BY sgrcmnt_seq_no";
		if($results=$this->_ADOdb->Execute($query))
		{
			while($row=$results->FetchRow())
			{
				$row=$this->cleanKeys('sgrcmnt_','r_',$row);
				$data[]=$row;
			}//end while
		}//end if
		return $data;
	}//end getSportComments

	/**
	 * getSportRoster returns a sport roster (SGRSPRT records)
	 *
	 * @since		version 1.0.0
	 * @param  	string $actc_code Banner sport code
	 * @param  	string $termcode Termcode to check (default is the globally defined termcode)
	 * @return	array
	 */
	function getSportRoster($actc_code,$termcode='')
	{
		$termcode=($termcode)?$termcode:$this->termcode;

		$data=array();
		$query="SELECT sgrsprt.*
							FROM sgrsprt,spriden
						 WHERE sgrsprt_actc_code='$actc_code'
							 AND sgrsprt_term_code='$termcode'
							 AND spriden_pidm=sgrsprt_pidm
							 AND spriden_change_ind is null
						ORDER BY spriden_last_name,spriden_first_name,spriden_mi";
		if($results=$this->_ADOdb->Execute($query))
		{
			while($row=$results->FetchRow())
			{
				$row=$this->cleanKeys('sgrsprt_','r_',$row);
				$data[]=$row;
			}//end while
		}//end if
		return $data;
	}//end getSportRoster

	/**
	 * getSports returns a list of sports a student participates in
	 *
	 * @since		version 1.0.0
	 * @param  	string $pid Banner pidm
	 * @param  	string $termcode Termcode to check (default is the globally defined termcode)
	 * @return	array
	 */
	function getSports($pid,$termcode='')
	{
		$termcode=($termcode)?$termcode:$this->termcode;

		$data=array();
		$query="SELECT *
							FROM sgrsprt
						 WHERE sgrsprt_pidm=$pid
							 AND sgrsprt_term_code='$termcode'";
		if($results=$this->_ADOdb->Execute($query))
		{
			while($row=$results->FetchRow())
			{
				$row=$this->cleanKeys('sgrsprt_','r_',$row);
				$data[]=$row;
			}//end while
		}//end if
		return $data;
	}//end getSports

	/**
	 * getStudentData returns student data from SGBSTDN
	 *
	 * @since		version 1.0.0
	 * @param  	string $pid Banner pidm
	 * @param  	string $termcode Termcode to check (default is the globally defined termcode)
	 * @param  	string $levl Student Level e.g. UG, GR, etc.
	 * @return	array
	 */
	function getStudentData($pid,$termcode='',$levl='',$pure = false)
	{
		$termcode=($termcode)?$termcode:$this->termcode;
		$levl=($levl)?$levl:$this->levl;

		$data=array();
		$query="SELECT s1.*";
		
		if(!$pure)
		{
		 $query.=",substr(f_class_calc_fnc(s1.sgbstdn_pidm,s1.sgbstdn_levl_code,s1.sgbstdn_term_code_eff),1,2) as sgbstdn_clas_code,
		               to_number(substr(f_split_fields(substr(f_concat_as_of_cum_gpa(s1.sgbstdn_pidm,s1.sgbstdn_term_code_eff,s1.sgbstdn_levl_code,'O'),1,42),2),1,5)) as sgbstdn_hours_earned";
		}
		
		$query.="FROM sgbstdn s1
						 WHERE sgbstdn_pidm=$pid
							 AND s1.sgbstdn_levl_code='$levl'
							 AND s1.sgbstdn_term_code_eff=
											(SELECT max(s2.sgbstdn_term_code_eff) 
												 FROM sgbstdn s2 
												WHERE s2.sgbstdn_pidm=s1.sgbstdn_pidm 
													AND s2.sgbstdn_term_code_eff<='$termcode' 
													AND s2.sgbstdn_levl_code=s1.sgbstdn_levl_code)";
		if($row=$this->_ADOdb->GetRow($query))
		{
				$row=$this->cleanKeys('sgbstdn_','r_',$row);
				$data=$row;
		}//end if

		return $data;
	}//end getStudentData

	/**
	 * getTermCredits returns a student's term credits
	 * @deprecated  Replaced by $person->student->ug/gr/etc->term_credits
	 *
	 * @since		version 1.0.0
	 * @param  	string $pid Banner pidm
	 * @param  	string $levl Student Level e.g. UG, GR, etc.
	 * @param  	string $termcode Termcode to check (default is the globally defined termcode)
	 * @return	array
	 */
	function getTermCredits($pid,$levl='',$termcode='')
	{
		$termcode=($termcode)?$termcode:$this->termcode;
		$levl=($levl)?$levl:$this->levl;
		$data=array();
		$query="SELECT sum(sfrstcr_credit_hr) sfrstcr_credit_hr 
							FROM sfrstcr,stvrsts 
						 WHERE sfrstcr_pidm=$pid 
							 AND sfrstcr_term_code='$termcode' 
							 AND sfrstcr_rsts_code=stvrsts_code 
							 AND stvrsts_incl_sect_enrl='Y'";

		if($row=$this->_ADOdb->GetRow($query))
		{
				$row=$this->cleanKeys('sfrstcr_','r_',$row);
				$data=$row;
		}//end if

		return $data;
	}//end getGPA

	/**
	 * getTermCredits returns a student's term GPA
	 * @deprecated  Replaced by $person->student->ug/gr/etc->gpa
	 *
	 * @since		version 1.0.0
	 * @param  	string $pid Banner pidm
	 * @param  	string $levl Student Level e.g. UG, GR, etc.
	 * @param  	string $termcode Termcode to check (default is the globally defined termcode)
	 * @param  	string $type GPA Type: I, O, or T
	 * @return	array
	 */
	function getTermGPA($pid,$levl='',$termcode='',$type='I')
	{
		$termcode=($termcode)?$termcode:$this->termcode;
		$levl=($levl)?$levl:$this->levl;
		$data=array();
		$query="SELECT *
							FROM shrtgpa
						 WHERE shrtgpa_pidm=$pid
							 AND shrtgpa_gpa_type_ind='$type'
							 AND shrtgpa_levl_code='$levl'
							 AND shrtgpa_term_code='$termcode'";

		if($row=$this->_ADOdb->GetRow($query))
		{
				$row=$this->cleanKeys('shrtgpa_','r_',$row);
				$data=$row;
		}//end if

		return $data;
	}//end getGPA

	/**
	 * getWebRegistrationData returns web registration data for a student
	 * @deprecated  replaced by $person->student->web_registration( $term );
	 *
	 * @since		version 1.0.0
	 * @param  	string $pid Banner pidm
	 * @param  	string $termcode Termcode to check (default is the globally defined termcode)
	 * @return	array
	 */
	function getWebRegistrationData($pid,$termcode='')
	{
		$termcode=($termcode)?$termcode:$this->termcode;

		$data=array();
		$query="SELECT sprapin_pin sfrwctl_pin,
									 sfrwctl_begin_date,
									 sfrwctl_end_date,
									 sfrwctl_hour_begin,
									 sfrwctl_hour_end
							FROM sprapin,
									 sfbrgrp,
									 sfbwctl,
									 sfrwctl
						 WHERE sprapin_pidm=$pid
							 AND sprapin_pidm=sfbrgrp_pidm
							 AND sfbwctl_rgrp_code=sfbrgrp_rgrp_code
							 AND sfrwctl_priority=sfbwctl_priority
							 AND sprapin_term_code ='$termcode'
							 AND sfbwctl_term_code=sprapin_term_code
							 AND sfrwctl_term_code=sprapin_term_code
							 AND sfbrgrp_term_code=sprapin_term_code";

		if($row=$this->_ADOdb->GetRow($query))
		{
				$row=$this->cleanKeys('sfrwctl_','r_',$row);
				$data=$row;
		}//end if

		return $data;
	}//end getWebRegistrationData

	/**
	 * isLevel checks if the level of the student matches the level passed in.
	 *
	 * @since		version 1.0.0
	 * @param  	string $pid Banner pidm
	 * @param  	string $levl Student Level e.g. UG, GR, etc.
	 * @param  	string $termcode Termcode to check (default is the globally defined termcode)
	 * @return	boolean
	 */
	function isLevel($pidm,$level,$termcode='')
	{
		$termcode=($termcode)?$termcode:$this->termcode;

		$student_data=$this->getStudentData($pidm,$termcode,$level);
		if(count($student_data)>0)
			return true;
		else
			return false;
	}//end isLevel

/**
 * gets the current term code
 * 
 * @param string $levl is either GR (graduate) or UG (undergraduate)
 * @param string $month e.g. JUN
 * @param string $year e.g. 2008
 * @param string $day e.g. 23
 * @return string of termcode
 */ 
	function reslifeCurrentTerm($levl, $month=null, $year=null, $day=null)
	{
		if(!$month) $month = date('m');
		if(!$year) $year = date('Y');
		if(!$day) $day = date('d');
		
		$searchdate = $year."-".$month."-".$day;

		if(strtoupper($levl)=='UG')
		{
			$ugsql = "SELECT stvterm_code FROM stvterm WHERE '$searchdate' >=trunc(stvterm_housing_start_date) AND '$searchdate' <= trunc(stvterm_housing_end_date) AND substr(stvterm_code,5,2) IN ('10','20','30','40','50') AND substr(stvterm_code,0,2) = '20'";

			$trm = $this->_ADOdb->GetOne($ugsql);

			if(!$trm)
			{
				switch($month)
				{
					case 'JAN':
						$trm = $year.'20';
						break;
					case 'FEB':
						$trm = $year.'30';
						break;
					case 'MAR':
						$trm = $year.'30';
						break;
					case 'APR':
						$trm = $year.'30';
						break;
					case 'MAY':
						$trm = $year.'40';
						break;
					case 'JUN':
						$trm = $year.'40';
						break;
					case 'JUL':
						$trm = $year.'40';
						break;
					case 'AUG':
						$trm = ($year + 1).'10';
						break;
					case 'SEP':
						$trm = ($year + 1).'10';
						break;
					case 'OCT':
						$trm = ($year + 1).'10';
						break;
					case 'NOV':
						$trm = ($year + 1).'10';
						break;
					case 'DEC':
						$trm = ($year + 1).'10';
						break;
				}
			}
		}

		if(strtoupper($levl)=='GR')
		{
			$grsql = "SELECT stvterm_code FROM stvterm WHERE '$searchdate' >=trunc(stvterm_housing_start_date) AND '$searchdate' <= trunc(stvterm_housing_end_date) AND substr(stvterm_code,5,2) IN ('91','92','93','94')";

			$trm = $this->_ADOdb->GetOne($grsql);

			if(!$trm)
			{
				switch($month)
				{
					case 'JAN':
						$trm = $year.'92';
						break;
					case 'FEB':
						$trm = $year.'93';
						break;
					case 'MAR':
						$trm = $year.'93';
						break;
					case 'APR':
						$trm = $year.'93';
						break;
					case 'MAY':
						$trm = $year.'93';
						break;
					case 'JUN':
						$trm = $year.'94';
						break;
					case 'JUL':
						$trm = $year.'94';
						break;
					case 'AUG':
						$trm = ($year + 1).'91';
						break;
					case 'SEP':
						$trm = ($year + 1).'91';
						break;
					case 'OCT':
						$trm = ($year + 1).'91';
						break;
					case 'NOV':
						$trm = ($year + 1).'91';
						break;
					case 'DEC':
						$trm = ($year + 1).'91';
						break;
				}
			}
		}
		
		return $trm;
	}//end reslifeCurrentTerm

	/**
	 * flags a student as sighted
	 *
	 * @since		version 1.0.1
	 * @param  	string $pid Banner pidm
	 * @param  	string $how_code code indicating how the student was sighted e.g. CL, etc.
	 * @return	boolean
	 */
	function sightStudent($pidm,$how_code, $date = 'sysdate')
	{
		$ok = false;
		if($pidm && ($how_code=='MC' || $how_code == 'RW' || substr($_SERVER['REMOTE_ADDR'],0,7)=='158.136'))
		{
			if($date != 'sysdate')
			{
				$date = "TO_DATE('DD-MON-RRRR','".strtoupper(date('d-M-Y',strtotime($date)))."')";
			}//end if

			$plus_one_week = strtotime('+1 week');
			$sql = "
				UPDATE sxbconf 
				   SET sxbconf_sighted = :how_code, 
					   sxbconf_sighted_date = ".$date." 
			     WHERE sxbconf_sighted is null 
				   AND sxbconf_pidm_key = :pidm 
				   AND sxbconf_term_code_key = f_get_currentterm('UG','".strtoupper(date('M',$plus_one_week))."','".date('Y',$plus_one_week)."','".date('d',$plus_one_week)."')");

			$args = array(
				'how_code' => $how_code,
				'pidm' => $pidm,
			);

			$ok = \PSU::db('banner')->Execute($sql, $args);
		}
		return $ok;
	}// end sightStudent
}//end BannerStudent
