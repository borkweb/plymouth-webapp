<?php

require_once 'BannerObject.class.php';

interface PSUPerson_Loader_Interface
{
	public function loader_preflight( $identifier );
}//end PSUPerson_Loader_Interface

class PSUPerson_Loader extends BannerObject
{
	public function destroy() {
		unset($this->person);
		parent::destroy();
	}
}
