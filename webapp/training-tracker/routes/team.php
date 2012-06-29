<?php

//teams creation page
respond( function( $request, $response, $app ) {

});
respond( 'GET', '/builder', function( $request, $response, $app ) {
	if (!$app->is_admin){
		die('You do not have access to this page.');
	}

	$wpid = $app->user->wpid;

	$staff_collection = new TrainingTracker\StaffCollection();
	$staff_collection->load();

	//getting all the mentors and mentees at the help desk.
	$mentors = $staff_collection->mentors();
	$mentees = $staff_collection->mentees();

	foreach ($mentees as $mentee){
	 	$teams = $mentee->team();
	}
 
	$teams = json_encode($teams);
 // die;
// TODO: no you.

	$app->tpl->assign('teams', $teams);
	$app->tpl->assign('mentees', $mentees);
	$app->tpl->assign('mentors', $mentors);
	$app->tpl->display('teams.tpl'); //go go gadget show page
});

respond( 'GET', '/list', function( $request, $responce, $app ) {

	$teams_array = TrainingTracker::get_teams();
	$app->tpl->assign('teams_array', $teams_array);
	$app->tpl->display('viewteams.tpl');
 
});

//the axax post part for teams
respond( 'POST', '/builder', function( $request, $responce, $app ) {

	//get the data to variables from the posted data
	$mentee_wpid = $_POST['data'][0];
	$mentor_wpid = $_POST['data'][1];

	if(TrainingTracker::valid_wpid($mentee_wpid)){
		if(TrainingTracker::valid_wpid($mentor_wpid)){
			$result = TrainingTracker::get_teams($mentee_wpid, 'mentee');

			if (isset($result[0])){
				TrainingTracker::team_set_mentee($mentee_wpid, $mentor_wpid);
				// if team exists with that mentee replace mentor	
			}
			else{
				TrainingTracker::team_insert($mentee_wpid, $mentor_wpid);
				//if the mentee isn't in a team make them a team
			}
			
		}
		else if ($mentor_wpid == 'unassigned'){
			//if you move the mentee back to the mentee category in the team builder, it removes their database entry.
			TrainingTracker::team_delete($mentee_wpid);
		}
	}
});

//view teams
respond( 'GET', '/list/[:wpid]', function( $request, $responce, $app ) {
	
	if (!$app->is_mentor){
		$responce->redirect('../list');
	}

	$wpid = $request->wpid;

	if(!TrainingTracker::valid_wpid($wpid)){
		$responce->redirect('../list');
	}

	$teams = TrainingTracker::get_teams(); 
	if (isset($teams["$wpid"])){	
		$my_team = $teams["$wpid"];
		unset ($my_team['mentor']);
		foreach ($my_team as &$member){
			if (isset($member['name'])){
				$current_user_parameter['wpid'] = $member['wpid'];
				$current_user = new TrainingTracker\Staff($current_user_parameter);
				$member = $current_user;
			}
		}

	}
	else{
		$my_team['mentor']['name'] = 'Loner';
		$my_team['mentor']['wpid'] = 'F4ilure';
		$my_team['loner']['name'] = 'You have no team =(';
		$my_team['loner']['wpid'] = 'loner.jpg';
	}
	$app->tpl->assign('teams', $my_team);
	$app->tpl->display('myteam.tpl');
});
