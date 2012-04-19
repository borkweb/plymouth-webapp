<?php

class MTEAPI {
	public static function mail( $id ) {
		$tpl = new PSUTemplate;

		$f = PSU::db('myplymouth')->GetRow("SELECT * FROM mte_career_day WHERE id_ = ?", array($id));
		$form = new MTEApply($f, true);
		$form->readonly(true);

		$tpl->assign('form', $form);
		$message = $tpl->fetch( 'email.tpl' );

		$to = array('rikp@plymouth.edu');

		// new app, putting myself in the receive list for starters
		if( PSU::isdev() ) {
			$to[] = 'ambackstrom@plymouth.edu';
		}

		$subject = sprintf("[MTECD 2011] Application for %s %s", $form->first_name->value(), $form->last_name->value());

		PSU::mail( $to, $subject, $message, 'Content-Type: text/html' );
	}//end mail
}//end class MTEAPI
