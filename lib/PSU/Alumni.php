<?php
/**
 * PSUAlumni.class.php
 *
 * Alumni Object
 *
 * &copy; 2009 Plymouth State University ITS
 *
 * @author		Matthew Batchelder <mtbatchelder@plymouth.edu>
 */

namespace PSU;

class Alumni extends \BannerObject {
	public $data = array();
	public $data_loaders = array();

	public function __construct($pidm, $term_code = null)
	{
		parent::__construct();
		
		$data_loaders = array(
			'primary_spouse' => 'spouse',
			'spouse_category' => 'spouse',
			'income' => 'constituent_information',
			'college' => 'constituent_information',
			'occupation' => 'constituent_information',
			'category' => 'constituent_information',
			'recent_gift_date' => 'gift_summary',
			'recent_gift_amount' => 'gift_summary',
			'recent_designation' => 'gift_summary',
			'recent_gift_year' => 'gift_summary',
			'high_gift_date' => 'gift_summary',
			'high_gift_amount' => 'gift_summary',
			'high_designation' => 'gift_summary',
			'number_gifts' => 'gift_summary',
			'hard_credit' => 'gift_summary',
			'pledge_payments' => 'gift_summary',
			'outright_gifts' => 'gift_summary'
		);

		$this->data_loaders = \PSU::params($data_loaders, $this->data_loaders);
		
		if(is_array($pidm))
		{
			$this->parse($pidm);
		}//end if
		else
		{
			$this->pidm = $pidm;
			$this->load($pidm);
		}//end else
	}//end constructor

	/**
	 * load base constituent record
	 */
	protected function load()
	{
		$sql = "SELECT * 
							FROM apbcons
						 WHERE apbcons_pidm = :pidm";
		$row = \PSU::db('banner')->GetRow($sql, array('pidm' => $this->pidm));
		$this->parse($row);
	}//end load

	/**
	 * parse out constituent record
	 */
	protected function parse($row)
	{
		$row = \PSU::cleanKeys('apbcons_', '', $row);
	
		foreach($row as $field => $value)
		{
			$this->$field = $value;
		}//end foreach
	}//end parse
	
	/**
	 * load activities
	 */
	protected function _load_activities()
	{
		$this->data['activities'] = array();
		$sql = "SELECT  apvacty_actc_desc,
										apvacty_actp_desc,
										apvacty_accg_desc,
										max(apracyr_year) latest_year,
                    count(apracyr_actc_code) total_years
							FROM  apvacty,
										apracyr
						 WHERE  apvacty_pidm=apracyr_pidm(+)
							 AND  apracyr_actc_code(+)=apvacty_actc_code
							 AND  apvacty_pidm=:pidm
					GROUP BY	apvacty_actc_desc, 
										apvacty_actp_desc, 
										apvacty_accg_desc";
		if($results = \PSU::db('banner')->Execute($sql, array('pidm' => $this->pidm)))
		{
			foreach($results as $row)
			{
				$row = \PSU::cleanKeys(array('apvacty_','apracyr_'), '', $row);
				$this->data['activities'][] = $row;
			}//end foreach
		}//end if
	}//end _load_activities

	/**
	 * load prospect information
	 */
	protected function _load_prospect_info()
	{
		$this->data['prospect_info'] = array();
		$sql = "SELECT  atvrefr_desc reference_type,
										amrinfo_reference,
										atvprst_desc status,
										amrinfo_web_pldg_ind,
										amrinfo_web_gift_ind
							FROM  amrinfo,
										atvrefr,
										atvprst
						 WHERE  amrinfo_refr_code = atvrefr_code
							 AND  amrinfo_status = atvprst_code
							 AND  amrinfo_pidm = :pidm";
		if($results = \PSU::db('banner')->Execute($sql, array('pidm' => $this->pidm)))
		{
			foreach($results as $row)
			{
				$row = \PSU::cleanKeys('amrinfo_', '', $row);
				$this->data['prospect_info'][] = $row;
			}//end foreach
		}//end if
	}//end _load_prospect_info

	/**
	 * load children
	 */
	protected function _load_children()
	{
		$this->data['children'] = array();
    
		$sql = "SELECT	spriden_id,
										decode(spriden_last_name,NULL,aprchld_first_name||' '||aprchld_mi||' '||aprchld_last_name,spriden_first_name||' '||spriden_mi||' '||spriden_last_name) aprchld_fullname,
										decode(aprchld_birth_date,NULL,spbpers_birth_date,aprchld_birth_date) aprchld_birth_date,
										decode(aprchld_sex,NULL,spbpers_sex,aprchld_sex) aprchld_sex,
										decode(aprchld_deceased_ind,NULL,spbpers_dead_ind,aprchld_deceased_ind) aprchld_deceased_ind,
										decode(aprchld_deceased_date,NULL,spbpers_dead_date,aprchld_deceased_date) aprchld_deceased_date
							FROM	aprchld,
										spriden,
										spbpers
						 WHERE	aprchld_pidm = :pidm
							 AND	aprchld_chld_pidm = spbpers_pidm(+)
							 AND	aprchld_chld_pidm = spriden_pidm(+)
							 AND	spriden_change_ind is null";
		if($results = \PSU::db('banner')->Execute($sql, array('pidm' => $this->pidm)))
		{
			foreach($results as $row)
			{
				$row = \PSU::cleanKeys(array('aprchld_','spriden_'), '', $row);
				if($row['birth_date'])
				{
					$row['age'] = \PSU::date_diff(time(), $row['birth_date']);
				}
				$this->data['children'][] = $row;
			}//end foreach
		}//end if
	}//end _load_children

	/**
	 * load Cross References
	 */
	protected function _load_cross_references()
	{
		$this->data['cross_references'] = array();
		$sql = "SELECT	spriden_id,
										spriden_first_name||' '||spriden_mi||' '||spriden_last_name spriden_fullname,
										spbpers_dead_ind,
										atvxref_desc,
										a.aprxref_cm_pri_ind primary_ind
							FROM	aprxref a,
										atvxref,
										spriden,
										spbpers
						 WHERE	a.aprxref_pidm = :pidm
							 AND	spriden_pidm = a.aprxref_xref_pidm 
							 AND  spriden_change_ind is null
							 AND	spbpers_pidm(+) = spriden_pidm
							 AND	atvxref_code = a.aprxref_xref_code";
		if($results = \PSU::db('banner')->Execute($sql, array('pidm' => $this->pidm)))
		{
			foreach($results as $row)
			{
				$row = \PSU::cleanKeys(array('aprxref_','atvxref_','spriden_','spbpers_'), '', $row);
				$this->data['cross_references'][] = $row;
			}//end foreach
		}//end if
	}//end _load_cross_references

