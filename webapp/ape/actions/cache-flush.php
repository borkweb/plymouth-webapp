<?php

/**
 * Flush the IDM cache. Includes current user's permissions and any
 * cached database records, such as the attribute hierarchy.
 */

unset($_SESSION['AUTHZ']);
$GLOBALS['BANNER']->CacheFlush();

header('Location: ' . $_SERVER['HTTP_REFERER']);
