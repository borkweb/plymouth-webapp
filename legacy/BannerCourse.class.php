<?php
/**
 * BannerCourse.class.php
 *
 * BannerCourse contains utility functions relating to Banner Course information.
 *
 * @version		1.0.0
 * @author		Matthew Batchelder <mtbatchelder@plymouth.edu>
 * @package   PSUBannerAPI
 * @copyright 2005, Plymouth State University, ITS
 */ 
require_once('PSUTools.class.php');

class BannerCourse
{
	/**
	 * @var ADOdb Banner database connection
	 */
	var $_ADOdb;
	
	/**
	 * @var string contains termcode used for Course queries
	 */
	var $termcode;

	/**
	 * BannerCourse
	 *
	 * make sure that a connection to adodb exists
	 *
	 * @access	public
	 * @param	adodb $adodb reference to adodb connection 
	 */
	function BannerCourse(&$adodb)
	{
			$this->_ADOdb=$adodb;
	}//end BannerCourse

	/**
	 * getCourseAttributes
	 *
	 * get attributes of a course
	 *
	 * @access	public
	 * @param	string $subj_code subject code related to course
	 * @param	string $crse_numb course number of course
	 * @param	string $termcode termcode of course, defaults to ''
	 * @return	array $data of attributes with fields r_code, and r_desc
	 */
	function getCourseAttributes($subj_code,$crse_numb,$termcode='')
	{
		$termcode=($termcode)?$termcode:$this->termcode;

		$data=array();
		$query="SELECT stvattr_code,stvattr_desc
							FROM scrattr s1,stvattr
						 WHERE s1.scrattr_subj_code='$subj_code'
							 AND s1.scrattr_crse_numb='$crse_numb'
							 AND s1.scrattr_attr_code=stvattr_code
							 AND s1.scrattr_eff_term=(SELECT max(s2.scrattr_eff_term) FROM scrattr s2 WHERE s2.scrattr_subj_code=s1.scrattr_subj_code AND s2.scrattr_crse_numb=s1.scrattr_crse_numb AND s2.scrattr_eff_term<='$termcode') ORDER BY stvattr_code";
		if($results=$this->_ADOdb->Execute($query))
		{
			while($row=$results->FetchRow())
			{
					$row=PSUTools::cleanKeys('stvattr_','r_',$row);
					$data[]=$row;
			}//end if
		}//end if
		return $data;
	}//end getCourseAttributes
	
	/**
	 * getCourseData
	 *
	 * get information about a course
	 *
	 * @access	public
	 * @param	string $subj_code subject code related to course
	 * @param	string $crse_numb course number of course
	 * @param	string $termcode termcode of course, defaults to ''
	 * @return	array $course of course info with fields r_attributes, r_text, r_description, r_long_title, r_grading_mode, r_level
	 */
	function getCourseData($subj_code,$crse_numb,$termcode='')
	{
		$termcode=($termcode)?$termcode:$this->termcode;

		$course=array();
		$query="SELECT *
							FROM scbcrse s1
						 WHERE s1.scbcrse_eff_term=(SELECT max(s2.scbcrse_eff_term) FROM scbcrse s2 WHERE s2.scbcrse_subj_code=s1.scbcrse_subj_code AND s2.scbcrse_crse_numb=s1.scbcrse_crse_numb AND s2.scbcrse_eff_term<='$termcode')
							 AND s1.scbcrse_subj_code='$subj_code'
							 AND s1.scbcrse_crse_numb='$crse_numb'";
		if($row=$this->_ADOdb->GetRow($query))
		{
				$row=PSUTools::cleanKeys(array('scbcrse_'),array('r_'),$row);
				$row['r_attributes']=$this->getCourseAttributes($row['r_subj_code'],$row['r_crse_numb'],$termcode);
				$row['r_text']=$this->getCourseText($row['r_subj_code'],$row['r_crse_numb'],$termcode);
				$row['r_description']=$this->getCourseDescription($row['r_subj_code'],$row['r_crse_numb'],$termcode);
				$row['r_long_title']=$this->getCourseTitle($row['r_subj_code'],$row['r_crse_numb'],$termcode,'long');
				$row['r_grading_mode']=$this->getCourseGradingMode($row['r_subj_code'],$row['r_crse_numb'],$termcode);
				$row['r_level']=$this->getCourseLevel($row['r_subj_code'],$row['r_crse_numb'],$termcode);
				$course=$row;
		}//end if

		return $course;
	}//end getCourseData
	
