<?php

namespace PSU\AR\PaymentPlan\Feed;

class Disbursements extends Collection {
	public static $child = '\PSU\AR\PaymentPlan\Feed\Disbursement';
	public $type = 'disbursement';
}//end class
