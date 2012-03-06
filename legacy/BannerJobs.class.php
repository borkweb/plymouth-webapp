<?php

/*
 * BannerJobs.class.php
 *
 * === Modification History ===
 * 0.1.0  22-may-2009  [djb]  original
 * 1.0.0  21-may-2010  [djb]  ready for production with TSRTBIL and RPEDISB
 *
 * @package 		PSUBannerAPI
 */

require_once 'PSUDatabase.class.php';

/**
 * Banner Job Submission API
 *
 * @version		1.0.0
 * @module		BannerJobs.class.php
 * @author		Dan Bramer <djbramer@plymouth.edu>
 * @copyright 2009, Plymouth State University, ITS
 */ 
class BannerJobs
{
	var $error;
	var $job_sequence_number;

	private $_username;
	private $_password;
	//private $_valid_jobs = array('TSRCBIL','GLBDATA','RORGRPS','TGRAPPL','AGPCASH');
	private $_valid_jobs = array(
		'APPSTDI', // Bursar
		'GLBDATA', // Bursar
		'ICGORLDI',// MIS (testing purposes)
		'RPEDISB', // Bursar
		'SFRFASC', // Bursar
		'SHRCGPA', // Registrar
		'SHRGPAC', // Registrar
		'SHRROLL', // Registrar
		'SLRDADD', // ResLife
		'SLRFASM', // Bursar
		'TGPHOLD', // Bursar
		'TGRAGES', // Bursar
		'TGRAPPL', // Bursar
		'TGRCLOS', // Bursar
		'TGRCSHR', // Bursar
		'TGRFEED', // Bursar
		'TGRRCON', // Bursar
		'TGRUNAP', // Bursar
		'TSRCBIL', // Bursar
		'TSRRFND', // Bursar
		'TSRTBIL', // Bursar
		'TVRCRED', // Bursar
	);
	private	$_shell_programs = array('RPEDISB','GLBDATA');		

	/**
	 * BannerJobs
	 *
	 * BannerJobs constructor with db connection. 
	 *
	 * @since     version 1.0.0
	 * @param     ADOdb $adodb ADOdb database connection
	 */
	function __construct()
	{
		$this->_conf = PSUDatabase::connect('other/jobsub','return');
		if (!$this->_conf)
		{
			exit("No configuration for this program found.");
		}
	}//end __construct


	/**
	 * Return the next job sequence number.
	 * @return integer
	 */
	function getJobSequenceNumber()
	{
		if (!isset($this->job_sequence_number))
		{
			// Get one-up number from job sequence	
			$this->job_sequence_number = PSU::db('banner')->getOne("SELECT gjbpseq.nextval from dual");
		}
		return $this->job_sequence_number;
	}//end getJobSequeneceNumber
	

	/**
	 * Validate a job name against a list of known jobs.
	 * @param $jobname \b string the job to test (ie. TSRTBIL)
	 * @return boolean
	 */
	function validateJobName($jobname)
	{
		// Check to make sure name of appointment is actually a Banner Program we've setup
		if(!in_array($jobname,$this->_valid_jobs))
		{
			$this->error = "$jobname is not a valid banner procedure ... looking for next event\n";
			return false;
		} // end if
		return true;
	}
	

