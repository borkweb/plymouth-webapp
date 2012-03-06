<?php
/**
 * guasyst.class.php
 *
 * === Modification History ===<br/>
 * 0.1.0  23-Apr-2009  [djb]  original<br/>
 *
 */

// Items to configure
//		Not ready at this time

/**
 * duplicates.class.php
 *
 * Class for Duplicates Application
 *
 * @version		0.1.0
 * @module		guasyst.class.php
 * @author		Dan Bramer <djbramer@plymouth.edu>
 * @copyright 2009, Plymouth State University, ITS
 */ 

class Duplicates
{

	function deduplicate($arr,$p1='id_1',$p2='id_2')
	{
		$unique_pidm=array();
		$dedup=array();
		foreach($arr as $line)
		{
			if (!in_array($line[$p1],$unique_pidm))
			{
				$unique_pidm[] = $line[$p1];
				$dedup[]=$line;
			}
			if (!in_array($line[$p2],$unique_pidm))
			{
				$unique_pidm[] = $line[$p2];
			}
		}
		//print_r($unique_pidm);
		//print_r($dedup);
		return $dedup;
	}
	
	function duplicateDOB($ssnstatus=-1) // can also be 0, 1, or 2
	{
		if ($ssnstatus == -1)
		{
			$extrawhere='';
		} else if ($ssnstatus == 0)
		{
			$extrawhere='AND pers1.spbpers_ssn is null AND pers2.spbpers_ssn is null';
		} else if ($ssnstatus == 1)
		{
			$extrawhere='AND (pers1.spbpers_ssn is not null OR pers2.spbpers_ssn not null)';
		} else if ($ssnstatus == 2)
		{
			$extrawhere='AND pers1.spbpers_ssn is not null AND pers2.spbpers_ssn is not null';
		} else
		{
			$extrawhere='';
		}
		
		$sql = "
			SELECT iden1.spriden_id AS id_1,
				iden1.spriden_pidm AS pidm_1,
				iden1.spriden_last_name AS last_name_1,
				iden1.spriden_first_name AS first_name_1,
				pers1.spbpers_birth_date AS birth_date_1,
				iden1.spriden_activity_date AS date_1,
				iden2.spriden_id AS id_2,
				iden2.spriden_pidm AS pidm_2,
				iden2.spriden_last_name AS last_name_2,
				iden2.spriden_first_name AS first_name_2,
				pers2.spbpers_birth_date AS birth_date_2,
				iden2.spriden_activity_date AS date_2
			FROM (
					(
						spriden iden1 INNER JOIN spbpers pers1
							ON iden1.spriden_pidm = pers1.spbpers_pidm
							AND iden1.spriden_change_ind IS NULL
					) INNER JOIN (
						spriden iden2 INNER JOIN spbpers pers2
							ON iden2.spriden_pidm = pers2.spbpers_pidm
							AND iden2.spriden_change_ind IS NULL
					)	
				ON iden1.spriden_pidm <> iden2.spriden_pidm       
				)     
			WHERE iden1.spriden_last_name = iden2.spriden_last_name
				AND SUBSTR(iden1.spriden_first_name,1,3) = SUBSTR(iden2.spriden_first_name,1,3)
				AND pers1.spbpers_birth_date = pers2.spbpers_birth_date
				".$extrawhere." 
			ORDER BY iden1.spriden_activity_date DESC
  		";
		$results = $this->_db->Execute($sql);
		while($row=$results->FetchRow())
		{
			$dups[] = $row;
		}
		return $dups;
	}
	
	function duplicateSSN()
	{
		$sql = "
			SELECT iden1.spriden_id AS id_1,
				iden1.spriden_pidm AS pidm_1,
				iden1.spriden_last_name AS last_name_1,
				iden1.spriden_first_name AS first_name_1,
				iden1.spriden_activity_date AS date_1,
				iden2.spriden_id AS id_2,
				iden2.spriden_pidm AS pidm_2,
				iden2.spriden_last_name AS last_name_2,
				iden2.spriden_first_name AS first_name_2,
				iden2.spriden_activity_date AS date_2
 			FROM (
					(
						spriden iden1 INNER JOIN spbpers pers1
							ON iden1.spriden_pidm = pers1.spbpers_pidm
							AND iden1.spriden_change_ind IS NULL
					) INNER JOIN (
						spriden iden2 INNER JOIN spbpers pers2
							ON iden2.spriden_pidm = pers2.spbpers_pidm
							AND iden2.spriden_change_ind IS NULL
					)
				ON iden1.spriden_pidm <> iden2.spriden_pidm
				)
			WHERE pers1.spbpers_ssn = pers2.spbpers_ssn
			ORDER BY pers1.spbpers_ssn,iden1.spriden_last_name
		";

		$results = $this->_db->Execute($sql);
		while($row=$results->FetchRow())
		{
			$dups[] = $row;
		}
		return $dups;
	}
	
	
	function duplicateAddress()
	{
	$sql = "

SELECT iden1.spriden_id AS id_1,
      iden1.spriden_pidm AS pidm_1,
      iden1.spriden_last_name AS last_name_1,
      iden1.spriden_first_name AS first_name_1,
      addr1.spraddr_street_line1 AS addr_1,
      addr1.spraddr_city AS city_1,
      addr1.spraddr_stat_code AS state_1,
      iden1.spriden_activity_date AS date_1,
      iden2.spriden_id AS id_2,
      iden2.spriden_pidm AS pidm_2,
      iden2.spriden_last_name AS last_name_2,
      iden2.spriden_first_name AS first_name_2,
      addr2.spraddr_street_line1 AS addr_2,
      addr2.spraddr_city AS city_2,
      addr2.spraddr_stat_code AS state_2,
      iden2.spriden_activity_date AS date_2
 FROM (
       (
        spriden iden1 INNER JOIN spraddr addr1
         ON iden1.spriden_pidm = addr1.spraddr_pidm
        AND iden1.spriden_change_ind IS NULL
        AND iden1.spriden_entity_ind = 'P'
        AND addr1.spraddr_atyp_code = 'MA'
        AND addr1.spraddr_seqno = (SELECT MAX(addr3.spraddr_seqno) 
                                      FROM spraddr addr3 
                                    where iden1.spriden_pidm = addr3.spraddr_pidm
                                      AND iden1.spriden_change_ind IS NULL
                                      AND iden1.spriden_entity_ind = 'P'
                                      AND addr3.spraddr_atyp_code = 'MA'
                                    )
       ) INNER JOIN (
        spriden iden2 INNER JOIN spraddr addr2
         ON iden2.spriden_pidm = addr2.spraddr_pidm
        AND iden2.spriden_change_ind IS NULL
        AND iden2.spriden_entity_ind = 'P'
        AND addr2.spraddr_atyp_code = 'MA'
        AND addr2.spraddr_seqno = (SELECT MAX(addr4.spraddr_seqno) 
                                      FROM spraddr addr4 
                                    where iden2.spriden_pidm = addr4.spraddr_pidm
                                      AND iden2.spriden_change_ind IS NULL
                                      AND iden2.spriden_entity_ind = 'P'
                                      AND addr4.spraddr_atyp_code = 'MA'
                                    )
       )
       ON iden1.spriden_pidm <> iden2.spriden_pidm
     )
WHERE iden1.spriden_last_name = iden2.spriden_last_name
  AND iden1.spriden_first_name = iden2.spriden_first_name
  AND addr1.spraddr_street_line1 = addr2.spraddr_street_line1
  AND addr1.spraddr_city = addr2.spraddr_city
  AND addr1.spraddr_stat_code = addr2.spraddr_stat_code
  ORDER BY iden1.spriden_activity_date DESC

  ";
  
		$results = $this->_db->Execute($sql);
		while($row=$results->FetchRow())
		{
			$dups[] = $row;
		}
		return $dups;
	}

