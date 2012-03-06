<?php
/**
 * BannerCatalog.class.php
 *
 * === Modification History ===<br/>
 * 1.0.0  18-may-2005  [mtb]  original<br/>
 *
 * @package 		PSUBannerAPI
 */

/**
 * BannerCatalog.class.php
 *
 * Banner API
 *
 * @version		1.0.0
 * @module		BannerCatalog.class.php
 * @author		Matthew Batchelder <mtbatchelder@plymouth.edu>
 * @copyright 2005, Plymouth State University, ITS
 */ 

require_once('BannerGeneral.class.php');    //idm class
require_once('BannerCourse.class.php');    //idm class

require_once('PSUTools.class.php');

class BannerCatalog extends BannerCourse
{

	/**
	 * BannerCatalog
	 *
	 * check the connection to the database
	 *
	 * @access	public
	 * @param	adodb $adodb reference to adodb object
	 */
	function BannerCatalog(&$adodb)
	{
		if($adodb)
		{
			$this->BannerCourse($adodb);
		}//end if
		else
		{
			echo 'Not Connected To Database.';
		}//end else
	}//end BannerCatalog
	
	/**
	 * getCourseDepartment
	 *
	 * get the Department associated with a course...note, does not actually return anything
	 *
	 * @access	public
	 * @param	string $subj_code the subject code of the couse
	 * @param	string $crse_number the course number
	 * @param	string $termcode the term code for the course
	 */
	function getCourseDepartment($subj_code,$crse_number,$termcode='')
	{
		$termcode=($termcode)?$termcode:$this->termcode;
		$query="SELECT a.scbcrse_dept_code,stvdept_desc FROM scbcrse a,stvdept  
		         WHERE a.scbcrse_dept_code=stvdept_code 
						  AND a.scbcrse_subj_code='$subj_code' 
							AND a.scbcrse_crse_numb='$crse_number' 
							AND a.scbcrse_csta_code='A'
							AND a.scbcrse_eff_term IN(
								SELECT MAX(b.scbcrse_eff_term) FROM scbcrse b
									WHERE b.scbcrse_eff_term <= '$termcode'
									AND b.scbcrse_subj_code = a.scbcrse_subj_code
									AND b.scbcrse_crse_numb = a.scbcrse_crse_numb
									AND b.scbcrse_csta_code='A'
								)";
		if($row=$this->_ADOdb->GetRow($query))
		{
				$row=PSUTools::cleanKeys(array('scbcrse_','stvdept_'),array('r_','r_'),$row);
		}//end if
	}//end getCourseDepartment

	/**
	 * getCoursesByDepartment
	 *
	 * get the courses associated with a department
	 *
	 * @access	public
	 * @param	string $dept_code code for department
	 * @param	string $order order to be returned
	 * @param	string $termcode the term code for the courses
	 * @return	array  courses returned by the getDepartmentCourses function
	 */
	function getCoursesByDepartment($dept_code,$order='',$termcode='')
	{
		return getDepartmentCourses($dept_code,$order,$termcode);
	}//end getCoursesByDepartment

