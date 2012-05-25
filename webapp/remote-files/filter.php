<?php

$path = $_GET['path'];
$filter = $_GET['filter'];

PSUHTML::redirect($GLOBALS['BASE_URL'] . '/' . $GLOBALS['SSH_HOST'] . ':browse' . $path . $filter);
