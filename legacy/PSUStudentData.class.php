<?php

require_once 'autoload.php';
trigger_error('PSUStudentData is deprecated, please use \PSU\Student\Data', E_USER_DEPRECATED);
class_alias('\PSU\Student\Data', 'PSUStudentData');