	/**
	 * getConcentrations
	 *
	 * get the concentrations/majors
	 *
	 * @access	public
	 * @param	string $dept_code code for department
	 * @param	string $termcode the term code for the courses
	 * @return	array $data of concentrations. Array has fields r_ccode, r_desc, r_dept_code
	 */
	function getConcentrations($dept_code='',$termcode='')
	{
		$termcode=($termcode)?$termcode:$this->termcode;
		$department=($dept_code)?"AND m1.sorcmjr_dept_code='$dept_code'":'';
		$data=array();
		$query="SELECT stvmajr_code, stvmajr_desc, m1.sorcmjr_dept_code
							FROM stvmajr, sorccon s1,sorcmjr m1
							WHERE s1.sorccon_majr_code_conc=stvmajr_code 
							AND stvmajr_valid_concentratn_ind='Y'
							AND s1.sorccon_term_code_eff=(SELECT max(s2.sorccon_term_code_eff) FROM sorccon s2 WHERE s2.sorccon_curr_rule=s1.sorccon_curr_rule AND s2.sorccon_term_code_eff<='$termcode')
							AND s1.sorccon_adm_ind='Y'
							AND s1.sorccon_cmjr_rule=m1.sorcmjr_cmjr_rule
							AND m1.sorcmjr_term_code_eff=(SELECT max(m2.sorcmjr_term_code_eff) FROM sorcmjr m2 WHERE m2.sorcmjr_curr_rule=m1.sorcmjr_curr_rule AND m2.sorcmjr_term_code_eff<='$termcode')
							AND stvmajr_code<>'NONE'
							$department
							ORDER BY stvmajr_desc";
		if($results=$this->_ADOdb->Execute($query))
		{
			while($row=$results->FetchRow())
			{
				$row=PSUTools::cleanKeys(array('stvmajr_','sorcmjr_'),array('r_','r_'),$row);
				$data[$row['r_code']]=$row;
			}//end while
		}//end if
		return $data;
	}//end getDepartmentConcentrations

