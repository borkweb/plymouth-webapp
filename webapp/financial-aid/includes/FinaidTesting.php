<?php

require_once 'autoload.php';

class FinaidTesting extends FinaidParams {
	public function key( $key ) {
		return 'testing-' . $key;
	}//end key

	public function mock( &$target ) {
		$params = new FinaidParams;

		if( $this['data_mock'] ) {
			$target->student->finaid->fafsa_receive_date = $this['mock_fafsa'];

			$mock = array(
				array ( 'rpratrm_period' => '201130', 'robprds_desc' => 'UG Spring 2011', 'rprawrd_fund_code' => 'SOSLSM', 'rfrbase_fund_title' => 'Smart Option Student Loan SM', 'rfrbase_fund_title_long' => 'Smart Option Private Student Loan Sallie Mae', 'rtvawst_desc' => 'Declined', 'rpratrm_offer_amt' => '6180', 'rpratrm_accept_amt' => NULL, 'rpratrm_decline_amt' => '6180', 'rpratrm_cancel_amt' => NULL, 'robprds_seq_no' => '1520', ),
				array ( 'rpratrm_period' => '201130', 'robprds_desc' => 'UG Spring 2011', 'rprawrd_fund_code' => 'DLUNS2', 'rfrbase_fund_title' => 'Federal Direct Unsub. Loan', 'rfrbase_fund_title_long' => 'Federal Direct Unsubsidized Loan', 'rtvawst_desc' => 'Accepted', 'rpratrm_offer_amt' => '2750', 'rpratrm_accept_amt' => '4750.03', 'rpratrm_decline_amt' => '0', 'rpratrm_cancel_amt' => NULL, 'robprds_seq_no' => '1520', ),
				array ( 'rpratrm_period' => '201130', 'robprds_desc' => 'UG Spring 2011', 'rprawrd_fund_code' => 'DLPL2', 'rfrbase_fund_title' => 'Federal Direct PLUS Loan', 'rfrbase_fund_title_long' => 'Federal Direct PLUS Loan', 'rtvawst_desc' => 'Loan Denied', 'rpratrm_offer_amt' => '8175', 'rpratrm_accept_amt' => NULL, 'rpratrm_decline_amt' => '8175', 'rpratrm_cancel_amt' => NULL, 'robprds_seq_no' => '1520', ),

				array ( 'rpratrm_period' => '201110', 'robprds_desc' => 'UG Fall 2011', 'rprawrd_fund_code' => 'AWES', 'rfrbase_fund_title' => 'Forrest Gump End', 'rfrbase_fund_title_long' => 'Forrest Gump Endowment', 'rtvawst_desc' => 'Accepted', 'rpratrm_offer_amt' => '499', 'rpratrm_accept_amt' => '450', 'rpratrm_decline_amt' => '48', 'rpratrm_cancel_amt' => '1', 'robprds_seq_no' => '1520', ),

				array ( 'rpratrm_period' => '201110', 'robprds_desc' => 'UG Fall 2010', 'rprawrd_fund_code' => 'TRUFIT', 'rfrbase_fund_title' => 'TruFit Private Student Loan', 'rfrbase_fund_title_long' => 'TruFit Private Student Loan', 'rtvawst_desc' => 'Accepted', 'rpratrm_offer_amt' => '5500', 'rpratrm_accept_amt' => '5500.41', 'rpratrm_decline_amt' => NULL, 'rpratrm_cancel_amt' => NULL, 'robprds_seq_no' => '1500', ),
				array ( 'rpratrm_period' => '201110', 'robprds_desc' => 'UG Fall 2010', 'rprawrd_fund_code' => 'SOSLSM', 'rfrbase_fund_title' => 'Smart Option Student Loan SM', 'rfrbase_fund_title_long' => 'Smart Option Private Student Loan Sallie Mae', 'rtvawst_desc' => 'Declined', 'rpratrm_offer_amt' => '6180', 'rpratrm_accept_amt' => NULL, 'rpratrm_decline_amt' => '6180', 'rpratrm_cancel_amt' => NULL, 'robprds_seq_no' => '1500', ),
				array ( 'rpratrm_period' => '201110', 'robprds_desc' => 'UG Fall 2010', 'rprawrd_fund_code' => 'DLPL2', 'rfrbase_fund_title' => 'Federal Direct PLUS Loan', 'rfrbase_fund_title_long' => 'Federal Direct PLUS Loan', 'rtvawst_desc' => 'Loan Denied', 'rpratrm_offer_amt' => '8175', 'rpratrm_accept_amt' => NULL, 'rpratrm_decline_amt' => '8175', 'rpratrm_cancel_amt' => NULL, 'robprds_seq_no' => '1500', ),
				array ( 'rpratrm_period' => '201110', 'robprds_desc' => 'UG Fall 2010', 'rprawrd_fund_code' => 'DLUNS2', 'rfrbase_fund_title' => 'Federal Direct Unsub. Loan', 'rfrbase_fund_title_long' => 'Federal Direct Unsubsidized Loan', 'rtvawst_desc' => 'Accepted', 'rpratrm_offer_amt' => '2750', 'rpratrm_accept_amt' => '4750.87', 'rpratrm_decline_amt' => '0', 'rpratrm_cancel_amt' => NULL, 'robprds_seq_no' => '1500', ),

				array ( 'rpratrm_period' => '201120', 'robprds_desc' => 'UG Winter 2011', 'rprawrd_fund_code' => 'TRUFIT', 'rfrbase_fund_title' => 'TruFit Private Student Loan', 'rfrbase_fund_title_long' => 'TruFit Private Student Loan', 'rtvawst_desc' => 'Accepted', 'rpratrm_offer_amt' => '5500', 'rpratrm_accept_amt' => '5500.1', 'rpratrm_decline_amt' => NULL, 'rpratrm_cancel_amt' => NULL, 'robprds_seq_no' => '1520', ),

				array ( 'rpratrm_period' => '201120', 'robprds_desc' => 'UG Winter 2011', 'rprawrd_fund_code' => 'TACOS', 'rfrbase_fund_title' => 'Town of Rumney Scholarship SM', 'rfrbase_fund_title_long' => 'Town of Rumney Summer Scholarship', 'rtvawst_desc' => 'Offered', 'rpratrm_offer_amt' => '5500', 'rpratrm_accept_amt' => null, 'rpratrm_decline_amt' => NULL, 'rpratrm_cancel_amt' => NULL, 'robprds_seq_no' => '1520', ),
			);

			$awards = new PSU_Student_Finaid_Awards( $target->pidm, '1011', $target->student->finaid->fund_messages );
			$awards->load( $mock );
			$target->student->finaid->awards = $awards;

			$mock = array(
				array ( 'rormesg_full_desc' => 'This is the full description of my test message.', 'rormesg_short_desc' => 'Short desc of test msg.', 'rormesg_activity_date' => '2011-01-03 12:42:51', 'rormesg_mesg_code' => 'SOMECODE', 'rtvmesg_mesg_desc' => 'This is the static message description for this message code.' ),
				array ( 'rormesg_full_desc' => 'This is a message with only a full description.', 'rormesg_short_desc' => '', 'rormesg_activity_date' => '2011-02-01 12:42:51', 'rormesg_mesg_code' => 'OTHCODE', 'rtvmesg_mesg_desc' => '' ),
				array ( 'rormesg_full_desc' => '', 'rormesg_short_desc' => '', 'rormesg_activity_date' => '2011-02-01 12:42:51', 'rormesg_mesg_code' => 'OTHCODE', 'rtvmesg_mesg_desc' => '- This is a message with no rormesg_full_desc, just a rtvmesg_mesg_desc. It was also prepended with a hyphen and space.' ),
			);
			$messages = new PSU_Student_Finaid_Messages( $target->pidm );
			$messages->load( $mock );
			$target->student->finaid->messages = $messages;

			$mock = array (
				0 => array ( 'rrrareq_stat_date' => '2010-02-25 00:00:00', 'rtvtrst_code' => 'E', 'rtvtrst_desc' => 'Required', 'rtvtrst_sat_ind' => 'N', 'rtvtreq_code' => '2CHLD0', 'rtvtreq_short_desc' => '2009 CHILD SUPT RCVD', 'rtvtreq_long_desc' => '- VERIFY 2009 CHILD SUPPORT RECEIVED (ALL FAMILY MEMBERS)', 'rtvtreq_instructions' => 'Please provide a letter from the NON-custodial parent identifying the amount of child support received by the custodial parent** for the previous year. The letter should include the amount paid for all family members, not JUST the student.  Note, if you are unable to obtain this letter, we will also accept a copy of your parent\'s divorce decree with the section that specifies child support.  ** The custodial parent is defined as the parent you lived with most during the past twelve months, or the parent with whom you last lived.', 'rtvtreq_url' => 'http://www.example.com/test.pdf',),
				1 => array ( 'rrrareq_stat_date' => '2010-02-25 00:00:00', 'rtvtrst_code' => 'E', 'rtvtrst_desc' => 'Required', 'rtvtrst_sat_ind' => 'N', 'rtvtreq_code' => 'CHLD11', 'rtvtreq_short_desc' => '2009 Child Supt Rcvd', 'rtvtreq_long_desc' => '- Verify 2009 Child Support Received (All family members)', 'rtvtreq_instructions' => 'Please provide a letter from the non-custodial parent identifying the amount of child support received by the custodial parent** for the previous year. The letter should include the amount paid for all family members, not JUST the student.  Note, if you are unable to obtain this letter, we will also accept a copy of your parent\'s divorce decree with the section that specifies child support.  ** The custodial parent is defined as the parent you lived with most during the past twelve months, or the parent with whom you last lived.  ', 'rtvtreq_url' => NULL,),
				2 => array ( 'rrrareq_stat_date' => '2010-02-26 00:00:00', 'rtvtrst_code' => 'S', 'rtvtrst_desc' => 'Received', 'rtvtrst_sat_ind' => 'Y', 'rtvtreq_code' => 'DLMPN', 'rtvtreq_short_desc' => '- Direct Loan MPN', 'rtvtreq_long_desc' => '- Direct Student Loan Master Promissory Note (MPN)', 'rtvtreq_instructions' => 'To access your Federal Direct Loan you will need to complete a Master Promissory Note (MPN) which is your promise to repay the loan once your repayment period begins.  Once this is completed, along with any other requirements on your account, PSU will be able to credit the loan funds to your bill.  If you have been awarded both an unsubsidized and subsidized direct loan this MPN requirement may appear more than once.   THIS PROCESS ONLY NEEDS TO BE COMPLETED ONCE!!', 'rtvtreq_url' => 'https://studentloans.gov/myDirectLoan/index.action',),
				3 => array ( 'rrrareq_stat_date' => '2010-02-26 00:00:00', 'rtvtrst_code' => 'S', 'rtvtrst_desc' => 'Received', 'rtvtrst_sat_ind' => 'Y', 'rtvtreq_code' => 'DLMPN', 'rtvtreq_short_desc' => '- Direct Loan MPN', 'rtvtreq_long_desc' => '- Direct Student Loan Master Promissory Note (MPN)', 'rtvtreq_instructions' => 'To access your Federal Direct Loan you will need to complete a Master Promissory Note (MPN) which is your promise to repay the loan once your repayment period begins.  Once this is completed, along with any other requirements on your account, PSU will be able to credit the loan funds to your bill.  If you have been awarded both an unsubsidized and subsidized direct loan this MPN requirement may appear more than once.   THIS PROCESS ONLY NEEDS TO BE COMPLETED ONCE!!', 'rtvtreq_url' => 'https://studentloans.gov/myDirectLoan/index.action',),
				4 => array ( 'rrrareq_stat_date' => '2010-02-25 00:00:00', 'rtvtrst_code' => 'S', 'rtvtrst_desc' => 'Received', 'rtvtrst_sat_ind' => 'Y', 'rtvtreq_code' => 'DPVR11', 'rtvtreq_short_desc' => '10-11 DEP VER WKST', 'rtvtreq_long_desc' => '- COMPLETED 2010-2011 DEPENDENT VERIFICATION WORKSHEET', 'rtvtreq_instructions' => 'Your Free Application for Federal Student Aid (FAFSA) was selected for a process called Verification by either the Department of Education or PSU. The Department of Education automatically selects approximately 40% of our students\' applications.  As such, we require you to complete and sign this Dependent Verification Worksheet and provide our office with copies of parent and student tax returns for the 2009 tax year (please be sure to include all schedules and forms and to make sure tax returns are signed either by the preparer or the person who has filed it). Please remember that once this documentation is received your file will then be reviewed, however sometimes additional follow-up is required for further clarification. Once we consider your information complete your financial aid award can then be determined.  ', 'rtvtreq_url' => 'http://www.plymouth.edu/finaid/forms1011/DependentVerificationDPVR11.pdf',),
				5 => array ( 'rrrareq_stat_date' => '2009-04-16 00:00:00', 'rtvtrst_code' => 'S', 'rtvtrst_desc' => 'Received', 'rtvtrst_sat_ind' => 'Y', 'rtvtreq_code' => 'ENTRAN', 'rtvtreq_short_desc' => 'ENTRANCE COUNSELING', 'rtvtreq_long_desc' => '- ENTRANCE LOAN COUNSELING INTERVIEW', 'rtvtreq_instructions' => 'Note:  Please cllick on the green "Sign In" button on the left side of this website to complete your Entrance Counseling.  Students who borrow Direct Student Loans are required by federal regulations to complete a one-time loan counseling session. The counseling session provides you with information on how to manage your federal student loans.  This 20 to 30 minute session should be completed online.  The PSU Financial Aid Team will be electronically notified when you have completed this process and your PSU financial aid record will be updated within 2 or 3 days.  Please keep your confirmation number for your records.  ', 'rtvtreq_url' => 'https://studentloans.gov/myDirectLoan/index.action',),
				6 => array ( 'rrrareq_stat_date' => '2009-04-16 00:00:00', 'rtvtrst_code' => 'S', 'rtvtrst_desc' => 'Received', 'rtvtrst_sat_ind' => 'Y', 'rtvtreq_code' => 'ENTRAN', 'rtvtreq_short_desc' => 'ENTRANCE COUNSELING', 'rtvtreq_long_desc' => '- ENTRANCE LOAN COUNSELING INTERVIEW', 'rtvtreq_instructions' => 'Note:  Please cllick on the green "Sign In" button on the left side of this website to complete your Entrance Counseling.  Students who borrow Direct Student Loans are required by federal regulations to complete a one-time loan counseling session. The counseling session provides you with information on how to manage your federal student loans.  This 20 to 30 minute session should be completed online.  The PSU Financial Aid Team will be electronically notified when you have completed this process and your PSU financial aid record will be updated within 2 or 3 days.  Please keep your confirmation number for your records.  ', 'rtvtreq_url' => 'https://studentloans.gov/myDirectLoan/index.action',),
				7 => array ( 'rrrareq_stat_date' => '2010-02-25 00:00:00', 'rtvtrst_code' => 'S', 'rtvtrst_desc' => 'Received', 'rtvtrst_sat_ind' => 'Y', 'rtvtreq_code' => 'FREELU', 'rtvtreq_short_desc' => 'Document Free Lunch', 'rtvtreq_long_desc' => '- Please submit documentation for free lunch received', 'rtvtreq_instructions' => 'You indicated on your FAFSA that you, your parents or someone else in your parents\' household qualified for Free or Reduced Price Lunch.  Please submit verification from the school or school district confirming receipt of this benefit.', 'rtvtreq_url' => NULL,),
				8 => array ( 'rrrareq_stat_date' => '2010-01-27 16:24:40', 'rtvtrst_code' => 'S', 'rtvtrst_desc' => 'Received', 'rtvtrst_sat_ind' => 'Y', 'rtvtreq_code' => 'ISIR', 'rtvtreq_short_desc' => 'FAFSA', 'rtvtreq_long_desc' => ' FREE APP FOR FED STUDENT AID (FAFSA)', 'rtvtreq_instructions' => 'PSU\'S FAFSA priority deadline is March 1.  Our PSU Financial Aid Team strongly encourages all families needing to access financial aid to file by this pr FAFSA and later updated.   Students who complete their FAFSA\'s after the March 1 priority deadline are considered late applicants.  Late applicants will be considered for any aid programs available after ontime students are considered.   Programs available after the priority deadline include Federal PELL, ACG, SMART Grants, and loans.  If you have not done so, complete your FAFSA now at www.fafsa.ed.gov.  If you do NOT plan on attending PSU in 2010-2011, please disregard this requirement.  Best wishes, PSU Financial Aid Team', 'rtvtreq_url' => 'http://www.fafsa.ed.gov/',),
				9 => array ( 'rrrareq_stat_date' => '2010-02-25 00:00:00', 'rtvtrst_code' => 'S', 'rtvtrst_desc' => 'Received', 'rtvtrst_sat_ind' => 'Y', 'rtvtreq_code' => 'PTAX11', 'rtvtreq_short_desc' => '2009 Parent 1040', 'rtvtreq_long_desc' => 'Signed Copy of Parent 2009 Federal Tax Form & Schedules', 'rtvtreq_instructions' => NULL, 'rtvtreq_url' => NULL,),
				10 => array ( 'rrrareq_stat_date' => '2010-02-25 00:00:00', 'rtvtrst_code' => 'S', 'rtvtrst_desc' => 'Received', 'rtvtrst_sat_ind' => 'Y', 'rtvtreq_code' => 'STAX11', 'rtvtreq_short_desc' => '2009 Student 1040', 'rtvtreq_long_desc' => 'Signed Copy of Student 2009 Federal Tax Forms', 'rtvtreq_instructions' => NULL, 'rtvtreq_url' => NULL,),
				11 => array ( 'rrrareq_stat_date' => '2010-01-12 00:00:00', 'rtvtrst_code' => 'E', 'rtvtrst_desc' => 'Required', 'rtvtrst_sat_ind' => 'N', 'rtvtreq_code' => 'SEVER', 'rtvtreq_short_desc' => 'Severed Students', 'rtvtreq_long_desc' => '- Student has been severed', 'rtvtreq_instructions' => NULL, 'rtvtreq_url' => NULL,),
				12 => array ( 'rrrareq_stat_date' => '2010-01-12 00:00:00', 'rtvtrst_code' => 'E', 'rtvtrst_desc' => 'Required', 'rtvtrst_sat_ind' => 'N', 'rtvtreq_code' => 'DANCE', 'rtvtreq_short_desc' => 'Dance Competition', 'rtvtreq_long_desc' => '- Student must win at a dance competition', 'rtvtreq_instructions' => NULL, 'rtvtreq_url' => 'http://www.example.com/',),
			);
			$requirements = new PSU_Student_Finaid_Requirements( $target->pidm );
			$requirements->load( $mock );
			$target->student->finaid->requirements = $requirements;

			$mock = array (
				0 => array ( 'rtvcomp_desc' => 'Tuition (Direct Billed Cost)', 'rbracmp_amt' => '7650', ),
				1 => array ( 'rtvcomp_desc' => 'Fees (Direct Billed Cost)', 'rbracmp_amt' => '2256', ),
				2 => array ( 'rtvcomp_desc' => 'Room/Board(Direct Billed Cost)', 'rbracmp_amt' => '8840', ),
				3 => array ( 'rtvcomp_desc' => 'Books and Supplies', 'rbracmp_amt' => '1202', ),
				4 => array ( 'rtvcomp_desc' => 'Personal Expenses', 'rbracmp_amt' => '1066', ),
				5 => array ( 'rtvcomp_desc' => 'Travel', 'rbracmp_amt' => '836', ),
			);
			$cost = new PSU_Student_Aidyear_AttendanceCost( $target->pidm );
			$cost->load( $mock );
			$target->student->aidyears[ $params['aid_year'] ]->attendancecost = $cost; 

			$target->student->finaid->status = array(
				new PSU_Student_Finaid_Status( array( 'rtvmesg_code' => 'BILL', 'rtvmesg_mesg_desc' => "- This is NOT your bill.  The cost of attendance (COA) reflects the average costs for an academic year.  These costs are used by the PSU Financial Aid Team to determine your financial aid eligibility for attendance at PSU.  Actual costs WILL vary.  Please contact the Student Account Services Office at 877-846-5775 or 603-535-2215 with any questions you may have regarding your bill." ) ),
				new PSU_Student_Finaid_Status( array( 'rtvmesg_code' => 'UNON', 'rtvmesg_mesg_desc' => "- Your estimated cost of attendance is based upon a non-resident undergraduate student living on or off campus.  Please notify the PSU Financial Aid Team if this is not correct so the appropriate adjustments can be made to your account.\n\n- Financial Aid awards are based upon the estimated costs provided below. Actual billed rates are approved by the University System of New Hampshire's Board of Trustees each year and are reflected on your PSU semester bill. For specific information about billing, please visit the PSU Student Account Service's Office website at http://www.plymouth.edu/office/student-account-services/." ) ),
			);
		}

		if( $this['empty_results'] ) {
			$target->student->finaid->fafsa_receive_date = null;

			$attendance = new PSU_Student_Aidyear_AttendanceCost( $target->pidm );
			$attendance->load( array() );
			$target->student->aidyears[ $params['aid_year'] ]->attendancecost = $attendance;

			$awards = new PSU_Student_Finaid_Awards( $target->pidm );
			$awards->load( array() );
			$target->student->finaid->awards = $awards;

			$requirements = new PSU_Student_Finaid_Requirements( $target->pidm );
			$requirements->load( array() );
			$target->student->finaid->requirements = $requirements;

			$messages = new PSU_Student_Finaid_Messages( $target->pidm );
			$messages->load( array() );
			$target->student->finaid->messages = $messages;
		}
	}//end mock
}//end class FinaidTesting