	function getGuasystInfo($pidm)
	{
		$guaheader = array("Student","Advancement","FinAid","AcctRecv");

		$guatable[0]=array("SRBRECR","SARADAP","SHRTRAM","SGBSTDN","SFBETRM","SLBRMAP","SIBINST");
		$guatable[1]=array("APBCONS","AOBORGN");
		$guatable[2]=array("RORSTAT");
		$guatable[3]=array("TBRACCD","TBRDEPO","TBRMEMO","TBBCPRF");

		$gualink = array(
			$guaheader[0]=>array(
				"SRBRECR"=>"Recruiting",
				"SARADAP"=>"Admissions",
				"SHRTRAM"=>"Transfer Work",
				"SGBSTDN"=>"General Student",
				"SFBETRM"=>"Registration",
				"SLBRMAP"=>"Housing",
				"SIBINST"=>"Faculty"
			),
			$guaheader[1]=>array(
				"APBCONS"=>"Individual",
				"AOBORGN"=>"Organization"
			),
			$guaheader[2]=>array(
				"RORSTAT"=>"Applicant"
			),
			$guaheader[3]=>array(
				"TBRACCD"=>"Accounts Receivable",
				"TBRDEPO"=>"Accounts Receivable1",
				"TBRMEMO"=>"Accounts Receivable2",
				"TBBCPRF"=>"Accounts Receivable3"
			)
		);
		$guastatus = array(
			$guaheader[0]=>array(
				"Recruiting"=>"N",
				"Admissions"=>"N",
				"Transfer Work"=>"N",
				"General Student"=>"N",
				"Registration"=>"N",
				"Housing"=>"N",
				"Faculty"=>"N"
			),
			$guaheader[1]=>array(
				"Individual"=>"N",
				"Organization"=>"N"
			),
			$guaheader[2]=>array(
				"Applicant"=>"N"
			),
			$guaheader[3]=>array(
				"Accounts Receivable"=>"N",
				"Accounts Receivable1"=>"N",
				"Accounts Receivable2"=>"N",
				"Accounts Receivable3"=>"N"
			)
		);


		for($i=0;$i<count($guaheader);$i++)
		{
			foreach($guatable[$i] as $arr)
			{
				$sql = "SELECT f_ispidmintable('".$arr."','".$arr."_PIDM',$pidm) from dual";
				//echo $sql;
				$res = $this->_db->GetOne($sql);
				$guastatus[($guaheader[$i])][$gualink[$guaheader[$i]][$arr]] = $this->_db->GetOne($sql);
			}
		}
		if (($guastatus['AcctRecv']['Accounts Receivable'] == 'Y') || ($guastatus['AcctRecv']['Accounts Receivable1'] == 'Y') || ($guastatus['AcctRecv']['Accounts Receivable2'] == 'Y') || ($guastatus['AcctRecv']['Accounts Receivable3'] == 'Y') )
		{
			$guastatus['AcctRecv'] = array("Accounts Receivable"=>"Y");
		} else
		{
			$guastatus['AcctRecv'] = array("Accounts Receivable"=>"N");
		}
		return $guastatus;
	}



	/**
	 * duplicates
	 *
	 * constructor sets instance of database connection
	 *
	 * @access		public
	 * @since		version 0.1.0
	 */
	function __construct($db=false)
	{
		if($db)
		{
			$this->_db=$db;
			$result = true;
		}//end if
		else
		{
			$GLOBALS['BANNER'] = PSUDatabase::connect('oracle/psc1_psu/fixcase');
		}//end else
	}
}
?>