	/**
	 * load Degree information
	 */
	protected function _load_degrees()
	{
		$this->data['degrees'] = array();
		$sql = "SELECT  stvsbgi_desc institution,
										DECODE(stvdegc_desc,null,'No Degree',stvdegc_desc) degree,
										stvhonr_desc honors,
										stvmajr_desc majors,
										apradeg_acyr_code year,
										stvcamp_desc campus
							FROM  apradeg,
										stvsbgi,
										stvdegc,
										stvhonr,
										apramaj,
										stvmajr,
										stvcamp
						 WHERE  apradeg_pidm = :pidm
							 AND  stvsbgi_code(+)=apradeg_sbgi_code
							 AND  stvdegc_code(+)=apradeg_degc_code
							 AND  stvhonr_code(+)=apradeg_honr_code
							 AND  apramaj_pidm(+) = apradeg_pidm
							 AND  apramaj_adeg_seq_no(+) = apradeg_seq_no
							 AND  stvmajr_code(+) = apramaj_majr_code
							 AND  stvcamp_code(+) = apradeg_camp_code
					ORDER BY  apradeg_seq_no";
		if($results = \PSU::db('banner')->Execute($sql, array('pidm' => $this->pidm)))
		{
			foreach($results as $row)
			{
				$row = \PSU::cleanKeys('apradeg_', '', $row);
				$this->data['degrees'][] = $row;
			}//end foreach
		}//end if
	}//end _load_degrees

	
	/**
	 * load employment history
	 */
	protected function _load_employment_history()
	{
		$this->data['employment_history'] = array();
		$sql = "SELECT	NVL(aprehis_empr_name, spriden_last_name) employer, 
										aprehis_empl_position position,
										atvemps_desc emp_status,
										aprehis_from_date from_date, 
										aprehis_to_date to_date
        		  FROM	aprehis, 
										spriden, atvemps 
		         WHERE	aprehis_pidm = :pidm 
							 AND	(aprehis_emps_code<>'X' OR aprehis_emps_code IS NULL)
							 AND	atvemps_code(+) = aprehis_emps_code
		           AND	aprehis_empr_pidm=spriden_pidm(+)
		           AND	spriden_change_ind is null";
		if($results = \PSU::db('banner')->Execute($sql, array('pidm' => $this->pidm)))
		{
			foreach($results as $row)
			{
				$row = \PSU::cleanKeys('aprehis_', '', $row);
				$this->data['employment_history'][] = $row;
			}//end foreach
		}//end if
	}//end _load_employment_history

	/**
	 * load spouse employment history
	 */
	protected function _load_spouse_employment_history()
	{
		$this->data['spouse_employment_history'] = array();
		$sql = "SELECT NVL(aprehis_empr_name, spriden_last_name) employer, 
			             aprehis_empl_position position, 
									 aprehis_from_date from_date, 
									 aprehis_to_date to_date
        		  FROM aprehis, spriden 
		         WHERE aprehis_pidm = :pidm 
		           AND aprehis_empr_pidm=spriden_pidm(+)
							 AND aprehis_emps_code='X'
		           AND spriden_change_ind is null";
		if($results = \PSU::db('banner')->Execute($sql, array('pidm' => $this->pidm)))
		{
			foreach($results as $row)
			{
				$row = \PSU::cleanKeys('aprehis_', '', $row);
				$this->data['spouse_employment_history'][] = $row;
			}//end foreach
		}//end if
	}//end _load_spouse_employment_history


	protected function _load_profile_preference()
	{
		$this->data['profile_preference'] = array();
		$row = \PSU::db('banner')->GetRow("SELECT * FROM aobdowp WHERE aobdowp_pidm = :pidm", array('pidm' => $this->pidm));
		$this->data['profile_preference'][] = \PSU::cleanKeys('aobdowp_', '', $row);
	}//end _load_profile_preference

	/** get the constituent comments from aprconf
	 */
	protected function _load_constituent_comments()
	{
		$this->data['constituent_comments'] = array();
		$sql = "SELECT	gtvsubj_desc comment_type,
										aprconf_confid_ind confid_ind,
										aprconf_entry_date entry_date,
										aprconf_comment comments,
										guriden_desc entered_by
							FROM	aprconf,
										aprsubj,
										guriden,
										gtvsubj
						 WHERE	aprconf_pidm = aprsubj_pidm(+)
						   AND	aprconf_grp_seq_no = aprsubj_grp_seq_no(+)
							 AND	aprconf_iden_code = aprsubj_iden_code(+)
							 AND	aprconf_iden_code = guriden_iden_code
							 AND	gtvsubj_code(+) = aprsubj_subj_code
							 AND	aprconf_pidm = :pidm
					ORDER BY	aprconf_entry_date desc,
										aprconf_grp_seq_no";
		if($rows = \PSU::db('banner')->GetAll($sql, array('pidm' => $this->pidm)))
		{
			$this->data['constituent_comments'] = $rows;
		}//end if
	}//end _load_constituent_comments

	/** get the spouse's basic information such as spouse category, spouse preferred class, 
	 *	colleges, spouse confidential indicator and spouse deceased indicator
	 */
	protected function _load_spouse()
	{
		$this->data['spouse'] = array();
		$sql = "SELECT	DISTINCT spriden_id id,
										spriden_first_name||' '||spriden_mi||' '||spriden_last_name fullname,
										spriden_first_name first_name,
										spriden_mi middle_name,
										spriden_last_name last_name,
										apbcons_pref_clas pref_clas,
										apbcons_coll_code_pref coll_code_pref,
										spbpers_confid_ind confidential,
										spbpers_dead_ind deceased
							FROM	spriden,
										aprcsps,
										apbcons,
										aprcatg,
										spbpers,
										stvcoll
						 WHERE	aprcsps_pidm=:pidm
							 AND	aprcsps_sps_pidm=aprcatg_pidm
							 AND	aprcsps_mars_ind='A'
							 AND	aprcsps_sps_pidm=spriden_pidm
							 AND	aprcsps_sps_pidm=apbcons_pidm
							 AND	spriden_change_ind is null
							 AND	aprcsps_sps_pidm=spbpers_pidm
			UNION
	
			SELECT	null id, 
							aprcsps_first_name||' '||aprcsps_mi||' '||aprcsps_last_name fullname,
							aprcsps_first_name first_name,
							aprcsps_mi middle_name,
							aprcsps_last_name last_name,
							null pref_clas, 
							null coll_code_pref, 
							null confidential, 
							null deceased
				FROM	aprcsps
			 WHERE	aprcsps_pidm=:pidm
				 AND	aprcsps_sps_pidm is null
				 AND	aprcsps_mars_ind='A'";

		if($spouse = \PSU::db('banner')->GetRow($sql, array('pidm' => $this->pidm)))
		{
			$this->data['spouse'] = $spouse;
		} // end if

		$sql = "SELECT  a.aprxref_cm_pri_ind current_ind
							FROM  aprxref a,
                    aprxref b
						 WHERE  a.aprxref_pidm = :pidm
               AND  b.aprxref_xref_pidm = a.aprxref_pidm
							 AND  a.aprxref_cm_pri_ind is NOT NULL
               AND  b.aprxref_cm_pri_ind is NOT NULL";
		if($ind = \PSU::db('banner')->GetOne($sql, array('pidm' => $this->pidm)))
		{
			$this->data['primary_spouse'] = $ind == 'P' ? true : false;
		}//end if

		$sql="SELECT	atvdonr_desc category 
						FROM	aprcatg,
									aprcsps,
									atvdonr
					 WHERE	aprcsps_pidm=:pidm
						 AND	aprcsps_mars_ind='A'
						 AND	aprcatg_pidm=aprcsps_sps_pidm
						 AND	aprcatg_donr_code=atvdonr_code";
		$this->data['spouse_category'] = \PSU::db('banner')->GetOne($sql, array('pidm' => $this->pidm));
	}//end _load_spouse

	/** 
	 * get the constituent information: income, college, occupation
	 */
	protected function _load_constituent_information()
	{
		$sql = "SELECT	atvincm_desc income,
										stvcoll_desc college,
										atvdott_desc occupation
							FROM	apbcons,
										atvincm,
										stvcoll,
										atvdott
						 WHERE	apbcons_pidm=:pidm
							 AND  apbcons_incm_code=atvincm_code(+)
							 AND	apbcons_coll_code_pref=stvcoll_code(+)
							 AND	apbcons_dott_code=atvdott_code(+)";
		if($row = \PSU::db('banner')->GetRow($sql, array('pidm' => $this->pidm)))
		{
			$this->data['income'] = $row['income'];
			$this->data['college'] = $row['college'];
			$this->data['occupation'] = $row['occupation'];
		}//end if

		$sql="SELECT	atvdonr_desc category
						FROM	aprcatg g1,
									atvdonr
					 WHERE	g1.aprcatg_pidm=:pidm
						 AND	g1.aprcatg_donr_code= (SELECT MIN(atvdonr_rpt_seq_ind) 
																					 FROM atvdonr, aprcatg g2 
																					WHERE g1.aprcatg_pidm=g2.aprcatg_pidm 
																						AND g2.aprcatg_donr_code=atvdonr_code)";
		$this->data['category'] = \PSU::db('banner')->GetOne($sql, array('pidm' => $this->pidm));
	}//end _load_constituent_information

