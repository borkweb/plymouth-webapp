<?php

class AnejfAPI {
	public static function mail( $id ) {
		$tpl = new PSU\Template;

		$f = PSU::db('myplymouth')->GetRow("SELECT * FROM anejf WHERE id_ = ?", array($id));
		$form = new AnejfApply($f, true);
		$form->readonly(true);

		$tpl->assign('form', $form);
		$message = $tpl->fetch( 'email.tpl' );

		$to = 'mastickney@plymouth.edu';

		if( PSU::isdev() ) {
			$to = 'adam@sixohthree.com';
		}

		$subject = sprintf("[ANEJF %d] Application for %s %s", $GLOBALS['ANEJF']['YEAR'], $form->first_name->value(), $form->last_name->value());

		PSU::mail( $to, $subject, $message, 'Content-Type: text/html' );
	}//end mail
}//end class AnejfAPI
