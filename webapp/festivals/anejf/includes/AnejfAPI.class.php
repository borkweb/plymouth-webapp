<?php

class AnejfAPI {
	public static function mail( $id ) {
		$tpl = new PSUTemplate;

		$f = PSU::db('myplymouth')->GetRow("SELECT * FROM anejf WHERE id_ = ?", array($id));
		$form = new AnejfApply($f, true);
		$form->readonly(true);

		$tpl->assign('form', $form);
		$message = $tpl->fetch( 'email.tpl' );

		$to = array();

		// new app, putting myself in the receive list for starters
		$to[] = 'mastickney@plymouth.edu';

		$subject = sprintf("[ANEJF 2012] Application for %s %s", $form->first_name->value(), $form->last_name->value());

		PSU::mail( $to, $subject, $message, 'Content-Type: text/html' );
	}//end mail
}//end class AnejfAPI
