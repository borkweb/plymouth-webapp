<?php

require 'PSUController.class.php';

class MTEController extends PSUController {
	public function apply() {
		$f = isset($_SESSION['mtecd']['f']) ? $_SESSION['mtecd']['f'] : array();
		$form = new MTEApply($f);

		$this->tpl->assign('form', $form);
		$this->display();
	}

	public static function delegate( $path = null, $class = __CLASS__ ) {
		parent::delegate( $path, $class );
	}//end delegate

	public function index() {
		$this->display();
	}

	public function thank_you() {
		$f = $_SESSION['mtecd']['f'];
		unset($_SESSION['mtecd']['f']);

		if( ! $f ) {
			PSU::redirect( $GLOBALS['BASE_URL'] . '/' );
		}

		$form = new MTEApply($f);
		$form->readonly(true);

		$this->tpl->assign('form', $form);

		$this->display();
	}

	public function _submit() {
		$f = $_POST;
		$app = new MTEApply($f);

		list( $required, $filled, $percent ) = $app->progress();

		$_SESSION['mtecd']['f'] = $app->form();

		if( $percent < 1 ) {
			$_SESSION['errors'][] = 'Please fill in all required fields.';
			PSU::redirect( $GLOBALS['BASE_URL'] . '/apply' );
		}

		if( $id = $app->save() ) {
			MTEAPI::mail( $id );
		} else {
			$_SESSION['errors'][] = 'There was an error saving your submission data.';
			PSU::redirect( $GLOBALS['BASE_URL'] . '/apply' );
		}

		PSU::redirect( $GLOBALS['BASE_URL'] . '/thank-you' );
	}
}//end MTEConroller
