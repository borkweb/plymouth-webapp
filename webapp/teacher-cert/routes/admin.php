<?php

use PSU\TeacherCert;

/**
 * namespace-wide initialization
 */
respond( function( $request, $response, $app ) {
	$app->positions = new TeacherCert\Positions;

	if( ! $app->permissions->has('admin') ) {
		$response->denied();
	}

	$app->breadcrumbs->push( 'Administration' );
});

/**
 * display admin page
 */
respond( 'GET', '/?', function( $request, $response, $app ) {
	$app->tpl->display( 'admin/admin.tpl' );
});

respond( '/[:route]/?[*]?', function( $request, $response, $app ) {
	$app->route = $request->param( 'route' );

	$app->request_class = '\PSU\TeacherCert\\';

	$block = array();

	switch ($app->route) {
		case 'checklist-item':
			$app->request_class .= 'ChecklistItem';
			$app->request_title = 'Checklist Item';
			$app->route_single = 'checklist-item';
			$app->target_class = '\PSU\TeacherCert\Gate';
			break;
		case 'constituents':
			$app->collection = new TeacherCert\Constituents;
			$app->collection_title = 'Consituents';
			$app->request_class .= 'Constituent';
			$app->request_title = 'Constituent';
			$app->route_single = 'constituent';
			$app->model = new TeacherCert\Model\Constituent;
			$block['sub_saus'] = TRUE;
			$block['sub_schools'] = TRUE;
			$block['sub_students'] = TRUE;
			break;
		case 'districts':
			$app->collection = new TeacherCert\Districts;
			$app->collection_title = 'Districts';
			$app->request_class .= 'District';
			$app->request_title = 'District';
			$app->route_single = 'district';
			$app->model = new TeacherCert\Model\District;
			$block['schools'] = TRUE;
			break;
		case 'gate-system':
			$app->collection = new TeacherCert\GateSystem;
			$app->collection_title = 'Gate Systems';
			$app->request_class .= 'GateSystem';
			$app->request_title = 'Gate System';
			$app->route_single = 'gate-system';
			break;
		case 'gate-systems':
			$app->collection = new TeacherCert\GateSystems;
			$app->collection_title = 'Gate Systems';
			$app->request_class .= 'GateSystem';
			$app->request_title = 'Gate System';
			$app->route_single = 'gate-system';
			break;
		case 'saus':
			$app->collection = new TeacherCert\SAUs;
			$app->collection_title = 'SAUs';
			$app->request_class .= 'SAU';
			$app->request_title = 'SAU';
			$app->route_single = 'sau';
			$app->model = new TeacherCert\Model\SAU;
			$block['sub_constituents'] = TRUE;
			$block['schools'] = TRUE;
			break;
		case 'schools':
			$app->collection = new TeacherCert\Schools;
			$app->collection_title = 'Schools';
			$app->request_class .= 'School';
			$app->request_title = 'School';
			$app->route_single = 'school';
			$app->model = new TeacherCert\Model\School;
			$block['sub_constituents'] = TRUE;
			break;
		case 'school-approval-levels':
			$app->collection = new TeacherCert\SchoolApprovalLevels;
			$app->collection_title = 'SchoolApprovalLevels';
			$app->request_class .= 'SchoolApprovalLevel';
			$app->request_title = 'School Approval Level';
			$app->route_single = 'school-approval-level';
			$app->model = new TeacherCert\Model\SchoolApprovalLevel;
			$block['schools'] = TRUE;
			break;
		case 'school-types':
			$app->collection = new TeacherCert\SchoolTypes;
			$app->collection_title = 'SchoolTypes';
			$app->request_class .= 'SchoolType';
			$app->request_title = 'School Type';
			$app->route_single = 'school-type';
			$app->model = new TeacherCert\Model\SchoolType;
			$block['schools'] = TRUE;
			break;
		default:
			throw new \UnexpectedValueException("The admin section you are attempting to edit ({$app->route}) is unknown");
			break;
	}

	$app->request_code_friendly = str_replace( '-', '_', $app->route_single );

	$app->breadcrumbs->push( $app->request_title ); 

	$app->tpl->assign( 'model', $app->model );
	$app->tpl->assign( 'block', $block );
	$app->tpl->assign( 'collection', $app->collection );
	$app->tpl->assign( 'collection_title', $app->collection_title );
	$app->tpl->assign( 'request_title', $app->request_title );
	$app->tpl->assign( 'route', $app->route );
	$app->tpl->assign( 'route_single', $app->route_single );
});

