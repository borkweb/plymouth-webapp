<?php

require_once 'PSUController.class.php';
require_once 'PSUTemplate.class.php';

class CDNController extends PSUController {
	public static function delegate( $path = null, $class = __CLASS__ ) {
		parent::delegate( $path, $class );
	}

	public function index() {
		$this->display();
	}

	public function path() {
		$args = func_get_args();
		$path = '/' . implode('/', $args) . '/';

		$this->tpl->assign('path', $path);

		$files = CDNAPI::files( $path );
		$this->tpl->assign('files', $files);

		$this->display();
	}

	public function update() {
		$result = CDNAPI::update( $_POST['cdnfiles'], $_SESSION['wp_id'] );
		PSU::redirect( $GLOBALS['BASE_URL'] . '/path' . $_POST['from'] );
	}
}//end CDNController
