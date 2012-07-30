#!/usr/local/bin/php
<?php

require 'autoload.php';
require dirname( __DIR__ ) . '/webapp/calllog/includes/functions.php';
require dirname( __DIR__ ) . '/webapp/calllog/includes/functions/my_options_functions.php';
require dirname( __DIR__ ) . '/webapp/calllog/includes/functions/user.class.php';
require dirname( __DIR__ ) . '/webapp/calllog/includes/functions/open_call_functions.php';

//PSU::db('calllog')->debug=true;

$options =  array( 
				'which' => 'my', 
				'who' => 'zbtirrell',
				);

$open_calls = getOpenCalls( $options );

PSU::puke($open_calls);

