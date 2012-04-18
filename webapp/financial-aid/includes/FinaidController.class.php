<?php

class FinaidController extends PSUController {
	/**
	 * The student whose data we are looking at.
	 */
	public $target;

	/**
	 * The PSUStudentData record for $this->target, based on aid year.
	 */
	public $studentdata;

	/**
	 * The user who is viewing the page; may be a user who is authorized to view
	 * $this->person's information.
	 */
	public $user;

	/**
	 * Iterface to persistent user-specific app parameters (i.e. session).
	 */
	public $params;

	/**
	 * Interface to testing options.
	 */
	public $testing;

	// redefine delegate so parent knows which controller to use as default.
	// placeholder until php 5.3 "static" keyword.
	public static function delegate( $path = null, $controller_class = __CLASS__ ) {
		parent::delegate( $path, $controller_class );
	}

	public function __construct( $title = null ) {
		parent::__construct( $title );

		$this->user = new PSUPerson( $_SESSION['wp_id'] );

		$this->params = new FinaidParams;
		$this->params['admin'] = IDMObject::authZ('permission', 'mis') || IDMObject::authZ('permission', 'finaid_myfinaid_admin');
		$this->tpl->assign('params', $this->params);

		$this->_init_person( $this->params['id'] );
		$this->params['aid_year'] = $this->_init_aid_years( $this->params['aid_year'] );

		//
		// setup testing data
		//

		$this->testing = new FinaidTesting();

		// admins can do test mode
		if( $this->params['admin'] ) {
			$this->params['testable'] = true;
		}

		if( $this->params['testable'] ) {
			$this->testing->mock( $this->target );
		}

		$this->tpl->assign('testing', $this->testing);

		// Warn user about pending relationships, no more than once every 5 minutes
		if( $this->params['warned-pending'] < time() - 300 && $pending = $this->user->myrelationships->get('pending') ) {
			$this->params['warned-pending'] = time();
			$count = count( $pending );
			$_SESSION['warnings']['finaid-pending'] = sprintf('You have %d pending relationship%s. Please note that you can\'t see another person\'s financial aid information until your relationship with that person is confirmed. Visit <a href="http://go.plymouth.edu/familychannel">Family Access</a> for more details.', $count, ($count == 1 ? '' : 's') );
		}
	}//end constructor

	public function index() {
		$this->_redirect_authorization();

		if( ! $this->target ) {
			return $this->display('not-student.tpl');
		}

		if( ! $this->target->student->finaid ) {
			$_SESSION['errors'][] = sprintf('Could not find any financial information for PSU ID %s in the %s.', $this->target->id, $this->aid_year->aidy_desc);
			$this->display('no-finaid.tpl');
			die();
		}

		// limit messages if a family member is viewing the page
		if( $this->target === $this->user ) {
			$messages = $this->target->student->finaid->messages->messages();
		} else {
			$messages = $this->target->student->finaid->messages->nonstudent_messages();
		}

		foreach( $messages as $message ) {
			$_SESSION['messages'][] = sprintf( '%s (%s)',
				$message->full_message(),
				strftime('%b %e, %Y', $message->activity_date_timestamp())
			);
		}

		if( ! $this->target->student->finaid->fafsa_received() ) {
			$_SESSION['errors'][] = sprintf( 'We have not received your %s FAFSA. Please complete your FAFSA at <a href="http://www.fafsa.ed.gov/">www.fafsa.ed.gov</a>.',
				$this->aid_year->year_range() );
		}

		$this->display();
	}//end index

	public function set_params() {
		$aid_year = $_REQUEST['aid_year'];
		$id = $_REQUEST['id'];

		if( $id ) {
			$this->params['id'] = $id;

			// reset aid year when setting id; ensures we get the most recent
			// if a user is specified without an explicit aid year (below)
			$this->params['aid_year'] = null;
		}

		if( $aid_year ) {
			$this->params['aid_year'] = $aid_year;
		}

		PSU::redirect( $GLOBALS['BASE_URL'] . '/' );
	}//end set_params

	public function testing( $setting = null ) {
		if( ! $this->params['testable'] ) {
			PSU::redirect( $GLOBALS['BASE_URL'] . '/' );
		}

		if( $setting ) {
			switch( $setting ) {
				case 'fafsa':
					$this->testing['mock_fafsa'] = $this->testing['mock_fafsa'] ? false : time() - 86400 * 7; // 1 week ago
					break;
			}

			PSU::redirect( $GLOBALS['BASE_URL'] . '/' );
		}

		$this->tpl->page_title = 'Testing Parameters';
		$this->display();
	}//end testing

