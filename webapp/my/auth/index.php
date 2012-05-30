<?php

require_once 'IDMObject.class.php';
require_once 'PSUTools.class.php';

IDMObject::authN('host=connect');

if( isset($_GET['goto']) ) {
	PSU::redirect( $_GET['goto'] );
} else {
	PSU::redirect("https://www.plymouth.edu/webapp/my/");
}
