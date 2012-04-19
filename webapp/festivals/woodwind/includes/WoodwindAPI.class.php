<?php

class WoodwindAPI {
	public static function mail( $id ) {
		$tpl = new PSUTemplate;

		$f = PSU::db('myplymouth')->GetRow("SELECT * FROM woodwindday WHERE id_ = ?", array($id));
		$form = new WoodwindApply($f, true);
		$form->readonly(true);

		$tpl->assign('form', $form);
		$message = $tpl->fetch( 'email.tpl' );

		$to = array('rikp@plymouth.edu');

		$subject = sprintf("[PSU Woodwind Day 2011] Registration for %s %s", $form->first_name->value(), $form->last_name->value());

		PSU::mail( $to, $subject, $message, 'Content-Type: text/html' );
	}//end mail
}//end class WoodwindAPI