	/**
	 * Force a useless version of the verification screen.
	 */
	public function testing_verify() {
		$_SESSION['messages'][] = 'You are viewing this in testing mode. Form submission will not work.';

		$this->display( 'verify.tpl' );
	}//end testing_verify

	public function _testing() {
		if( ! $this->params['testable'] ) {
			PSU::redirect( $GLOBALS['BASE_URL'] . '/' );
		}

		$form = $_POST;

		// reset checkboxes
		$this->testing['data_mock'] = false;
		$this->testing['empty_results'] = false;
		$this->testing['force_verify'] = false;

		foreach( $form as $key => $value ) {
			$this->testing[$key] = $value;
		}

		PSU::redirect( $GLOBALS['BASE_URL'] . '/testing' );
	}//end _testing

	/**
	 * Let a relation verify their identity and gain access to student data.
	 */
	public function verify() {
		// already verified? send back to index
		if( $this->_check_authorization() ) {
			PSU::redirect( $GLOBALS['BASE_URL'] . '/' );
		}

		$this->display();
	}//end verify

	/**
	 *
	 */
	public function _verify() {
		$year = (int)$_POST['Date_Year'];
		$month = (int)$_POST['Date_Month'];
		$day = (int)$_POST['Date_Day'];

		$birthdate = FinaidAPI::fields2birthdate( $year, $month, $day );
		$last4 = sprintf("%04d", $_POST['last4']);

		$application = FinaidAPI::verifyIdentity( $this->target, $last4, $birthdate );

		if( $application ) {
			FinaidAPI::logAuthorization( $this->target->wp_id, $this->user->wp_id, $application->seqno, $application->aid_year );
			PSU::redirect( $GLOBALS['BASE_URL'] . '/' );
		} else {
			$_SESSION['errors'][] = 'That information was not found in the FAFSA for the requested financial aid year.';
			// TODO: enable this log
			//FinaidAPI::logAuthFailure( $this->target->wp_id, $this->user->wp_id, $application->seqno, $application->aid_year );
			PSU::redirect( $GLOBALS['BASE_URL'] . '/verify' );
		}
	}//end _verify

	/**
	 * Check to see if this family member is authorized to view this financial data.
	 */
	private function _check_authorization() {
		if( $this->params['admin'] && ! $this->testing['force_verify'] ) {
			return true;
		}

		if( $this->target->wp_id === $this->user->wp_id ) {
			return true;
		}

		return FinaidAPI::isAuthorized( $this->target, $this->user, $this->aid_year->aidy_code );
	}//end _check_authorization

	private function _redirect_authorization() {
		$authorized = $this->_check_authorization();

		if( ! $authorized ) {
			$_SESSION['errors'][] = 'Please verify that you are authorized to access this financial information.';
			PSU::redirect( $GLOBALS['BASE_URL'] . '/verify' );
		}
	}

	/**
	 * initialize default, selected, and available aid years
	 *
	 * @param $selected_aid_year int The selected aid year
	 */
	private function _init_aid_years( $selected_aid_year ) {
		if( ! isset($selected_aid_year) || ! $selected_aid_year ) {
			if( $this->target->student->finaid) {
				$selected_aid_year = $this->target->student->finaid->max_aid_year;
			}

			if( ! $selected_aid_year ) {
				$selected_aid_year = PSUStudent::getAidYear( PSUStudent::getCurrentTerm( 'UG' ) );
			}
		}

		$this->aid_years = PSU_Student_Finaid::aid_years();
		$this->aid_year = $this->aid_years[$selected_aid_year];
		$this->target->student->finaid->aid_year = $this->aid_year->aidy_code;

		$this->tpl->assign( 'aid_years', $this->aid_years );
		$this->tpl->assign( 'aid_year', $this->aid_year );

		$this->tpl->assign( 'student_aid_year', $this->target->student->aidyears[$this->aid_year->aidy_code] );

		return $selected_aid_year;
	}//end _init_aid_years

	/**
	 * initialize the person object.  If no $id is given, $this->target
	 * is populated by $this->user
	 *
	 * @param $id mixed Identifier of the person being viewed
	 */
	private function _init_person( $id ) {
		if( !$id && $this->target ) {
			return;
		}//end if

		$this->target = null;

		FinaidAPI::initPerson( $id, $this->target, $this->user );

		$this->tpl->assign( 'user', $this->user );
		$this->tpl->assign( 'target', $this->target );
		$this->tpl->assign( 'finaid', $this->target->student->finaid );
	}//end _init_person
}//end class FinaidController
