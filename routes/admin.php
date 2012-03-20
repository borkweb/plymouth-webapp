<?php
//this page will deal with all of the routing for the admin pages
//
//admin/equipment
//admin/reservation
require_once $GLOBALS['BASE_DIR'] . '/includes/CTSdatabaseAPI.class.php';

respond('/admin/equipment', function( $request, $response, $app) {
	$app->tpl->assign( 'manufacturers', CTSdatabaseAPI::manufacturers() );
	
	PSU::dbug(CTSdatabaseAPI::manufacturer());
	$app->tpl->assign( 'types', CTSdatabaseAPI::types() );
	$app->tpl->assign( 'models', array_keys(CTSdatabaseAPI::models()) );
	$app->tpl->display('admincps.tpl');

});