	/**
	 * getCourseDescription
	 *
	 * get description of course
	 *
	 * @access	public
	 * @param	string $subj_code subject code related to course
	 * @param	string $crse_numb course number of course
	 * @param	string $termcode termcode of course, defaults to ''
	 * @return	string $data of course description
	 */
	function getCourseDescription($subj_code,$crse_numb,$termcode='')
	{
		$termcode=($termcode)?$termcode:$this->termcode;

		$data='';
		$query="SELECT *
							FROM scbdesc s1
						 WHERE s1.scbdesc_term_code_eff=(SELECT max(s2.scbdesc_term_code_eff) FROM scbdesc s2 WHERE s2.scbdesc_subj_code=s1.scbdesc_subj_code AND s2.scbdesc_crse_numb=s1.scbdesc_crse_numb AND s2.scbdesc_term_code_eff<='$termcode')
							 AND s1.scbdesc_subj_code='$subj_code'
							 AND s1.scbdesc_crse_numb='$crse_numb'";
		if($row=$this->_ADOdb->GetRow($query))
		{
				$row=PSUTools::cleanKeys(array('scbdesc_'),array('r_'),$row);
				$data=$row['r_text_narrative'];
		}//end if

		return $data;
	}//end getCourseDescription

	/**
	 * getCourseGradingMode
	 *
	 * get grading mode of course
	 *
	 * @access	public
	 * @param	string $subj_code subject code related to course
	 * @param	string $crse_numb course number of course
	 * @param	string $termcode termcode of course, defaults to ''
	 * @return	string $data of grading mode
	 */
	function getCourseGradingMode($subj_code,$crse_numb,$termcode='')
	{
		$termcode=($termcode)?$termcode:$this->termcode;

		$data='';
		$query="SELECT *
							FROM scrgmod s1
						 WHERE s1.scrgmod_eff_term=(SELECT max(s2.scrgmod_eff_term) FROM scrgmod s2 WHERE s2.scrgmod_subj_code=s1.scrgmod_subj_code AND s2.scrgmod_crse_numb=s1.scrgmod_crse_numb AND s2.scrgmod_eff_term<='$termcode')
							 AND s1.scrgmod_subj_code='$subj_code'
							 AND s1.scrgmod_crse_numb='$crse_numb'";
		if($row=$this->_ADOdb->GetRow($query))
		{
				$row=PSUTools::cleanKeys(array('scrgmod_'),array('r_'),$row);
				$data=$row['r_gmod_code'];
		}//end if

		return $data;
	}//end getCourseGradingMode

	/**
	 * getCourseLevel
	 *
	 * get course level
	 *
	 * @access	public
	 * @param	string $subj_code subject code related to course
	 * @param	string $crse_numb course number of course
	 * @param	string $termcode termcode of course, defaults to ''
	 * @return	string $data of course level
	 */
	function getCourseLevel($subj_code,$crse_numb,$termcode='')
	{
		$termcode=($termcode)?$termcode:$this->termcode;

		$data='';
		$query="SELECT *
							FROM scrlevl s1
						 WHERE s1.scrlevl_eff_term=(SELECT max(s2.scrlevl_eff_term) FROM scrlevl s2 WHERE s2.scrlevl_subj_code=s1.scrlevl_subj_code AND s2.scrlevl_crse_numb=s1.scrlevl_crse_numb AND s2.scrlevl_eff_term<='$termcode')
							 AND s1.scrlevl_subj_code='$subj_code'
							 AND s1.scrlevl_crse_numb='$crse_numb'";
		if($row=$this->_ADOdb->GetRow($query))
		{
				$row=PSUTools::cleanKeys(array('scrlevl_'),array('r_'),$row);
				$data=$row['r_levl_code'];
		}//end if

		return $data;
	}//end getCourseLevel

	/**
	 * getCourseLongTitle
	 *
	 * get the long title of the course
	 *
	 * @access	public
	 * @param	string $subj_code subject code related to course
	 * @param	string $crse_numb course number of course
	 * @param	string $termcode termcode of course, defaults to ''
	 * @return	string of the long course title
	 */
	function getCourseLongTitle($subj_code,$crse_numb,$termcode='')
	{
		$termcode=($termcode)?$termcode:$this->termcode;

		$query="SELECT scrsyln_long_course_title
							FROM scrsyln s1
						 WHERE s1.scrsyln_term_code_eff=(SELECT max(s2.scrsyln_term_code_eff) FROM scrsyln s2 WHERE s2.scrsyln_subj_code=s1.scrsyln_subj_code AND s2.scrsyln_crse_numb=s1.scrsyln_crse_numb AND s2.scrsyln_term_code_eff<='$termcode' AND (s2.scrsyln_term_code_end >'$termcode' OR s2.scrsyln_term_code_end is null))
							 AND s1.scrsyln_subj_code='$subj_code'
							 AND s1.scrsyln_crse_numb='$crse_numb'";

		return $this->_ADOdb->GetOne($query);
	}//end getCourseLongTitle

