<?php

require_once('PSUECommerce.class.php');
require_once('ecommerce/ETrans.class.php');
require_once('PSUPerson.class.php');

/**
 * @ingroup psuecommerce
 */
class ETransAR extends ETrans {
	public $person;
	public $bill = array();

	public $txraccd = array();
	public $charge_number = 0;

	public function document_number() {
		return 'E' . str_pad($this->fileid, 7, '0', STR_PAD_LEFT);
	}//end document_number

	public function init_template() {
		$payment = array(
			'pidm'               => $this->transaction->person->pidm,
			'detail_code'        => $this->detail_code,
			'user'               => $this->user,
			'entry_date'         => strtoupper($this->entry_date),
			'effective_date'     => strtoupper($this->effective_date),
			'desc'               => $this->detail_desc,
			'srce_code'          => 'K',
			'acct_feed_ind'      => 'Y',
			'activity_date'      => strtoupper($this->entry_date),
			'session_number'     => '000',
			'trans_date'         => strtoupper($this->trans_date),
			'term_code'          => ($this->userchoice2 ?: $this->bursar_term),
			'document_number'    => $this->document_number(),
			'payment_id'         => $this->payment_id(),
		);

		return $payment;
	}//end init_template

	public function payment_id() {
		return substr($this->transactionid . '_' . $this->userchoice9, 0, 20);
	}//end payment_id

	public function process() {		
		if($this->psu_status == 'eod') {
			\PSU::db('banner')->StartTrans();

			if($this->status_flag == 'success') {
				if($this->transactiontype == 1) {
					$this->detail_code = 'IQEW';
					$this->detail_desc = 'Credit-Card-Payment-Thank You';
					$this->multiplier = -1;

					// send notification to bursar if an unknown creditcard payment has been received
					if( $this->transactionstatus == 4 ) {
						$message = 'There was a "Transaction Status = 4" payment paid via Commerce Manager.  Here is the debug information:'."\n\n";
						$message .= print_r( $this, true );
						\PSU::mail('bursar@plymouth.edu,mtbatchelder@plymouth.edu', 'Alert: Unknown Credit Card Payment via Nelnet', $message);		
					}//end if
				} elseif($this->transactiontype == 2) {
					$this->detail_code = 'IREW';
					$this->detail_desc = 'Credit-Card-Refund';
					$this->multiplier = 1;						

					// send notification to bursar if an unknown creditcard refund has been received
					if( $this->transactionstatus == 4 ) {
						$message = 'There was a "Transaction Status = 4" credit card refund fed by via Commerce Manager.  Here is the debug information:'."\n\n";
						$message .= print_r( $this, true );
						$email = array(
							'nrporter@plymouth.edu',
						);

						if( ! \PSU::isDev() ) {
							$email[] = 'bursar@plymouth.edu';
						}//end if

						\PSU::mail($email, 'Alert: Unknown Credit Card Refund via Nelnet', $message);		
					}//end if
				} elseif($this->transactiontype == 3) {
					$this->detail_code = 'IQEC';
					$this->detail_desc = 'E-Check-Payment-Thank You';
					$this->multiplier = -1;						
				}//end else

				$this->transaction = new \PSU\AR\Transaction\Receivable( $this->ordernumber, ( $this->totalamount / 100 ), $this->multiplier );
				$this->bursar_term = $this->transaction->term_code;

				$receivable_template = $this->init_template();

				$this->transaction->split( $receivable_template );

				if( \PSU::has_filter('etrans_post_split') ) {
					\PSU::apply_filters( 'etrans_post_split', $this );
				}//end if

				$this->transaction->save();

				if( \PSU::has_filter('etrans_post_save') ) {
					\PSU::apply_filters( 'etrans_post_save', $this );
				}//end if
				
				$this->psu_status = 'loaded';
				$this->save();

				$amount = (!PSU::db('banner')->HasFailedTrans()) ? ($this->totalamount/100) : false;
			} else {
				$this->psu_status = 'no_load';
				$this->save();
				
				$amount = 0;
			}//end else

			PSU::db('banner')->CompleteTrans();
			return $amount;
		}//end if
		PSU::db('banner')->CompleteTrans(false);
		return false;
	}//end process

	public function __construct($params = false, $prod = false) {
		parent::__construct($params, $prod);
		
		$this->user = ($_SESSION['username']) ? 'NELNET_'.$_SESSION['username'] : 'NELNET_ECOMMERCESCRIPT';
		
		$this->bursar_term = \PSU::db('banner')->GetOne("SELECT value FROM psu.gxbparm WHERE param = 'ug_bill_default_term'");

		$this->entry_date = $this->activity_date = date('Y-m-d H:i:s');
		$this->trans_date = $this->effective_date = date('Y-m-d');
	}//end __construct
}//end class ETransAR