/**
 * move a checklist item
 */
respond( 'POST', '/checklist-item/reorder/?', function( $request, $response, $app ) {
	$items = $request->param('items');

	$success = TRUE;

	\PSU::db('banner')->StartTrans();
	foreach( $items as $child => $parent ) {
		$checklist_item = new TeacherCert\ChecklistItem( $child );
		$gate = new TeacherCert\Gate( $parent );
		if( $checklist_item->id && $gate->id ) {
			$checklist_item->gate_id = $gate->id;
			if( ! $checklist_item->save() ) {
				$success = FALSE;
			}//end if
		}//end if
	}//end foreach
	\PSU::db('banner')->CompleteTrans( $success );

	die( $success ? 'success' : 'error');
});

/**
 * display school browser
 */
respond( 'GET', '/[:route]/?', function( $request, $response, $app ) {
	$template = 'admin/collection.' . $app->route . '.tpl';

	if( ! file_exists( $GLOBALS['TEMPLATES'] . '/' . $template ) ) {
		$template = 'admin/collection.tpl';
	}//end if

	$app->tpl->display( $template );
});

/**
 * add sau browser
 */
respond( 'POST', '/[:route]/?', function( $request, $response, $app ) {
	$app->parse_model_results( 
		TeacherCert::save_model( $app->request_title, $_POST, $app->model, $app->request_class ) 
	);

	$response->redirect( $request->uri() );
});

/**
 * catch-all for viewing/editing a district
 */
respond( '/[:route]/[i:id]/[add-constituent|add-sau|add-school|edit|move:action]?/[i:target]?', function( $request, $response, $app ) {
	$app->action = $request->param('action');
	$app->target = $request->param('target');

	$app->id = $id = preg_replace( '/[^a-zA-Z0-9\-_]/', '', $request->param('id') );
	$class = $app->request_class;
	$app->object = new $class( $id );

	if( $app->model && 'edit' !== $app->action ) {
		$app->model->readonly( true );
	}

	$app->tpl->assign('app', $app);
	$app->tpl->assign('object', $app->object);
});

/**
 * view specific sau
 */
respond( 'GET', '/[:route]/[i:id]/[edit:action]?', function( $request, $response, $app ) {
	$rowcache = new TeacherCert\RowCache;
	$rowcache->cache( 'PSU\TeacherCert\Constituent' );
	TeacherCert\CooperatingTeacher::$rowcache = $rowcache;

	$schools = new TeacherCert\Schools;
	$constituents = new TeacherCert\Constituents;
	$constituent_schools = new TeacherCert\CooperatingTeachers;
	$constituent_saus = new TeacherCert\ConstituentSAUs;

	$constituent_schools->sort( function($a, $b){
		return strnatcasecmp( $a->constituent()->last_name, $b->constituent()->last_name );
	});

	$include_schools = 'get_by_'.$app->request_code_friendly;
	$exclude_schools = 'exclude_by_'.$app->request_code_friendly;

	switch ($app->route) {
		case 'constituents':
			$include_constituent_schools = $constituent_schools->get_by_constituent( $app->object->id );
			$app->tpl->assign('include_schools', $include_constituent_schools);

			$include_constituent_saus = $constituent_saus->get_by_constituent( $app->object->id );
			$app->tpl->assign('include_saus', $include_constituent_saus);

			$students = (array) $app->object->students();
			$app->tpl->assign('include_students', $students);

			$app->tpl->assign('exclude_schools', $schools);

			$saus = new TeacherCert\SAUs;
			$app->tpl->assign('exclude_saus', $saus);
			break;
		case 'gate-system':
			break;
		case 'saus':
			$include_constituent_saus = $constituent_saus->get_by_sau( $app->object->id );
			$app->tpl->assign('include_constituents', $include_constituent_saus);

			// name is a bit misleading; these are not excluded constituents, but
			// a list of constituents with the exclusions applied. for now, include all.
			$app->tpl->assign('exclude_constituents', $constituents);
			break;
		case 'schools':
			$include_constituent_schools = $constituent_schools->get_by_school( $app->object->id );
			$app->tpl->assign('include_constituents', $include_constituent_schools);

			// name is a bit misleading; these are not excluded constituents, but
			// a list of constituents with the exclusions applied. for now, include all.
			$app->tpl->assign('exclude_constituents', $constituents);
			break;
	}//end switch

	if( 'constituents' != $app->route ) {
		if( method_exists( $schools, $include_schools ) ) {
			$include_schools = $schools->$include_schools( $app->object->id );
			$app->tpl->assign('include_schools', $include_schools);
		}//end if

		if( method_exists( $schools, $exclude_schools ) ) {
			$exclude_schools = $schools->$exclude_schools( $app->object->id );
			$app->tpl->assign('exclude_schools', $exclude_schools);
		}//end if
	}//end else

	if( $app->model ) {
		$app->model->form( get_object_vars( $app->object ) );
	}//end if

	$app->tpl->assign( array(
		'object' => $app->object,
		'schools' => $schools,
		'edit' => 'edit' == $app->action ? TRUE : FALSE,
	));

	$template = 'admin/section.' . $app->route . '.tpl';

	if( ! file_exists( $GLOBALS['TEMPLATES'] . '/' . $template ) ) {
		$template = 'admin/section.tpl';
	}//end if

	$app->tpl->display( $template );
});

