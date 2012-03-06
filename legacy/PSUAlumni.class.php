<?php

require_once 'autoload.php';
trigger_error( 'PSUAlumni is deprecated, please use \PSU\Alumni', E_USER_DEPRECATED );
class_alias( '\PSU\Alumni', 'PSUAlumni' );
