#!/usr/local/bin/php
<?php
require dirname( __DIR__ ) . '/legacy/git-bootstrap.php';
require_once 'autoload.php';
unset($argv[0]);

foreach ($argv as $a) {
	$a = substr($a,2);
	$items=explode('=',$a);
	if (strlen($items[1]) == 0) {
		$items[1] = null;
	}
	$params[$items[0]] = $items[1];
}
PSU\Runner\AddressVerification::Batch_Verify_SPRADDR($params);
