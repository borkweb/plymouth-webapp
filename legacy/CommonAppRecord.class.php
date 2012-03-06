<?php
require_once 'BannerObject.class.php';
require_once 'PSUTools.class.php';

class CommonAppRecord extends BannerObject
{
	public $data = array();
	
	public function __construct($id, $xml = null)
	{
		$this->id = $id;
		
		if($xml) $this->parse($xml);
		else $this->load($this->id);
	}//end constructor

  public function checkSABIDEN($client_id)
	{
		// check to see if the client_id matches the sabiden_id_edi in sabiden (record already exists)
		$chks="SELECT sabiden_aidm
		         FROM sabiden
					  WHERE sabiden_id_edi=lpad(:client_id,9,'0')";
		return PSU::db('banner')->GetOne($chks, compact('client_id'));	
	}// end checkSABIDEN

	public function getSABNSTUAidm($client_id)
	{
		// retrieve aidm being used in sabnstu
		$prms="SELECT sabnstu_aidm
					  FROM sabnstu
					 WHERE sabnstu_id = lpad(:client_id,9,'0')";
		return PSU::db('banner')->GetOne($prms, compact('client_id'));		
		
	} // end getSABNSTUAidm

	public function getSARHEADApplSeqno($aidm)
	{
		$args="SELECT nvl(max(sarhead_appl_seqno), 0)
		        FROM sarhead
					 WHERE sarhead_aidm = :aidm";

		return PSU::db('banner')->GetOne($args, compact('aidm'));
	} // end getSARHEADApplSeqno

	public function getSARPERSseqno($aidm,$appl_seqno,$rltn_code)
	{
		// retrieve sarpers sequence number for all pers_seqno columns in Saraddr
		$seqs="SELECT distinct sarpers_seqno
		         FROM sarpers
						WHERE sarpers_aidm = :aidm
						  AND sarpers_appl_seqno = :appl_seqno
							AND sarpers_rltn_cde = :rltn_code";
		return PSU::db('banner')->GetOne($seqs,compact('aidm','appl_seqno','rltn_code'));
	}// end getSARPERSseqno

 /**
  * Imports data from xml into Banner
  */
	public function import()
	{
		$successfully_moved_to_banner = false;
		// start a transaction so we can rollback on errors
		PSU::db('banner')->StartTrans();
		// PSU::db('banner')->debug=true;
		//
		// Flesh out the Banner insert logic
		//

		// insert id/pin data
		$this->insertSABNSTU();
		
		// insert application header data into Banner
		$this->insertSARHEAD();
		
		// insert person data
		$this->insertSARPERS();

		// insert person preferences
		$this->insertSARPRFN();
		
		// insert addresses
		$this->insertSARADDR();

		// insert phones
		$this->insertSARPHON();

		// insert phones
		$this->insertSARPRAC();
		
		// insert sports 
		$this->insertSARACTV();

		// insert major/concentration data
		$this->insertSAREFOS();

		// insert curriulum rule data
		$this->insertSARETRY();

		// insert highschool data
		$this->insertSARHSCH();
		
		// insert custom fields 
		$this->insertSARRQST();
		
		// insert previous college data
		$this->insertSARPCOL();
		$this->insertSARPSES();


		//psu::puke("The functions have been shut off for the time being...");

		// if data was successfully moved to banner, mark the common app feed record as loaded
		if($successfully_moved_to_banner = true)
		{
			$this->markAsImported();
		}//end if

		// we might be nested, make sure we return false if there was an error
		if( PSU::db('banner')->HasFailedTrans() ) {
			// still have to do completetrans to pop one off the stack
			PSU::db('banner')->CompleteTrans();
			return false;
		}
		return PSU::db('banner')->CompleteTrans();
	}//end import

