<?php
PSU::get()->banner = PSU::db('psc1');

respond('/?', function( $request, $response, $app ) {
	$ecommerce_running = shell_exec('ps ef | grep ecommerce_process.php | grep -v grep');

	if( $ecommerce_running ) {
		$_SESSION['messages'][] = 'The ECommerce processing script is currently running. Reload to check the status.';
		$app->tpl->assign('ecommerce_processing', true);
	}//end if
	
	$app->tpl->assign('ecommerce_pending_files', \PSU\Ecommerce::pending_files() );
	$app->tpl->assign('ecommerce_pending', \PSU\Ecommerce::pending() );
	$app->tpl->assign('ecommerce_files', \PSU\Ecommerce::file_info() );
	$app->tpl->assign('ecommerce_report', \PSU\Ecommerce::report() );
	$app->tpl->display('ecommerce.tpl');
});

respond('/process', function( $request, $response, $app ) {
	$user = PSU::isDev() ? 'nrporter' : 'webguru';

	if( PSU::isDev() && ! IDMObject::authN('mis') ) {
		return;
	}//end if

	$command ='~'.$user.'/scripts/ecommerce_process.php --instance='.strtolower( PSU::db('banner')->database ) . ' &';
	exec( $command );
	
	$_SESSION['successes'][] = 'The ECommerce processing script has begun.  Please check back shortly.';

	PSU::redirect( $GLOBALS['BASE_URL'] );
});
