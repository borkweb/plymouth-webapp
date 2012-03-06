<?php

/**
 * admitcounselor.class.php
 *
 * === Modification History ===<br/>
 * 0.1.0  22-Sep-2009  [djb]  original<br/>
 *
 */

/**
 * admitcounselor.class.php
 *
 * API for admissions paperfree counselor applications
 *
 * @version		0.1.0
 * @module		admitcounselor.class.php
 * @author		Dan Bramer <djbramer@plymouth.edu>
 * @copyright 2009, Plymouth State University, ITS
 */ 


class Admitcounselor
{
	/**
	 * getDemogData
	 * Returns array of display data for Applicants currently being held for committee
	 *
	 * @since		version 0.1.0
	 * @param		int $pidm of applicant
	 */
	function getDemogData($pidm)
	{
		$applquery="SELECT 
					a.pidm,
					c.sorhsch_sbgi_code,
					d.saradap_resd_code,
					d.saradap_styp_code,
					d.saradap_admt_code,
					d.saradap_term_code_entry,
					d.saradap_appl_no,
					e.stvsbgi_desc
				FROM 
					psu.admit_counselor_form a,
					spriden b, 
					sorhsch c, 
					saradap d, 
					stvsbgi e 
				WHERE a.pidm = b.spriden_pidm
					AND a.pidm = c.sorhsch_pidm
					AND a.pidm = d.saradap_pidm
					AND d.saradap_appl_no = 
						(SELECT MAX(e.saradap_appl_no)
						FROM saradap e
						WHERE d.saradap_pidm = e.saradap_pidm
							AND e.saradap_term_code_entry = a.term_code)
					AND e.stvsbgi_code = c.sorhsch_sbgi_code
					AND a.pidm = :pidm";
	
		return PSU::db('banner')->GetRow($applquery, array('pidm' => $pidm));
	}
	
	
	/**
	 * getHeldApplicantData
	 * Returns array of display data for Applicants currently being held for committee
	 *
	 * @since		version 0.1.0
	 * @return		array $data of applicant held for committee
	 */
	function getHeldApplicantData()
	{
		$query="SELECT 
			  a.pidm,
			  b.spriden_id,
			  b.spriden_last_name,
			  b.spriden_first_name,
			  c.sorhsch_sbgi_code,
			  d.saradap_resd_code,
			  d.saradap_styp_code,
			  d.saradap_admt_code,
			  d.saradap_term_code_entry,
			  d.saradap_appl_no,
			  e.stvsbgi_desc,
			  f.field13
			FROM 
			  psu.admit_counselor_form a,
			  spriden b, 
			  sorhsch c, 
			  saradap d, 
			  stvsbgi e, 
			  otgmgr.ae_dt509 f
			WHERE a.pidm = b.spriden_pidm
			  AND a.pidm = c.sorhsch_pidm
			  AND a.pidm = d.saradap_pidm
			  AND d.saradap_appl_no = 
				(SELECT MAX(e.saradap_appl_no)
				  FROM saradap e
				  WHERE d.saradap_pidm = e.saradap_pidm
					AND e.saradap_term_code_entry = a.term_code)
			  AND e.stvsbgi_code = c.sorhsch_sbgi_code
			  AND f.field1 = b.spriden_id
			  AND f.field8 = d.saradap_term_code_entry
			  AND f.field3 = 'ADMISSIONS APP'
			  AND f.field10 = 'AA'
			  AND f.field8 = a.term_code
			  AND a.committee_hold = 'Y'
			  ORDER BY
				b.spriden_last_name,
				b.spriden_first_name
			  "; 

		$results=PSU::db('banner')->Execute($query);
		while($row=$results->FetchRow())
		{
			$data[]=$row;
		}//end while

		return $data;

	}