	/**
	 * getCourseShortTitle
	 *
	 * get the short title of the course
	 *
	 * @access	public
	 * @param	string $subj_code subject code related to course
	 * @param	string $crse_numb course number of course
	 * @param	string $termcode termcode of course, defaults to ''
	 * @return	string of the short course title
	 */
	function getCourseShortTitle($subj_code,$crse_numb,$termcode='')
	{
		$termcode=($termcode)?$termcode:$this->termcode;

		$query="SELECT s1.scbcrse_title
							FROM scbcrse s1
						 WHERE s1.scbcrse_eff_term=(SELECT max(s2.scbcrse_eff_term) FROM scbcrse s2 WHERE s2.scbcrse_subj_code=s1.scbcrse_subj_code AND s2.scbcrse_crse_numb=s1.scbcrse_crse_numb AND s2.scbcrse_eff_term<='$termcode')
							 AND s1.scbcrse_subj_code='$subj_code'
							 AND s1.scbcrse_crse_numb='$crse_numb'";

		return $this->_ADOdb->GetOne($query);
	}//end getCourseShortTitle

	/**
	 * getCourseTitle
	 *
	 * get the title of the course
	 *
	 * @access	public
	 * @param	string $subj_code subject code related to course
	 * @param	string $crse_numb course number of course
	 * @param	string $termcode termcode of course, defaults to ''
	 * @param	string $type of title to retrieve, defaults to best, always returns long
	 * @return	string of the course title
	 */
	function getCourseTitle($subj_code,$crse_numb,$termcode='',$type='best')
	{
		$termcode=($termcode)?$termcode:$this->termcode;

		switch($type)
		{
			case 'best':
				return $this->getCourseLongTitle($subj_code,$crse_numb,$termcode);
				break;
			case 'short':
				return $this->getCourseLongTitle($subj_code,$crse_numb,$termcode);
				break;
			case 'long':
				return $this->getCourseLongTitle($subj_code,$crse_numb,$termcode);
				break;
		}//end switch
	}//end getCourseTitle

	/**
	 * getCourseText
	 *
	 * get the test related to the course
	 *
	 * @access	public
	 * @param	string $subj_code subject code related to course
	 * @param	string $crse_numb course number of course
	 * @param	string $termcode termcode of course, defaults to ''
	 * @return	array $data of texts associated to the course
	 */
	function getCourseText($subj_code,$crse_numb,$termcode='')
	{
		$termcode=($termcode)?$termcode:$this->termcode;

		$data=array();
		$query="SELECT scrtext_text
							FROM scrtext s1
						 WHERE s1.scrtext_subj_code='$subj_code'
							 AND s1.scrtext_crse_numb='$crse_numb'
							 AND s1.scrtext_text_code='A'
							 AND s1.scrtext_eff_term=(SELECT max(s2.scrtext_eff_term) FROM scrtext s2 WHERE s2.scrtext_subj_code=s1.scrtext_subj_code AND s2.scrtext_crse_numb=s1.scrtext_crse_numb AND s2.scrtext_eff_term<='$termcode') ORDER BY scrtext_seqno";
		if($results=$this->_ADOdb->Execute($query))
		{
			while($row=$results->FetchRow())
			{
					$row=PSUTools::cleanKeys('scrtext_','r_',$row);
					$data[]=$row;
			}//end if
		}//end if
		return $data;
	}//end getCourseText

	/**
	 * getCRN
	 *
	 * get the crn of a course
	 *
	 * @access	public
	 * @param	string $subject code related to course
	 * @param	string $course_number course number of course
	 * @param	string $section section of the course being taught
	 * @param	string $termcode termcode of course, defaults to ''
	 * @return	integer crn of course
	 */
	function getCRN($subject,$course_number,$section,$termcode='')
	{
		$termcode=($termcode)?$termcode:$this->termcode;

		$query="SELECT ssbsect_crn FROM ssbsect WHERE ssbsect_subj_code='$subject' AND ssbsect_crse_numb='$course_number' AND ssbsect_seq_numb='$section' AND ssbsect_term_code='$termcode'";
		return $this->_ADOdb->GetOne($query);
	}//end getCRN

