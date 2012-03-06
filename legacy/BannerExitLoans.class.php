<?php
/**
 * BannerExitLoans.class.php
 *
 * === Modification History ===<br/>
 * 1.0.0  16-OCT-2009  [lmo]  original<br/>
 *
 */

/**
 * BannerExitLoans.class.php
 *
 * @version		1.0.0
 * @module		BannerExitLoans.class.php
 * @author		Laurianne Olcott <max@mail.plymouth.edu>
 * @copyright 2009, Plymouth State University, ITS
 */ 
require_once('PSUTools.class.php');

class BannerExitLoans
{
	/**
	 * BannerExitLoans
	 *
	 * srSaluteAlt gets the student's PRIVATE loan information.
	 *
	 * @since		version 1.0.0
	 * @return		array
	 */
	public static function srSaluteAlt($fund_source,$fund_type,$id)
	{
		$data=array();
		$query="SELECT  aid_year_key, 
										student_id, 
										sum(nvl(award_accept_amount,0)) amount_borrowed, 
										sum(nvl(award_paid_amount,0)) amount_paid, 
										pidm_key,
										'8' interest_rate,
										'Ten Years' as term
							FROM	baninst1.ar_award_detail_by_year
						 WHERE  award_paid_amount > 0 
							 AND	student_id=:id
							 AND  fund_source_code=:fund_source 
							 AND  fund_type_code=:fund_type
					GROUP BY  aid_year_key, 
										student_id, 
										pidm_key
						HAVING	(sum(award_paid_amount) > 0)
					ORDER BY	aid_year_key desc";
		$results=PSU::db('banner')->Execute($query,compact('fund_source','fund_type','id'));
		if($results)
		{
			while($row=$results->FetchRow())
			{
				$data[]=PSU::cleanKeys('','',$row);			
			}// end while
		}// end if
		return $data;
	}//end srSaluteAlt

	/**
	 * BannerExitLoans
	 *
	 * getSoslsmAmt gets the student's PRIVATE SOSLSM loan information.
	 *
	 * @since		version 1.0.0
	 * @return		array
	 */
	public static function getSoslsmAmt($fund_source,$fund_type,$id,$aidyear)
	{
		$data=array();
		$query="SELECT  nvl(award_accept_amount,0) soslsm_amount
							FROM	baninst1.ar_award_detail_by_year
						 WHERE  award_paid_amount > 0
							 AND	student_id=:id
							 AND  fund_source_code=:fund_source 
							 AND  fund_type_code=:fund_type
							 AND	aid_year_key=:aidyear
							 AND	fund_code_key='SOSLSM'";
		return PSU::db('banner')->GetOne($query,compact('fund_source','fund_type','id','aidyear'));
	}//end getSoslsmAmt


	/**
	 * BannerExitLoans
	 *
	 * srSalutePerk gets the student's PERKINS loan information.
	 *
	 * @since		version 1.0.0
	 * @return		array
	 */
	public static function srSalutePerk($fund_code_key,$id)
	{
		$data=array();
		$query="SELECT  aid_year_key, 
										student_id, 
										sum(nvl(award_accept_amount,0)) amount_borrowed, 
										sum(nvl(award_paid_amount,0)) amount_paid, 
										pidm_key,
										'5' interest_rate,
										'Ten Years' as term
							FROM	baninst1.ar_award_detail_by_year
						 WHERE	fund_code_key like :fund_code_key
						   AND	student_id=:id
					GROUP BY	aid_year_key, 
										student_id, 
										pidm_key
						HAVING	(sum(award_paid_amount) > 0)
					ORDER BY	aid_year_key desc";
		$results=PSU::db('banner')->Execute($query,compact('fund_code_key','id'));
		if($results)
		{
			while($row=$results->FetchRow())
			{
				$data[]=PSU::cleanKeys('','',$row);		
			}// end while
		}// end if
		return $data;
	}//end srSalutePerk

