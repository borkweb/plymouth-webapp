<?php

namespace PSU\AR\PaymentPlan\Feed;

class Disbursement extends \PSU\AR\PaymentPlan\Feed {
	public static $record_collection = '\PSU\AR\PaymentPlan\Disbursements';
	public static $processed_collection = '\PSU\AR\PaymentPlan\Disbursement\Processed';
}//end class
