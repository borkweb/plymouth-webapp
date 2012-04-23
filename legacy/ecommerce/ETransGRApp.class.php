<?php

require_once 'autoload.php';
require_once 'ecommerce/ETrans.class.php';
require_once '/web/pscpages/webapp/graduate/includes/Application.class.php';

/**
 * @ingroup psuecommerce
 */
class ETransGRApp extends ETrans {
	public $Application = null;

	/**
	 * Load the iGrad application object into $this.
	 */
	public function loadApplication() {
		// nothing to do if application has been loaded
		if( $this->Application !== null ) {
			return;
		}

		$psp_user_id = (int)$this->ordernumber;
		$appid = ApplicationDB::appid_for_pspid( $psp_user_id );

		if( ! $appid ) {
			throw new Exception("Appid not found for psp_user_id of $psp_user_id");
		}

		$f = ApplicationDB::applicationForm($appid);
		$this->Application = new Application($appid, $f);
	}//end loadApplication

	public function db_has_error( $db ) {
		return $db->ErrorNo() > 0;
	}//end check_error

	public function db_log_error( $db, $message = null ) {
		if( $message ) {
			$message = sprintf( "%s (%s): %s", $db->ErrorMsg(), $db->ErrorNo(), $message );
		} else {
			$message = sprintf( "%s (%s)", $db->ErrorMsg(), $db->ErrorNo() );
		}

		$this->log( $message );
	}

	public function log( $message ) {
		$log = \PSU::get( 'log/igrad/x_ecommerce' );
		$log->write( sprintf( "[%s] %s", $this->psp_user_id(), $message ) );
	}

	public function psp_user_id() {
		return (int)$this->ordernumber;
	}

	/**
	 * Process the ecommerce record, and update the Application with a success flag.
	 */
	public function process() {
		ob_start( array( $this, 'ob_log' ) );

		PSU::db('psp')->debug = true;

		$this->loadApplication();

		$args = array( 'psp_user_id' => $this->psp_user_id() );
		$appid = PSU::db('banner')->GetOne( "SELECT app_id FROM psu_psp.app_2008_app WHERE psp_user_id = :psp_user_id AND active = 'Y'", $args );

		if( $this->db_has_error(PSU::db('banner')) ) {
			$this->db_log_error( PSU::db('banner'), "problem getting app_id" );
			ob_end_clean();
			return false;
		}

		// no result from query
		if( false == $appid ) {
			$this->log( "failed app_id query, 0 results" );
			ob_end_clean();
			return false;
		}

		$appid = (int)$appid;

		$this->log( "found app id $appid" );

		if( $this->psu_status != 'eod' && $this->psu_status != 'receipt' ) {	
			$this->log( "skipping psu_status of {$this->psu_status}" );
			ob_end_clean();
			return false;
		}

		PSU::db('banner')->StartTrans();
		
		// default flag in the database is "N" (not paid)
		switch( $this->status_flag ) {
			case 'success':  $flag = 'Y'; break;
			case 'rejected': $flag = 'F'; break;
			case 'error':    $flag = 'E'; break;
			default:         $flag = 'N'; break;
		}//end switch

		$this->log( "status flag {$this->status_flag} resulted in local flag {$flag}" );
					
		// dump the flag into the application
		if( $flag == 'Y' ) {
			// mark_paid() does its own Application->save()
			$this->Application->Fee()->mark_paid( false );
		} else {
			$this->Application->Fee()->status_ = $flag;
			$this->Application->save();
		}

		if( ! PSU::db('banner')->HasFailedTrans() ) {
			$this->psu_status = 'loaded';
			$this->save();
			
			$result = PSU::db('banner')->CompleteTrans() ? ($this->totalamount/100) : false;

			if( $this->db_has_error( PSU::db('banner') ) ) {
				$this->db_log_error( PSU::db('banner'), "error in CompleteTrans" );
			} elseif( ! $result ) {
				$this->log( "error, result set to " . serialize($result) );
			}

			ob_end_clean();
			return $result;
		}//end else

		$this->log( "error, transaction was marked as failed" );

		PSU::db('banner')->CompleteTrans(false);

		ob_end_clean();
		return false;
	}//end process

	/**
	 * Log output buffer to a file.
	 */
	public function ob_log( $buffer ) {
		file_put_contents( '/web/temp/etransgrapp.log', $buffer, FILE_APPEND );
		return $buffer;
	}//end ob_log

	/**
	 * Handle webapp/ecommerce/receipt.html display. This should happen for everyone, unless the user closes
	 * their browser page while the payment is submitting. If this runs, there is no need to send a payment
	 * notification via email.
	 */
	public function receipt() {
		$this->loadApplication();

		$this->process();

		if( $this->psu_status == 'loaded' ) {
			// no notification is necessary for people who saw their receipt
			$sql = "UPDATE psu_psp.app_2008_app SET app_paid_notify = SYSDATE WHERE app_id = :appid";
			PSU::db('banner')->Execute($sql, array('appid' => $this->Application->appid));
		}
	}//end receipt
	
	/**
	 * Generate the URL for linking off to our ecommerce processor.
	 */
	public function url($appid, $name, $amount, $program = '') {
		$psp_user_id = PSU::db('psp')->GetOne('SELECT psp_user_id FROM app_2008_app WHERE app_id = :appid', array('appid' => $appid));

		if( !$psp_user_id ) {
			throw new Exception('Could not find the psp_user_id for that appid');
		}

		$params = array(
			'orderNumber' => $psp_user_id,
			'amount' => $amount,
			'amountDue' => $amount,
			'orderType' => 'Admission GR App',
			'orderDescription' => 'Graduate Admission App Fee',
			'name' => $name,
			'userChoice2' => $program,
			'userChoice3' => $appid,
		);

		$this->setURLParam('redirectUrl', str_replace('igrad', 'www', $this->base_url) . '/receipt.html');
		$this->setURLParam('retriesAllowed', 5);
		
		$this->setURLParam('redirectUrlParameters', implode(',', $this->_redirect_params));

		return $this->_url( PSU::isdev() ? 'test' : 'prod', $params);
	}

	public function __construct($params = false, $prod = false) {
		parent::__construct($params, $prod);
	}//end __construct
}//end class ETransGRApp