	/**
	 * getNonBannerData
	 * submits a decision workflow for the applicant
	 *
	 * @since		version 0.1.0
	 * @param		int $pidm of user's identification
	 * @return		array $data from nonbanner sourcesapplicant held for committee
	 */
	function getNonBannerData($pidm,$term_code)
	{
		$sqlnonbanner = "SELECT *
						FROM psu.admit_counselor_form
						WHERE pidm = :pidm
							AND term_code = :term_code";
		return PSU::db('banner')->GetRow($sqlnonbanner,array('pidm'=>$pidm,'term_code' => $term_code));
	}
	
	
	/**
	 * p_retrieve_banner
	 * php call to stored proceedure with the same name
	 *
	 * @since		version 0.1.0
	 * @param		int $n_pidm of user's identification
	 * @param		string $s_term_code of applicant's application
	 * @param		int $n_appl_no user's application number
	 * @return		array $return banner data for applicant
	 */
	function p_retrieve_banner( $n_pidm, $s_term_code, $n_appl_no ) {
        PSU::db('banner')->debug = false;

        $out = array('s_gpa', 's_rank', 's_size', 's_pct', 's_satv', 's_satm', 's_satt',
                's_actt', 's_major','s_program','s_math_code', 's_science_code', 's_socsci_code', 's_lang_fr', 's_lang_sp',
                's_lang_la', 's_lang_ge', 's_lang_ot', 's_attr_agrd', 's_attr_amth', 's_attr_anfn', 
                's_attr_ano2', 's_attr_aprb', 's_attr_as01', 's_attr_as02', 's_attr_as03', 's_attr_as06', 
                's_attr_as09', 's_attr_as10', 's_attr_as11', 's_attr_aspc', 's_attr_asvr', 's_attr_aund', 's_attr_awrn', 
                's_attr_hnrs','s_attr_afsn','n_trans_cred', 's_fee', 's_decsn'
        );

        $sql = "BEGIN pkg_wf_admit_counselor_form.p_retrieve_banner(
                n_pidm => :n_pidm,
                s_term_code => :s_term_code,
                n_appl_no => :n_appl_no";

        $sql .= array_reduce( $out, create_function('$a,$b', 'return $a . ", $b => :$b";') );

        $sql .= "); END;";

        $stmt = PSU::db('banner')->PrepareSP($sql);

        PSU::db('banner')->InParameter($stmt, $n_pidm, 'n_pidm');
        PSU::db('banner')->InParameter($stmt, $s_term_code, 's_term_code');
        PSU::db('banner')->InParameter($stmt, $n_appl_no, 'n_appl_no');

        // store all our out parameters in an array for easy returning
        $return = array();
        foreach( $out as $var ) {
                PSU::db('banner')->OutParameter($stmt, $return[$var], $var);
        }

        PSU::db('banner')->Execute($stmt);

        return $return;
}