/**
 * re-assign school sau
 */
respond( 'POST', '/[:route]/[i:id]/add-school', function( $request, $response, $app ) {
	$school_id = $request->param('school_id');

	if( $school = School::get( $school_id ) ) {
		$id = $app->request_code_friendly . '_id';
		$school->$id = $app->object->id;

		if( $school->save() ) {
			$_SESSION['successes'][] = 'Added the school "'. $school->name .'" to the '.$app->object->name.' '.$app->request_title.' successfully!';
		} else {
			$_SESSION['errors'][] = 'There was a problem adding the school "'. $school->name .'" to the '.$app->object->name.' '.$app->request_title.'. Please contact ITS.';
		}//end else
	}//end if

	$response->redirect( $GLOBALS['BASE_URL'] . '/admin/'.$app->route.'/' . $app->object->id );
});

/**
 * re-assign school sau
 */
respond( 'POST', '/[:route]/[i:id]/add-constituent', function( $request, $response, $app ) {
	$constituent_id = $request->param('constituent_id');

	if( 'saus' == $app->route ) {
		$_POST['sau_id'] = $app->id;
		$constituent_join = new TeacherCert\ConstituentSAU( $_POST );
	} elseif( 'schools' == $app->route ) {
		$_POST['school_id'] = $app->id;
		$constituent_join = new TeacherCert\ConstituentSchool( $_POST );
	}//end if
	
	if( $constituent_join ) {
		$constituent = $constituent_join->constituent();

		if( $constituent_join->save() ) {
			$_SESSION['successes'][] = sprintf( 'Added the Constituent <em>%s, %s %s</em> to the %s <em>%s</em> successfully!',
				$constituent->last_name, $constituent->first_name, $constituent->mi, $app->request_title, $app->object->name );
		} else {
			$_SESSION['errors'][] = sprintf( 'There was a problem adding the Constituent <em>%s, %s %s</em> to the %s <em>%s</em>. Please contact ITS.',
				$constituent->last_name, $constituent->first_name, $constituent->mi, $app->request_title, $app->object->name );
		}//end else
	} else {
		$_SESSION['errors'][] = 'You can not add constituents to '.$app->collection_title .'.';
	}//end if

	$response->redirect( $GLOBALS['BASE_URL'] . '/admin/'.$app->route.'/' . $app->object->id );
});

/**
 * view specific sau
 */
respond( 'POST', '/[:route]/[i:id]/edit', function( $request, $response, $app ) {
	$app->parse_model_results( 
		TeacherCert::save_model( $app->request_title, $_POST, $app->model, $app->object ) 
	);

	$response->redirect( $GLOBALS['BASE_URL'] . '/admin/'.$app->route.'/' . $app->object->id );
});