	/** get the solicitor information
	 */
	protected function _load_solicitor_information()
	{
		$this->data['solicitor_information'] = array();
		$sql="SELECT  afbcamp_name campaign,
									atvsolc_desc type,
									asbsorg_name organization,
									afrctyp_dcyr_code year,
									a.spriden_first_name||' '||a.spriden_mi||' '||a.spriden_last_name solicitor,
									afrctyp_ask_amount target_ask_amount,
									atvrate_desc rating,
									b.spriden_first_name||' '||b.spriden_mi||' '||b.spriden_last_name rater
						FROM  afbcamp,
									atvsolc,
									asbsorg,
									spriden a,
									afrctyp,
									spriden b,
									atvrate
					 WHERE  afrctyp_campaign=afbcamp_campaign
						 AND  afrctyp_rate_code=atvrate_code
						 AND  afrctyp_solc_code=atvsolc_code
						 AND  afrctyp_sol_org=afrctyp_sol_org
						 AND  afrctyp_pidm=a.spriden_pidm
						 AND	afrctyp_pidm=:pidm
						 AND  afrctyp_rater_pidm=b.spriden_pidm";
		if($results = \PSU::db('banner')->Execute($sql, array('pidm' => $this->pidm)))
		{
			foreach($results as $row)
			{
				$this->data['solicitor_information'][] = $row;
			}//end foreach
		}//end if
	}//end _load_solicitor_information

	/** get the membership information
	 */
	protected function _load_membership_information()
	{
		$this->data['membership_information'] = array();
		$sql="SELECT  aabmshp_name program,
									atvamst_desc status,
									aarmemb_entry_date joined,
									aarmemb_renewal_date renewed,
									aarmemb_exp_date expiration
						FROM  aarmemb,
									aabmshp,
									atvamst
					 WHERE  aarmemb_pidm=:pidm
						 AND  aabmshp_membership=aarmemb_membership
						 AND  atvamst_code=aarmemb_amst_code";
		if($results = \PSU::db('banner')->Execute($sql, array('pidm' => $this->pidm)))
		{
			foreach($results as $row)
			{
				$this->data['membership_information'][] = $row;
			}//end foreach
		}//end if
	}//end _load_membership_information

	/** get the gift gift society information
	 */
	protected function _load_gift_society()
	{
		$this->data['gift_society'] = array();
		$sql="SELECT  atvdcnp_desc gift_society,
									atvdcst_desc type,
									atvdcst_pri_ind priority,
									aprdclb_dcyr_code year
						FROM  aprdclb,
									atvdcnp,
									atvdcst
					 WHERE  aprdclb_dcnp_code=atvdcnp_code
						 AND  atvdcnp_dcst_code=atvdcst_code
						 AND  aprdclb_pidm=:pidm";
		if($rows = \PSU::db('banner')->GetAll($sql, array('pidm' => $this->pidm)))
		{
			$this->data['gift_society'] = $rows;
		}//end if
	}//end _load_gift_society

	/** get the exclusions
	 */
	protected function _load_exclusions()
	{
		$this->data['exclusions'] = array();
		$sql="SELECT  atvexcl_desc exclusion,
									atvexcl_exclude_mail from_mailings,
									atvexcl_exclude_phone from_phone_calls,
									aprexcl_reason reason,
									aprexcl_date date_effective,
									aprexcl_end_date date_ending
						FROM  aprexcl,
									atvexcl
					 WHERE  aprexcl_excl_code = atvexcl_code
						 AND  aprexcl_pidm = :pidm";
		if($rows = \PSU::db('banner')->GetAll($sql, array('pidm' => $this->pidm)))
		{
			$this->data['exclusions'] = $rows;
		}//end if
	}//end _load_exclusions

	/** get the mail codes
	 */
	protected function _load_mail_codes()
	{
		$this->data['mail_codes'] = array();
		$sql="SELECT  gtvmail_desc mailing,
									stvatyp_desc address,
									aprmail_date starting_date
						FROM  gtvmail,
									stvatyp,
									aprmail
					 WHERE  aprmail_mail_code = gtvmail_code
						 AND	aprmail_pidm=:pidm
						 AND  aprmail_atyp_code = stvatyp_code";
		if($results = \PSU::db('banner')->Execute($sql, array('pidm' => $this->pidm)))
		{
			foreach($results as $row)
			{
				$this->data['mail_codes'][] = $row;
			}//end foreach
		}//end if
	}//end _load_mail_codes

	/** get the endowment scholarship contacts
	 */
	protected function _load_endowmt_scholarshp_contacts()
	{
		$this->data['endowmt_scholarshp_contacts'] = array();
		$sql="SELECT	atvdist.atvdist_desc as status,
									atvditp.atvditp_desc as contact_type,
									adbdesg.adbdesg_name as endowment_scholarship
						FROM	((adrdids
									LEFT JOIN atvditp ON adrdids.adrdids_ditp_code = atvditp.atvditp_code)
									LEFT JOIN adbdesg ON adrdids.adrdids_desg = adbdesg.adbdesg_desg)
									LEFT JOIN atvdist ON adrdids.adrdids_dist_code = atvdist.atvdist_code
					 WHERE	adrdids_pidm = :pidm
				ORDER BY	adbdesg.adbdesg_name";
		if($results = \PSU::db('banner')->Execute($sql, array('pidm' => $this->pidm)))
		{
			foreach($results as $row)
			{
				$this->data['endowmt_scholarshp_contacts'][] = $row;
			}//end foreach
		}//end if
	}// end _load_endowmt_scholarshp_contacts
	/** get the contacts
	 */
	protected function _load_contacts()
	{
		$this->data['contacts'] = array();
		$sql="SELECT  
									DECODE (aoborgn_prefix_contact, '', '',aoborgn_prefix_contact || ' ')|| DECODE	(aoborgn_first_name_contact,'', '', aoborgn_first_name_contact || ' ')|| DECODE (aoborgn_mi_contact,'', '', aoborgn_mi_contact || ' ')|| DECODE (aoborgn_last_name_contact,'', '', aoborgn_last_name_contact || ' ')|| DECODE (aoborgn_suffix_contact,'', '', aoborgn_suffix_contact || ' ') fullname,
									aoborgn_title_contact position,
									a.atvjobc_desc category,
									c.stvatyp_desc address_type,
									e.spraddr_house_number house_number,
									e.spraddr_street_line1 street_line1,
									e.spraddr_street_line2 street_line2,
									e.spraddr_street_line3 street_line3,
									e.spraddr_street_line4 street_line4,
									e.spraddr_city city,
									e.spraddr_stat_code state,
									e.spraddr_zip zip,
									e.spraddr_cnty_code cnty_code,
									e.spraddr_natn_code natn_code,
									DECODE (spbpers_name_prefix, '', '',spbpers_name_prefix || ' ')|| DECODE (spriden_first_name, '', '',spriden_first_name || ' ')|| DECODE (spriden_mi, '', '',spriden_mi || ' ')|| DECODE(spriden_surname_prefix,'','',spriden_surname_prefix || ' ')|| DECODE (spriden_last_name, '', '', spriden_last_name || ' ') || DECODE (spbpers_name_suffix,'', '',spbpers_name_suffix || ' ') fullname2,
									spriden_id id,
									DECODE (aorcont_name_prefix, '', '', aorcont_name_prefix || ' ')|| DECODE (aorcont_first_name, '', '',aorcont_first_name || ' ')|| DECODE (aorcont_mi, '', '',aorcont_mi || ' ')|| DECODE(aorcont_surname_prefix,'','',aorcont_surname_prefix || ' ')|| DECODE (aorcont_last_name, '', '',aorcont_last_name || ' ')|| DECODE (aorcont_name_suffix, '', '',aorcont_name_suffix || ' ') fullname3,        
									aorcont_title position2,
									b.atvjobc_desc category2,
									d.stvatyp_desc address_type2,
									f.spraddr_house_number house_number2,
									f.spraddr_street_line1 street_line12,
									f.spraddr_street_line2 street_line22,
									f.spraddr_street_line3 street_line32,
									f.spraddr_street_line4 street_line42,
									f.spraddr_city city2,
									f.spraddr_stat_code state2,
									f.spraddr_zip zip2,
									f.spraddr_cnty_code cnty_code2,
									f.spraddr_natn_code natn_code2, 
									aorcont_ctry_code_phone ctry_code_phone,
									aorcont_phone_area phone_area,
									aorcont_phone_ext phone_ext
						FROM  aorcont,
									aoborgn,
									atvjobc a,
									atvjobc b,
									stvatyp c,
									stvatyp d,
									spraddr e,
									spraddr f,
									spbpers,
									spriden
					 WHERE  aorcont_pidm = :pidm
						 AND  aorcont_pidm = aoborgn_pidm(+)
						 AND  aoborgn_jobc_code = a.atvjobc_code
						 AND  aorcont_pidm = e.spraddr_pidm(+)
						 AND  aorcont_contact_pidm = f.spraddr_pidm(+)
						 AND  c.stvatyp_code = e.spraddr_atyp_code
						 AND  e.spraddr_status_ind IS NULL
						 AND  (NVL (e.spraddr_from_date, SYSDATE) <= SYSDATE
						 AND  NVL (e.spraddr_to_date, SYSDATE) >= SYSDATE)
						 AND  aorcont_contact_pidm = spriden_pidm
						 AND  spriden_change_ind is null
						 AND  aorcont_jobc_code = b.atvjobc_code
						 AND  f.spraddr_atyp_code = d.stvatyp_code";
		if($results = \PSU::db('banner')->Execute($sql, array('pidm' => $this->pidm)))
		{
			foreach($results as $row)
			{
				$this->data['contacts'][] = $row;
			}//end foreach
		}//end if
	}//end _load_contacts

