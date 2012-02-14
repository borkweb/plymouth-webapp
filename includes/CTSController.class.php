<?php

require_once 'PSUController.class.php';

class CTSController extends PSUController {
	// redefine delegate so parent knows which controller to use as default.
	// placeholder until php 5.3 "static" keyword.
	public static function delegate( $path = null, $controller_class = __CLASS__ ) {
		parent::delegate( $path, $controller_class );
	}
}//end class CTSController
