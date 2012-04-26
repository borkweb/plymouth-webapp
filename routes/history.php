<?php
respond('/', function( $request, $response, $app){
	$reservations=ReserveDatabaseAPI::by_wp_id($_SESSION['wp_id']);
	$app->tpl->assign( 'locations' , ReserveDatabaseAPI::locations());
	$app->tpl->assign('reservations', $reservations);
	$app->tpl->display('history.tpl');
});//end /

respond('/pending', function( $request, $response, $app){
	$reservations=ReserveDatabaseAPI::by_wp_id_pending($_SESSION['wp_id']);
	$app->tpl->assign( 'locations' , ReserveDatabaseAPI::locations());

	$app->tpl->assign('reservations', $reservations);
	$app->tpl->display('history-pending.tpl');
});//end pending

respond('/search/id/[i:id]' , function( $request, $response, $app){
	$reservation_idx=(int)$request->id;
	$app->tpl->assign( 'locations' , ReserveDatabaseAPI::locations());
	$app->tpl->assign( 'reservation_idx', $reservation_idx);
	$app->tpl->assign( 'reservation' , ReserveDatabaseAPI::by_id($reservation_idx));
	$app->tpl->display( 'history-reservation.tpl' );

});//end reservation/search/


respond('/copy/[i:id]' , function( $request, $response, $app){
	$reservation_idx=(int)$request->id;
	$app->tpl->assign( 'locations' , ReserveDatabaseAPI::locations());
	$app->tpl->assign( 'reservation_idx', $reservation_idx);
	$reserve=ReserveDatabaseAPI::by_id($reservation_idx);
	unset($_SESSION['cts']);
	$_SESSION['cts']['first_name']=$reserve[$reservation_idx]['fname'];
	$_SESSION['cts']['last_name']=$reserve[$reservation_idx]['lname'];
	$_SESSION['cts']['phone']=$reserve[$reservation_idx]['phone'];
	$_SESSION['cts']['email']=$reserve[$reservation_idx]['email'];
	$_SESSION['cts']['title']=$reserve[$reservation_idx]['title'];
	$_SESSION['cts']['start_date']=date('m/d/Y', strtotime($reserve[$reservation_idx]['start_date']));
	$_SESSION['cts']['end_date']=date('m/d/Y', strtotime($reserve[$reservation_idx]['end_date']));

	$_SESSION['cts']['location']=$reserve[$reservation_idx]['building_idx'];
	$_SESSION['cts']['comments']=$reserve[$reservation_idx]['memo'];
	$_SESSION['cts']['room']=$reserve[$reservation_idx]['room'];

	$_SESSION['cts']['starthour']=date("g",strtotime($reserve[$reservation_idx]['start_time']));
	$_SESSION['cts']['startminute']=date("i",strtotime($reserve[$reservation_idx]['start_time']));
	$_SESSION['cts']['startampm']=date("A",strtotime($reserve[$reservation_idx]['start_time']));
		
	$_SESSION['cts']['endhour']=date("g",strtotime($reserve[$reservation_idx]['end_time']));
	$_SESSION['cts']['endminute']=date("i",strtotime($reserve[$reservation_idx]['end_time']));
	$_SESSION['cts']['endampm']=date("A",strtotime($reserve[$reservation_idx]['end_time']));

	$app->tpl->assign('reserve',$_SESSION['cts']);

	$app->tpl->assign('copy', true);
	$app->tpl->display( 'event.tpl' );

});//end reservation/search/