	/**
	 * search_bluesheets
	 * searches admit_counselor_table on string given
	 *
	 * @since		version 0.2.0
	 * @param		string text of user's identification
	 * @return		array $data from nonbanner sourcesapplicant held for committee
	 */
	function search_bluesheets($in_clause,$term_code=null)
	{
		$sql = "SELECT 
							term_code,
							pidm,
							spriden_id,
							spriden_last_name,
							spriden_first_name
						FROM psu.admit_counselor_form,spriden
						WHERE pidm = spriden_pidm
							AND pidm IN $in_clause
							AND spriden_change_ind IS NULL";
		
		if (!is_null($term_code))
		{
			$sql .= " AND term_code = '".$term_code."'";
		}
		return PSU::db('banner')->GetAll($sql);
	}
	
	
	/**
	 * workflowSubmit
	 * submits a decision workflow for the applicant
	 *
	 * @since		version 0.1.0
	 * @param		int $pidm of user's identification
	 */
	function workflowSubmit($pidm)
	{
		$query="SELECT 
					a.pidm,
					b.spriden_id,
					b.spriden_last_name,
					b.spriden_first_name,
					c.sorhsch_sbgi_code,
					d.saradap_resd_code,
					d.saradap_styp_code,
					d.saradap_admt_code,
					d.saradap_term_code_entry,
					d.saradap_appl_no,
					e.stvsbgi_desc,
					f.field13
				FROM 
					psu.admit_counselor_form a,
					spriden b, 
					sorhsch c, 
					saradap d, 
					stvsbgi e, 
					otgmgr.ae_dt509 f
				WHERE a.pidm = b.spriden_pidm
					AND a.pidm = c.sorhsch_pidm
					AND a.pidm = d.saradap_pidm
					AND d.saradap_appl_no = 
						(SELECT MAX(e.saradap_appl_no)
						FROM saradap e
						WHERE d.saradap_pidm = e.saradap_pidm)
					AND e.stvsbgi_code = c.sorhsch_sbgi_code
					AND f.field1 = b.spriden_id
					AND f.field8 = d.saradap_term_code_entry
					AND f.field3 = 'ADMISSIONS APP'
					AND f.field10 = 'AA'
					AND f.field8 = a.term_code
					AND a.pidm = :pidm
					";
			  
		$data=PSU::db('banner')->GetRow($query,array('pidm'=>$pidm));

		//get sequence number
		$sql = "SELECT gobeseq.nextval FROM dual";
		
		$event_id = PSU::db('banner')->getOne($sql);
		
		//Insert sequence details into gobeqrc
		$sql = "INSERT INTO gobeqrc
					(gobeqrc_seqno,
					 gobeqrc_eqts_code,
					 gobeqrc_eqnm_code,
					 gobeqrc_status_ind,
					 gobeqrc_user_id,
					 gobeqrc_activity_date)
				VALUES
					(:event_id,
					 'WORKFLOW',
					 'S_ADM_DECSN',
					 '2',
					 'WORKFLOW',
					 sysdate)
				";
	
		$results = PSU::db('banner')->Execute($sql,array('event_id'=>$event_id));				
	
		$eventData = array(
			'eventName' => 'S_ADM_DECSN',
			'workflowName' => 'counselorcmte_'.$data['saradap_term_code_entry'].'_'.$data['saradap_styp_code'].'_'.$data['saradap_admt_code'].'_'.$data['saradap_resd_code'].'_'.$data['sorhsch_sbgi_code'].'_'.$data['spriden_last_name'].'_'.$data['spriden_first_name'].'_'.$data['spriden_id'],
			'productTypeName' => 'SCT Banner',
			'externalSource' => 'bannerTest',
			'externalID' => $event_id,
			'externalDate' => strtotime('now')
		);
	
		$parameters = array(
			array('name' => 'ADMT_CODE', 'stringValue' => $data['saradap_admt_code']),
			array('name' => 'APPL_NO', 'numericValue' => $data['saradap_appl_no']),
			array('name' => 'DATE', 'stringValue' => $data['field13']),
			array('name' => 'FirstName', 'stringValue' => $data['spriden_first_name']),
			array('name' => 'HS_CODE', 'stringValue' => $data['sorhsch_sbgi_code']),
			array('name' => 'HS_DESC', 'stringValue' => $data['stvsbgi_desc']),
			array('name' => 'ID', 'stringValue' => $data['spriden_id']),
			array('name' => 'LastName', 'stringValue' => $data['spriden_last_name']),
			array('name' => 'PIDM', 'numericValue' => $pidm),
			array('name' => 'RESD_CODE', 'stringValue' => $data['saradap_resd_code']),
			array('name' => 'STYP_CODE', 'stringValue' => $data['saradap_styp_code']),
			array('name' => 'TERM', 'stringValue' => $data['saradap_term_code_entry'])
			);
		$update = "UPDATE psu.admit_counselor_form
					SET committee_hold = 'N'
					WHERE pidm = :pidm
					AND term_code = :term_code";
		$results=PSU::db('banner')->Execute($update,array('pidm'=>$pidm,'term_code'=>$data['saradap_term_code_entry']));
		$GLOBALS['WORKFLOW']->postExternalEvent($eventData,$parameters);
	}

	
	/**
	 * updateNonBannerData
	 * submits a decision workflow for the applicant
	 *
	 * @since		version 0.1.0
	 * @param		array $arr containing all data (associative matching bind varaibles
	 */
	function updateNonBannerData($arr)
	{
		$sql= "
			UPDATE psu.admit_counselor_form 
			SET 
				math_9 = :s_math_9,
				math_10 = :s_math_10,
				math_11 = :s_math_11,
				math_12 = :s_math_12,
				math_PG = :s_math_pg,
				engl_9 = :s_engl_9,
				engl_10 = :s_engl_10,
				engl_11 = :s_engl_11,
				engl_12 = :s_engl_12,
				engl_PG = :s_engl_pg,
				science_9 = :s_science_9,
				science_10 = :s_science_10,
				science_11 = :s_science_11,
				science_12 = :s_science_12,
				science_PG = :s_science_pg,
				socsci_9 = :s_socsci_9,
				socsci_10 = :s_socsci_10,
				socsci_11 = :s_socsci_11,
				socsci_12 = :s_socsci_12,
				socsci_PG = :s_socsci_pg,
				language_9 = :s_language_9,
				language_10 = :s_language_10,
				language_11 = :s_language_11,
				language_12 = :s_language_12,
				language_PG = :s_language_pg,
				elective_9 = :s_elective_9,
				elective_10 = :s_elective_10,
				elective_11 = :s_elective_11,
				elective_12 = :s_elective_12,
				elective_PG = :s_elective_pg,
				other_9 = :s_other_9,
				other_10 = :s_other_10,
				other_11 = :s_other_11,
				other_12 = :s_other_12,
				other_PG = :s_other_pg,
				notes = :s_notes
			WHERE pidm = :n_pidm
			AND term_code = :s_term_code
			";
			
		PSU::db('banner')->Execute($sql,$arr);
	}

	/**
	 * AdmitCounselor
	 *
	 * constructor initializes variables
	 *
	 * @since		version 0.1.0
	 */
	function __construct($in=null)
	{
		if (is_null($in))
		{
			if (preg_match('/www\./',$_SERVER['SERVER_NAME']))
			{
				$which='psc1';
			} else
			{
				$which='test';
			}
		} else 
		{
			$which=$in;
		}
		$this->_instance = $which;
	}


}