	/** get names and salutations
	 */
	protected function _load_names_salutations()
	{
		$this->data['names_salutations'] = array();
		$sql="SElECT  DISTINCT atvsalu_desc salutation_type,
									aprsalu_salutation salutation,
									decode(spriden_entity_ind,'P',apbcons_society_name,'C',aoborgn_society_name) gift_society_name,
									apbcons_cm_name combined_name,
									decode(spriden_ntyp_code,'N',spriden_first_name||' '||decode(spriden_mi,null,null,spriden_mi||' ')||decode(spriden_surname_prefix,null,null,spriden_surname_prefix||' ')||spriden_last_name) alternate_names,
									spriden_activity_date activity_date,
									gtvntyp_code name_type
						FROM  aprsalu,
									spriden,
									apbcons,
									aobdowp,
									aoborgn,
									atvsalu,
									gtvntyp
           WHERE  aprsalu_pidm = :pidm
             AND  aprsalu_salu_code=atvsalu_code
             AND  aprsalu_pidm=apbcons_pidm
             AND  aprsalu_pidm=aoborgn_pidm(+)
             AND  aprsalu_pidm=aobdowp_pidm(+)
             AND  aprsalu_pidm=spriden_pidm
             AND  spriden_ntyp_code=gtvntyp_code
        ORDER BY  atvsalu_desc";
		if($rows = \PSU::db('banner')->GetAll($sql, array('pidm' => $this->pidm)))
		{
			$this->data['names_salutations'] = $rows;
		}//end if
	}//end _load_names_salutations

	/** get external ratings
	 */
	protected function _load_external_ratings()
	{
		$this->data['external_ratings'] = array();
		$sql="SELECT  DISTINCT atvexrs_desc rate_source,
									amrexrt_ext_score rate_score,
									amrexrt_ext_value rate_value,
									amrexrt_ext_level rate_level,
									amrexrt_activity_date rate_date
						FROM  amrexrt,
									atvexrs
					 WHERE  amrexrt_pidm = :pidm
						 AND  amrexrt_exrs_code = atvexrs_code";
		if($rows = \PSU::db('banner')->GetAll($sql, array('pidm' => $this->pidm)))
		{
			$this->data['external_ratings'] = $rows;
		}//end if
	}//end _load_external_ratings

	/** get ratings
	 */
	protected function _load_ratings()
	{
		$this->data['ratings'] = array();
		$sql="  SELECT  atvrtgt_desc rate_type,
										atvrate_desc rating,
										decode(amrprrt_primary_ind,'Y','Yes','No') rate_primary,
										atvrate_rate_amt rating_amount,
										atvrscr_desc rater_type,
										amrprrt_activity_date rate_date
							FROM  amrprrt,
										atvrtgt,
										atvrate,
										atvrscr
						 WHERE  amrprrt_pidm = :pidm
							 AND  amrprrt_rtgt_code = atvrtgt_code
							 AND  amrprrt_rate_code = atvrate_code
							 AND  amrprrt_rscr_code = atvrscr_code
					ORDER BY  amrprrt_activity_date";
		if($rows = \PSU::db('banner')->GetAll($sql, array('pidm' => $this->pidm)))
		{
			$this->data['ratings'] = $rows;
		}//end if
	}//end _load_ratings

	/** get staff assignments
	 */
	protected function _load_staff_assignments()
	{
		$this->data['staff_assignments'] = array();
		$sql="SELECT	atvstft_desc staff_type,
									DECODE(guriden_desc,null,DECODE (spriden_first_name, '', '',spriden_first_name || ' ')|| DECODE (spriden_mi, '', '',spriden_mi || ' ') ||DECODE(spriden_surname_prefix,'','',spriden_surname_prefix || ' ') || spriden_last_name,guriden_desc) staff_name,
									DECODE(amrstaf_primary_ind,'Y','Yes','No') staff_primary,
									amrstaf_activity_date staff_date
						FROM  amrstaf,
									guriden,
									spriden,
									atvstft
					 WHERE  amrstaf_pidm = :pidm
						 AND  amrstaf_stft_code = atvstft_code
						 AND  amrstaf_iden_code = guriden_iden_code(+)
						 AND  amrstaf_staff_pidm = spriden_pidm(+)
						 AND  spriden_change_ind is null
				ORDER BY  amrstaf_primary_ind asc,
									amrstaf_activity_date";
		if($rows = \PSU::db('banner')->GetAll($sql, array('pidm' => $this->pidm)))
		{
			$this->data['staff_assignments'] = $rows;
		}//end if
	}//end _load_staff_assignments

	/** get staff assignments
	 */
	protected function _load_letter_history()
	{
		$this->data['letter_history'] = array();
		$sql="SELECT  DISTINCT gtvletr_desc letter,
									gurmail_date_printed date_printed,
									DECODE(guriden_desc,null,gurmail_user,guriden_desc) originator
						FROM  aprmail,
									gurmail,
									gtvletr,
									guriden
					 WHERE  aprmail_pidm=gurmail_pidm
						 AND  gurmail_user=guriden_user_id(+)
						 AND  gurmail_letr_code=gtvletr_code
						 AND  aprmail_pidm=:pidm
				ORDER BY	gurmail_date_printed desc";
		if($rows = \PSU::db('banner')->GetAll($sql, array('pidm' => $this->pidm)))
		{
			$this->data['letter_history'] = $rows;
		}//end if
	}//end _load_letter_history

