<?php

require 'PSUController.class.php';

class WoodwindController extends PSUController {
	public function register() {
		$f = isset($_SESSION['woodwind-day']['f']) ? $_SESSION['woodwind-day']['f'] : array();
		$form = new WoodwindApply($f);

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
		$f = $_SESSION['woodwind-day']['f'];
		unset($_SESSION['woodwind-day']['f']);

		if( ! $f ) {
			PSU::redirect( $GLOBALS['BASE_URL'] . '/' );
		}

		$form = new WoodwindApply($f);
		$form->readonly(true);

		$this->tpl->assign('form', $form);

		$this->display();
	}

	public function _submit() {
		$f = $_POST;
		$app = new WoodwindApply($f);

		list( $required, $filled, $percent ) = $app->progress();

		$_SESSION['woodwind-day']['f'] = $app->form();

		if( $percent < 1 ) {
			$_SESSION['errors'][] = 'Please fill in all required fields.';
			PSU::redirect( $GLOBALS['BASE_URL'] . '/register' );
		}

		if( $id = $app->save() ) {
			WoodwindAPI::mail( $id );
		} else {
			$_SESSION['errors'][] = 'There was an error saving your submission data.';
			PSU::redirect( $GLOBALS['BASE_URL'] . '/register' );
		}

		PSU::redirect( $GLOBALS['BASE_URL'] . '/thank-you' );
	}
}//end WoodwindConroller