 /**
  * insert id/pin data
  */
	public function insertSABNSTU()
	{
		// insert sabnstu data
		// use commonapplicationClientID as the SABNSTU_ID
		// generate a random pin using PSUTools
		// first check sabiden to see if person already has an application
		$aidm=$this->checkSABIDEN($this->commonapplicantclientid);
		if($aidm)
		{
			psu::puke("This person, Client ID: ".$this->commonapplicantclientid." is already in sabiden.");
		}
		if(!$aidm)
		{
			$aidm = PSU::db('banner')->GetOne( "SELECT sabaseq.nextval FROM dual" );
		}
		$pin = PSU::randomString(6, "1234567890");
		$client_id = $this->commonapplicantclientid;
		$sql="INSERT INTO sabnstu (
								sabnstu_id,
								sabnstu_aidm,
								sabnstu_locked_ind,
								sabnstu_pin,
								sabnstu_activity_date
							) VALUES (
								lpad(:client_id,9,'0'),
								:aidm,
								'N',
								:pin,
								sysdate
							)";
		$rs = PSU::db('banner')->Execute($sql, compact('client_id', 'aidm', 'pin'));
		if($rs)
		{
			$successfully_moved_to_banner = true;
		}
		else
		{
			$successfully_moved_to_banner = false;
			psu::puke($client_id." Failed trying to insert into Banner table SABNSTU.");
		}
		return $successfully_moved_to_banner;
	}//end insertSABNSTU

	/**
  * insert application header data into Banner
  */
	public function insertSARHEAD()
	{
		// insert sarhead data
		$aidm = $this->getSABNSTUAidm($this->commonapplicantclientid);

		// find max SARHEAD application number
		$appl_seqno = $this->getSARHEADApplSeqNo($aidm);
		// one-up the sequence number
		$appl_seqno++;
		switch (strtolower($this->applicationtype))
		{
			case 'first-year':
				$wapp_code = 'W1';
			break;
			case 'transfer':
				$wapp_code = 'W2';
			break;
		}
		$add_date=date("d-M-Y",strtotime($this->createddate));
		//$term_code_entry need translation/parsing switch for this
		switch(strtoupper(substr($this->termid,0,4)))
		{
			case "SPRI":
				$suffix="30";
				$year=substr($this->termid,7,4);
				$term_code_entry=$year.$suffix;
			break;
			case "FALL":
				$suffix="10";
				$year=substr($this->termid,5,4)+1;
				$term_code_entry=$year.$suffix;
			break;
		}// end switch

		$sql="INSERT INTO sarhead (
	  	          sarhead_aidm,
	  	          sarhead_appl_seqno,
								sarhead_term_code_entry,
	  	          sarhead_appl_comp_ind,
	  	          sarhead_appl_status_ind,
								sarhead_pers_status_ind,
								sarhead_apls_code,
								sarhead_process_ind,
								sarhead_appl_accept_ind,
								sarhead_wapp_code,
	  	          sarhead_add_date,
								sarhead_activity_date
	  	        ) VALUES (
	  	          :aidm,
	  	          :appl_seqno,
								:term_code_entry,
	  	          'Y',
	  	          'N',
								'N',
								'WEB',
								'N',
								'U',
								:wapp_code,
								decode(:add_date, null, null, TO_DATE(:add_date,'DD-MON-YYYY')),
								sysdate
	  	        )";

		$rs = PSU::db('banner')->Execute($sql, compact('aidm','appl_seqno','term_code_entry','wapp_code','add_date'));
		if($rs)
		{
			$successfully_moved_to_banner = true;
		}
		else
		{
			$successfully_moved_to_banner = false;
			psu::puke($client_id." Failed trying to insert into Banner table SARHEAD.");
		}
		return $successfully_moved_to_banner;
	}//end insertSARHEAD


 /**
  * insert sports into Banner
  */
	public function insertSARACTV()
	{
		// insert sports data
		$aidm = $this->getSABNSTUaidm($this->commonapplicantclientid);
		$appl_seqno=$this->getSARHEADApplSeqno($aidm);

		$prms="SELECT nvl(max(saractv_seqno),0)+1
						FROM saractv
					 WHERE saractv_aidm=:aidm
						 AND saractv_appl_seqno=:appl_seqno";
						 
		$sql="INSERT INTO saractv (
								saractv_aidm,
								saractv_appl_seqno,
								saractv_seqno,
								saractv_load_ind,
								saractv_actv_cde,
								saractv_activity_date
							) VALUES (
								:aidm,
								:appl_seqno,
								:seqno,
								'N',
								:actv_cde,
								sysdate
							)";

		// first check for cheerleading
		if (stristr($this->descriptionact1,'cheer')||stristr($this->descriptionact2,'cheer')||stristr($this->descriptionact3,'cheer')||stristr($this->descriptionact4,'cheer')||stristr($this->descriptionact5,'cheer')||stristr($this->descriptionact6,'cheer')||stristr($this->descriptionact7,'cheer'))
		{
			$actv_cde='B3';
			$row['actv_cde']=$actv_cde;
			$row['aidm']=$aidm;
			$row['appl_seqno']=$appl_seqno;
			$seqno = PSU::db('banner')->GetOne($prms, compact('aidm','appl_seqno'));
			$row['seqno']=$seqno;
			$rs = PSU::db('banner')->Execute($sql, $row);
			if($rs)
			{
				$successfully_moved_to_banner = true;
			}
			else
			{
				$successfully_moved_to_banner = false;
				psu::puke($client_id." Failed trying to insert ACTIVITIES into Banner table SARACTV.");
			}
		}

		// now check all other activities
		$actv=array();
		$actv[]=$this->activity1;
		$actv[]=$this->activity2;
		$actv[]=$this->activity3;
		$actv[]=$this->activity4;
		$actv[]=$this->activity5;
		$actv[]=$this->activity6;
		$actv[]=$this->activity7;
		$actv[]=$this->ath_sport1;
		$actv[]=$this->ath_sport2;
		$actv[]=$this->ath_sport3;
		$actv[]=$this->ath_sport4;
		$actv[]=$this->ath_sport5;
		$actv[]=$this->ath_sport6;
		$actv[]=$this->ath_sport7;
		foreach($actv as $a)
		{
			$flag='F';
			// first check the ath_sport fields
			switch(strtoupper($a))
			{
				case 'INS':
					$actv_cde='A1';
					$flag='T';
				break;
				case 'STU':
					$actv_cde='A3';
					$flag='T';
				break;
				case 'JRN':
					$actv_cde='A4';
					$flag='T';
				break;
				case 'INT':
					$actv_cde='AA';
					$flag='T';
				break;
				case 'DAN':
					$actv_cde='AJ';
					$flag='T';
				break;
				case 'THE':
					$actv_cde='A7';
					$flag='T';
				break;
				case 'VOC':
					$actv_cde='A2';
					$flag='T';
				break;
				case 'BSB':
					$actv_cde='B1';
					$flag='T';
				break;
				case 'BSK':
					$actv_cde='B2';
					$flag='T';
				break;
				case 'FOT':
					$actv_cde='B5';
					$flag='T';
				break;
				case 'FLD':
					$actv_cde='B4';
					$flag='T';
				break;
				case 'ICE':
					$actv_cde='B6';
					$flag='T';
				break;
				case 'LAX':
					$actv_cde='B7';
					$flag='T';
				break;
				case 'SKI':
					$actv_cde='B8';
					$flag='T';
				break;
				case 'SOF':
					$actv_cde='BB';
					$flag='T';
				break;
				case 'SOC':
					$actv_cde='B9';
					$flag='T';
				break;
				case 'SWM':
					$actv_cde='BC';
					$flag='T';
				break;
				case 'TEN':
					$actv_cde='BD';
					$flag='T';
				break;
				case 'VOL':
					$actv_cde='BE';
					$flag='T';
				break;
				case 'WRE':
					$actv_cde='BF';
					$flag='T';
				break;
			}//end switch
			if($flag=='T')
			{
				$row['aidm']=$aidm;
				$row['actv_cde']=$actv_cde;
				$row['appl_seqno']=$appl_seqno;
				$seqno = PSU::db('banner')->GetOne($prms, compact('aidm','appl_seqno'));
				$row['seqno']=$seqno;
				$rs = PSU::db('banner')->Execute($sql, $row);
				if($rs)
				{
					$successfully_moved_to_banner = true;
				}
				else
				{
					$successfully_moved_to_banner = false;
					psu::puke($client_id." Failed trying to insert ACTIVITIES into Banner table SARACTV.");
				}
			}// end if - this will only execute if sports and activities are found
		}//end foreach
		return $successfully_moved_to_banner;
	}//end insertSARACTV
	

 /**
  * insert addresses into Banner
  */
	public function insertSARADDR()
	{
		// insert saraddr data - note that pers sequence numbers come from sarpers! 
		$sql="INSERT INTO saraddr (
							saraddr_aidm,
							saraddr_appl_seqno,
							saraddr_pers_seqno,
							saraddr_seqno,
							saraddr_load_ind,
							saraddr_lcql_cde,
							saraddr_street_line1,
							saraddr_street_line2,
							saraddr_city,
							saraddr_stat_cde,
							saraddr_zip,
							saraddr_natn_cde,
							saraddr_cnty_cde,
							saraddr_activity_date
						) VALUES (
							:aidm,
							:appl_seqno,
							:pers_seqno,
							:seqno,
							'N',
							:lcql_cde,
							:street_line1,
							:street_line2,
							:city,
							:stat_cde,
							:zip,
							:natn_cde,
							:cnty_cde,
							sysdate
						)";

		$aidm=$this->getSABNSTUAidm($this->commonapplicantclientid);
		$appl_seqno=$this->getSARHEADApplSeqno($aidm);

		$prms="SELECT decode(max(saraddr_seqno)+1,null,1,max(saraddr_seqno)+1)
						FROM saraddr
					 WHERE saraddr_aidm=:aidm
						 AND saraddr_appl_seqno=:appl_seqno";

		if($this->permaddress1)
		{
			$seqno = PSU::db('banner')->GetOne($prms, compact('aidm','appl_seqno'));
			$row['seqno']=$seqno;
			$row['aidm']=$aidm;
			$row['appl_seqno']=$appl_seqno;
			$rltn_code="";
			$row['pers_seqno']='1';
			if($this->differentcurrentaddress == 1)
			{
				$row['lcql_cde']="P";
			}
			else
			{
				$row['lcql_cde']="M";
			}
			$row['street_line1']=$this->permaddress1;
			$row['street_line2']=$this->permaddress2;
			$row['city']=$this->permcity;
			$row['stat_cde']=$this->permstate;
			if($this->permcountry && strlen($this->permcountry)<=3 && $this->permcountry !="840")
			{
				$row['natn_cde']=$this->translateNationCode($this->permcountry);
			}
			else
			{
				$row['natn_cde']="";
			}
			if(strlen($this->permstate) > 2)
			{
				$row['stat_cde']=$this->translateState($this->permstate);
			}
			$row['zip']=$this->permzippostalcode;
			$row['cnty_cde']=$this->translateCntyCode($this->permzippostalcode);
			$rs=PSU::db('banner')->Execute($sql,$row);
			if($rs)
			{
				$successfully_moved_to_banner = true;
			}
			else
			{
				$successfully_moved_to_banner = false;
				psu::puke($client_id." Failed trying to insert PERM ADDRESS into Banner table SARADDR.");
			}
		}

		// getting current addresses....
		if($this->differentcurrentaddress == 1)
		{
			$row['lcql_cde']="M";
			$rltn_code="";
			$seqno = PSU::db('banner')->GetOne($prms, compact('aidm','appl_seqno'));
			$row['seqno']=$seqno;
			$row['pers_seqno']='1';
			$row['street_line1']=$this->currentaddress1;
			$row['street_line2']=$this->currentaddress2;
			$row['city']=$this->currentcity;
			$row['stat_cde']=$this->currentstate;
			if(strlen($this->currentstate)> 2)
			{
				$row['stat_cde']=$this->translateState($this->currentstate);
			}
			if($this->currentcountry && strlen($this->currentcountry)<=3 && $this->currentcountry != "840")
			{
				$row['natn_cde']=$this->translateNationCode($this->currentcountry);
			}
			else
			{
				$row['natn_cde']="";
			}
			$row['zip']=$this->currentzippostalcode;
			$row['cnty_cde']=$this->translateCntyCode($this->currentzippostalcode);
			$rs=PSU::db('banner')->Execute($sql,$row);
			if($rs)
			{
				$successfully_moved_to_banner = true;
			}
			else
			{
				$successfully_moved_to_banner = false;
				psu::puke($client_id." Failed trying to insert CURRENT ADDRESS into Banner table SARADDR.");
			}
		}// end if current addresses

		if($this->parent1sameaddress == 1 && $this->parent1type && strtolower($this->parent1type)!="unknown")
		{
			if($this->parent1type == "Father")
			{
				$rltn_code="01";
			}
			elseif($this->parent1type == "Mother")
			{
				$rltn_code="02";
			}
			$seqno = PSU::db('banner')->GetOne($prms, compact('aidm','appl_seqno'));
			$row['seqno']=$seqno;
			$row['pers_seqno']=$this->getSARPERSseqno($aidm,$appl_seqno,$rltn_code);   
			$row['lcql_cde']="M";
			$row['street_line1']=$this->permaddress1;
			$row['street_line2']=$this->permaddress2;
			$row['city']=$this->permcity;
			$row['stat_cde']=$this->permstate;
			if(strlen($this->permstate)> 2)
			{
				$row['stat_cde']=$this->translateState($this->permstate);
			}
			if($this->permcountry && strlen($this->permcountry)<=3 && $this->permcountry !="840")
			{
				$row['natn_cde']=$this->translateNationCode($this->permcountry);
			}
			else
			{
				$row['natn_cde']="";
			}
			$row['zip']=$this->permzippostalcode;
			$row['cnty_cde']=$this->translateCntyCode($this->permzippostalcode);
			$rs=PSU::db('banner')->Execute($sql,$row);
			if($rs)
			{
				$successfully_moved_to_banner = true;
			}
			else
			{
				$successfully_moved_to_banner = false;
				psu::puke($client_id." Failed trying to insert PARENT 1 ADDRESS into Banner table SARADDR.");
			}
		}
		elseif($this->parent1sameaddress != 1 && $this->parent1type && $this->parent1address1 && strtolower($this->parent1type)!="unknown")
		{
			if($this->parent1type == "Father")
			{
				$rltn_code="01";
			}
			elseif($this->parent1type == "Mother")
			{
				$rltn_code="02";
			}
			$seqno = PSU::db('banner')->GetOne($prms, compact('aidm','appl_seqno'));
			$row['seqno']=$seqno;
			$row['pers_seqno']=$this->getSARPERSseqno($aidm,$appl_seqno,$rltn_code);   
			$row['lcql_cde']="M";
			$row['street_line1']=$this->parent1address1;
			$row['street_line2']=$this->parent1address2;
			$row['city']=$this->parent1city;
			$row['stat_cde']=$this->parent1state;
			if(strlen($this->parent1state)> 2)
			{
				$row['stat_cde']=$this->translateState($this->parent1state);
			}
			if($this->parent1country && strlen($this->parent1country)<=3 && $this->parent1country != "840")
			{
				$row['natn_cde']=$this->translateNationCode($this->parent1country);
			}
			else
			{
				$row['natn_cde']="";
			}
			$row['zip']=$this->parent1postalcode;
			$row['cnty_cde']=$this->translateCntyCode($this->parent1postalcode);
			$rs=PSU::db('banner')->Execute($sql,$row);
			if($rs)
			{
				$successfully_moved_to_banner = true;
			}
			else
			{
				$successfully_moved_to_banner = false;
				psu::puke($client_id." Failed trying to insert PARENT 1 ADDRESS into Banner table SARADDR.");
			}
		}// end if parent 2 addresses

		if($this->parent2sameaddress == 1 && $this->parent2type)
		{
			if($this->parent2type == "Father")
			{
				$rltn_code="01";
			}
			elseif($this->parent2type == "Mother")
			{
				$rltn_code="02";
			}
			$seqno = PSU::db('banner')->GetOne($prms, compact('aidm','appl_seqno'));
			$row['seqno']=$seqno;
			$row['pers_seqno']=$this->getSARPERSseqno($aidm,$appl_seqno,$rltn_code);   
			$row['lcql_cde']="M";
			$row['street_line1']=$this->permaddress1;
			$row['street_line2']=$this->permaddress2;
			$row['city']=$this->permcity;
			$row['stat_cde']=$this->permstate;
			if(strlen($this->permstate)> 2)
			{
				$row['stat_cde']=$this->translateState($this->permstate);
			}
			if($this->permcountry && strlen($this->permcountry)<=3 && $this->permcount !="840")
			{
				$row['natn_cde']=$this->translateNationCode($this->permcountry);
			}
			else
			{
				$row['natn_cde']="";
			}
			$row['zip']=$this->permzippostalcode;
			$row['cnty_cde']=$this->translateCntyCode($this->permzippostalcode);
			$rs=PSU::db('banner')->Execute($sql,$row);
			if($rs)
			{
				$successfully_moved_to_banner = true;
			}
			else
			{
				$successfully_moved_to_banner = false;
				psu::puke($client_id." Failed trying to insert PARENT 2 ADDRESS into Banner table SARADDR.");
			}
		}
		elseif($this->parent2sameaddress != 1 && $this->parent2type && $this->parent2address1)
		{
			if($this->parent2type == "Father")
			{
				$rltn_code="01";
			}
			elseif($this->parent2type == "Mother")
			{
				$rltn_code="02";
			}
			$seqno = PSU::db('banner')->GetOne($prms, compact('aidm','appl_seqno'));
			$row['seqno']=$seqno;
			$row['pers_seqno']=$this->getSARPERSseqno($aidm,$appl_seqno,$rltn_code);   
			$row['lcql_cde']="M";
			$row['street_line1']=$this->parent2address1;
			$row['street_line2']=$this->parent2address2;
			$row['city']=$this->parent2city;
			$row['stat_cde']=$this->parent2state;
			if(strlen($this->parent2state)> 2)
			{
				$row['stat_cde']=$this->translateState($this->parent2state);
			}
			if($this->parent2country && strlen($this->parent2country)<=3 && $this->parent2country != "840")
			{
				$row['natn_cde']=$this->translateNationCode($this->parent2country);
			}
			else
			{
				$row['natn_cde']="";
			}
			$row['zip']=$this->parent2postalcode;
			$row['cnty_cde']=$this->translateCntyCode($this->parent2postalcode);
			$rs=PSU::db('banner')->Execute($sql,$row);
			if($rs)
			{
				$successfully_moved_to_banner = true;
			}
			else
			{
				$successfully_moved_to_banner = false;
				psu::puke($client_id." Failed trying to insert PARENT 2 ADDRESS into Banner table SARADDR.");
			}
		}// end if parent 2 addresses

		if($this->legalguardiansameaddress == 1 && $this->legalguardianrelationship)
		{
			//getting legal guardian addresses... 
			$rltn_code="03";
			$seqno = PSU::db('banner')->GetOne($prms, compact('aidm','appl_seqno'));
			$row['seqno']=$seqno;
			$row['pers_seqno']=$this->getSARPERSseqno($aidm,$appl_seqno,$rltn_code);   
			$row['lcql_cde']="M";
			$row['street_line1']=$this->permaddress1;
			$row['street_line2']=$this->permaddress2;
			$row['city']=$this->permcity;
			$row['stat_cde']=$this->permstate;
			if(strlen($this->permstate)> 2)
			{
				$row['stat_cde']=$this->translateState($this->permstate);
			}
			if($this->permcountry && strlen($this->permcountry)<=3 && $this->permcountry != "840")
			{
				$row['natn_cde']=$this->translateNationCode($this->permcountry);
			}
			else
			{
				$row['natn_cde']="";
			}
			$row['zip']=$this->permzippostalcode;
			$row['cnty_cde']=$this->translateCntyCode($this->permzippostalcode);
			$rs=PSU::db('banner')->Execute($sql,$row);
			if($rs)
			{
				$successfully_moved_to_banner = true;
			}
			else
			{
				$successfully_moved_to_banner = false;
				psu::puke($client_id." Failed trying to insert GUARDIAN ADDRESS into Banner table SARADDR.");
			}
		}
		elseif($this->legalguardiansameaddress != 1 && $this->legalguardianaddress1 && $this->legalguardianrelationship)
		{
			$rltn_code="03";
			$seqno = PSU::db('banner')->GetOne($prms, compact('aidm','appl_seqno'));
			$row['seqno']=$seqno;
			$row['pers_seqno']=$this->getSARPERSseqno($aidm,$appl_seqno,$rltn_code);   
			$row['lcql_cde']="M";
			$row['street_line1']=$this->legalguardianaddress1;
			$row['street_line2']=$this->legalguardianaddress2;
			$row['city']=$this->legalguardiancity;
			$row['stat_cde']=$this->legalguardianstate;
			if(strlen($this->legalguardianstate)> 2)
			{
				$row['stat_cde']=$this->translateState($this->legalguardianstate);
			}
			if($this->legalguardiancountry && strlen($this->legalguardiancountry)<=3 && $this->legalguardiancountry != "840")
			{
				$row['natn_cde']=$this->translateNationCode($this->legalguardiancountry);
			}
			else
			{
				$row['natn_cde']="";
			}
			$row['zip']=$this->legalguardianpostalcode;
			$row['cnty_cde']=$this->translateCntyCode($this->legalguardianpostalcode);
			$rs=PSU::db('banner')->Execute($sql,$row);
			if($rs)
			{
				$successfully_moved_to_banner = true;
			}
			else
			{
				$successfully_moved_to_banner = false;
				psu::puke($client_id." Failed trying to insert GUARDIAN ADDRESS into Banner table SARADDR.");
			}
		}// end if legal guardian addresses 
		return $successfully_moved_to_banner;
	}//end insertSARADDR

 /**
	* insert curriculum rule info into Banner
	*/
	public function insertSARETRY()
	{
		// insert saretry data
		$sql = "INSERT INTO saretry(
							saretry_aidm,
							saretry_appl_seqno,
							saretry_seqno,
							saretry_load_ind,
							saretry_activity_date,
							saretry_priority,
							saretry_curr_rule
						)VALUES(
							:aidm,
							:appl_seqno,
							:seqno,
							'N',
							sysdate,
							'1',
							:curr_rule
						)";

		$aidm = $this->getSABNSTUAidm($this->commonapplicantclientid);
		
		$appl_seqno = $this->getSARHEADApplSeqno($aidm);
		
		$prms="SELECT decode(max(saretry_seqno)+1,null,1,max(saretry_seqno)+1)
						FROM saretry
					 WHERE saretry_aidm=:aidm
						 AND saretry_appl_seqno=:appl_seqno";
		$seqno = PSU::db('banner')->GetOne($prms, compact('aidm','appl_seqno'));

		$rqry = "SELECT sorcmjr_curr_rule
							 FROM sarefos,sorcmjr a
							WHERE sarefos_aidm=:aidm
							  AND sarefos_appl_seqno=:appl_seqno
								AND sarefos_lfos_rule = a.sorcmjr_cmjr_rule
							  AND a.sorcmjr_term_code_eff=(SELECT MAX(b.sorcmjr_term_code_eff)
																							 FROM sorcmjr b
																							WHERE b.sorcmjr_cmjr_rule=a.sorcmjr_cmjr_rule
																								AND substr(b.sorcmjr_term_code_eff,5,2) in ('10','30')
																								AND b.sorcmjr_disp_web_ind='Y')
								AND sarefos_etry_seqno=:seqno
								AND sarefos_flvl_cde='M'";
		$curr_rule=PSU::db('banner')->GetOne($rqry,compact('aidm','appl_seqno','seqno'));
		if($curr_rule)
		{
			$row['aidm']=$aidm;
			$row['appl_seqno']=$appl_seqno;
			$row['seqno']=$seqno;
			$row['curr_rule']=$curr_rule;
			$rs= PSU::db('banner')->Execute($sql,$row);
			if($rs)
			{
				$successfully_moved_to_banner = true;
			}
		}
		else
		{
			$successfully_moved_to_banner = false;
			psu::puke($client_id." Failed trying to insert CURRICULUM RULE into Banner table SARETRY.");
			}
		$seqno = PSU::db('banner')->GetOne($prms, compact('aidm','appl_seqno'));

		$cqry = "SELECT sorccon_curr_rule
							 FROM sarefos,sorccon a
							WHERE sarefos_aidm=:aidm
							  AND sarefos_appl_seqno=:appl_seqno
								AND sarefos_lfos_rule = a.sorccon_cmjr_rule
							  AND a.sorccon_term_code_eff=(SELECT MAX(b.sorccon_term_code_eff)
																							 FROM sorccon b
																							WHERE b.sorccon_cmjr_rule=a.sorccon_cmjr_rule
																								AND substr(b.sorccon_term_code_eff,5,2) in ('10','30')
																								AND b.sorccon_disp_web_ind='Y')
								AND sarefos_etry_seqno=:seqno
								AND sarefos_flvl_cde='C'";
		$curr_rule=PSU::db('banner')->GetOne($rqry,compact('aidm','appl_seqno','seqno'));
		if($curr_rule)
		{
			$row['aidm']=$aidm;
			$row['appl_seqno']=$appl_seqno;
			$row['seqno']=$seqno;
			$row['curr_rule']=$curr_rule;
			$rs= PSU::db('banner')->Execute($sql,$row);
			if($rs)
			{
				$successfully_moved_to_banner = true;
			}
		}
		else 
		{
			$successfully_moved_to_banner = false;
			psu::puke($client_id." Failed trying to insert CURRICULUM RULE into Banner table SARETRY.");
		}
		return $successfully_moved_to_banner;
	}// end insertSARETRY

 /**
  * insert major/concentration into Banner
  */
	public function insertSAREFOS()
	{
		// insert sarefos data
		$sql = "INSERT INTO sarefos(
							sarefos_aidm,
							sarefos_appl_seqno,
							sarefos_etry_seqno,
							sarefos_seqno,
							sarefos_load_ind,
							sarefos_activity_date,
							sarefos_flvl_cde,
							sarefos_lfos_rule
						)VALUES(
							:aidm,
							:appl_seqno,
							1,
							:seqno,
							'N',
							sysdate,
							:flvl_cde,
							:lfos_rule
						)";

		$aidm = $this->getSABNSTUAidm($this->commonapplicantclientid);
		
		$appl_seqno = $this->getSARHEADApplSeqno($aidm);
		
		$prms="SELECT decode(max(sarefos_seqno)+1,null,1,max(sarefos_seqno)+1)
						FROM sarefos
					 WHERE sarefos_aidm=:aidm
						 AND sarefos_appl_seqno=:appl_seqno";
		
		// @todo - redo this as soon as I get answer back from Sue whether this is free form or not
		if($this->major)
		{
			$flag="";
			// first fix some common repeating issues (major is freehand by the user so it easily messes up) 
			if(strlen(trim($this->major))==4)
			{
				$this->major=$this->major."-NONE";
			}
			// now check for art
			if(stristr($this->major,'art')&& !stristr($this->major,'arte')&& !stristr($this->major,'arth')&& !stristr($this->major,'artk')&& !stristr($this->major,'artl'))
			{
				$row['aidm']=$aidm;
				$row['appl_seqno']=$appl_seqno;
				$row['seqno'] = PSU::db('banner')->GetOne($prms, compact('aidm','appl_seqno'));
				$lfos_rule=$this->translateMajorCode(substr(strtoupper($this->major),0,3),$flag);
				$row['lfos_rule']=$lfos_rule;
				$row['flvl_cde']="M";
				$rs= PSU::db('banner')->Execute($sql,$row);
				if($rs)
				{
					$successfully_moved_to_banner = true;
				}
				else
				{
					$successfully_moved_to_banner = false;
					psu::puke($client_id." Failed trying to insert MAJOR INFO into Banner table SAREFOS.");
				}
				$str = trim(substr($this->major,3,5));
				$cstr=preg_replace("/[^a-z0-9]/i", "", $str);
				$row['aidm']=$aidm;
				$row['appl_seqno']=$appl_seqno;
				$row['seqno'] = PSU::db('banner')->GetOne($prms, compact('aidm','appl_seqno'));
				$row['lfos_rule']=$this->translateConcCode(substr(strtoupper($cstr),0,4),$lfos_rule);
				$row['flvl_cde']='C';
				$rs= PSU::db('banner')->Execute($sql,$row);
				if($rs)
				{
					$successfully_moved_to_banner = true;
				}
				else
				{
					$successfully_moved_to_banner = false;
					psu::puke($client_id." Failed trying to insert CONCENTRATION into Banner table SAREFOS.");
				}
			} // end art check
			else
			{
				if(strtoupper($this->major)=='000-NONE')
				{
					$this->major="0000-NONE";
				}
				elseif(strtoupper($this->major)=='UNDECIDED')
				{
					$this->major="0000-NONE";
				}
				if(stristr($this->major,'HNON'))
				{
					$this->major=preg_replace("/HNON/", "NONE", $this->major);
					$this->major=preg_replace("/H-NON/", "NONE", $this->major);
				}
				if(stristr($this->major,'PSYC')||stristr($this->major,'MATH')||stristr($this->major,'BIOL'))
				{
					if(stristr($this->major,'NONE'))
					{
						$flag="BA";
					}
				}
				$row['aidm']=$aidm;
				$row['appl_seqno']=$appl_seqno;
				$row['seqno'] = PSU::db('banner')->GetOne($prms, compact('aidm','appl_seqno'));
				$lfos_rule=$this->translateMajorCode(substr(strtoupper($this->major),0,4),$flag);
				$row['lfos_rule']=$lfos_rule;
				$row['flvl_cde']="M";
				$rs= PSU::db('banner')->Execute($sql,$row);
				if($rs)
				{
					$successfully_moved_to_banner = true;
				}
				else
				{
					$successfully_moved_to_banner = false;
					psu::puke($client_id." Failed trying to insert MAJOR INFO into Banner table SAREFOS.");
				}
				$str = trim(substr($this->major,4,5));
				$cstr=preg_replace("/[^a-z0-9]/i", "", $str);
				$row['aidm']=$aidm;
				$row['appl_seqno']=$appl_seqno;
				$row['seqno'] = PSU::db('banner')->GetOne($prms, compact('aidm','appl_seqno'));
				$row['lfos_rule']=$this->translateConcCode(substr(strtoupper($cstr),0,4),$lfos_rule);
				$row['flvl_cde']='C';
				$rs= PSU::db('banner')->Execute($sql,$row);

				if($rs)
				{
					$successfully_moved_to_banner = true;
				}
				else
				{
					$successfully_moved_to_banner = false;
					psu::puke($client_id." Failed trying to insert CONCENTRATION into Banner table SAREFOS.");
				}
			}// end not art
		}// end if major
		return $successfully_moved_to_banner;
	}//end insertSAREFOS

 /**
  * insert highschool into Banner
  */
	public function insertSARHSCH()
	{
		// insert sarhsch data
		if($this->ceebcode || $this->othersecschool1ceeb)
		{
			$sql="INSERT INTO sarhsch(
							sarhsch_aidm,
							sarhsch_appl_seqno,
							sarhsch_seqno,
							sarhsch_load_ind,
							sarhsch_activity_date, 
							sarhsch_iden_cde1,
							sarhsch_name1,
							sarhsch_street_line1,
							sarhsch_street_line2,
							sarhsch_city,
							sarhsch_stat_cde,
							sarhsch_zip,
							sarhsch_natn_cde,
							sarhsch_hsgr_dte,
							sarhsch_idql_cde1,
							sarhsch_dfmt_cde_hsgr,
							sarhsch_enty_cde1
						)VALUES(
							:aidm,
							:appl_seqno,
							:seqno,
							'N',
							sysdate,
							:sbgi_code,
							:name1,
							:street_line1,
							:street_line2,
							:city,
							:stat_code,
							:zip,
							:natn_code,
							:hsgr_dte,
							'WB',
							'MDC',
							'HS'
						)";
			$aidm = $this->getSABNSTUAidm($this->commonapplicantclientid);
			$appl_seqno = $this->getSARHEADApplSeqno($aidm);
			
			$prms="SELECT decode(max(sarhsch_seqno)+1,null,1,max(sarhsch_seqno)+1)
							FROM sarhsch
						 WHERE sarhsch_aidm=:aidm
							 AND sarhsch_appl_seqno=:appl_seqno";
									
			$qry="SELECT  sobsbgi_sbgi_code,
										stvsbgi_desc as stvsbgi_name1,
										sobsbgi_street_line1,
										sobsbgi_street_line2,
										sobsbgi_city,
										sobsbgi_stat_code,
										sobsbgi_zip,
										sobsbgi_natn_code
							FROM  sobsbgi,stvsbgi 
						 WHERE  sobsbgi_sbgi_code=:ceeb
							 AND  stvsbgi_code=sobsbgi_sbgi_code";

			if(strlen(trim($this->ceebcode))==6)
			{
				$seqno = PSU::db('banner')->GetOne($prms, compact('aidm','appl_seqno'));
				$ceeb=trim($this->ceebcode);
				$recs = PSU::db('banner')->GetRow($qry, compact('ceeb'));
				$row['sbgi_code']=$this->ceebcode;
				$row['name1']=$recs['stvsbgi_name1'];
				$row['street_line1']=$recs['sobsbgi_street_line1'];
				$row['street_line2']=$recs['sobsbgi_street_line2'];
				$row['city']=$recs['sobsbgi_city'];
				$row['stat_code']=$recs['sobsbgi_stat_code'];
				$row['zip']=$recs['sobsbgi_zip'];
				$row['natn_code']=$recs['sobsbgi_natn_code'];		
				$row['hsgr_dte'] = $this->secschooldategraduation;
				$row['aidm'] = $aidm;
				$row['appl_seqno'] = $appl_seqno;
				$row['seqno'] = $seqno;
				$rs = PSU::db('banner')->Execute($sql,$row);
				if($rs)
				{
					$successfully_moved_to_banner = true;
				}
				else
				{
					$successfully_moved_to_banner = false;
					psu::puke($client_id." Failed trying to insert HIGH SCHOOL into Banner table SARHSCH.");
				}
			}
			if(strlen(trim($this->othersecschool1ceeb))==6)
			{
				$seqno = PSU::db('banner')->GetOne($prms, compact('aidm','appl_seqno'));
				$ceeb=trim($this->othersecschool1ceeb);
				$recs = PSU::db('banner')->GetRow($qry, compact('ceeb'));
				$row['sbgi_code']=$this->othersecschool1ceeb;
				$row['name1']=$recs['stvsbgi_name1'];
				$row['street_line1']=$recs['sobsbgi_street_line1'];
				$row['street_line2']=$recs['sobsbgi_street_line2'];
				$row['city']=$recs['sobsbgi_city'];
				$row['stat_code']=$recs['sobsbgi_stat_code'];
				$row['zip']=$recs['sobsbgi_zip'];
				$row['natn_code']=$recs['sobsbgi_natn_code'];		
				$row['hsgr_dte'] = $this->secschooldategraduation;
				$row['aidm'] = $aidm;
				$row['appl_seqno'] = $appl_seqno;
				$row['seqno'] = $seqno;
				$rs = PSU::db('banner')->Execute($sql,$row);
				if($rs)
				{
					$successfully_moved_to_banner = true;
				}
				else
				{
					$successfully_moved_to_banner = false;
					psu::puke($client_id." Failed trying to insert HIGH SCHOOL into Banner table SARHSCH.");
				}
			}
			return $successfully_moved_to_banner;
		}// end if - this will only execute if a high school ceeb is found (iden_cde1) 
	}//end insertSARHSCH

 /**
  * insert previous college base data into Banner
  */
	public function insertSARPCOL()
	{
		// insert sarpcol data
		if($this->collegecredit1ceeb || $this->collegecredit2ceeb || $this->collegecredit3ceeb || $this->ceeb || $this->othersecschool1ceeb)
		{
			$aidm = $this->getSABNSTUAidm($this->commonapplicantclientid);
			$appl_seqno = $this->getSARHEADApplSeqno($aidm);
			$sql="INSERT INTO sarpcol(
							sarpcol_aidm,
							sarpcol_appl_seqno,
							sarpcol_seqno,
							sarpcol_load_ind,
							sarpcol_activity_date, 
							sarpcol_iden_cde,
							sarpcol_inst_name,
							sarpcol_street_line1,
							sarpcol_street_line2,
							sarpcol_city,
							sarpcol_stat_cde,
							sarpcol_zip,
							sarpcol_natn_cde,
							sarpcol_idql_cde
						)VALUES(
							:aidm,
							:appl_seqno,
							:seqno,
							'N',
							sysdate, 
							:sbgi_code,
							:name,
							:street_line1,
							:street_line2,
							:city,
							:stat_code,
							:zip,
							:natn_code,
							'WB'
						)";
			$prms="SELECT decode(max(sarpcol_seqno)+1,null,1,max(sarpcol_seqno)+1)
							FROM sarpcol
						 WHERE sarpcol_aidm=:aidm
							 AND sarpcol_appl_seqno=:appl_seqno";

			$qry="SELECT  stvsbgi_desc as stvsbgi_name,
										sobsbgi_street_line1,
										sobsbgi_street_line2,
										sobsbgi_city,
										sobsbgi_stat_code,
										sobsbgi_zip,
										sobsbgi_natn_code
							FROM  sobsbgi,stvsbgi 
						 WHERE  sobsbgi_sbgi_code=:ceeb
							 AND  stvsbgi_code=sobsbgi_sbgi_code";

			if(strlen(trim($this->ceebcode))==4)
			{
				$seqno = PSU::db('banner')->GetOne($prms, compact('aidm','appl_seqno'));
				$ceeb=trim($this->ceebcode);
				$recs = PSU::db('banner')->GetRow($qry, compact('ceeb'));
				$row['sbgi_code']=$recs['sobsbgi_sbgi_code'];
				$row['name']=$recs['stvsbgi_name'];
				$row['street_line1']=$recs['sobsbgi_street_line1'];
				$row['street_line2']=$recs['sobsbgi_street_line2'];
				$row['city']=$recs['sobsbgi_city'];
				$row['stat_code']=$recs['sobsbgi_stat_code'];
				$row['zip']=$recs['sobsbgi_zip'];
				$row['natn_code']=$recs['sobsbgi_natn_code'];		
				$row['sbgi_code']=$ceeb;
				$row['aidm'] = $aidm;
				$row['appl_seqno'] = $appl_seqno;
				$row['seqno'] = $seqno;
				$rs = PSU::db('banner')->Execute($sql,$row);
				if($rs)
				{
					$successfully_moved_to_banner = true;
				}
				else
				{
					$successfully_moved_to_banner = false;
					psu::puke($client_id." Failed trying to insert COLLEGE into Banner table SARPCOL.");
				}
			}// end if - this will only execute if a college ceeb is found (ceebcode) 

			if(strlen(trim($this->collegecredit1ceeb))==4 && $this->collegecredit1ceeb != $this->ceebcode)
			{
				$seqno = PSU::db('banner')->GetOne($prms, compact('aidm','appl_seqno'));
				$ceeb=trim($this->collegecredit1ceeb);
				$recs = PSU::db('banner')->GetRow($qry, compact('ceeb'));
				$row['sbgi_code']=$recs['sobsbgi_sbgi_code'];
				$row['name']=$recs['stvsbgi_name'];
				$row['street_line1']=$recs['sobsbgi_street_line1'];
				$row['street_line2']=$recs['sobsbgi_street_line2'];
				$row['city']=$recs['sobsbgi_city'];
				$row['stat_code']=$recs['sobsbgi_stat_code'];
				$row['zip']=$recs['sobsbgi_zip'];
				$row['natn_code']=$recs['sobsbgi_natn_code'];		
				$row['sbgi_code']=$ceeb;
				$row['aidm'] = $aidm;
				$row['appl_seqno'] = $appl_seqno;
				$row['seqno'] = $seqno;
				$rs = PSU::db('banner')->Execute($sql,$row);
				if($rs)
				{
					$successfully_moved_to_banner = true;
				}
				else
				{
					$successfully_moved_to_banner = false;
					psu::puke($client_id." Failed trying to insert COLLEGE 1 into Banner table SARPCOL.");
				}
			}// end if - this will only execute if a college ceeb is found (collegecredit1ceeb) 
			if(strlen(trim($this->collegecredit2ceeb))==4)
			{
				$seqno = PSU::db('banner')->GetOne($prms, compact('aidm','appl_seqno'));
				$ceeb=trim($this->collegecredit2ceeb);
				$recs = PSU::db('banner')->GetRow($qry, compact('ceeb'));
				$row['sbgi_code']=$recs['sobsbgi_sbgi_code'];
				$row['name']=$recs['stvsbgi_name'];
				$row['street_line1']=$recs['sobsbgi_street_line1'];
				$row['street_line2']=$recs['sobsbgi_street_line2'];
				$row['city']=$recs['sobsbgi_city'];
				$row['stat_code']=$recs['sobsbgi_stat_code'];
				$row['zip']=$recs['sobsbgi_zip'];
				$row['natn_code']=$recs['sobsbgi_natn_code'];		
				$row['sbgi_code']=$ceeb;
				$row['aidm'] = $aidm;
				$row['appl_seqno'] = $appl_seqno;
				$row['seqno'] = $seqno;
				$rs = PSU::db('banner')->Execute($sql,$row);
				if($rs)
				{
					$successfully_moved_to_banner = true;
				}
				else
				{
					$successfully_moved_to_banner = false;
					psu::puke($client_id." Failed trying to insert COLLEGE 2 into Banner table SARPCOL.");
				}
			}// end if - this will only execute if a college ceeb is found (collegecredit2ceeb) 

			if(strlen(trim($this->collegecredit3ceeb))==4)
			{
				$seqno = PSU::db('banner')->GetOne($prms, compact('aidm','appl_seqno'));
				$qry="SELECT  stvsbgi_desc as stvsbgi_name,
											sobsbgi_street_line1,
											sobsbgi_street_line2,
											sobsbgi_city,
											sobsbgi_stat_code,
											sobsbgi_zip,
											sobsbgi_natn_code
								FROM  sobsbgi,stvsbgi 
							 WHERE  sobsbgi_sbgi_code=:ceeb
								 AND  stvsbgi_code=sobsbgi_sbgi_code";
				$ceeb=trim($this->collegecredit3ceeb);
				$recs = PSU::db('banner')->GetRow($qry, compact('ceeb'));
				$row['sbgi_code']=$recs['sobsbgi_sbgi_code'];
				$row['name']=$recs['stvsbgi_name'];
				$row['street_line1']=$recs['sobsbgi_street_line1'];
				$row['street_line2']=$recs['sobsbgi_street_line2'];
				$row['city']=$recs['sobsbgi_city'];
				$row['stat_code']=$recs['sobsbgi_stat_code'];
				$row['zip']=$recs['sobsbgi_zip'];
				$row['natn_code']=$recs['sobsbgi_natn_code'];		
				$row['sbgi_code']=$ceeb;
				$row['aidm'] = $aidm;
				$row['appl_seqno'] = $appl_seqno;
				$row['seqno'] = $seqno;
				$rs = PSU::db('banner')->Execute($sql,$row);
				if($rs)
				{
					$successfully_moved_to_banner = true;
				}
				else
				{
					$successfully_moved_to_banner = false;
					psu::puke($client_id." Failed trying to insert COLLEGE 3 into Banner table SARPCOL.");
				}
			}// end if - this will only execute if a college ceeb is found (collegecredit3ceeb) 

			if(strlen(trim($this->othersecschool1ceeb))==4)
			{
				$seqno = PSU::db('banner')->GetOne($prms, compact('aidm','appl_seqno'));
				$ceeb=trim($this->othersecschool1ceeb);
				$recs = PSU::db('banner')->GetRow($qry, compact('ceeb'));
				$row['sbgi_code']=$recs['sobsbgi_sbgi_code'];
				$row['name']=$recs['stvsbgi_name'];
				$row['street_line1']=$recs['sobsbgi_street_line1'];
				$row['street_line2']=$recs['sobsbgi_street_line2'];
				$row['city']=$recs['sobsbgi_city'];
				$row['stat_code']=$recs['sobsbgi_stat_code'];
				$row['zip']=$recs['sobsbgi_zip'];
				$row['natn_code']=$recs['sobsbgi_natn_code'];		
				$row['sbgi_code']=$ceeb;
				$row['aidm'] = $aidm;
				$row['appl_seqno'] = $appl_seqno;
				$row['seqno'] = $seqno;
				$rs = PSU::db('banner')->Execute($sql,$row);
				if($rs)
				{
					$successfully_moved_to_banner = true;
				}
				else
				{
					$successfully_moved_to_banner = false;
					psu::puke($client_id." Failed trying to insert OTHER SEC SCHOOL into Banner table SARPCOL.");
				}
			}// end if - this will only execute if a college ceeb is found (othersecschool1ceeb) 
			return $successfully_moved_to_banner;
		}// end if - testing if a college even exists...otherwise this will not run
	}//end insertSARPCOL

 /**
  * insert ethnicity data into Banner
  */
	public function insertSARPRAC()
	{
		// insert sarprac data
		$aidm=$this->getSABNSTUAidm($this->commonapplicantclientid);
		$sql = "INSERT INTO sarprac(
							sarprac_aidm,
							sarprac_race_cde,
							sarprac_activity_date	
						)VALUES(
							:aidm,
							:race_cde,
							sysdate	
						)";
			$row['aidm']=$aidm;
			$row['race_cde']=""; // initialize first
			if(stristr($this->memberinwhichgroup,'N'))
			{
				$row['race_cde']="1";
				$rs=PSU::db('banner')->Execute($sql,$row);
				if($rs)
				{
					$successfully_moved_to_banner = true;
				}
				else
				{
					$successfully_moved_to_banner = false;
					psu::puke($client_id." Failed trying to insert AMERICAN INDIAN OR ALASKA OPTIONS into Banner table SARPRAC.");
				}
			}
			if(stristr($this->memberinwhichgroup,'A'))
			{
				$row['race_cde']="3";
				$rs=PSU::db('banner')->Execute($sql,$row);
				if($rs)
				{
					$successfully_moved_to_banner = true;
				}
				else
				{
					$successfully_moved_to_banner = false;
					psu::puke($client_id." Failed trying to insert ASIAN OPTIONS into Banner table SARPRAC.");
				}
			}
			if(stristr($this->memberinwhichgroup,'B'))
			{
				$row['race_cde']="2";
				$rs=PSU::db('banner')->Execute($sql,$row);
				if($rs)
				{
					$successfully_moved_to_banner = true;
				}
				else
				{
					$successfully_moved_to_banner = false;
					psu::puke($client_id." Failed trying to insert BLACK OR AFRICAN AMERICAN OPTIONS into Banner table SARPRAC.");
				}
			}
			if(stristr($this->memberinwhichgroup,'P'))
			{
				$row['race_cde']="3";
				$rs=PSU::db('banner')->Execute($sql,$row);
				if($rs)
				{
					$successfully_moved_to_banner = true;
				}
				else
				{
					$successfully_moved_to_banner = false;
					psu::puke($client_id." Failed trying to insert NATIVE HAWAIIAN OPTIONS into Banner table SARPRAC.");
				}
			}
			if(stristr($this->memberinwhichgroup,'W'))
			{
				$row['race_cde']="5";
				$rs=PSU::db('banner')->Execute($sql,$row);
				if($rs)
				{
					$successfully_moved_to_banner = true;
				}
				else
				{
					$successfully_moved_to_banner = false;
					psu::puke($client_id." Failed trying to insert WHITE OPTIONS into Banner table SARPRAC.");
				}
			}
		return $successfully_moved_to_banner;
	}//end insertSARPRAC

 /**
  * insert person data into Banner
 */
	public function insertSARPERS()
	{
		// insert sarpers data
		$aidm=$this->getSABNSTUAidm($this->commonapplicantclientid);
		$appl_seqno=$this->getSARHEADApplSeqno($aidm);
		$sql = "INSERT INTO sarpers(
							sarpers_aidm,
							sarpers_appl_seqno,
							sarpers_seqno,
							sarpers_load_ind,
							sarpers_activity_date,
							sarpers_rltn_cde,
							sarpers_last_name,
							sarpers_first_name,
							sarpers_middle_name1,
							sarpers_suffix,
							sarpers_former_name,		
							sarpers_nickname,
							sarpers_gender,
							sarpers_ethn_category,
							sarpers_birth_dte,
							sarpers_dfmt_cde_birth,							
							sarpers_citz_cde,
							sarpers_reltn_deceased_resp
						)VALUES(
							:aidm,
							:appl_seqno,
							:seqno,
							'N'	,
							SYSDATE,
							:rltn_cde,
							:last_name,
							:first_name,
							:middle_name1,
							:suffix,
							:former_name,		
							:nickname,
							:gender,
							:ethn_category,
							:birth_dte,
							'MDC',
							:citz_cde,
							:reltn_deceased_resp
						)";

			$prms="SELECT decode(max(sarpers_seqno)+1,null,1,max(sarpers_seqno)+1)
							FROM sarpers
						 WHERE sarpers_aidm=:aidm
							 AND sarpers_appl_seqno=:appl_seqno";
			$row['seqno'] = PSU::db('banner')->GetOne($prms, compact('aidm','appl_seqno'));
			$row['aidm']=$aidm;
			$row['appl_seqno']=$appl_seqno;
			$row['last_name']=$this->lastname;
			$row['first_name']=$this->firstname;
			$row['middle_name1']=$this->middlename;
			$row['suffix']=$this->suffix;
			$row['gender']=substr($this->sex,0,1);
			$row['former_name']=$this->formerlastname;
			$row['nickname']=$this->preferredname;
			$row['birth_dte']=$this->birthmonth."/".$this->birthday."/".$this->birthyear;
			$row['citz_cde']="";
			switch($this->citizenshipstatus)
			{
				case 1:
					$row['citz_cde']="U";  
				break;
				case 2:
					$row['citz_cde']="U";
				break;
				case 3:
					$row['citz_cde']="R";  
				break;
				case 4:
					$row['citz_cde']="N";
				break;
			}
			$row['rltn_cde']="";
			$row['reltn_deceased_resp']="";
			if(strtoupper($this->hispanicorlatino)=="N")
			{
				$row['ethn_category']="1";
			}
			elseif(strtoupper($this->hispanicorlatino)=="Y")
			{
				$row['ethn_category']="2";
			}
			elseif(!$this->hispanicorlatino)
			{
				$row['ethn_category']="";
			}
			$rs=PSU::db('banner')->Execute($sql,$row);
			if($rs)
			{
				$successfully_moved_to_banner = true;
			}
			else
			{
				$successfully_moved_to_banner = false;
				psu::puke($client_id." Failed trying to insert STUDENT INFO into Banner table SARPERS.");
			}

			if($this->parent1type && $this->parent1lastname && strtolower($this->parent1type)!="unknown")
			{
				$row['seqno'] = PSU::db('banner')->GetOne($prms, compact('aidm','appl_seqno'));
				$row['aidm']=$aidm;
				$row['appl_seqno']=$appl_seqno;
				$row['last_name']=$this->parent1lastname;
				$row['first_name']=$this->parent1firstname;
				$row['middle_name1']=$this->parent1middle;
				$row['suffix']="";
				$row['former_name']="";
				$row['nickname']="";
				$row['gender']="";
				$row['birth_dte']="";
				$row['citz_cde']="";  
				$row['ethn_category']="";
				$row['rltn_cde']="";  // INITIALIZE
				if($this->parent1type=="Mother")
				{
					$row['rltn_cde']="02";
				}
				elseif($this->parent1type=="Father")
				{
					$row['rltn_cde']="01";
				}
				elseif($this->parent2type !="Father" && $this->parent1type=="Mother")
				{
					$row['rltn_cde']="01";
				}
				elseif($this->parent2type !="Mother" && $this->parent1type=="Father")
				{
					$row['rltn_cde']="02";
				}
				if($this->parent1alive=="1")
				{
					$row['reltn_deceased_resp']="";
				}
				elseif($this->parent1alive=="0")
				{
					$row['reltn_deceased_resp']="Y";
				}
				elseif(!$this->parent1alive)
				{
					$row['reltn_deceased_resp']="";
				}
				$rs=PSU::db('banner')->Execute($sql,$row);
				if($rs)
				{
					$successfully_moved_to_banner = true;
				}
				else
				{
					$successfully_moved_to_banner = false;
					psu::puke($client_id." Failed trying to insert PARENT 1 INFO into Banner table SARPERS.");
				}
			}

			if($this->parent2type && $this->parent2lastname && strtolower($this->parent2type)!="unknown")
			{
				$row['seqno'] = PSU::db('banner')->GetOne($prms, compact('aidm','appl_seqno'));
				$row['aidm']=$aidm;
				$row['appl_seqno']=$appl_seqno;
				$row['last_name']=$this->parent2lastname;
				$row['first_name']=$this->parent2firstname;
				$row['middle_name1']=$this->parent2middle;
				$row['suffix']="";
				$row['former_name']="";
				$row['nickname']="";
				$row['gender']="";
				$row['birth_dte']="";
				$row['citz_cde']="";  
				$row['ethn_category']="";
				$row['rltn_cde']="";  // INITIALIZE
				if($this->parent2type=="Mother")
				{
					$row['rltn_cde']="02";
				}
				elseif($this->parent2type=="Father")
				{
					$row['rltn_cde']="01";
				}
				elseif($this->parent2type !="Father" && $this->parent1type=="Mother")
				{
					$row['rltn_cde']="01";
				}
				elseif($this->parent2type !="Mother" && $this->parent1type=="Father")
				{
					$row['rltn_cde']="02";
				}
				if($this->parent2alive=="1")
				{
					$row['reltn_deceased_resp']="";
				}
				elseif($this->parent2alive=="0")
				{
					$row['reltn_deceased_resp']="Y";
				}
				elseif(!$this->parent2alive)
				{
					$row['reltn_deceased_resp']="";
				}
				$rs=PSU::db('banner')->Execute($sql,$row);
				if($rs)
				{
					$successfully_moved_to_banner = true;
				}
				else
				{
					$successfully_moved_to_banner = false;
					psu::puke($client_id." Failed trying to insert PARENT 2 INFO into Banner table SARPERS.");
				}
			}

			if($this->legalguardianlastname && $this->legalguardianrelationship)
			{
				$row['seqno'] = PSU::db('banner')->GetOne($prms, compact('aidm','appl_seqno'));
				$row['aidm']=$aidm;
				$row['appl_seqno']=$appl_seqno;
				$row['last_name']=$this->legalguardianlastname;
				$row['first_name']=$this->legalguardianfirstname;
				$row['middle_name1']=$this->legalguardianmiddle;
				$row['suffix']="";
				$row['former_name']="";
				$row['nickname']="";
				$row['gender']="";
				$row['birth_dte']="";
				$row['citz_cde']="";  
				$row['ethn_category']="";
				$row['rltn_cde']="";  // INITIALIZE
				if($this->legalguardianrelationship)
				{
					$row['rltn_cde']='03';
				}
				$row['reltn_deceased_resp']="";
				$rs=PSU::db('banner')->Execute($sql,$row);
				if($rs)
				{
					$successfully_moved_to_banner = true;
				}
				else
				{
					$successfully_moved_to_banner = false;
					psu::puke($client_id." Failed trying to insert GUARDIAN INFO into Banner table SARPERS.");
				}
			}
		return $successfully_moved_to_banner;
	}//end insertSARPERS

 /**
  * insert phones into Banner
  */
	public function insertSARPHON()
	{
		// @insert sarphon data
		$aidm = $this->getSABNSTUAidm($this->commonapplicantclientid);
		$appl_seqno = $this->getSARHEADApplSeqno($aidm);
		$sql="INSERT INTO sarphon(
						sarphon_aidm,
						sarphon_appl_seqno,
						sarphon_pers_seqno,
						sarphon_seqno,
						sarphon_load_ind,
						sarphon_activity_date,
						sarphon_phone,
						sarphon_pqlf_cde
					)VALUES(
						:aidm,
						:appl_seqno,
						:pers_seqno,
						:seqno,
						'N',
						sysdate,
						:phone,
						:pqlf_cde
					)";
		$prms="SELECT decode(max(sarphon_seqno)+1,null,1,max(sarphon_seqno)+1)
						FROM sarphon
					 WHERE sarphon_aidm=:aidm

						 AND sarphon_appl_seqno=:appl_seqno";

		if($this->areacodecountrycode && $this->phone )
		{
			$phone=preg_replace("/[^a-z \d]/i", "", $this->phone);
			$row['phone'] ="*WEB*".$this->areacodecountrycode."   ".$phone;
			$row['pqlf_cde'] = "HP";
			$row['aidm'] = $aidm;
			$row['appl_seqno'] = $appl_seqno;
			$rltn_code="";
			$seqno = PSU::db('banner')->GetOne($prms, compact('aidm','appl_seqno'));
			$row['seqno']=$seqno;
			$row['pers_seqno']='1';
			$rs = PSU::db('banner')->Execute($sql,$row);
			if($rs)
			{
				$successfully_moved_to_banner = true;
			}
			else
			{
				$successfully_moved_to_banner = false;
				psu::puke($client_id." Failed trying to insert PHONE into Banner table SARPHON.");
			}
		}// end if 

		if($this->curareacodecurcountrycode && $this->currentphone )
		{
			$phone=preg_replace("/[^a-z \d]/i", "", $this->currentphone);
			$row['phone'] ="*WEB*".$this->curareacodecurcountrycode."   ".$phone;
			$row['pqlf_cde'] = "HP";
			$row['aidm'] = $aidm;
			$row['appl_seqno'] = $appl_seqno;
			$rltn_code="";
			$seqno = PSU::db('banner')->GetOne($prms, compact('aidm','appl_seqno'));
			$row['seqno']=$seqno;
			$row['pers_seqno']='1';
			$rs = PSU::db('banner')->Execute($sql,$row);
			if($rs)
			{
				$successfully_moved_to_banner = true;
			}
			else
			{
				$successfully_moved_to_banner = false;
				psu::puke($client_id." Failed trying to insert CURRENT PHONE into Banner table SARPHON.");
			}
		}// end if 

		if($this->email)
		{
			$row['phone'] = $this->email;
			$row['pqlf_cde'] = "EM";
			$row['aidm'] = $aidm;
			$row['appl_seqno'] = $appl_seqno;
			$rltn_code="";
			$seqno = PSU::db('banner')->GetOne($prms, compact('aidm','appl_seqno'));
			$row['seqno']=$seqno;
			// in this case pers seqno would always be a 1 for incoming student
			$row['pers_seqno']='1';   
			$rs = PSU::db('banner')->Execute($sql,$row);
			if($rs)
			{
				$successfully_moved_to_banner = true;
			}
			else
			{
				$successfully_moved_to_banner = false;
				psu::puke($client_id." Failed trying to insert EMAIL into Banner table SARPHON.");
			}
		}// end if 

		if($this->parent1email && $this->parent1type && strtolower($this->parent1type)!="unknown")
		{
			$row['phone'] = $this->parent1email;
			$row['pqlf_cde'] = "EM";
			$row['aidm'] = $aidm;
			$row['appl_seqno'] = $appl_seqno;
			if($this->parent1type == "Father")
			{
				$rltn_code="01";
			}
			elseif($this->parent1type == "Mother")
			{
				$rltn_code="02";
			}
			$seqno = PSU::db('banner')->GetOne($prms, compact('aidm','appl_seqno'));
			$row['seqno']=$seqno;
			$row['pers_seqno']=$this->getSARPERSseqno($aidm,$appl_seqno,$rltn_code);   
			$rs = PSU::db('banner')->Execute($sql,$row);
			if($rs)
			{
				$successfully_moved_to_banner = true;
			}
			else
			{
				$successfully_moved_to_banner = false;
				psu::puke($client_id." Failed trying to insert PARENT 1 EMAIL into Banner table SARPHON.");
			}
		}// end if 

		if($this->parent1areacode && $this->parent1phone && $this->parent1type  && strtolower($this->parent1type)!="unknown" && $this->parent1phone != $this->phone && $this->parent1phone != $this->currentphone)
		{
			$phone=preg_replace("/[^a-z \d]/i", "", $this->parent1phone);
			$row['phone'] = "*WEB*".$this->parent1areacode."   ".$phone;
			$row['pqlf_cde'] = "OT";
			$row['aidm'] = $aidm;
			$row['appl_seqno'] = $appl_seqno;
			if($this->parent1type == "Father")
			{
				$rltn_code="01";
			}
			elseif($this->parent1type == "Mother")
			{
				$rltn_code="02";
			}
			$seqno = PSU::db('banner')->GetOne($prms, compact('aidm','appl_seqno'));
			$row['seqno']=$seqno;
			$row['pers_seqno']=$this->getSARPERSseqno($aidm,$appl_seqno,$rltn_code);   
			$rs = PSU::db('banner')->Execute($sql,$row);
			if($rs)
			{
				$successfully_moved_to_banner = true;
			}
			else
			{
				$successfully_moved_to_banner = false;
				psu::puke($client_id." Failed trying to insert PARENT 1 PHONE into Banner table SARPHON.");
			}
		}// end if 

		if($this->parent2email && $this->parent2type && strtolower($this->parent2type)!="unknown")
		{
			$row['phone'] = $this->parent2email;
			$row['pqlf_cde'] = "EM";
			$row['aidm'] = $aidm;
			$row['appl_seqno'] = $appl_seqno;
			if($this->parent2type == "Father")
			{
				$rltn_code="01";
			}
			elseif($this->parent2type == "Mother")
			{
				$rltn_code="02";
			}
			$seqno = PSU::db('banner')->GetOne($prms, compact('aidm','appl_seqno'));
			$row['seqno']=$seqno;
			$row['pers_seqno']=$this->getSARPERSseqno($aidm,$appl_seqno,$rltn_code);   
			$rs = PSU::db('banner')->Execute($sql,$row);
			if($rs)
			{
				$successfully_moved_to_banner = true;
			}
			else
			{
				$successfully_moved_to_banner = false;
				psu::puke($client_id." Failed trying to insert PARENT 2 EMAIL into Banner table SARPHON.");
			}
		}// end if

		if($this->legalguardianemail && $this->legalguardianrelationship)
		{
			$row['phone'] = $this->legalguardianemail;
			$row['pqlf_cde'] = "EM";
			$row['aidm'] = $aidm;
			$row['appl_seqno'] = $appl_seqno;
			$rltn_code="03";
			$seqno = PSU::db('banner')->GetOne($prms, compact('aidm','appl_seqno'));
			$row['seqno']=$seqno;
			$row['pers_seqno']=$this->getSARPERSseqno($aidm,$appl_seqno,$rltn_code);   
			$rs = PSU::db('banner')->Execute($sql,$row);
			if($rs)
			{
				$successfully_moved_to_banner = true;
			}
			else
			{
				$successfully_moved_to_banner = false;
				psu::puke($client_id." Failed trying to insert GUARDIAN EMAIL into Banner table SARPHON.");
			}
		}// end if 

		return $successfully_moved_to_banner;
	}//end insertSARPHON

 /**
  * insert person preferences into Banner
  */
	public function insertSARPRFN()
	{
		// insert sarprfn data
		if($this->ssncombined)
		{
			$aidm = $this->getSABNSTUAidm($this->commonapplicantclientid);
			$appl_seqno = $this->getSARHEADApplSeqno($aidm);
			$prms="SELECT decode(max(sarprfn_seqno)+1,null,1,max(sarprfn_seqno)+1)
							FROM sarprfn
						 WHERE sarprfn_aidm=:aidm
							 AND sarprfn_appl_seqno=:appl_seqno";
			$seqno = PSU::db('banner')->GetOne($prms, compact('aidm','appl_seqno'));
			$row['aidm']=$aidm;
			$row['appl_seqno']=$appl_seqno;
			$row['ref_no'] = $this->ssncombined;
			$row['seqno']=$seqno;
			$row['pers_seqno']='1';
			$sql = "INSERT INTO sarprfn(
								sarprfn_aidm,
								sarprfn_appl_seqno,
								sarprfn_pers_seqno,
								sarprfn_seqno,
								sarprfn_activity_date,
								sarprfn_ref_no,
								sarprfn_rfql_cde
							)VALUES(
								:aidm,
								:appl_seqno,
								:pers_seqno,
								:seqno,
								sysdate,
								:ref_no,
								'SY'
							)";
				$rs = PSU::db('banner')->Execute($sql,$row);
				if($rs)
				{
					$successfully_moved_to_banner = true;
				}
				else
				{
					$successfully_moved_to_banner = false;
					psu::puke($client_id." Failed trying to insert SSN into Banner table SARPRFN.");
				}
				return $successfully_moved_to_banner;
		}// end if ssncombined
	}//end insertSARPRFN

 /**
  * insert previous college session into Banner
  */
	public function insertSARPSES()
	{
		// insert sarpses data
		if($this->collegecredit1ceeb || $this->collegecredit2ceeb || $this->collegecredit3ceeb)
		{
			$aidm = $this->getSABNSTUAidm($this->commonapplicantclientid);
			$appl_seqno = $this->getSARHEADApplSeqno($aidm);
			$sql="INSERT INTO sarpses(
							sarpses_aidm,
							sarpses_appl_seqno,
							sarpses_pcol_seqno,
							sarpses_seqno,
							sarpses_load_ind,
							sarpses_activity_date,	
							sarpses_begin_dte,
							sarpses_end_dte
						)VALUES(
							:aidm,
							:appl_seqno,
							:pcol_seqno,
							:seqno,
							'N',
							sysdate,	
							:begin_dte,
							:end_dte
						)";

			$pcol="SELECT sarpcol_seqno
							FROM sarpcol
						 WHERE sarpcol_aidm=:aidm
							 AND sarpcol_appl_seqno=:appl_seqno
							 AND sarpcol_iden_cde=:ceeb";

			$prms="SELECT decode(max(sarpses_seqno)+1,null,1,max(sarpses_seqno)+1)
							FROM sarpses
						 WHERE sarpses_aidm=:aidm
							 AND sarpses_appl_seqno=:appl_seqno";

			if(strlen(trim($this->collegecredit1ceeb))==4)
			{
				$ceeb = trim($this->collegecredit1ceeb);				
				$pcol_seqno = PSU::db('banner')->GetOne($pcol, compact('aidm','appl_seqno','ceeb'));
				$seqno = PSU::db('banner')->GetOne($prms, compact('aidm','appl_seqno'));
				$row['aidm'] = $aidm;
				$row['appl_seqno'] = $appl_seqno;
				$row['pcol_seqno'] = $pcol_seqno;  //only for testing -need to force sequence numbers for now
				$row['seqno'] = $seqno;
				$row['begin_dte'] = $this->collegecredit1fromdate;
				$row['end_dte'] = $this->collegecredit1todate;
				$rs = PSU::db('banner')->Execute($sql,$row);
				if($rs)
				{
					$successfully_moved_to_banner = true;
				}
				else
				{
					$successfully_moved_to_banner = false;
					psu::puke($client_id." Failed trying to insert COLLEGE CREDIT 1 into Banner table SARPSES.");
				}
			}// end if - this will only execute if a college ceeb is found (collegecredit1ceeb) 

			if(strlen(trim($this->collegecredit2ceeb))==4)
			{
				$ceeb = trim($this->collegecredit2ceeb);				
				$pcol_seqno = PSU::db('banner')->GetOne($pcol, compact('aidm','appl_seqno','ceeb'));
				$seqno = PSU::db('banner')->GetOne($prms, compact('aidm','appl_seqno'));
				$row['aidm'] = $aidm;
				$row['appl_seqno'] = $appl_seqno;
				$row['pcol_seqno'] = $pcol_seqno;   //make sure to uncomment and delete next line below.
				$row['seqno'] = $seqno;
				$row['begin_dte'] = $this->collegecredit2fromdate;
				$row['end_dte'] = $this->collegecredit2todate;
				$rs = PSU::db('banner')->Execute($sql,$row);
				if($rs)
				{
					$successfully_moved_to_banner = true;
				}
				else
				{
					$successfully_moved_to_banner = false;
					psu::puke($client_id." Failed trying to insert COLLEGE CREDIT 2 into Banner table SARPSES.");
				}
			}// end if - this will only execute if a college ceeb is found (collegecredit2ceeb) 

			if(strlen(trim($this->collegecredit3ceeb))==4)
			{
				$ceeb = trim($this->collegecredit3ceeb);				
				$pcol_seqno = PSU::db('banner')->GetOne($pcol, compact('aidm','appl_seqno','ceeb'));
				$seqno = PSU::db('banner')->GetOne($prms, compact('aidm','appl_seqno'));
				$row['aidm'] = $aidm;
				$row['appl_seqno'] = $appl_seqno;
				$row['pcol_seqno'] = $pcol_seqno;   //uncomment after testing and delete next line
				$row['seqno'] = $seqno;
				$row['begin_dte'] =  $this->collegecredit3fromdate;
				$row['end_dte'] = $this->collegecredit3todate;
				$rs = PSU::db('banner')->Execute($sql,$row);
				if($rs)
				{
					$successfully_moved_to_banner = true;
				}
				else
				{
					$successfully_moved_to_banner = false;
					psu::puke($client_id." Failed trying to insert COLLEGE CREDIT 3 into Banner table SARPSES.");
				}
			}// end if - this will only execute if a college ceeb is found (collegecredit3ceeb) 
			return $successfully_moved_to_banner;
		}// end if checking if there are any college credits
	}//end insertSARPSES

 /**
  * insert custom fields into Banner
  */
	public function insertSARRQST()
	{
		// @insert sarrqst data
		$sql="INSERT INTO sarrqst(
						sarrqst_aidm,
						sarrqst_appl_seqno,
						sarrqst_seqno,
						sarrqst_load_ind,
						sarrqst_activity_date,
						sarrqst_qstn_desc,
						sarrqst_qstn_cde,
						sarrqst_ansr_desc,
						sarrqst_wudq_no,
						sarrqst_resp_flag
					)VALUES(
						:aidm,
						:appl_seqno,
						:seqno,
						'N',
						sysdate,
						:qstn_desc,
						:qstn_cde,
						:ansr_desc,
						:wudq_no,
						:resp_flag
					)";

		$aidm = $this->getSABNSTUAidm($this->commonapplicantclientid);
		$appl_seqno = $this->getSARHEADApplSeqno($aidm);
		$prms="SELECT decode(max(sarrqst_seqno)+1,null,1,max(sarrqst_seqno)+1)
						FROM sarrqst
					 WHERE sarrqst_aidm=:aidm
						 AND sarrqst_appl_seqno=:appl_seqno";
		$row['aidm'] = $aidm;
		$row['appl_seqno'] = $appl_seqno;

		if ($this->fulltimestudent)
		{
			$row['seqno'] = PSU::db('banner')->GetOne($prms, compact('aidm','appl_seqno'));
			if ($this->fulltimestudent=='Y')
			{
				$row['qstn_desc'] = "Are you applying as a full-time student?";
				$row['qstn_cde'] = "";
				$row['ansr_desc'] = "Yes";
				$row['resp_flag']="";
				$row['wudq_no'] = "13";
			}
			if ($this->fulltimestudent=='N')
			{
				$row['qstn_desc'] = "Are you applying as a full-time student?";
				$row['qstn_cde'] = "";
				$row['ansr_desc'] = "No";
				$row['resp_flag']="";
				$row['wudq_no'] = "13";
			}
			$rs = PSU::db('banner')->Execute($sql,$row);
			if($rs)
			{
				$successfully_moved_to_banner = true;
			}
			else
			{
				$successfully_moved_to_banner = false;
				psu::puke($client_id." Failed trying to insert FULL TIME STUDENT INFO into Banner table SARRQST.");
			}
		}// end if

		if($this->cellareacode && $this->cellphone)
		{
			$row['seqno'] = PSU::db('banner')->GetOne($prms, compact('aidm','appl_seqno'));
			$row['qstn_desc'] = "Your cell phone number (with area code):";
			$row['qstn_cde'] = "";
			$phone=preg_replace("/[^a-z \d]/i", "", $this->cellphone);
			$row['ansr_desc'] = $this->cellareacode.$phone;
			$row['wudq_no'] = "25";
			$row['resp_flag']="";
			$rs = PSU::db('banner')->Execute($sql,$row);
			if($rs)
			{
				$successfully_moved_to_banner = true;
			}
			else
			{
				$successfully_moved_to_banner = false;
				psu::puke($client_id." Failed trying to insert CELL PHONE into Banner table SARRQST.");
			}
		}// end if

		$row['seqno'] = PSU::db('banner')->GetOne($prms, compact('aidm','appl_seqno'));
		$row['qstn_cde'] = "RS";
		$row['qstn_desc'] = "";
		$row['ansr_desc'] = "";
		$row['wudq_no'] = "";	
		$row['resp_flag']=$this->resident;
		$rs = PSU::db('banner')->Execute($sql,$row);
		if($rs)
		{
			$successfully_moved_to_banner = true;
		}
		else
		{
			$successfully_moved_to_banner = false;
			psu::puke($client_id." Failed trying to insert RESIDENT INFO into Banner table SARRQST.");
		}

		if($this->parent1degree || $this->parent1graddegree || $this->parent1othergraddegree)
		{
			$row['seqno'] = PSU::db('banner')->GetOne($prms, compact('aidm','appl_seqno'));
			$row['qstn_cde']="";
			if($this->parent1type=="Father")
			{
				$row['qstn_desc'] ="What is the highest level of education your father completed?:<br>Enter ONLY the ONE LETTER CODE.<br>H = High School or GED<br>A = Associate Degree<br>B = Bachelors Degree<br>G = Graduate Degree";
				$row['resp_flag']="";
				$row['wudq_no']="16";
			}
			elseif($this->parent1type=="Mother")
			{
				$row['qstn_desc'] ="What is the highest level of education your mother completed?:<br>Enter ONLY the ONE LETTER CODE.<br>H = High School or GED<br>A = Associate Degree<br>B = Bachelors Degree<br>G = Graduate Degree";
				$row['resp_flag']="";
				$row['wudq_no']="17";
			}
			if($this->parent1degree == 1)
			{
				$deg1ans="";
				$row['ansr_desc']="";
			}
			elseif($this->parent1degree == "2" || $this->parent1degree == "3" || $this->parent1degree == "4" || $this->parent1degree == "5")
			{
				$deg1ans="H";
				$row['ansr_desc']="H";
			}
			elseif($this->parent1degree == "6")
			{
				$deg1ans="A";
				$row['ansr_desc']="A";
			}
				elseif($this->parent1degree == "7")
			{
				$deg1ans="B";
				$row['ansr_desc']="B";
			}
			if($this->parent1graddegree == "1" || $this->parent1othergraddegree == "1")
			{
				$row['ansr_desc']=$deg1ans;
			}
			elseif($this->parent1graddegree == "2" || $this->parent1graddegree == "3" || $this->parent1graddegree == "4" || $this->parent1othergraddegree == "2" || $this->parent1othergraddegree == "3" || $this->parent1othergraddegree == "4")
			{
				$row['ansr_desc']="M";
			}
			elseif($this->parent1graddegree == "5" || $this->parent1graddegree == "6" || $this->parent1graddegree == "7" || $this->parent1othergraddegree == "5" || $this->parent1othergraddegree == "6" || $this->parent1othergraddegree == "7")
			{
				$row['ansr_desc']="G";
			}
			$rs = PSU::db('banner')->Execute($sql,$row);
			if($rs)
			{
				$successfully_moved_to_banner = true;
			}
			else
			{
				$successfully_moved_to_banner = false;
				psu::puke($client_id." Failed trying to insert PARENT 1 DEGREE into Banner table SARRQST.");
			}
		}

		if($this->parent2degree || $this->parent2graddegree || $this->parent2othergraddegree)
		{
			$row['seqno'] = PSU::db('banner')->GetOne($prms, compact('aidm','appl_seqno'));
			$row['qstn_cde']="";
			if($this->parent2type=="Father")
			{
				$row['qstn_desc'] ="What is the highest level of education your father completed?:<br>Enter ONLY the ONE LETTER CODE.<br>H = High School or GED<br>A = Associate Degree<br>B = Bachelors Degree<br>G = Graduate Degree";
				$row['resp_flag']="";
				$row['wudq_no']="16";
			}
			elseif($this->parent2type=="Mother")
			{
				$row['qstn_desc'] ="What is the highest level of education your mother completed?:<br>Enter ONLY the ONE LETTER CODE.<br>H = High School or GED<br>A = Associate Degree<br>B = Bachelors Degree<br>G = Graduate Degree";
				$row['resp_flag']="";
				$row['wudq_no']="17";
			}
			if($this->parent2degree == 1)
			{
				$deg1ans="";
				$row['ansr_desc']="";
			}
			elseif($this->parent2degree == "2" || $this->parent2degree == "3" || $this->parent2degree == "4" || $this->parent2degree == "5")
			{
				$deg1ans="H";
				$row['ansr_desc']="H";
			}
			elseif($this->parent2degree == "6")
			{
				$deg1ans="A";
				$row['ansr_desc']="A";
			}
				elseif($this->parent2degree == "7")
			{
				$deg1ans="B";
				$row['ansr_desc']="B";
			}
			if($this->parent2graddegree == "1" || $this->parent2othergraddegree == "1")
			{
				$row['ansr_desc']=$deg1ans;
			}
			elseif($this->parent2graddegree == "2" || $this->parent2graddegree == "3" || $this->parent2graddegree == "4" || $this->parent2othergraddegree == "2" || $this->parent2othergraddegree == "3" || $this->parent2othergraddegree == "4")
			{
				$row['ansr_desc']="M";
			}
			elseif($this->parent2graddegree == "5" || $this->parent2graddegree == "6" || $this->parent2graddegree == "7" || $this->parent2othergraddegree == "5" || $this->parent2othergraddegree == "6" || $this->parent2othergraddegree == "7")
			{
				$row['ansr_desc']="G";
			}
			$rs = PSU::db('banner')->Execute($sql,$row);
			if($rs)
			{
				$successfully_moved_to_banner = true;
			}
			else
			{
				$successfully_moved_to_banner = false;
				psu::puke($client_id." Failed trying to insert PARENT 2 DEGREE into Banner table SARHEAD.");
			}
		}
		return $successfully_moved_to_banner;
	}// end insertSARRQST

 /**
  * Loads data from the common app feed table in the object
  *
  * @param $record_id ID of common app feed record
  */	
	public function load($record_id)
	{
		$sql = "SELECT * FROM psu.common_app_feed WHERE id = :id";
		
		if($data = PSU::db('banner')->GetRow($sql, compact('record_id')))
		{
			$this->parse($data['application_xml']);
		}//end if
		return false;
	}//end load
	
 /**
  * Marks an application record as imported
  */
  public function markAsImported()
  {
		$id=$this->id;
  	$sql = "UPDATE psu.common_app_feed SET load_date = sysdate WHERE id = :id";
  	return PSU::db('banner')->Execute($sql, compact('id'));
  }//end markAsImported

 /**
  * Parses application xml into the object
  *
  * @param $xml XML data for applicaiton
  */
	public function parse($xml)
	{
		//parse xml
		$dom = new DOMDocument();
		$dom->preserveWhiteSpace = false;
		$dom->loadXML($xml);

		$applications = $dom->getElementsByTagName('application')->item(0);
		
		if($applications->hasChildNodes())
		{
			foreach($applications->childNodes as $app_node)
			{				
				$node_name = strtolower(str_replace('-', '', $app_node->nodeName));
				$this->$node_name = $app_node->nodeValue;
			}//end foreach
		}//end if
	}//end parse

	/**
	*  Translates concentration codes against sorccon to bring back the conc rule code
	*/
	public function translateConcCode($conc,$lfos_rule)
	{
		$cqry="SELECT a.sorccon_ccon_rule
						 FROM sorccon a
					  WHERE a.sorccon_majr_code_conc=:conc
					      AND a.sorccon_cmjr_rule=:lfos_rule
						  AND a.sorccon_term_code_eff=(SELECT MAX(b.sorccon_term_code_eff)
																						 FROM sorccon b
																						WHERE b.sorccon_majr_code_conc=:conc
																				            AND b.sorccon_cmjr_rule=:lfos_rule
																						  AND substr(b.sorccon_term_code_eff,5,2) in ('10','30'))
						  AND substr(a.sorccon_term_code_eff,5,2) in ('10','30')";
  	return PSU::db('banner')->GetOne($cqry, compact('conc','lfos_rule'));
	}// end translateConcCode

	/**
	*  Translates zip codes against gtvzipc to bring back the county codes for saraddr
	*/
	public function translateCntyCode($zip)
	{
		$cqry="SELECT gtvzipc_cnty_code
						 FROM gtvzipc
						WHERE gtvzipc_code=:zip";
		return PSU::db('banner')->GetOne($cqry, compact('zip'));
	}// end translateCntyCode


	/**
	*  Translates major codes against sorcmjr to bring back the majr rule code
	*/
	public function translateMajorCode($majr,$flag)
	{
		$flag_sql = $flag == 'BA' ? "AND sorcmjr_desc LIKE '%(BA)'" : '';

		$mqry="SELECT a.sorcmjr_cmjr_rule
						 FROM sorcmjr a
						WHERE a.sorcmjr_majr_code=:majr
							AND a.sorcmjr_term_code_eff=(SELECT MAX(b.sorcmjr_term_code_eff)
																						 FROM sorcmjr b
																						WHERE b.sorcmjr_majr_code=:majr
																							AND substr(b.sorcmjr_term_code_eff,5,2) in ('10','30')
																							AND b.sorcmjr_disp_web_ind='Y')
							AND substr(a.sorcmjr_term_code_eff,5,2) in ('10','30')
							AND a.sorcmjr_disp_web_ind='Y'
							    $flag_sql";

		return PSU::db('banner')->GetOne($mqry, compact('majr'));
	}// end translateMajorCode


	/**
	*  Translates spelled out states to their abbreviation
	*/
	public function translateNationCode($nation)
	{
		if(!$nation)
		{
			return;
		}
		$nqry="SELECT nation_code
						 FROM country_codes
						WHERE coa_code=:nation";
		return PSU::db('banner')->GetOne($nqry, compact('nation'));
	}// end translateNationCode

	/**
	*  Translates spelled out states to their abbreviation
	*/
	public function translateState($state)
	{
		switch(strtoupper($state))
		{
			case "ALABAMA":
				$stat_cde="AL";
			break;
			case "ALASKA":
				$stat_cde="AK";
			break;  
			case "ARIZONA":
				$stat_cde="AZ";
			break;  
			case "ARKANSAS":
				$stat_cde="AR";
			break; 
			case "CALIFORNIA":
				$stat_cde="CA";
			break;
			case "COLORADO":
				$stat_cde="CO";
			break;
			case "CONNECTICUT":
				$stat_cde="CT";
			break;
			case "DELAWARE":
				$stat_cde="DE";
			break;
			case "FLORIDA":
				$stat_cde="FL";
			break;
			case "GEORGIA":
				$stat_cde="GA";
			break;
			case "HAWAII":
				$stat_cde="HI";
			break;
			case "IDAHO":
				$stat_cde="ID";
			break;
			case "ILLINOIS":
				$stat_cde="IL";
			break;
			case "INDIANA":
				$stat_cde="IN";
			break;
			case "IOWA":
				$stat_cde="IA";
			break; 
			case "KANSAS":
				$stat_cde="KS";
			break; 
			case "KENTUCKY":
				$stat_cde="KY";
			break;
			case "LOUISIANA":
				$stat_cde="LA";
			break;
			case "MAINE":
				$stat_cde="ME";
			break; 
			case "MARYLAND":
				$stat_cde="MD";
			break; 
			case "MASSACHUSETTS":
				$stat_cde="MA";
			break;
			case "MICHIGAN":
				$stat_cde="MI";
			break;
			case "MINNESOTA":
				$stat_cde="MN";
			break;
			case "MISSISSIPPI":
				$stat_cde="MS";
			break;
			case "MISSOURI":
				$stat_cde="MO";
			break;
			case "MONTANA":
				$stat_cde="MT";
			break;
			case "NEBRASKA":
				$stat_cde="NE";
			break;
			case "NEVADA":
				$stat_cde="NV";
			break;
			case "NEW HAMPSHIRE":
				$stat_cde="NH";
			break;
			case "NEW JERSEY":
				$stat_cde="NJ";
			break;
			case "NEW MEXICO":
				$stat_cde="NM";
			break;
			case "NEW YORK":
				$stat_cde="NY";
			break; 
			case "NORTH CAROLINA":
				$stat_cde="NC";
			break; 
			case "NORTH DAKOTA":
				$stat_cde="ND";
			break; 
			case "OHIO":
				$stat_cde="OH";
			break; 
			case "OKLAHOMA":
				$stat_cde="OK";
			break; 
			case "OREGON":
				$stat_cde="OR";
			break; 
			case "PENNSYLVANIA":
				$stat_cde="PA";
			break; 
			case "RHODE ISLAND":
				$stat_cde="RI";
			break; 
			case "SOUTH CAROLINA":
				$stat_cde="SC";
			break; 
			case "SOUTH DAKOTA":
				$stat_cde="SD";
			break; 
			case "TENNESSEE":
				$stat_cde="TN";
			break; 
			case "TEXAS":
				$stat_cde="TX";
			break; 
			case "UTAH":
				$stat_cde="UT";
			break; 
			case "VERMONT":
				$stat_cde="VT";
			break; 
			case "VIRGINIA":
				$stat_cde="VA";
			break; 
			case "WASHINGTON":
				$stat_cde="WA";
			break;
			case "WEST VIRGINIA":
				$stat_cde="WV";
			break;
			case "WISCONSIN":
				$stat_cde="WI";
			break;
			case "WYOMING":
				$stat_cde="WY";
			break;
		}
		return $stat_cde;
	}// end translateState

}//end class CommonAppRecord