	/** get pledge summary
	 */
	protected function _load_pledges()
	{
		$this->data['pledges'] = array();
		$sql="SELECT  agbpldg_pidm pidm,
									agbpldg_pledge_no pledge_no,
									agbpldg_pledge_date pledge_date,
									agbpldg_amt_pledged amount,
									agbpldg_amt_pledged-agrpdes_amt_paid balance, 
									atvpsta_desc status,
									'P' pldg_mult_mem_ind,
									afbcamp_name campaign,
                  afbcamp_cmtp_code campaign_type,
									adbdesg_name designation,
                  adbdesg_dgrp_code designation_type,
                  agbpldg_pcls_code pledge_class,
                  agbpldg_pcls_code_2 pledge_class2,
                  agbpldg_pcls_code_3 pledge_class3,
									agrpdes_campaign campaign_code
						FROM  agbpldg,
									agrpdes,
									afbcamp,
									adbdesg,
									atvpsta
					 WHERE  agrpdes_pidm = :pidm
						 AND  agrpdes_pidm = agbpldg_pidm
						 AND  agbpldg_psta_code = atvpsta_code
						 AND  agrpdes_campaign = afbcamp_campaign
						 AND  agrpdes_desg = adbdesg_desg
						 AND  agrpdes_pledge_no = agbpldg_pledge_no
					 UNION
					SELECT  agbpldg_pidm,
									agbpldg_pledge_no,
									agbpldg_pledge_date,
									agbpldg_amt_pledged,
									agbpldg_amt_pledged-agrpmlt_credit, 
									atvpsta_desc,
									'H',
									afbcamp_name,
                  '',
									adbdesg_name,
                  '',
                  agbpldg_pcls_code pledge_class,
                  agbpldg_pcls_code_2 pledge_class2,
                  agbpldg_pcls_code_3 pledge_class3,
									''
						FROM  agbpldg, 
									agrpmlt,
									afbcamp,
									adbdesg,
									atvpsta
					 WHERE  agrpmlt_xref_pidm = :pidm 
						 AND  agbpldg_pidm = agrpmlt_pidm
						 AND  agbpldg_psta_code = atvpsta_code
						 AND  agrpmlt_campaign = afbcamp_campaign
						 AND  agrpmlt_desg = adbdesg_desg
						 AND  agbpldg_pledge_no = agrpmlt_pledge_no
					 UNION
					SELECT  agbpldg_pidm,
									agbpldg_pledge_no,
									agbpldg_pledge_date,
									agbpldg_amt_pledged,
									agbpldg_amt_pledged-agrpmmo_credit, 
									atvpsta_desc,
									'S',
									afbcamp_name,
                  '',
									adbdesg_name,
                  '',
                  agbpldg_pcls_code pledge_class,
                  agbpldg_pcls_code_2 pledge_class2,
                  agbpldg_pcls_code_3 pledge_class3,
									''
						FROM  agbpldg, 
									agrpmmo,
									afbcamp,
									adbdesg,
									atvpsta
					 WHERE  agrpmmo_xref_pidm = :pidm 
						 AND  agbpldg_pidm = agrpmmo_pidm
						 AND  agbpldg_psta_code = atvpsta_code
						 AND  agrpmmo_campaign = afbcamp_campaign
						 AND  agrpmmo_desg = adbdesg_desg
						 AND  agbpldg_pledge_no = agrpmmo_pledge_no
				ORDER BY  3 desc, 
									2 desc, 
									1 ";
		if($results = \PSU::db('banner')->Execute($sql, array('pidm' => $this->pidm)))
		{
			foreach($results as $row)
			{
				if ($row['pldg_mult_mem_ind'] == 'P')
				{
					$row['credit']= 'Self';
				}
				elseif ($row['pldg_mult_mem_ind'] == 'H') 
				{
					$row['credit']= 'Hard';
				}
				else
				{			
					$row['credit']= 'Soft';
				}
				$this->data['pledges'][] = $row;
			}//end if
		}// end foreach
	}//end _load_pledges

	/** get gift summary
	 */
	protected function _load_gift_summary()
	{
		$sql="  SELECT  DISTINCT  apbghis_last_gift_date recent_gift_date,
										a.adbdesg_name recent_designation,
										apbghis_last_gift_amt recent_gift_amount,
										apbghis_fisc_code_last_gift recent_gift_year,
										apbghis_high_gift_date high_gift_date,
										apbghis_high_gift_amt high_gift_amount,
										b.adbdesg_name high_designation,
										apbghis_total_no_gifts number_gifts,
										SUM(aprchis_amt_pledged_paid)+SUM(aprchis_amt_gift) hard_credit,
										SUM(NVL(aprchis_amt_pledged_paid,0)) pledge_payments,
										SUM(NVL(aprchis_amt_gift,0)) outright_gifts
							FROM  apbghis,
										aprchis,
										adbdesg a,
										adbdesg b,
										agrgdes c,
										agrgdes d
						 WHERE  apbghis_pidm=:pidm
							 AND  aprchis_pidm=apbghis_pidm
							 AND  apbghis_pidm=c.agrgdes_pidm
							 AND  c.agrgdes_gift_no=apbghis_last_gift_no
							 AND  d.agrgdes_gift_no=apbghis_high_gift_no
							 AND  c.agrgdes_desg=a.adbdesg_desg
							 AND  d.agrgdes_desg=b.adbdesg_desg
					GROUP BY  apbghis_last_gift_date,
										a.adbdesg_name,
										apbghis_last_gift_amt,
										apbghis_fisc_code_last_gift,
										apbghis_high_gift_date,
										apbghis_high_gift_amt,
										b.adbdesg_name,
										apbghis_total_no_gifts";
		if($gift_summary = \PSU::db('banner')->GetRow($sql, array('pidm' => $this->pidm)))
		{
			foreach( $gift_summary as $key => $value ) {
				$this->data[ $key ] = $value;
			}//end foreach
		}//end if
	}//end _load_gift_summary