	/**
	 * Validate paramters and prepare sql for Banner insertion, or just validate parameters.
	 * @param $jobname \b string the job to test (ie. TSRTBIL)
	 * @param $params \b array of parameters
	 * @param $returnsql \b boolean will return parameter insertion code if true
	 * @return $sql \b boolean or array, depending on $returnsql
	 */
	function jobValidateParams($jobname,$params,$returnsql=true)
	{
		if (!$this->validateJobName($jobname)) return false;
		
		// TODO: need to validate number of parameters
		
		$sql = array();
		foreach($params as $line)
		{
			$items = explode(';',rtrim($line));
			$d=array_pop($items);
			foreach($items as $item)
			{
				list($key,$value,$label) = explode(':',trim($item));
				$value = trim($value,"'");
				$label = ($label) ? $label : 'null';
				//echo "$key,$value,$label\n";
				if ($this->jobParamValidate($jobname,$key,$value))
				{
					if($returnsql)
					{
						//Check to see if 'E' (current_date) is the value
						if ($value == 'E')
						{
							$value = $this->replaceDate($jobname,$key,$value);
						}
						$toexecute = array();
						$toexecute['sql'] = "INSERT INTO GJBPRUN (
									GJBPRUN_JOB,
									GJBPRUN_ONE_UP_NO,
									GJBPRUN_NUMBER,
									GJBPRUN_ACTIVITY_DATE,
									GJBPRUN_VALUE,
									GJBPRUN_LABEL
								  ) values (
								  	:jobname,
								  	:seqno,
								  	:key,
								  	sysdate,
								  	:value,
								  	:label
								  )";
						$toexecute['params'] = array(
							'jobname'=>$jobname,
							'seqno'=>$this->getJobSequenceNumber(),
							'key'=>$key,
							'value'=>$value,
							'label'=>$label
						);
						$sql[] = $toexecute;
					}
					else
					{
						$sql = true;
					}
				} 
				else
				{
					return false;
				}
			}
		}
		return $sql;
	}
	
	/**
	 * Mirror Job Submission property where is parameter = 'E', it substitues current day
	 * @param $jobname \b string the job to test (ie. TSRTBIL)
	 * @param $key \b int of parameter number
	 * @param $value \b string of parameter value
	 * @return $value \b string of parameter value
	 */
	function replaceDate($jobname,$key,$value)
	{
		if ($value != 'E') return $value;
		$replace = false;

		if ($jobname == 'TSRTBIL' &&  ($key == '05' || $key == '15')) $replace = true;
		if ($jobname == 'TSRCBIL' &&  ($key == '02' || $key == '17')) $replace = true;
		if ($jobname == 'SFRFASC' &&  $key == '02') $replace = true;
		if ($jobname == 'TGRAGES' &&  $key == '08') $replace = true;
		if ($jobname == 'TGRFEED' &&  $key == '01') $replace = true;
		if ($jobname == 'TSRRFND' &&  $key == '05') $replace = true;
		if ($jobname == 'SLRDADD' &&  $key == '02') $replace = true;

		if ($replace) $value = strtoupper(date('d-M-Y'));
		return $value;
	}
	
	/**
	 * Validate a job's parameters, if possible.
	 * @param $jobname \b string the job to test (ie. TSRTBIL)
	 * @param $key \b array of parameters
	 * @param $value \b boolean will return parameter insertion code if true
	 * @return boolean
	 */
	function jobParamValidate($jobname,$key,$value)
	{
		if (!$this->validateJobName($jobname)) return false;
		// $a and $b are parameter number and value
		if(strlen($key) == 2) // parameter number must be 2 digits
		{
			$sql="SELECT gjbpval_value 
				    FROM gjbpval 
				   WHERE gjbpval_job = :jobname 
				     AND gjbpval_number = :key";
			$params = array(
				'jobname' => $jobname,
				'key' => $key
				);
			// Check to see if this parameter has pre-defined valid values
			$valid_values = PSU::db('banner')->getCol($sql,$params);
			if (!$valid_values || in_array($value,(array)$valid_values)) 
			{
				return true;
			}
			else
			{
				echo $this->error = "FAIL! Bad Parameter: $value not acceptable for parameter $key in $jobname";
				return false;
			}
		}
		else
		{
			$this->error = "Bad Parameter: Length of key $key not two digits.";
			return false;
		}
	}

	/**
	 * Run the specified job.
	 * @param $jobname \b string the job name
	 * @param $params \b array a list of parameters for BannerJobs::jobValidateParams()
	 */
	function runJob($jobname,$params)
	{
		$sql = $this->jobValidateParams($jobname,$params);
		if ($sql)
		{
			// Populate general.gjbprun with paramters
			foreach ($sql as $cmd)
			{
				if (!($result = PSU::db('banner')->Execute($cmd['sql'],$cmd['params'])))
				{
					$this->error = "SQL Error: ".PSU::db('banner')->ErrorMsg();
					// Please turn the next two lines into a function if I get used too much ... kthx bye
					$delete = PSU::db('banner')->Execute("DELETE FROM GJBPRUN WHERE GJBPRUN_ONE_UP_NO = :seqno",array('seqno'=>$this->getJobSequenceNumber()));
					unset($this->job_sequence_number);
					return false;
				}
			}

			// Execute system command for program to run job
			if (in_array($jobname,$this->_shell_programs))
			{
	
				$sysstr = "sh ".$GLOBALS['BANNER_HOME']."/links/".strtolower($jobname).".shl ".$this->_conf['username']." ".PSUSecurity::password_decode($this->_conf['password'])." ".$this->getJobSequenceNumber()." ".strtoupper($jobname)." DATABASE";

			}
			else
			{
				$sysstr = "echo \"{$this->job_sequence_number}\" | ".$GLOBALS['BANNER_HOME']."/general/exe/".strtolower($jobname)." -o /u02/SCT/banjobs/".strtolower($jobname)."_{$this->job_sequence_number}.lis ".$this->_conf['username']."/".PSUSecurity::password_decode($this->_conf['password'])." 1> /u02/SCT/banjobs/".strtolower($jobname)."_{$this->job_sequence_number}.log 2>&1";
			}
			$result = shell_exec($sysstr);
			unset($this->job_sequence_number);
			if ($retval ==0)
			{
				return true;
			}
			else
			{
				return $retval;
			}
		}
		else 
		{
			return false;
		}
	}//end runJob
}//end BannerJobs