	/**
	 * BannerExitLoans
	 *
	 * srSaluteStaff gets the student's STAFFORD loan information.
	 *
	 * @since		version 1.0.0
	 * @return		array
	 */
	public static function srSaluteStaff($fund_code_key1, $fund_code_key2,$id)
	{
		$data=array();
		$query="SELECT  aid_year_key, 
										student_id, 
										sum(nvl(award_accept_amount,0)) amount_borrowed, 
										sum(nvl(award_paid_amount,0)) amount_paid, 
										pidm_key,
										rfraspc_interest_rate interest_rate,
										'Ten Years' as term
							FROM	baninst1.ar_award_detail_by_year,rfraspc
						 WHERE	(fund_code_key like :fund_code_key1 
								OR	fund_code_key like :fund_code_key2)
							 AND	rfraspc_fund_code=fund_code_key
							 AND	rfraspc_aidy_code=aid_year_key
							 AND	student_id=:id
					GROUP BY	aid_year_key, 
										student_id, 
										pidm_key,
                    rfraspc_interest_rate
						HAVING	(sum(award_paid_amount) > 0)
					ORDER BY	aid_year_key desc";
		$results=PSU::db('banner')->Execute($query,compact('fund_code_key1','fund_code_key2','id'));
		if($results)
		{
			while($row=$results->FetchRow())
			{
				$data[]=PSU::cleanKeys('','',$row);			
			}// end while
		}// end if
		return $data;
	}//end srSaluteStaff

	/**
	 * BannerExitLoans
	 *
	 * getAcyrCode gets the process year for the STAFFORD loan using STVTERM.
	 *
	 * @since		version 1.0.0
	 * @return		array
	 */
	public static function getAcyrCode($aidyear)
	{
		$data=array();
		$query="SELECT	max(stvterm_acyr_code) stvterm_acyr_code
							FROM	stvterm
						 WHERE	stvterm_fa_proc_yr=:aidyear"; 
		return PSU::db('banner')->GetOne($query,compact('aidyear'));
	}//end getAcyrCode


	/**
	 * BannerExitLoans
	 *
	 * srSaluteParent gets the student's Parent loan information.
	 *
	 * @since		version 1.0.0
	 * @return		array
	 */
	public static function srSaluteParent($id)
	{
		$data=array();
		$query="SELECT  aid_year_key, 
										student_id, 
										sum(nvl(award_accept_amount,0)) amount_borrowed, 
										sum(nvl(award_paid_amount,0)) amount_paid, 
										pidm_key,
										'Ten Years' as term
							FROM	baninst1.ar_award_detail_by_year
						 WHERE	federal_fund_id = 'PLUS'
							 AND	student_id=:id
					GROUP BY	aid_year_key, 
										student_id, 
										pidm_key
						HAVING	(sum(award_paid_amount) > 0)
					ORDER BY	aid_year_key desc";
		$results=PSU::db('banner')->Execute($query,compact('id'));
		if($results)
		{
			while($row=$results->FetchRow())
			{
				$data[]=PSU::cleanKeys('','',$row);			
			}// end while
		}// end if
		return $data;
	}//end srSaluteParent

	/**
	 * BannerExitLoans
	 *
	 * srSaluteAddress gets the student's address information.
	 *
	 * @since		version 1.0.0
	 * @return		array
	 */
	public static function srSaluteAddress($atyp_code,$id)
	{
		$query="SELECT  spraddr_pidm, 
										spraddr_street_line1, 
										spraddr_street_line2, 
										spraddr_street_line3, 
										spraddr_city, 
										spraddr_stat_code, 
										spraddr_zip, 
										spriden_id, 
										spriden_first_name, 
										spriden_last_name
							FROM	spriden,
										spraddr 
						 WHERE	spraddr_atyp_code(+)=:atyp_code 
							 AND	spriden_id=:id
							 AND	spraddr_pidm(+) = spriden_pidm
							 AND	spriden_change_ind is null
							 AND	(spraddr_to_date is null 
											OR spraddr_to_date >= sysdate) 
							 AND	(spraddr_from_date is null
											OR spraddr_from_date <=sysdate)
							 AND	spraddr_status_ind(+) is null
					GROUP BY	spraddr_pidm, 
										spraddr_street_line1, 
										spraddr_street_line2, 
										spraddr_street_line3, 
										spraddr_city, 
										spraddr_stat_code, 
										spraddr_zip, 
										spriden_id, 
										spriden_first_name, 
										spriden_last_name";
		return PSU::db('banner')->GetRow($query,compact('atyp_code','id'));
	}//end srSaluteAddress

	/**
	 * BannerExitLoans
	 *
	 * srSaluteName gets the student's Name information.
	 *
	 * @since		version 1.0.0
	 * @return		array
	 */
	public static function srSaluteName($pidm)
	{
		$query="SELECT  spriden_id, 
										spriden_first_name, 
										spriden_last_name
							FROM	spriden 
						 WHERE	spriden_pidm=:pidm
							 AND	spriden_change_ind is null";
		return PSU::db('banner')->GetRow($query,compact('pidm'));
	}//end srSaluteName



}//end BannerExitLoans
