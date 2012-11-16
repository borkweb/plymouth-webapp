<?php

Class CommonApp_AutoIndex {

	/*
	 * return array of filenames filtered by file extenstion
	 */
	public function filterFiles ($file_arr, $extension, $ext_length = 3) {
		foreach ((array)$file_arr as $file) {
			if (substr($file,(-1*$ext_length),$ext_length) == $extension) {
				$return_arr[] = $file;
			} // end if
		} // end foreach
		return $return_arr;	
	} // end function

	/*
	 * split the common app id from the filename and return both in an array
	 */
	public function getIDandFilename($files) {
		foreach ((array)$files as $pdf) {
			// Pertinent data only after the ! in the filename
			list(,$custom_info) = explode('!',$pdf);

			// Split apart pertinent data for the common app id
			list($commonappid,$lname,$fname,$junk) = explode('_',$custom_info);
			$data[$commonappid]['commonappid'] = $commonappid;
			$data[$commonappid]['pdffile'] = $pdf;
		} // end foreach
		return $data;
	} // end function

	/*
	 * Take an array of xml filenames, open each and mine out the term code.
	 * Also, add which xml file the app's data is in.
	 * Return the information in an array.
	 */
	public function getXMLData($xmlfiles, $data, $path) {
		foreach ((array)$xmlfiles as $file) {
			$xml = simplexml_load_file($path.'/'.$file);

			foreach($xml->application as $app) {
				$appID = $app->commonapplicantClientID;
				list($season,$year) = explode(' ',$app->termID);
				if ($season == 'Fall') {
					$year++;
					$term = $year.'10';
				}  // end if
				elseif ($season = 'Spring') {
					$term = $year.'30';
				} // end else if
				elseif ($season = 'Winter') {
					$term = $year.'20';
				} // end else if
				elseif ($season = 'Summer') {
					$term = $year.'40';
				} // end elseif
				$data[(string)$appID]['term_code'] = $term;
				$data[(string)$appID]['xmlfile'] = $file;
			} // end foreach
		} // end foreach
		return $data;
	} // end function

	/*
	 * Retrieve Banner information for applicant based on commonapp id (part of sabnstu_id) and term_code
	 * If there is not a result, the original data array is returned.
	 */
	public function getBannerInfo($data) {
		$sql = "
			SELECT 
				spriden_id id,
				spriden_pidm pidm,
				'ADMISSIONS APP',
				spriden_last_name,
				spriden_first_name,
				substr(spbpers_ssn,1,3)||'-'||substr(spbpers_ssn,4,2)||'-'||substr(spbpers_ssn,6,4) ssn,
				TO_CHAR(spbpers_birth_date,'DD-MON-YYYY'),
				:term_code term_code,
				saradap_appl_no appl_no,
				'AA',
				null a,
				null b,
				TO_CHAR(sysdate,'YYYY-MM-DD HH24:MI:SS'), 
				null c,
				:pdffile filename,
				:xmlfile xmlfile	
			  FROM spriden
			  JOIN spbpers
			    ON spbpers_pidm = spriden_pidm
			  JOIN saradap s1
			    ON s1.saradap_pidm = spriden_pidm
			   AND s1.saradap_term_code_entry = :term_code
			   AND s1.saradap_appl_no = (
				   SELECT MAX(s2.saradap_appl_no)
			 	     FROM saradap s2
				    WHERE s2.saradap_pidm = s1.saradap_pidm
				      AND s2.saradap_term_code_entry = s1.saradap_term_code_entry
				      AND s2.saradap_appl_no = s1.saradap_appl_no
			     )
			  JOIN sabiden
			    ON sabiden_pidm = spriden_pidm
			  JOIN sabnstu
			    ON sabnstu_aidm = sabiden_aidm
			 WHERE spriden_change_ind IS NULL
			   AND (sabnstu_id = '00'||:commonappid
			   		 OR sabnstu_id = '0'||:commonappid
						)
			";

			if ($results = PSU::db('banner')->Execute($sql,$data) ) {
				if (strlen($results->fields['id']) == 9) {
					return $results;
				} // end if
				else {
					return $data;
				}
			} // end else
	} // end function

	/*
	 * Returns data (if available) from the B-S-ADMN Xtender application for an applicant
	 */
	public function getBDMSInfo ($applicant) {
	$sql = "
		SELECT field1 
		  FROM otgmgr.ae_dt509
		 WHERE field1 = :id
		   AND field8 = :term_code
		   AND field9 = :appl_no
		   AND field3 = 'ADMISSIONS APP'
		   AND field10 = 'AA'
			 ";

			$params = array(
				'id' => $applicant['id'],
				'term_code' => $applicant['term_code'],
				'appl_no' => $applicant['appl_no'],
			);
			if ($results = PSU::db('banner')->Execute($sql,$params) ) {
				$applicant['field1'] = $results->fields['field1'];
				return $applicant;
			} // end if
	} // end function

} // end Class

