<?php
class CAController extends PSUController {
	public function duplicates() {
		$this->display();
	}//end common_matching

	public static function delegate( $path = null, $controller_class = __CLASS__ ) {
		parent::delegate( $path, $controller_class );
	}
}//end class CAController
