<?php

require_once 'autoload.php';
trigger_error( 'PSUCourse is deprecated, please use \PSU\Course', E_USER_DEPRECATED );
class_alias( '\PSU\Course', 'PSUCourse' );
