<?php

require_once('PSUECommerce.class.php');
require_once('ecommerce/ETransLegacy.class.php');
require_once('PSUPerson.class.php');

/**
 * @ingroup psuecommerce
 */
class ETransLegacyAR extends ETransLegacy {
	public $person;
	public $bill = array();

	public $txraccd = array();
	public $charge_number = 0;

	public function init_template($term_code = '') {
		$payment = array(
			'pidm'               => $this->transaction->person->pidm,
			'detail_code'        => $this->detail_code,
			'user'               => $this->user,
			'entry_date'         => strtoupper($this->entry_date),
			'effective_date'     => strtoupper($this->effective_date),
			'desc'               => $this->detail_desc,
			'srce_code'          => 'T',
			'acct_feed_ind'      => 'Y',
			'activity_date'      => strtoupper($this->entry_date),
			'session_number'     => '000',
			'trans_date'         => strtoupper($this->trans_date),
			'term_code'          => $term_code,
			'document_number'    => (stristr($this->txbepay_file, 'ebill')) ? 'BillPay' : 'RegPay',
		);
		
		return $payment;
	}//end init_template

	public function process() {		
		if(!$this->txbepay_load_status) {
			PSU::db('banner')->StartTrans();

			if($this->status_flag == 'success') {
				if($this->txbepay_trans_type == 1) {
					$this->detail_code = 'IQEW';
					$this->detail_desc = 'Credit-Card-Payment-Thank You';
					$this->multiplier = 1;
				}//end if
				elseif($this->txbepay_trans_type == 2) {
					$this->detail_code = 'IREW';
					$this->detail_desc = 'Credit-Card-Refund';
					$this->multiplier = -1;						
				}//end else
				elseif($this->txbepay_trans_type == 3) {
					$this->detail_code = 'IQEC';
					$this->detail_desc = 'E-Check-Payment-Thank You';
					$this->multiplier = 1;						
				}//end else

				$this->transaction = new \PSU\AR\Transaction\Receivable( $this->txbepay_order_number, ( $this->txbepay_trans_total / 100 ), $this->multiplier );
				$this->bursar_term = $this->transaction->term_code;

				$receivable_template = $this->init_template();

				$this->transaction->split( $receivable_template );

				if( PSU::has_filter('etrans_post_split') ) {
					PSU::apply_filters( 'etrans_post_split', $this );
				}//end if

				$this->transaction->save();

				if( PSU::has_filter('etrans_post_save') ) {
					PSU::apply_filters( 'etrans_post_save', $this );
				}//end if
				
				$this->txbepay_load_status = 'loaded';
				$this->txbepay_load_date = date('Y-m-d');
				$this->save();
				
				$amount = (!PSU::db('banner')->HasFailedTrans()) ? ($this->txbepay_trans_total/100) : false;
			} else {
				$this->txbepay_load_status = 'no_load';
				$this->txbepay_load_date = date('Y-m-d');
				$this->save();
				
				$amount = 0;
			}//end else

			PSU::db('banner')->CompleteTrans();
			return $amount;
		}//end if

		return false;
	}//end process

	public function __construct($params = false)
	{
		parent::__construct($params);
		
		$this->user = ($_SESSION['username']) ? 'NELNET_'.$_SESSION['username'] : 'NELNET_ECOMMERCESCRIPT';
		
		$this->bursar_term = PSU::db('banner')->GetOne("SELECT value FROM psu.gxbparm WHERE param = 'ug_default_term'");

		$this->entry_date = $this->activity_date = date('Y-m-d H:i:s');
		$this->trans_date = $this->effective_date = date('Y-m-d');
	}//end __construct
}//end class ETransLegacyAR
