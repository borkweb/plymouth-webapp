<?php

require_once 'autoload.php';
trigger_error( 'PSUStudent is deprecated, please use \PSU\Student', E_USER_DEPRECATED );
class_alias( '\PSU\Student', 'PSUStudent' );