	/** get matching and soft gift summary
	 */
	protected function _load_matching_soft()
	{
		$this->data['matching_soft'] = array();

		$sql="SELECT	DECODE(agrgmlt_3pp_pledge_no,NULL,'No','Yes') third_party_credit
						FROM	agrgmlt
					 WHERE	(agrgmlt_pidm = :pidm OR agrgmlt_xref_pidm = :pidm)";
		$third_party_credit = \PSU::db('banner')->GetOne($sql, array('pidm' => $this->pidm));

		$sql="SELECT	NVL(sum(axrmcam_amt),0) xmg_pledge
					FROM  axrmcam,
								axbmgid,
								agbpldg
				 WHERE	axrmcam_pidm        =  :pidm
					 AND  axrmcam_pledge_no   =  axbmgid_pledge_no
					 AND  axbmgid_pidm        =  :pidm
					 AND	(axbmgid_void_ind   = 'V'
						OR  axbmgid_void_ind    IS NULL)
					 AND	axbmgid_temp_ind    IS NULL
					 AND	axbmgid_pledge_no   =  axrmcam_pledge_no
					 AND	axrmcam_empr_pidm   =  axbmgid_empr_pidm
					 AND	agbpldg_pidm        =  :pidm
					 AND	agbpldg_pledge_no   =  axrmcam_pledge_no";
		$xmg_pledge = \PSU::db('banner')->GetOne($sql, array('pidm' => $this->pidm));

		$sql="SELECT	NVL(SUM(mg.agrgcam_amt),0) xmg_paymnt
				FROM  axbmgid, 
							agrgcam mg, 
							agrgcam empl, 
							agrmgif, 
							agbpldg
			 WHERE	axbmgid_pidm            = :pidm
				 AND  axbmgid_pidm            =  agbpldg_pidm
				 AND  axbmgid_pidm            =  agrmgif_pidm
				 AND  agbpldg_pledge_no       =  axbmgid_pledge_no
				 AND  mg.agrgcam_pidm         =  axbmgid_empr_pidm
				 AND  mg.agrgcam_gift_no      =  agrmgif_empr_gift_no
				 AND  mg.agrgcam_pidm         =  agrmgif_empr_pidm
				 AND  empl.agrgcam_gift_no    =  agrmgif_gift_no
				 AND  empl.agrgcam_pledge_no  =  axbmgid_pledge_no
				 AND  empl.agrgcam_pidm       =  axbmgid_pidm
				 AND  empl.agrgcam_pidm       =  agrmgif_pidm
				 AND  empl.agrgcam_pidm       = :pidm";
		$xmg_payment = \PSU::db('banner')->GetOne($sql, array('pidm' => $this->pidm));

    $sql="SELECT NVL(SUM(agrmcam_amt),0) xmg_gift
						FROM  agbmgid, agrmcam,agrgcam,agbgift
					 WHERE	agrmcam_empl_pidm     =  agbmgid_empl_pidm
						 AND  agrmcam_gift_no       =  agbmgid_empl_gift_no
						 AND  agbmgid_empl_pidm     =  :pidm
						 AND  (agbmgid_status       = 'I'
						  OR  agbmgid_status        IS NULL)
						 AND  agbmgid_delete_ind    IS NULL
						 AND  agrmcam_empr_pidm     =  agbmgid_empr_pidm
						 AND  agbgift_pidm          =  :pidm
						 AND  agbgift_gift_no       =  agrmcam_gift_no
						 AND  agrgcam_pidm          =  agbmgid_empl_pidm
						 AND  agrgcam_campaign      =  agrmcam_campaign
						 AND  agrgcam_gift_no       =  agrmcam_gift_no
						 AND  AGRGCAM_PLEDGE_NO     = '0000000'";
		$xmg_gift = \PSU::db('banner')->GetOne($sql, array('pidm' => $this->pidm));

		$sql="SELECT	ROUND(NVL(SUM(NVL(agrmcam_amt, 0) * NVL(((NVL(
									agbmgid_amt, 0) - NVL(agbmgid_amt_paid, 0)) /
									agbmgid_amt), 0)), 0), 2) wait_mg
						FROM  agbmgid, 
									agrmcam
					 WHERE  agrmcam_empl_pidm = agbmgid_empl_pidm
						 AND  agrmcam_gift_no = agbmgid_empl_gift_no
						 AND  agbmgid_empl_pidm = :pidm
						 AND  agbmgid_empl_gift_no IN  (SELECT	agbgift_gift_no
														  FROM  agbgift
														 WHERE  agbgift_pidm = :pidm)
						 AND  (agbmgid_status = 'I'
							OR  agbmgid_status IS NULL)
						 AND  agbmgid_delete_ind IS NULL
						 AND  agrmcam_empr_pidm = agbmgid_empr_pidm";   
		$wait_mg = \PSU::db('banner')->GetOne($sql, array('pidm' => $this->pidm));

		$sql="SELECT	NVL(SUM(x.agrgmmo_credit), 0) match_gift_sum
						FROM  agrgmmo x
					 WHERE  x.agrgmmo_xref_pidm = :pidm
						 AND  x.agrgmmo_gift_no IN  (SELECT	  agbgift_gift_no
													   FROM   agbgift y
													  WHERE	  agbgift_pidm = x.agrgmmo_pidm
														AND	  agbgift_gift_no IN  (SELECT		agrmgif_empr_gift_no
																																				 FROM   agrmgif
																																				WHERE   y.agbgift_gift_no = agrmgif_empr_gift_no
																																					AND   y.agbgift_pidm = agrmgif_empr_pidm
																																					AND		agrmgif_pidm = :pidm)  )";
		$match_gift_sum = \PSU::db('banner')->GetOne($sql, array('pidm' => $this->pidm));

		$sql="SELECT	NVL(SUM(x.agrgmmo_credit), 0) match_gift_sum2
						FROM  agrgmmo x
					 WHERE  x.agrgmmo_pidm = :pidm
						 AND  x.agrgmmo_gift_no IN  (SELECT		agbgift_gift_no
																					 FROM		agbgift  y
																					WHERE		agbgift_pidm = x.agrgmmo_pidm
																						AND		agbgift_gift_no IN  (SELECT		agrmgif_empr_gift_no
																																				 FROM   agrmgif
																																				WHERE		y.agbgift_gift_no = agrmgif_empr_gift_no
																																					AND		y.agbgift_pidm = agrmgif_empr_pidm )  )";
		$match_gift_sum2 = \PSU::db('banner')->GetOne($sql, array('pidm' => $this->pidm));

    $sql="SELECT	NVL(SUM(agrgmmo_credit), 0) soft_credit
						FROM	agrgmmo
					 WHERE	agrgmmo_xref_pidm = :pidm
						 AND	NOT EXISTS (SELECT	'Y' 
																FROM	agrmgif
															 WHERE	agrmgif_empr_pidm = agrgmmo_pidm
																 AND	agrmgif_empr_gift_no = agrgmmo_gift_no
																 AND	agrmgif_pidm = agrgmmo_xref_pidm)";
		$soft_credit = \PSU::db('banner')->GetOne($sql, array('pidm' => $this->pidm));

		$sql="SELECT	NVL(SUM(agrgmmo_credit), 0) memo_cred_sum
						FROM	agrgmmo
					 WHERE	agrgmmo_xref_pidm = :pidm
						 AND	NOT EXISTS (SELECT 'Y' 
																FROM agrmgif
															 WHERE agrmgif_empr_pidm = agrgmmo_pidm
																 AND agrmgif_empr_gift_no = agrgmmo_gift_no
																 AND agrmgif_pidm = agrgmmo_xref_pidm)";
		$memo_cred_sum = \PSU::db('banner')->GetOne($sql, array('pidm' => $this->pidm));

		$fields = array(
			'total_pledge_amount' => 'aprchis_amt_pledged',
			'total_paymt_amount' => 'aprchis_amt_pledged_paid',
			'total_gift_amount' => 'aprchis_amt_gift'
		);

		foreach( $fields as $index => $sql_field ) {
			$sql="SELECT NVL (SUM (".$sql_field."),0) FROM	aprchis WHERE	aprchis_pidm = :pidm";
			$$index = \PSU::db('banner')->GetOne($sql, array('pidm' => $this->pidm));
		}//end foreach

    $sql="SELECT	NVL (SUM (agrgmem_3pp_amt), 0) total_3pp_memo_credit FROM	agrgmem WHERE	agrgmem_memo_pidm = :pidm";
		$total_3pp_memo_credit = \PSU::db('banner')->GetOne($sql, array('pidm' => $this->pidm));

    $sql="SELECT	NVL (SUM (agrgids_3pp_amt), 0) total_3pp_mult_credit FROM	agrgids WHERE  agrgids_xref_pidm = :pidm AND	agrgids_amt = 0";
		$total_3pp_mult_credit = \PSU::db('banner')->GetOne($sql, array('pidm' => $this->pidm));

		/**** not sure what the use of memo credit is if it is being added then subtracted 
		$this->data['matching_soft']['total_payment_amount'] = $this->data['matching_soft']['total_3pp_memo_credit'] + $this->data['matching_soft']['total_3pp_mult_credit'];
		$this->data['matching_soft']['total_payment_amount'] = $this->data['matching_soft']['total_payment_amount'] - $this->data['matching_soft']['total_3pp_memo_credit'];
		****/
		$pledge_total = $xmg_pledge - $xmg_payment;
		$pledge_total = $pledge_total ? $pledge_total : 0;

		$waiting_match = $pledge_total + $xmg_gift;
		$waiting_match = $waiting_match == 0 ? $wait_mg : $waiting_match;
		$waiting_match = $waiting_match ? $waiting_match : 0;

		$match_credit = $match_gift_sum == 0 ? $match_gift_sum2 : $match_gift_sum;

		$total_soft_credit = $match_credit + $memo_cred_sum;

		$total_payment_amount = $total_3pp_mult_credit;
		$gift_summary = $total_gift_amount + $total_payment_amount;
		$grand_total = $total_soft_credit + $gift_summary;

		$this->data['matching_soft']['third_party_credit'] = $third_party_credit;
		$this->data['matching_soft']['waiting_match'] = $waiting_match;
		$this->data['matching_soft']['match_credit'] = $match_credit;
		$this->data['matching_soft']['soft_credit'] = $soft_credit;
		$this->data['matching_soft']['total_soft_credit'] = $total_soft_credit;
		$this->data['matching_soft']['grand_total'] = $grand_total;
	}//end _load_matching_soft

	/** get projects and interests
	 */
	protected function _load_projects()
	{
		$this->data['projects'] = array();
		$sql="SELECT  atvproj_desc project_interest,
									atvgivh_desc giving_vehicle,
									amrprin_target_ask_amt target_ask_amt,
									amrprin_target_ask_date target_ask_date
						FROM  atvproj,
									atvgivh,
									amrprin
					 WHERE  atvproj_code(+) = amrprin_proj_code
						 AND  atvgivh_code(+) = amrprin_givh_code
						 AND  amrprin_pidm = :pidm";
		if($rows = \PSU::db('banner')->GetAll($sql, array('pidm' => $this->pidm)))
		{
			$this->data['projects'] = $rows;
		}//end if
	}//end _load_projects

	/** get strategy plans
	 */
	protected function _load_strategy_plans()
	{
		$this->data['strategy_plans'] = array();
		$sql="SELECT  ambplan_stgy_plan strategy,
									atvproj_desc project,
									ambplan_start_date start_date,
									guriden_desc moves_manager
						FROM  guriden,
									atvproj,
									ambplan
					 WHERE  guriden_iden_code = ambplan_iden_code
						 AND  atvproj_code = ambplan_proj_code
						 AND  ambplan_pidm = :pidm
				ORDER BY  ambplan_start_date desc";
		if($rows = \PSU::db('banner')->GetAll($sql, array('pidm' => $this->pidm)))
		{
			$this->data['strategy_plans'] = $rows;
		}//end if
	}//end _load_strategy_plans

	/** get prospect contacts
	 */
	protected function _load_prospect_contacts()
	{
		$this->data['prospect_contacts'] = array();
		$sql="SELECT  amrcont_item_refno contact_refno,
									atvprop_desc proposal,
									amrcont_prop_seq_no seqno,
									guriden_desc originator,
									atvscnt_desc contact,
									amrcont_contact_date contact_date,
									atvmove_desc moves,
									amrcont_tickler action,
									amrcont_tick_date action_date,
									atvproj_desc project,
									amrcont_contact descripton,
									amrcont_call_report call_report,
									gtvexpn_desc expenses,
									amrpexp_activity_date exp_date,
									amrpexp_expn_amt amount
						FROM  amrcont,
									atvprop,
									guriden,
									atvscnt,
									atvmove,
									atvproj,
									gtvexpn,
									amrpexp,
									aobdowp
					 WHERE	amrcont_pidm = :pidm
						 AND	atvprop_code(+) = amrcont_prop_code
						 AND	guriden_iden_code(+) = amrcont_iden_code
						 AND	atvscnt_code(+) = amrcont_scnt_code
						 AND	amrcont_move_code = atvmove_code(+)
						 AND	amrcont_proj_code = atvproj_code(+)
						 AND	amrpexp_pidm(+) = amrcont_pidm
						 AND	amrpexp_expn_code = gtvexpn_code(+)
						 AND	aobdowp_pidm(+)=amrcont_pidm
						 AND	((SELECT ADD_MONTHS (sysdate, aobdowp_contact_months * - 1)
										 FROM DUAL) IS NULL OR (SELECT ADD_MONTHS (sysdate, aobdowp_contact_months * - 1)
										 FROM DUAL) > amrcont_contact_date)
				ORDER BY	amrcont_contact_date desc, amrcont_item_refno desc";
		if($rows = \PSU::db('banner')->GetAll($sql, array('pidm' => $this->pidm)))
		{
			$this->data['prospect_contacts'] = $rows;
		}//end if
	}//end _load_prospect_contacts


	/** get proposals
	 */
	protected function _load_proposal()
	{
		$this->data['proposal'] = array();
		$sql="SELECT  DISTINCT atvprop_desc proposal,
									ambprop_prop_seq_no sequence_no,
									atvprst_desc status,
									DECODE(guriden_desc,NULL,DECODE (spriden_first_name, '', '', spriden_first_name || ' ' )|| DECODE (spriden_mi,'', '',spriden_mi || ' ')|| DECODE(spriden_surname_prefix,'','',spriden_surname_prefix || ' '),guriden_desc) || spriden_last_name staff,
									ambprop_create_date create_date,
									ambprop_ask_amount amount,
									ambprop_due_date due_date,
									amrprop_frbprop_code finance_proposal,
									atvproj_desc project,
									atvgivh_desc giving_vehicle,
									ambprop_prop_code prop_code,
									ambprop_ask_amount target_ask_amount
						FROM  ambprop,
									atvprop,
									atvprst,
									atvproj, 
									atvgivh,
									guriden,
									spriden,
									amrprop
					 WHERE  ambprop_prop_code = atvprop_code(+)
						 AND  amrprop_proj_code = atvproj_code(+)
						 AND  amrprop_givh_code = atvgivh_code(+)
						 AND  ambprop_pidm = :pidm
						 AND  amrprop_pidm=ambprop_pidm
						 AND  ambprop_prst_code = atvprst_code(+)
						 AND  ambprop_staff_iden_code = guriden_iden_code(+)
						 AND  ambprop_staff_pidm = spriden_pidm(+)
						 AND  spriden_change_ind is null";
		if($results = \PSU::db('banner')->Execute($sql, array('pidm' => $this->pidm)))
		{
			foreach($results as $row)
			{
				$sql2=" SELECT  ambprop_comment comments
									FROM  ambprop
								 WHERE  ambprop_prop_code = '".$row['prop_code']."'
									 AND  ambprop_pidm = :pidm
									 AND	ambprop_prop_seq_no = '".$row['sequence_no']."'
									 AND	ambprop_ask_amount = '".$row['amount']."'";
				$row['comments'] = \PSU::db('banner')->GetOne($sql2, array('pidm' => $this->pidm));
				$this->data['proposal'][] = $row;
			}//end foreach
		}//end if
	}//end _load_proposal


	/** get research data
	 */
	protected function _load_research_data()
	{
		$this->data['research_data'] = array();
		$sql="SELECT	atvusrc_desc data_type,
									amrpusr_effect_date effective_date,
									amrpusr_activity_date activity_date,
									amrpusr_value research_data
						FROM	amrpusr, 
									atvusrc
					 WHERE	amrpusr_pidm=:pidm
						 AND	amrpusr_usrc_code = atvusrc_code
				ORDER BY	trunc(amrpusr_effect_date) desc,
									upper(atvusrc_desc)";
		if($rows = \PSU::db('banner')->GetAll($sql, array('pidm' => $this->pidm)))
		{
			$this->data['research_data'] = $rows;
		}//end if
	}//end _load_research_data

	/** get research data
	 */
	protected function _load_psu_names()
	{
		$this->data['psu_names'] = array();
		$sql="SELECT	pres_name_line_1,
									pres_name_line_2,
									pres_salutation
						FROM	odsmgr.psu_names
					 WHERE	pidm_key=:pidm";
		if($rows = \PSU::db('pods')->GetAll($sql, array('pidm' => $this->pidm)))
		{
			$this->data['psu_names'] = $rows;
		}//end if
	}//end _load_psu_names


	/** get prospect comments
	 */
	protected function _load_prospect_comments()
	{
		$this->data['prospect_comments'] = array();
		$sql="  SELECT  DISTINCT guriden_desc originator,
										amrconf_entry_date entry_date,
										gtvsubj_desc subject_indexes,
										amrconf_confid_ind confidential,
										amrconf_grp_seq_no grp_seq_no,
										amrsubj_subj_code subj_code,
										amrconf_iden_code iden_code
							FROM  amrconf, 
										guriden,
										gtvsubj,
										amrsubj
						 WHERE  amrconf_pidm = :pidm
							 AND	amrconf_grp_seq_no = amrsubj_grp_seq_no
							 AND  amrsubj_subj_code = gtvsubj_code
							 AND  amrconf_iden_code = guriden_iden_code
					ORDER BY  trunc(amrconf_entry_date) desc,
										guriden_desc";
		if($results = \PSU::db('banner')->Execute($sql, array('pidm' => $this->pidm)))
		{
			foreach($results as $row)
			{
				$sql2=" SELECT  amrconf_comment comments
									FROM  amrconf, 
												guriden,
												gtvsubj,
												amrsubj
								 WHERE  amrconf_pidm = :pidm
									 AND	guriden_desc = '".trim($row['originator'])."'
									 AND	gtvsubj_desc  = '".trim($row['subject_indexes'])."'
									 AND	amrconf_grp_seq_no = '".trim($row['grp_seq_no'])."'
									 AND  amrsubj_subj_code = '".trim($row['subj_code'])."'
									 AND  amrconf_iden_code = '".trim($row['iden_code'])."'
									 AND	rownum=1";
				$row['comments'] = \PSU::db('banner')->GetOne($sql2, array('pidm' => $this->pidm));
				$this->data['prospect_comments'][] = $row;
			}//end foreach
		}//end if
	}//end _load_prospect_comments

	/** get gift history table
	 */
	protected function _load_gift_history()
	{
		$this->data['gift_history'] = array();
		$sql="SELECT  agbgift_pidm gift_pidm, 
									agbgift_gift_no gift_no, 
									agbgift_gift_date gift_date,
									agrgdes_amt gift_amount,
									'' third_party_pledge, 
									'G' gift_mult_mem_ind,
									afbcamp_name campaign,
                  afbcamp_cmtp_code campaign_type,
									adbdesg_name designation,
                  adbdesg_dgrp_code designation_type,
									agrgdes_pledge_no pledge_no,
									agbgift_gcls_code gift_class,
									agbgift_gcls_code_2 gift_class2,
									agbgift_gcls_code_3 gift_class3,
									agrgdes_campaign campaign_code
						FROM  agbgift, 
									agrgdes, 
									adbdesg, 
									afbcamp
					 WHERE  agbgift_pidm = :pidm
						 AND  agbgift_gift_no = agrgdes_gift_no
						 AND  agbgift_pidm = agrgdes_pidm
						 AND  agrgdes_campaign = afbcamp_campaign
						 AND  agrgdes_desg = adbdesg_desg
					 UNION
					SELECT  agbgift_pidm, 
									agbgift_gift_no, 
									agbgift_gift_date,
									agbgift_amt_tot, 
									agrgmlt_3pp_pledge_no, 
									'H', 
									afbcamp_name, 
                  '',
									adbdesg_name,
                  '',
									agrgmlt_pledge_no,
									agbgift_gcls_code gift_class,
									agbgift_gcls_code_2 gift_class2,
									agbgift_gcls_code_3 gift_class3,
									''
						FROM  agbgift, 
									agrgmlt, 
									adbdesg, 
									afbcamp
					 WHERE  agrgmlt_xref_pidm = :pidm 
						 AND  agbgift_gift_no = agrgmlt_gift_no 
						 AND  agbgift_pidm = agrgmlt_pidm
						 AND  agrgmlt_campaign = afbcamp_campaign
						 AND  agrgmlt_desg = adbdesg_desg
					 UNION
					SELECT  agbgift_pidm, 
									agbgift_gift_no, 
									agbgift_gift_date,
									agbgift_amt_tot, 
									agrgmmo_3pp_pledge_no, 
									'S',
									afbcamp_name, 
                  '',
									adbdesg_name,
                  '',
									agrgmmo_pledge_no,
									agbgift_gcls_code gift_class,
									agbgift_gcls_code_2 gift_class2,
									agbgift_gcls_code_3 gift_class3,
									''
						FROM  agbgift, 
									agrgmmo, 
									adbdesg, 
									afbcamp
					 WHERE  agrgmmo_xref_pidm = :pidm 
						 AND  agbgift_gift_no = agrgmmo_gift_no 
						 AND  agbgift_pidm = agrgmmo_pidm
						 AND  agrgmmo_campaign = afbcamp_campaign
						 AND  agrgmmo_desg = adbdesg_desg
				ORDER BY  3 desc, 
									2 desc, 
									1 ";
		if($results = \PSU::db('banner')->Execute($sql, array('pidm' => $this->pidm)))
		{
			foreach($results as $row)
			{
				if($row['pledge_no'] !='0000000')
				{
          $row['type'] = 'Payment';
				}
				else
				{
          $row['type'] = 'Gift';
				}
        if($row['gift_mult_mem_ind'] == 'G')
        {
					$row['credit'] = 'Self';
				}
				elseif($row['gift_mult_mem_ind'] == 'H')
				{
					$row['credit'] = 'Hard';
				}
        else
				{
					$row['credit'] = 'Soft';
				}
				$this->data['gift_history'][] = $row;
			}//end foreach
		}//end if
	}//end _load_gift_history

	static function donorDescriptions() {
		$descs = array();

		$sql = "SELECT atvdonr_code, atvdonr_desc FROM atvdonr ORDER BY atvdonr_desc";
		$res = \PSU::db('banner')->Execute($sql);
		while($row = $res->FetchRow()) {
			$descs[$row['atvdonr_code']] = $row['atvdonr_desc'];			
		}
		return $descs;
	}// end donorDescription

	static function campaignTypes() {
		$camptypes = array();

		$sql = "SELECT atvcmtp_code, atvcmtp_desc FROM atvcmtp ORDER BY atvcmtp_desc";
		$results = \PSU::db('banner')->Execute($sql);
		while($row = $results->FetchRow()) {
			$camptypes[$row['atvcmtp_code']] = $row['atvcmtp_desc'];			
		}
		return $camptypes;
	}// end campaignTypes

	static function campaignDescription() {
		$campdesc = array();

		$sql = "SELECT afbcamp_campaign, afbcamp_name FROM afbcamp ORDER BY afbcamp_name";
		$results = \PSU::db('banner')->Execute($sql);
		while($row = $results->FetchRow()) {
			$campdesc[$row['afbcamp_campaign']] = $row['afbcamp_name'];			
		}
		return $campdesc;
	}// end campaignDescription

	static function designationDescription() {
		$desgdesc = array();

		$sql = "SELECT adbdesg_desg, adbdesg_name FROM adbdesg ORDER BY adbdesg_name";
		$results = \PSU::db('banner')->Execute($sql);
		while($row = $results->FetchRow()) {
			$desgdesc[$row['adbdesg_desg']] = $row['adbdesg_name'];			
		}
		return $desgdesc;
	}// end designationDescription

}//end class PSU_Alumni
