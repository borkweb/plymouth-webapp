<?php	

	function formatRecord( $person, $merge_with = array() ) {
		$base_person = array(
			'pidm' => '',
			'wpid' => '',
			'psu_id' => '',
			'username' => '',
			'email' => '',
			'msc' => '',
			'name_first' => '',
			'name_first_formatted' => '',
			'name_first_metaphone' => '',
			'name_last' => '',
			'name_last_formatted' => '',
			'name_last_metaphone' => '',
			'name_middle_formatted' => '',
			'name_full' => '',
			'phone_of' => '',
			'phone_vm' => '',
			'emp' => '0',
			'stu' => '0',
			'stu_account' => '0',
			'dept' => '',
			'title' => '',
			'major' => '',
			'has_idcard' => '0'
		);
		
		$person['office_phone'] = PSU::stripPunct( $person['office_phone'] );
		$person['phone_number'] = PSU::stripPunct( $person['phone_number'] );

		if($merge_with) {
			$merge_with = PSU::params($merge_with, $base_person);
			$person = PSU::params($person, $merge_with);
		} else {
			$person = PSU::params($person, $base_person);
		}//end else
		
		$final = array(
			'pidm' => $person['pidm'],
			'wpid' => $person['wp_id'],
			'psu_id' => $person['psu_id'],
			'username' => !strpos( $person['username'], '@') ? trim( $person['username'] ) : substr( $person['username'], 0, strpos( $person['username'], '@' ) ),
			'email' => !strpos( $person['email'], '@') ? trim( $person['email'] ) : substr( $person['email'], 0, strpos( $person['email'], '@' ) ),
			'msc' => $person['msc'] ? $person['msc'] : '',
			'name_first' => PSU::stripPunct( $person[ 'first_name' ] ),
			'name_first_formatted' => $person[ 'first_name' ],
			'name_first_metaphone' => metaphone( PSU::stripPunct( $person[ 'first_name' ] )),
			'name_last' => PSU::stripPunct( $person[ 'last_name' ] ),
			'name_last_formatted' => $person[ 'last_name' ],
			'name_last_metaphone' => metaphone( PSU::stripPunct( $person[ 'last_name' ] )),
			'name_middle_formatted' => $person[ 'middle_name' ],
			'name_full' => trim( preg_replace('/\s\s+/', ' ', $person[ 'first_name' ] .' '. substr( $person[ 'middle_name' ], 0, 1 ).' '. $person[ 'last_name' ] .' '. $person[ 'spbpers_name_suffix' ] )),
			'phone_of' => ( $person['office_phone'] ? '(603) '. substr( $person['office_phone'], 0, 3 ) .'-'. substr(  $person['office_phone'], 3 ) : FALSE ),
			'phone_vm' => ( $person['phone_number'] ? '(603) '. substr( $person['phone_number'], 0, 3 ) .'-'. substr( $person['phone_number'], 3 ) : FALSE ),
			'emp' => $person['emp'] ? 1 : 0,
			'stu' => $person['stu'] ? 1 : 0,
			'stu_account' => $person['stu_account'] ? 1 : 0,
			'dept' => $person['department'] ?: '',
			'title' => $person['title'] ?: '',
			'major' => $person['major'] ?: '',
			'has_idcard' => $person['has_idcard'],
		);
		return $final;
	}
	
	function insertRecords( $insert, $public = FALSE ) {
		if(! count( $insert ))
			return( FALSE );

		foreach( (array) $insert as $in )
			$imploded_insert[] = implode( ',', array_map( array( PSU::db('phonebook'), 'quote' ), $in )) . ',NOW() ,'. abs( (int) $public );
		$sql = "INSERT INTO phonebook_build (
		        	pidm, 
		        	wpid, 
		        	psu_id, 
		        	username, 
		        	email, 
		        	msc, 
		        	name_first, 
		        	name_first_formatted, 
		        	name_first_metaphone, 
		        	name_last, 
		        	name_last_formatted, 
		        	name_last_metaphone, 
		        	name_middle_formatted, 
		        	name_full, 
		        	phone_of, 
		        	phone_vm, 
		        	emp, 
		        	stu,
		        	stu_account,
		        	dept, 
		        	title, 
		        	major, 
		        	has_idcard, 
		        	lastup, 
		        	public
		        ) VALUES (
		        	". implode( '),(', $imploded_insert ) ."
		        ) ON DUPLICATE KEY UPDATE 
		        		psu_id = VALUES( psu_id ), 
		        		wpid = VALUES( wpid ), 
		        		username = VALUES( username ), 
		        		email = VALUES( email ), 
		        		msc = VALUES( msc ), 
		        		name_last = VALUES( name_last ), 
		        		name_last_formatted = VALUES( name_last_formatted ), 
		        		name_last_metaphone = VALUES( name_last_metaphone ), 
		        		name_first = VALUES( name_first ), 
		        		name_first_formatted = VALUES( name_first_formatted ), 
		        		name_first_metaphone = VALUES( name_first_metaphone ), 
		        		name_middle_formatted = VALUES( name_middle_formatted ), 
		        		name_full = VALUES( name_full ), 
		        		phone_of = VALUES( phone_of ), 
		        		phone_vm = VALUES( phone_vm ), 
		        		emp = VALUES( emp ), 
		        		stu = VALUES( stu ), 
		        		stu_account = VALUES( stu_account ), 
		        		dept = VALUES( dept ), 
		        		title = VALUES( title ), 
		        		major = VALUES( major ), 
		        		has_idcard = VALUES( has_idcard ), 
		        		lastup = NOW(), 
		        		public = VALUES( public )";
		PSU::db('phonebook')->Execute($sql);
	}

	function updatePhonebook( $pidm = null ) {
		ini_set('memory_limit', '256M');

		$pidm = (int)$pidm;
		if( $pidm ) {
			$spbpers_pidm = "AND spbpers_pidm = $pidm";
			$spriden_pidm = "AND spriden_pidm = $pidm";
			$pid_pidm = "AND pid = $pidm";
			$demog_pidm = "AND d.pidm = $pidm";
		}

		// truncate the build table to prep
		PSU::db('phonebook')->execute('TRUNCATE TABLE phonebook_build');
		PSU::db('phonebook')->execute('OPTIMIZE TABLE phonebook_build');

		// get everybody
		$interval = 1000;
		$sql = "SELECT COUNT(*) 
		          FROM psu_identity.person_identifiers,
		               spbpers
		         WHERE pid = spbpers_pidm 
		           AND login_name IS NOT NULL $spbpers_pidm";
		if( $tot = PSU::db('psc1')->GetOne($sql)) {
			for( $i = 0; ( $i * $interval ) <= $tot; $i++ ) {
				set_time_limit( 600 );
				$insert = array();

				$sql = "SELECT pid AS pidm, 
				               wp_id, 
				               psu_id, 
				               first_name, 
				               middle_name, 
				               last_name, 
				               name_suffix AS spbpers_name_suffix, 
				               login_name AS username, 
				               login_name AS email, 
											 spbpers_confid_ind, 
											 has_idcard 
					         FROM (SELECT k.pid, 
					                      w.wp_id, 
					                      psu_id, 
					                      first_name, 
					                      middle_name, 
					                      last_name, 
					                      name_suffix, 
					                      login_name, 
					                      spbpers_confid_ind, 
					                      decode(spbcard_id, NULL, 0, 1) as has_idcard, 
					                      ROWNUM AS rnum
						               FROM psu_identity.person_identifiers k
																LEFT OUTER JOIN spbpers
																	ON spbpers_pidm = k.pid
																LEFT OUTER JOIN psu.spbcard
																	ON spbcard_pidm = k.pid
																LEFT OUTER JOIN psu_identity.person_ext_cache w
																	ON w.pid = k.pid
						              WHERE login_name IS NOT NULL 
						                AND k.pid not in(53166, 46242)
					                      $pid_pidm
					               ) 
					         WHERE rnum BETWEEN " . $i * $interval ." AND ". ( $i + 1 ) * $interval . "
						             AND pid NOT IN(53166, 46242)";
				$rset = PSU::db('psc1')->Execute($sql);
				foreach( $rset as $person ) {
					$insert[ $person['pidm'] ] = formatRecord( $person );
				}
				insertRecords( $insert, FALSE );
			}
		}

		set_time_limit( 600 );
		$insert = array();
		
		// get all student account actives
		$sql = "SELECT aa.pidm, 
								  w.wp_id,
									spriden_id AS psu_id, 
									spriden_first_name AS first_name, 
									spriden_mi AS middle_name, 
									spriden_last_name AS last_name, 
									spbpers_name_suffix, 
									stu.vm_phone_number AS phone_number, 
									gobtpac.gobtpac_external_user AS username, 
									gobtpac.gobtpac_external_user AS email, 
									stu.major, 
									ca_address1 AS msc, 
									decode(spbcard_id, NULL, 0, 1) as has_idcard 
						FROM v_student_account_active aa
									INNER JOIN spriden
										ON spriden_pidm = aa.pidm
										AND spriden_change_ind IS NULL
									LEFT OUTER JOIN spbpers
										ON spbpers_pidm = aa.pidm
										AND (spbpers_confid_ind IS NULL
													OR
													spbpers_confid_ind = 'N'
												)
									LEFT OUTER JOIN datamart.ps_as_student_demographics stu
										ON stu.pidm = aa.pidm
									LEFT OUTER JOIN gobtpac
										ON gobtpac_pidm = aa.pidm
									LEFT OUTER JOIN spbcard
										ON spbcard_pidm = aa.pidm
								  LEFT OUTER JOIN psu_identity.person_ext_cache w
									  ON w.pid = aa.pidm
					  WHERE 1 = 1
								  $spriden_pidm
						ORDER BY spriden_last_name,
									spriden_first_name,
									spriden_mi,
									aa.pidm";
		foreach( PSU::db('psc1')->GetAll($sql) as $person ) { 
			$person['emp'] = false;
			$person['stu'] = true;
			$person['stu_account'] = true;
			$insert[ $person['pidm'] ] = formatRecord( $person , $insert[ $person['pidm'] ] );
		}

		// get all public employees
		$sql = "SELECT d.pidm, 
									 w.wp_id,
		               d.id as psu_id, 
		               d.first_name, 
		               d.middle_name, 
		               d.last_name, 
		               spbpers_name_suffix, 
		               d.title, 
		               d.department, 
		               d.office_phone, 
		               d.email_address AS email, 
		               d.msc, 
		               decode(spbcard_id, NULL, 0, 1) as has_idcard
							FROM datamart.ps_as_employee_demog d
									 LEFT OUTER JOIN spbpers
										ON spbpers_pidm = d.pidm
									 LEFT OUTER JOIN spbcard
										ON spbcard_pidm = d.pidm
									 LEFT OUTER JOIN psu_identity.person_ext_cache w
										ON w.pid = d.pidm
						 WHERE EXISTS(SELECT 1 
		                        FROM v_employee e
		                       WHERE e.pidm = d.pidm
		                         AND e.pidm not in(53166, 46242)
		               )
									 $demog_pidm
		         ORDER BY last_name,
		               first_name,
		               middle_name,
		               pidm";
		$rset = PSU::db('psc1')->Execute($sql);
		foreach( $rset as $person ) {
			$person['emp'] = true;
			$insert[ $person['pidm'] ] = formatRecord( $person, $insert[ $person['pidm'] ] );
		}

		$max = 50;
		$loopmax = ceil( count( $insert ) / $max );
		for($i = 0; $i <= $loopmax; $i++) {
			insertRecords( array_slice( $insert, ( $i * $max ), $max ), TRUE );
		}

		$sql = "INSERT INTO phonebook 
		        SELECT * FROM phonebook_build ON DUPLICATE KEY UPDATE 
							wpid = VALUES( wpid ),
		        	psu_id = VALUES( psu_id ), 
		        	lastup = VALUES( lastup ), 
		        	public = VALUES( public ), 
		        	username = VALUES( username ), 
		        	email = VALUES( email ), 
		        	msc = VALUES( msc ), 
		        	name_last = VALUES( name_last ), 
		        	name_last_formatted = VALUES( name_last_formatted ), 
		        	name_last_metaphone = VALUES( name_last_metaphone ), 
		        	name_first = VALUES( name_first ), 
		        	name_first_formatted = VALUES( name_first_formatted ), 
		        	name_first_metaphone = VALUES( name_first_metaphone ), 
		        	name_middle_formatted = VALUES( name_middle_formatted ), 
		        	name_full = VALUES( name_full ), 
		        	phone_of = VALUES( phone_of ), 
		        	phone_vm = VALUES( phone_vm ), 
		        	emp = VALUES( emp ), 
		        	stu = VALUES( stu ), 
		        	stu_account = VALUES( stu_account ), 
		        	dept = VALUES( dept ), 
		        	title = VALUES( title ), 
		        	major = VALUES( major ), 
		        	hint = phonebook.hint, 
		        	has_idcard = VALUES( has_idcard )";
		PSU::db('phonebook')->execute($sql);

		// remove old records
		PSU::db('phonebook')->execute('DELETE FROM phonebook WHERE DATE_SUB( CURDATE(), INTERVAL 1 DAY ) > lastup');

 		return( count( $insert ) );
	}	

function javascript_escape($str) {
	$new_str = '';

	$str_len = strlen($str);
	for($i = 0; $i < $str_len; $i++) {
		$new_str .= '\\x' . dechex(ord(substr($str, $i, 1)));
	}

	return $new_str;
}
