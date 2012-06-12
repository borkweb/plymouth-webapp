<?php

/*
 * Simple script to set editing back to true and return to the main page.
 */

AEStudent::removeConfirmation($_SESSION['pidm'], $GLOBALS['TERM']);

$_SESSION['editing'] = true;
header('Location: ' . $GLOBALS['BASE_URL']);
