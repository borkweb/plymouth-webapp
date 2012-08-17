<?php
PSU::get()->banner = PSU::db('psc1');

respond('/?', function( $request, $response, $app ) {
	$app->tpl->display('schedule.tpl');
});
