<?php

require 'PSUController.class.php';

class AnejfController extends PSUController {
	public function apply() {
		$f = isset($_SESSION['anejf']['f']) ? $_SESSION['anejf']['f'] : array();
		$form = new AnejfApply($f);

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
		$f = $_SESSION['anejf']['f'];
		unset($_SESSION['anejf']['f']);

		if( ! $f ) {
			PSU::redirect( $GLOBALS['BASE_URL'] . '/' );
		}

		$form = new AnejfApply($f);
		$form->readonly(true);

		$this->tpl->assign('form', $form);

		$this->display();
	}

	public function _submit() {
		$f = $_POST;
		$app = new AnejfApply($f);

		list( $required, $filled, $percent ) = $app->progress();

		$_SESSION['anejf']['f'] = $app->form();

		if( $percent < 1 ) {
			$_SESSION['errors'][] = 'Please fill in all required fields.';
			PSU::redirect( $GLOBALS['BASE_URL'] . '/apply' );
		}

		if( $id = $app->save() ) {
			AnejfAPI::mail( $id );
		} else {
			$_SESSION['errors'][] = 'There was an error saving your submission data.';
			PSU::redirect( $GLOBALS['BASE_URL'] . '/apply' );
		}

		PSU::redirect( $GLOBALS['BASE_URL'] . '/thank-you' );
	}
}//end AnejfConroller
