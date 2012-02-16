<?php

respond( '/?[*]', function() {
	echo 'festivals';
});

with( '/anejf', __DIR__ . '/festivals/anejf.php' );
