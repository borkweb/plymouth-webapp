<?php

$from = $_GET['from'];

$_SESSION['javascript'] = ! $_SESSION['javascript'];

PSUHTML::redirect($from);