	/**
	 * getInstructors
	 *
	 * get the instructor of a course
	 *
	 * @access	public
	 * @param	integer $crn crn of course
	 * @param	string $termcode termcode of course, defaults to ''
	 * @return	array $instructors array of instructors for course
	 */
	function getInstructors($crn,$termcode='')
	{
		$termcode=($termcode)?$termcode:$this->termcode;

		$instructors=array();
		$query="SELECT *
							FROM sirasgn
						 WHERE sirasgn_crn=$crn
							 AND sirasgn_term_code='$termcode'";
		if($results=$this->_ADOdb->Execute($query))
		{
			while($row=$results->FetchRow())
			{
					$row=PSUTools::cleanKeys('sirasgn_','r_',$row);
					$instructors[]=$row;
			}//end if
		}//end if
		return $instructors;
	}//end getInstructors

	/**
	 * getRoster
	 *
	 * get the roster of a course
	 *
	 * @access	public
	 * @param	integer $crn crn of course
	 * @param	string $termcode termcode of course, defaults to ''
	 * @return	array $students array of students in the course
	 */
	function getRoster($crn,$termcode='')
	{
		$termcode=($termcode)?$termcode:$this->termcode;

		$students=array();
		$query="SELECT sfrstcr.*
							FROM sfrstcr,spriden
						 WHERE sfrstcr_crn=$crn
							 AND sfrstcr_term_code='$termcode'
							 AND spriden_pidm=sfrstcr_pidm
							 AND spriden_change_ind is null
						ORDER BY spriden_last_name,spriden_first_name,spriden_mi";
		if($results=$this->_ADOdb->Execute($query))
		{
			while($row=$results->FetchRow())
			{
					$row=PSUTools::cleanKeys('sfrstcr_','r_',$row);
					$students[]=$row;
			}//end if
		}//end if
		return $students;
	}//end getRoster

	/**
	 * getSectionData
	 *
	 * get the section data of a course
	 *
	 * @access	public
	 * @param	integer $crn crn of course
	 * @param	string $termcode termcode of course, defaults to ''
	 * @return	array $course array of data about course section with fields r_attributes, and r_text
	 */
	function getSectionData($crn,$termcode='')
	{
		$termcode=($termcode)?$termcode:$this->termcode;

		$course=array();
		$query="SELECT *
							FROM ssbsect,scbcrse s1
						 WHERE ssbsect_crn=$crn
							 AND ssbsect_term_code='$termcode'
							 AND ssbsect_subj_code=s1.scbcrse_subj_code
							 AND ssbsect_crse_numb=s1.scbcrse_crse_numb
							 AND s1.scbcrse_eff_term=(SELECT max(s2.scbcrse_eff_term) FROM scbcrse s2 WHERE s2.scbcrse_subj_code=s1.scbcrse_subj_code AND s2.scbcrse_crse_numb=s1.scbcrse_crse_numb AND s2.scbcrse_eff_term<='$termcode')";
		if($row=$this->_ADOdb->GetRow($query))
		{
				$row=PSUTools::cleanKeys(array('ssbsect_','scbcrse_'),array('r_','r_'),$row);
				$row['r_attributes']=$this->getCourseAttributes($row['r_subj_code'],$row['r_crse_numb'],$termcode);
				$row['r_text']=$this->getCourseText($row['r_subj_code'],$row['r_crse_numb'],$termcode);
				$course=$row;
		}//end if

		return $course;
	}//end getSectionData

	/**
	 * getSectionDescription
	 *
	 * get the section description of a course
	 *
	 * @access	public
	 * @param	integer $crn crn of course
	 * @param	string $termcode termcode of course, defaults to ''
	 * @return	string description associated with course section
	 */
	function getSectionDescription($crn,$termcode='')
	{
		$termcode=($termcode)?$termcode:$this->termcode;

		$query="SELECT ssbdesc_text_narrative
							FROM ssbdesc s1
						 WHERE s1.ssbdesc_term_code='$termcode'
							 AND s1.ssbdesc_crn='$crn'";
		return $this->_ADOdb->GetOne($query);
	}//end getSectionDescription
}//end BannerCourse

?>