	/**
	 * getCourses
	 *
	 * get courses based on iput
	 *
	 * @access	public
	 * @param	string $dept_code code for department
	 * @param	string $order order to retur results by	 
	 * @param	string $termcode the term code for the courses
	 * @return	array $data of courses has fields r_subj_code and r_crse_numb
	 */
	function getCourses($dept_code='',$order='',$termcode='')
	{
		$termcode=($termcode)?$termcode:$this->termcode;

		$order=($order)?'ORDER BY '.$order:'';
		$department=($dept_code)?"AND a.scbcrse_dept_code='$dept_code'":'';

		$data=array();
		/*
		$query="SELECT a.scbcrse_subj_code,a.scbcrse_crse_numb
						  FROM scbcrse a,scbcrky
						 WHERE a.scbcrse_csta_code='A'
							AND a.scbcrse_eff_term IN(
								SELECT MAX(b.scbcrse_eff_term) FROM scbcrse b
									WHERE b.scbcrse_eff_term <= '$termcode'
									AND b.scbcrse_subj_code = a.scbcrse_subj_code
									AND b.scbcrse_crse_numb = a.scbcrse_crse_numb
									AND b.scbcrse_csta_code='A'
								)
							$department
							AND a.scbcrse_subj_code=scbcrky_subj_code
							AND a.scbcrse_crse_numb=scbcrky_crse_numb
							AND a.scbcrse_eff_term=scbcrky_term_code_start
							AND (scbcrky_term_code_end is null OR scbcrky_term_code_end >= '$termcode')
						 ORDER BY a.scbcrse_subj_code, a.scbcrse_crse_numb";
						 */
		$query="SELECT a.*,
										sdesc.scbdesc_text_narrative r_description,
										sgmod.scrgmod_gmod_code r_gmod_code,
										ssyln.scrsyln_long_course_title r_long_title
						  FROM scbcrse a
										LEFT OUTER JOIN (
														SELECT *
													FROM scbdesc s1
												 WHERE s1.scbdesc_term_code_eff=(SELECT max(s2.scbdesc_term_code_eff) FROM scbdesc s2 WHERE s2.scbdesc_subj_code=s1.scbdesc_subj_code AND s2.scbdesc_crse_numb=s1.scbdesc_crse_numb AND s2.scbdesc_term_code_eff<=:term)
										) sdesc
									 ON sdesc.scbdesc_subj_code=a.scbcrse_subj_code
									 AND sdesc.scbdesc_crse_numb=a.scbcrse_crse_numb
										LEFT OUTER JOIN (
													SELECT *
														FROM scrgmod s1
													 WHERE s1.scrgmod_eff_term=(SELECT max(s2.scrgmod_eff_term) FROM scrgmod s2 WHERE s2.scrgmod_subj_code=s1.scrgmod_subj_code AND s2.scrgmod_crse_numb=s1.scrgmod_crse_numb AND s2.scrgmod_eff_term<=:term)
										) sgmod
										 ON sgmod.scrgmod_subj_code=a.scbcrse_subj_code
										 AND sgmod.scrgmod_crse_numb=a.scbcrse_crse_numb
										LEFT OUTER JOIN (
													SELECT *
														FROM scrsyln s1
													 WHERE s1.scrsyln_term_code_eff=(SELECT max(s2.scrsyln_term_code_eff) FROM scrsyln s2 WHERE s2.scrsyln_subj_code=s1.scrsyln_subj_code AND s2.scrsyln_crse_numb=s1.scrsyln_crse_numb AND s2.scrsyln_term_code_eff<=:term AND (s2.scrsyln_term_code_end >:term OR s2.scrsyln_term_code_end is null))
										) ssyln
										 ON ssyln.scrsyln_subj_code=a.scbcrse_subj_code
										 AND ssyln.scrsyln_crse_numb=a.scbcrse_crse_numb
										LEFT OUTER JOIN (
													SELECT *
														FROM scrlevl s1
													 WHERE s1.scrlevl_eff_term=(SELECT max(s2.scrlevl_eff_term) FROM scrlevl s2 WHERE s2.scrlevl_subj_code=s1.scrlevl_subj_code AND s2.scrlevl_crse_numb=s1.scrlevl_crse_numb AND s2.scrlevl_eff_term<=:term)
										) slevl
										 ON slevl.scrlevl_subj_code=a.scbcrse_subj_code
										 AND slevl.scrlevl_crse_numb=a.scbcrse_crse_numb
						 WHERE a.scbcrse_csta_code='A'
							AND a.scbcrse_eff_term IN(
								SELECT MAX(b.scbcrse_eff_term) FROM scbcrse b
									WHERE b.scbcrse_eff_term <= :term
									AND b.scbcrse_subj_code = a.scbcrse_subj_code
									AND b.scbcrse_crse_numb = a.scbcrse_crse_numb
								)
							$department
						 ORDER BY a.scbcrse_subj_code, a.scbcrse_crse_numb";
		if($results=$this->_ADOdb->Execute($query, array('term'=>$termcode)))
		{
			while($row=$results->FetchRow())
			{
				$row=PSUTools::cleanKeys(array('scbcrse_'),array('r_'),$row);
				$row['r_attributes']=$this->getCourseAttributes($row['r_subj_code'],$row['r_crse_numb'],$termcode);
				$row['r_text']=$this->getCourseText($row['r_subj_code'],$row['r_crse_numb'],$termcode);
				$data[]=$row;
			}//end while
		}//end if

		return $data;
	}//end getDepartmentCourses

	/**
	 * getDepartmentFaculty
	 *
	 * get faculty by department
	 *
	 * @access	public
	 * @param	string $dept_code code for department
	 * @param	string $termcode the term code for the courses
	 * @return	array $data of faculty with fields r_pidm, r_id, r_last_name, r_first_name, r_mi
	 */
	function getDepartmentFaculty($dept_code,$termcode='')
	{
		$termcode=($termcode)?$termcode:$this->termcode;

		$data=array();
		$query="SELECT spriden_pidm,spriden_id,spriden_last_name,spriden_first_name,spriden_mi,b1.*
							FROM sirdpcl s1,spriden,sibinst b1
						 WHERE spriden_change_ind is null
							 AND spriden_pidm=s1.sirdpcl_pidm
							 AND sibinst_pidm=spriden_pidm
							 AND s1.sirdpcl_dept_code='$dept_code'
							 AND s1.sirdpcl_term_code_eff=(SELECT max(s2.sirdpcl_term_code_eff) FROM sirdpcl s2 WHERE s2.sirdpcl_pidm=s1.sirdpcl_pidm AND s2.sirdpcl_dept_code=s1.sirdpcl_dept_code AND s2.sirdpcl_term_code_eff<='$termcode') 
							 AND b1.sibinst_term_code_eff=(SELECT max(b2.sibinst_term_code_eff) FROM sibinst b2 WHERE b2.sibinst_pidm=b1.sibinst_pidm AND b2.sibinst_term_code_eff<='$termcode') 
							 AND b1.sibinst_fcst_code='AC'
							 ORDER BY spriden_last_name,spriden_first_name";
		if($results=$this->_ADOdb->Execute($query))
		{
			while($row=$results->FetchRow())
			{
				$row=PSUTools::cleanKeys(array('spriden_','sibinst_'),array('r_','r_'),$row);
				$data[$row['r_pidm']]=$row;
			}//end while
		}//end if
		return $data;
	}//end getDepartmentFaculty

