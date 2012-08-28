<?php
//the axax post part for checklist check boxes 
respond('POST', '/checklist/checkbox', function( $request, $responce, $app ) {
	$checked_id = $request->data['checkboxId'];
	if (preg_match('/^\\d{1,3}$/', $checked_id)){
		$wpid = $request->data['wpid'];
		if (TrainingTracker::valid_wpid($wpid)){
			$response = $request->data['response'];

			$person = PSUPerson::get($wpid);
			$pidm=$person->pidm;
			$modified_by = $_SESSION['pidm'];
			
			$checklist_id = TrainingTracker::get_checklist_id($pidm);
			
			if (!TrainingTracker::checkbox_exists($checked_id, $checklist_id)){

				TrainingTracker::checkbox_insert($checked_id, $checklist_id, $response, $_SESSION['pidm']);
			}
			else{
				TrainingTracker::checkbox_update($response, $modified_by, $checked_id, $checklist_id);
			}
		}
	}
});

//the post part for checklist comments
respond('POST', '/checklist/comments/[:wpid]', function( $request, $responce, $app ) {

	  // TODO: Y U NO REMOVE?
	 // TODO: Y U NO RAGE BETTAR?
	$wpid = $request->wpid;
	if (TrainingTracker::valid_wpid($wpid)){
		if ($_POST['name'] == 'save'){
			$comments = $_POST['comments'];
			$wpid = $request->wpid;
			if (TrainingTracker::valid_wpid($wpid)){
				$pidm = PSUPerson::get($wpid)->pidm;
				$modified_by = $app->user->pidm;

				$comments = htmlentities($comments);
				$comments = stripslashes($comments);
				$comments = trim($comments);
				
				$checklist_id = TrainingTracker::get_checklist_id($pidm);

				 // checking to see if the person already exists in the database
				if (TrainingTracker::comment_exists($checklist_id)){
					Trainingtracker::comment_update($comments, $checklist_id);
					//  if person has a db entry already	
				}
				else{
					TrainingTracker::comment_insert($checklist_id, $comments, $modified_by);
					//if they don't, make them one
				}
			}
		}
		else if ($_POST['name'] == 'confirm'){

			$current_user_level = TrainingTracker::get_user_level($wpid);

			$people['active'] = $app->user;

			$person = PSUPerson::get($wpid);
			$people['current'] = $person;

			$level = TrainingTracker::level_translation($current_user_level);
			$pay = TrainingTracker::pay_translation($current_user_level);

			TrainingTracker::mail($pay, $level, $people);

		}
	}
	$responce->redirect('/webapp/training-tracker/');
});


//promote/demote ajax page
respond('POST', '/fate', function( $request, $response, $app ) {

	$permission = $request->data['permission'];
	$wpid = $request->data['wpid'];

	if ($permission == 'supervisor' || $permission == 'shift_leader' || $permission == 'sta' || $permission == 'trainee'){
		if(TrainingTracker::valid_wpid($wpid)){

			$pidm = PSUPerson::get($wpid)->pidm;
			$type = TrainingTracker::checklist_type($permission);

			TrainingTracker::set_user_level($wpid, $permission);
			if (!TrainingTracker::checklist_exists($pidm, $type, 1)){
				TrainingTracker::checklist_close($pidm);
				TrainingTracker::checklist_insert($pidm, $type);
			}
			else{
				TrainingTracker::checklist_close($pidm);
				TrainingTracker::checklist_open($pidm, $type);
			}
		}
	}
});

//admin page
respond( 'GET', '/fate', function( $request, $response, $app ) {

	if (!$app->is_admin){
		die('You do not have access to this page.');
	}

	$staff_collection = new TrainingTracker\StaffCollection();
	$staff_collection->load(); 

	$staff = $staff_collection->promotion_users();
	foreach ($staff as $person){
		$permission	= TrainingTracker::get_user_level($person->wpid);
		$person->permission_slug = $permission;
		$person->permission = TrainingTracker::level_translation($permission);
	}
	$app->tpl->assign('staff', $staff);
	$app->tpl->display('admin.tpl');
});


