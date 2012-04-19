<?php
//this file will contain all of the routes for the history pages
//
//history/past
//history/pending
//
require_once $GLOBALS['BASE_DIR'] . '/includes/reserveDatabaseAPI.class.php';


respond('/', function( $request, $response, $app){
	$reservations=reserveDatabaseAPI::by_wp_id($_SESSION['wp_id']);
	$app->tpl->assign( 'locations' , reserveDatabaseAPI::locations());
	$app->tpl->assign('reservations', $reservations);
$app->tpl->display('history.tpl');
});//end /

respond('/pending', function( $request, $response, $app){
	$reservations=reserveDatabaseAPI::by_wp_id_pending($_SESSION['wp_id']);
	$app->tpl->assign( 'locations' , reserveDatabaseAPI::locations());

	$app->tpl->assign('reservations', $reservations);
$app->tpl->display('historypending.tpl');
});//end pending

respond('/search/id/[i:id]' , function( $request, $response, $app){
	$reservation_idx=$request->id;
	$app->tpl->assign( 'locations' , reserveDatabaseAPI::locations());
	$app->tpl->assign( 'reservation_idx', $reservation_idx);
	$app->tpl->assign( 'reservation' , reserveDatabaseAPI::by_id($reservation_idx));
	$app->tpl->display( 'historyreservation.tpl' );

});//end reservation/search/


respond('/copy/[i:id]' , function( $request, $response, $app){
	$reservation_idx=$request->id;
	$app->tpl->assign( 'locations' , reserveDatabaseAPI::locations());
	$app->tpl->assign( 'reservation_idx', $reservation_idx);
	$reserve=reserveDatabaseAPI::by_id($reservation_idx);
	unset($_SESSION['cts']);
	$_SESSION['cts']['first_name']=$reserve[$reservation_idx]['fname'];
	$_SESSION['cts']['last_name']=$reserve[$reservation_idx]['lname'];
	$_SESSION['cts']['phone']=$reserve[$reservation_idx]['phone'];
	$_SESSION['cts']['email']=$reserve[$reservation_idx]['email'];
	$_SESSION['cts']['title']=$reserve[$reservation_idx]['title'];
	$_SESSION['cts']['start_date']=date('m/d/Y', strtotime($reserve[$reservation_idx]['start_date']));
	$_SESSION['cts']['end_date']=date('m/d/Y', strtotime($reserve[$reservation_idx]['end_date']));

	$_SESSION['cts']['location']=$reserve[$reservation_idx]['building_idx'];
	$_SESSION['cts']['comment']=$reserve[$reservation_idx]['memo'];
	$_SESSION['cts']['room']=$reserve[$reservation_idx]['room'];

	$_SESSION['cts']['starthour']=date("g",strtotime($reserve[$reservation_idx]['start_time']));
	$_SESSION['cts']['startminute']=date("i",strtotime($reserve[$reservation_idx]['start_time']));
	$_SESSION['cts']['startampm']=date("A",strtotime($reserve[$reservation_idx]['start_time']));
		
	$_SESSION['cts']['endhour']=date("g",strtotime($reserve[$reservation_idx]['end_time']));
	$_SESSION['cts']['endminute']=date("i",strtotime($reserve[$reservation_idx]['end_time']));
	$_SESSION['cts']['endampm']=date("A",strtotime($reserve[$reservation_idx]['end_time']));
	$app->tpl->assign( 'hours' , array(1=>1,2=>2,3=>3,4=>4,5=>5,6=>6,7=>7,8=>8,9=>9,10=>10,11=>11,12=>12));
	$app->tpl->assign( 'minutes', array(00=>0,05=>5,10=>10,15=>15,20=>20,25=>25,30=>30,35=>35,40=>40,45=>45,50=>50,55=>55));
	$app->tpl->assign( 'ampm' , array("AM"=>"AM","PM"=>"PM"));

	$app->tpl->assign('reserve',$_SESSION['cts']);

	$app->tpl->assign('step', '1');
	$app->tpl->display( 'event.tpl' );

});//end reservation/search/