	/**
	 * getMajors
	 *
	 * get majors
	 *
	 * @access	public
	 * @param	string $dept_code code for department
	 * @param	string $termcode the term code for the courses
	 * @return	array $data of majors with fields r_code, r_desc, r_dept_code
	 */
	function getMajors($dept_code='',$termcode='')
	{
		$termcode=($termcode)?$termcode:$this->termcode;
		$department=($dept_code)?"AND s1.sorcmjr_dept_code='$dept_code'":'';

		$data=array();
		$query="SELECT stvmajr_code, stvmajr_desc, s1.sorcmjr_dept_code
							FROM stvmajr, sorcmjr s1
							WHERE s1.sorcmjr_majr_code=stvmajr_code 
							AND stvmajr_valid_major_ind='Y'
							AND s1.sorcmjr_term_code_eff=(SELECT max(s2.sorcmjr_term_code_eff) FROM sorcmjr s2 WHERE s2.sorcmjr_curr_rule=s1.sorcmjr_curr_rule AND s2.sorcmjr_term_code_eff<='$termcode')
							AND s1.sorcmjr_adm_ind='Y'
							$department
							ORDER BY stvmajr_desc";
		if($results=$this->_ADOdb->Execute($query))
		{
			while($row=$results->FetchRow())
			{
				$row=PSUTools::cleanKeys(array('stvmajr_','sorcmjr_'),array('r_','r_'),$row);
				$data[$row['r_code']]=$row;
			}//end while
		}//end if
		return $data;
	}//end getDepartmentMajors

	/**
	 * getMinors
	 *
	 * get Minors
	 *
	 * @access	public
	 * @param	string $termcode the term code for the courses
	 * @return	array $data of minors with fields r_code, r_desc
	 */
	function getMinors($termcode='')
	{
		$termcode=($termcode)?$termcode:$this->termcode;

		$data=array();
		$query="SELECT stvmajr_code, stvmajr_desc
							FROM stvmajr, sorcmjr s1
							WHERE stvmajr_valid_minor_ind='Y'
							ORDER BY stvmajr_desc";
		if($results=$this->_ADOdb->Execute($query))
		{
			while($row=$results->FetchRow())
			{
				$row=PSUTools::cleanKeys(array('stvmajr_'),array('r_'),$row);
				$data[$row['r_code']]=$row;
			}//end while
		}//end if
		return $data;
	}//end getMinors

	/**
	 * getDepartmentOptions
	 *
	 * get options by department
	 *
	 * @access	public
	 * @param	string $dept_code code for department
	 * @param	string $termcode the term code for the courses
	 * @return	array of options returned by getDepartmentConcentrations
	 */
	function getDepartmentOptions($dept_code='',$termcode='')
	{
		$termcode=($termcode)?$termcode:$this->termcode;
		return $this->getDepartmentConcentrations($dept_code,$termcode);
	}//end getDepartmentOptions
}//end BannerCatalog

?>
