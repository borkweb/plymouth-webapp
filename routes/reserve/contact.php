<?php
$curr_page='/reserve/contact';
respond( 'POST',function( $request, $respond, $app){
	//required parameters
	$first_name=$request->param('first_name');
	$last_name=$request->param('last_name');
	$phone=$request->param('phone');

	if( ! $first_name ){ //if there is no first name
		$_SESSION['errors'][]='First name not found'; //throw error
		$response->redirect( $GLOBALS['BASE_URL'] . $curr_page ); //redirect them back to the same page
	}

	if( ! $last_name ){ //if there is no first name
		$_SESSION['errors'][]='Last name not found'; //throw error
		$response->redirect( $GLOBALS['BASE_URL'] . $curr_page ); //redirect them back to the same page
	}

	if( ! $phone ){ //if there is no first name
		$_SESSION['errors'][]='Phone number not found'; //throw error
		$response->redirect( $GLOBALS['BASE_URL'] . $curr_page ); //redirect them back to the same page
	}
});
