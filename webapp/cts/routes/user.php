<?php

respond ('/', function( $request, $response, $app){
	$app->tpl->assign('step', $_SESSION['cts']['step']);
	$app->tpl->assign( 'announcements',ReserveDatabaseAPI::get_current_announcements());
	$app->tpl->display( 'user-home.tpl' );

});

respond ('/delete', function( $request, $response, $app){
	unset($_SESSION['cts']);
	$response->redirect($GLOBALS['BASE_URL'] . '/user/'); 
});

respond ('/help', function( $request, $response, $app ){
	$app->tpl->display( 'help.tpl' );

});