//statistics/checklist page
respond( 'GET', '/statistics/[:wpid]', function( $request, $responce, $app ) {

	$wpid = $request->wpid;
	
	if(!TrainingTracker::valid_wpid($wpid)){
		$responce->redirect('../../');
	}

	$current_user_parameter['wpid'] = $wpid;
	$current_user = new TrainingTracker\Staff($current_user_parameter);
	$current_user_level = TrainingTracker::get_user_level($current_user->wpid);

	$active_user_level = TrainingTracker::get_user_level($app->user->wpid);
	$checklist_id = TrainingTracker::get_checklist_id($current_user->person()->pidm);

	if (strlen($checklist_id) > 2){
		 //get the data for which check boxes are checked
		$checklist_checked = TrainingTracker::checklist_checked($checklist_id);

		 // TODO: Y U NO USE FOREACH( $something as &$item ) by reference?
		// yes
		$tooltip = array();
		foreach ($checklist_checked as &$checked){
			$item_id = $checked['item_id'];
			$tooltip[$item_id]['item_id'] = $item_id;
			$tooltip[$item_id]['updated_by'] = PSUPerson::get($checked['updated_by'])->formatname('f l');
			$tooltip[$item_id]['updated_time'] = $checked["activity_date"];
			$checked = $checked['item_id'];
		}

		$last_modified_info = TrainingTracker::last_modified($checklist_id);
		$last_modified = $last_modified_info['time'];
		$modified_by = $last_modified_info['modified_by'];
	}

	//the title is the title name in the box.
	if (strlen($modified_by)>2){
		$title = $current_user->person()->formatName('f l') . ' - Last modified by ' . PSUPerson::get($modified_by)->formatname('f l') . ' on ' . $last_modified . '.';
	}
	else{
		$title = $current_user->person()->formatName('f l'); 
	}

	//getting comments
	$comments = TrainingTracker::get_comments($checklist_id);

	$staff_collection = new TrainingTracker\StaffCollection(); //get the people that work at the helpdesk
	$staff_collection->load(); 
	$mentor = $staff_collection->mentors();//select all the mentors
	$mentee = $staff_collection->mentees();//select all the mentees

	//populating some variables to generate the checklist.
	
	$checklist_builder = TrainingTracker::checklist_builder($current_user_level, $current_user->wpid);

	$checklist_items = $checklist_builder['items']; 
	$checklist_item_sub_cat = $checklist_builder['sub_cat']; 
	$checklist_item_cat = $checklist_builder['category']; 

	//adding the tooltip data to the checklist_items
	foreach ($checklist_items as &$checklist_item){
		$item_id = $checklist_item['id'];
		$checklist_item['updated_by'] = $tooltip[$item_id]['updated_by'];
		$checklist_item['updated_time'] = $tooltip[$item_id]['updated_time'];
	}

	$stats = $current_user->stats();
	$progress = $current_user->stats('progress');

	foreach ($checklist_item_sub_cat as &$sub_cat){
		$id = $sub_cat['id'];
		$sub_cat['stat'] = $stats[$id];
	}

	if ($current_user->wpid == $app->user->wpid){
		$disabled = true;
	}else{
		$disabled = false;
	}

	$app->tpl->assign('disabled', $disabled);
	$app->tpl->assign('progress', $progress);	
	$app->tpl->assign('title', $title);	
	$app->tpl->assign('checked', $checklist_checked);
	$app->tpl->assign('comments', $comments);	
	$app->tpl->assign('current_user', $current_user);	
	$app->tpl->assign('current_user_level', $current_user_level);	
	$app->tpl->assign('active_user_level', $active_user_level);	
	$app->tpl->assign('mentee', $mentee);	
	$app->tpl->assign('checklist_items', $checklist_items);
	$app->tpl->assign('checklist_item_sub_cat', $checklist_item_sub_cat);
	$app->tpl->assign('checklist_item_cat', $checklist_item_cat);
	$app->tpl->assign('current_level', $current_level);
	$app->tpl->assign('stats', $stats);
	$app->tpl->display('statistics.tpl');
});


respond( 'POST', '/merit/remove', function( $request, $responce, $app ) {
	
	$id = $request->data['id'];
	TrainingTracker::merit_remove($id);

});

respond( 'POST', '/merit', function( $request, $responce, $app ) {

	$type = $request->data['type'];
	$comments = $request->data['comments'];
	$wpid = $request->data['wpid'];
	if(TrainingTracker::valid_wpid($wpid)){
		$comments = htmlentities($comments);
		$comments = stripslashes($comments);
		$comments = trim($comments);
		if ($type == 'star' || $type == 'dog-house'){
				$checklist_id = TrainingTracker::get_checklist_id($wpid);
				// If they don't have a checklist, most likely a mentor
				if (!$checklist_id){
					$user_type = TrainingTracker::level_translation(TrainingTracker::get_user_level($wpid)); 
					TrainingTracker::checklist_insert($wpid, $user_type);					
					$checklist_id = TrainingTracker::get_checklist_id($wpid);
				}
				if ($type == 'star'){
					$type = 'merit';
				}	
				else{
					$type = 'demerit';
				}
				$updated_by = $app->user->pidm;
				$item_id = 42;
				TrainingTracker::merit_insert($item_id, $checklist_id, $type, $comments, $updated_by);
				$last_insert_id = TrainingTracker::last_insert_id();

				$data = array(
					'id' => $last_insert_id,
				);
				
				// echoed for ajax to pickup and use.
				echo json_encode($data);

		}
	}
});

respond( 'GET', '/merit', function( $request, $responce, $app ) {

	$staff_collection = new TrainingTracker\StaffCollection();
	$staff_collection->load();

	$staff = $staff_collection->merit_users();

	foreach ($staff as $person){
		$merits[$person->wpid]['merits'] = TrainingTracker::merit_get($person->wpid);
		$merits[$person->wpid]['demerits'] = TrainingTracker::demerit_get($person->wpid);
	}

	$app->tpl->assign('merits', $merits);
	$app->tpl->assign('staff', $staff);
	$app->tpl->display('merit.tpl');
});

