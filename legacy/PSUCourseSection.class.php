<?php

require_once 'autoload.php';
trigger_error( 'PSUCourseSection is deprecated, please use \PSU\Course\Section', E_USER_DEPRECATED );
class_alias( '\PSU\Course\Section', 'PSUCourseSection' );
