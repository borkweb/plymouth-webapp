<?php

require_once 'autoload.php';
trigger_error( 'PSUTemplate is deprecated, please use \PSU\Template', E_USER_DEPRECATED );
class_alias( '\PSU\Template', 'PSUTemplate' );
