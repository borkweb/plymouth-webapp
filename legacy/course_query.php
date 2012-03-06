<?php
require_once('PSUDatabase.class.php');
require_once('xtemplate.php');
require_once('BannerCourse.class.php');

$GLOBALS['QUERY_BANNER'] = PSUDatabase::connect('oracle/psc1_psu');

if(!$GLOBALS['BannerCourse'])
{
	$GLOBALS['BannerCourse']=new BannerCourse($GLOBALS['QUERY_BANNER']);
}//endif

function courseQueryResults($parameters='',$termcode='',$department='',$crn='',$subj_code='',$number='',$section='',$online='',$session='')
{
	$tpl=new XTemplate('/web/pscpages/includes/templates/course_query_form.tpl');
	if(is_array($parameters))
	{
		$termcode=$parameters['termcode'];
		$department=$parameters['department'];
		$crn=$parameters['crn'];
		$subj_code=$parameters['subj_code'];
		$number=$parameters['number'];
		$section=$parameters['section'];
		$online=$parameters['online'];
		$session=$parameters['session'];
	}//end if

	$courses=array();
	$query="SELECT sect.ssbsect_crn 						as crn,
								 sect.ssbsect_subj_code 			as subject_code,
								 sect.ssbsect_crse_numb 			as course_number,
								 sect.ssbsect_seq_numb 				as course_section,
								 crse.scbcrse_title 					as course_title,
								 sect.ssbsect_crse_title 			as section_title,
								 ssts.stvssts_desc						as course_status,
								 sect.ssbsect_ssts_code				as csta_code,
								 sect.ssbsect_gmod_code 			as grading_mode,
								 substr(decode(sect.ssbsect_sess_code,null,null,f_student_get_desc('STVSESS',sect.ssbsect_sess_code,30)),1,30)
																							as session_data,
								 sect.ssbsect_credit_hrs 			as credit_hours,
								 crse.scbcrse_credit_hr_high  		as credit_high,
								 crse.scbcrse_credit_hr_low			as credit_low,
								 crse.scbcrse_credit_hr_ind			as credit_ind,
								 sect.ssbsect_bill_hrs 				as billing_hours,
								 sect.ssbsect_prior_enrl 			as prior_enroll,
								 sect.ssbsect_proj_enrl 			as projected_enroll,
								 sect.ssbsect_max_enrl 				as max_enroll,
								 sect.ssbsect_enrl 						as current_enroll,
								 sect.ssbsect_seats_avail 		as seats_available,
								 sect.ssbsect_tot_credit_hrs 	as total_credit_hours,
								 sect.ssbsect_wait_capacity 	as wait_capacity,
								 sect.ssbsect_wait_count 			as wait_count,
								 sect.ssbsect_wait_avail 			as wait_available,
								 sect.ssbsect_lec_hr 					as lecture_hours,
								 sect.ssbsect_lab_hr 					as lab_hours,
								 sect.ssbsect_oth_hr 					as other_hours,
								 sect.ssbsect_cont_hr 				as contact_hours,
								 substr(decode(crse.scbcrse_dept_code,null,null,f_student_get_desc('STVDEPT',crse.scbcrse_dept_code,30)),1,30)
																							as department,
								 substr(decode(sect.ssbsect_ptrm_code,null,null,f_student_get_desc('STVPTRM',sect.ssbsect_ptrm_code,30)),1,30)
																							as term_length,
								 iden.spriden_pidm            as instructor_pidm,
								 nvl(iden.spriden_last_name,'STAFF')
																							as instructor_last,
								 iden.spriden_first_name			as instructor_first,
								 substr(iden.spriden_mi,1,1)	as instructor_mi,
								 sect.ssbsect_insm_code				as insm_code,
								 substr(decode(sect.ssbsect_sess_code,null,null,f_student_get_desc('STVSESS',sect.ssbsect_sess_code,30)),1,30)
																							as session_ind
						FROM ssbsect sect,
								 scbcrse crse,
								 spriden iden,
								 stvssts ssts
					 WHERE sect.ssbsect_term_code='$termcode'
		             AND crse.scbcrse_subj_code=sect.ssbsect_subj_code
		             AND crse.scbcrse_crse_numb=sect.ssbsect_crse_numb
		             AND crse.scbcrse_eff_term=(SELECT max(crse2.scbcrse_eff_term)
		             															FROM scbcrse crse2
		             														 WHERE crse2.scbcrse_subj_code=crse.scbcrse_subj_code
		             														   AND crse2.scbcrse_crse_numb=crse.scbcrse_crse_numb
		             														   AND crse2.scbcrse_eff_term<='$termcode')
		             AND crse.scbcrse_coll_code='PL'
		             AND iden.rowid(+) = f_get_instr_spriden_rowid(sect.ssbsect_crn , '$termcode','Y',null)
		             AND ssts.stvssts_code=sect.ssbsect_ssts_code";
	if($department) $query.=" AND crse.scbcrse_dept_code='".strtoupper($department)."'";
	if($crn) $query.=" AND sect.ssbsect_crn like '".strtoupper($crn)."%'";
	if($subj_code) $query.=" AND upper(sect.ssbsect_subj_code) like '".strtoupper($subj_code)."%'";
	if($number) $query.=" AND sect.ssbsect_crse_numb like '".strtoupper($number)."%'";
	if($section) $query.=" AND sect.ssbsect_seq_numb like '%".strtoupper($section)."%'";
	if($online) 
	{
		if($session=='blended') 
			$query.=" AND sect.ssbsect_sess_code in ('B','K','V')";
		elseif($session=='evening')
			$query.=" AND sect.ssbsect_sess_code in ('E','V')";
		elseif($session=='weekend')
			$query.=" AND sect.ssbsect_sess_code in ('W','K')";
		elseif($session=='online')
			$query.=" AND sect.ssbsect_sess_code in ('D')";
		else $query.=" AND sect.ssbsect_sess_code IN ('E','W','B','D','K','V')";
		$query.=" AND sect.ssbsect_ssts_code IN (SELECT stvssts_code FROM stvssts WHERE stvssts_reg_ind='Y')";
	}//end if
	$query.=" ORDER BY sect.ssbsect_subj_code,sect.ssbsect_crse_numb,sect.ssbsect_seq_numb";
	
	if($results=$GLOBALS['QUERY_BANNER']->Execute($query))
	{
		while($row=$results->FetchRow())
		{
			$row=array_change_key_case($row,CASE_LOWER);
			$courses[$row['crn']]=$row;
			$query="SELECT meet.ssrmeet_begin_time			as begin_time,
										 meet.ssrmeet_end_time				as end_time,
										 meet.ssrmeet_bldg_code				as building_code,
										 substr(decode(meet.ssrmeet_bldg_code,null,null,f_student_get_desc('STVBLDG',meet.ssrmeet_bldg_code,30)),1,30)
																									as building,
										 meet.ssrmeet_room_code				as room,
										 meet.ssrmeet_start_date			as start_date,
										 meet.ssrmeet_end_date				as end_date,
										 meet.ssrmeet_catagory				as catagory,
										 meet.ssrmeet_sun_day					as sunday,
										 meet.ssrmeet_mon_day					as monday,
										 meet.ssrmeet_tue_day					as tuesday,
										 meet.ssrmeet_wed_day					as wednesday,
										 meet.ssrmeet_thu_day					as thursday,
										 meet.ssrmeet_fri_day					as friday,
										 meet.ssrmeet_sat_day					as saturday,
										 meet.ssrmeet_schd_code				as meeting_type,
										 meet.ssrmeet_hrs_week        as meeting_hours 
								FROM ssrmeet meet 
							 WHERE meet.ssrmeet_term_code='$termcode'
								 AND meet.ssrmeet_crn='".$row['crn']."'
							 ORDER BY meet.ssrmeet_start_date,meet.ssrmeet_begin_time,meet.ssrmeet_end_date,meet.ssrmeet_end_time";
			if($results2=$GLOBALS['QUERY_BANNER']->Execute($query))
			{
				while($row2=$results2->FetchRow())
				{
					$row2=array_change_key_case($row2,CASE_LOWER);
					$row2['start_date']=date('M j',strtotime($row2['start_date']));
					$row2['end_date']=date('M j',strtotime($row2['end_date']));
					if($row2['begin_time'])
						$row2['begin_time']=date('g:ia',strtotime($row2['begin_time']));
					if($row2['end_time'])
						$row2['end_time']=date('g:ia',strtotime($row2['end_time']));
					$courses[$row['crn']]['meeting_times'][]=$row2;
				}//end while

			}//end if
			$query="SELECT spriden_pidm pidm,
										 spriden_id id,
										 spriden_first_name first_name,
										 spriden_mi mi,
										 spriden_last_name last_name
								FROM sirasgn,spriden
							 WHERE sirasgn_term_code='$termcode'
								 AND sirasgn_crn='".$row['crn']."'
								 AND sirasgn_pidm=spriden_pidm
								 AND spriden_change_ind IS NULL
							 ORDER BY sirasgn_primary_ind,spriden_last_name,spriden_first_name";
			if($results2=$GLOBALS['QUERY_BANNER']->Execute($query))
			{
				while($row2=$results2->FetchRow())
				{
					$courses[$row['crn']]['instructors'][$row2['pidm']]=array_change_key_case($row2,CASE_LOWER);
					$courses[$row['crn']]['instructors'][$row2['pidm']]['first_name']=substr($courses[$row['crn']]['instructors'][$row2['pidm']]['first_name'],0,1).'.';
					$courses[$row['crn']]['instructors'][$row2['pidm']]['mi']=($courses[$row['crn']]['instructors'][$row2['pidm']]['mi'])?substr($courses[$row['crn']]['instructors'][$row2['pidm']]['mi'],0,1).'.':'';
				}//end while
			}//end if
			if($online)
			{
				$sql="SELECT ssrattr_attr_code FROM ssrattr WHERE ssrattr_attr_code like 'B%' AND ssrattr_crn=".$row['crn']." AND ssrattr_term_code='$termcode'";
				if($attr_results=$GLOBALS['QUERY_BANNER']->Execute($sql))
				{
					while($attr=$attr_results->FetchRow())
					{
						$courses[$row['crn']]['blended_attr'][]=$attr['SSRATTR_ATTR_CODE'];
					}
				}//end if
			}//end if
		}//end while
	}//end if
	return $courses;
}//end courseQueryResults

function courseQuery($output,$parameters='',$termcode='',$department='',$crn='',$subj_code='',$number='',$section='',$online='')
{
	/*******************************
		format: 
			out = out the template
			text = return the text of the template
	********************************/

	if(is_array($parameters))
	{
		$termcode=$parameters['termcode'];
		$department=$parameters['department'];
		$crn=$parameters['crn'];
		$subj_code=$parameters['subj_code'];
		$number=$parameters['number'];
		$section=$parameters['section'];
		$online=$parameters['online'];
		$session=$parameters['session'];
		$link=$parameters['link'];
		$default_styles=$parameters['default_styles'];
	}//end if

	$tpl=new XTemplate('/web/pscpages/includes/templates/course_query.tpl');
	$tpl->assign('termcode',$termcode);
	$tpl->assign('department',$department);
	$tpl->assign('crn',$crn);
	$tpl->assign('subj_code',$subj_code);
	$tpl->assign('number',$number);
	$tpl->assign('section',$section);

	if(!$GLOBALS['QUERY_BANNER']->IsConnected())
	{
		$tpl->parse('course_query.database_down');
	}//end if

	$courses=courseQueryResults('',$termcode,$department,$crn,$subj_code,$number,$section,$online,$session);

	$count=0;
	foreach($courses as $course)
	{
		$course['mod']=($count%2)+1;
		$course['description']=$GLOBALS['BannerCourse']->getCourseDescription($course['subject_code'],$course['course_number'],$termcode);
		$course['section_description']=$GLOBALS['BannerCourse']->getSectionDescription($course['crn'],$termcode);
		$tpl->assign('course',$course);
		if($course['section_description'])
			$tpl->parse('course_query.results.course.section_description');

		if($course['section_title'])
			$tpl->parse('course_query.results.course.section_title');
		else
			$tpl->parse('course_query.results.course.course_title');

		if($course['seats_available']>0)
			$tpl->parse('course_query.results.course.open');
		else
			$tpl->parse('course_query.results.course.closed');

		if($course['credit_high'])
			$tpl->parse('course_query.results.course.credit_high');

		if(is_array($course['meeting_times']))
		{
			foreach($course['meeting_times'] as $meeting)
			{
				if($meeting['building']=='NA') $meeting['building']='';
				if($meeting['room']=='ROOM') $meeting['room']='';
				if($meeting['start_date']) $meeting['start_date']=date('M d',strtotime($meeting['start_date']));
				if($meeting['end_date']) $meeting['end_date']=date('M d',strtotime($meeting['end_date']));
				if($meeting['begin_time']) $meeting['begin_time']=date('g:ia',strtotime($meeting['begin_time']));
				if($meeting['end_time']) $meeting['end_time']=date('g:ia',strtotime($meeting['end_time']));

				$meeting['days']='';
				if($meeting['sunday']) $meeting['days']='Sundays';
				if($meeting['monday']) $meeting['days'].=(($meeting['days'])?', ':'').'Mondays';
				if($meeting['tuesday']) $meeting['days'].=(($meeting['days'])?', ':'').'Tuesdays';
				if($meeting['wednesday']) $meeting['days'].=(($meeting['days'])?', ':'').'Wednesdays';
				if($meeting['thursday']) $meeting['days'].=(($meeting['days'])?', ':'').'Thursdays';
				if($meeting['friday']) $meeting['days'].=(($meeting['days'])?', ':'').'Fridays';
				if($meeting['saturday']) $meeting['days'].=(($meeting['days'])?', ':'').'Saturdays';

				$tpl->assign('meeting',$meeting);
				if($meeting['sunday'] || $meeting['monday'] || $meeting['tuesday'] || $meeting['wednesday'] || $meeting['thursday'] || $meeting['friday'] || $meeting['saturday'])
				{
					$tpl->parse('course_query.results.course.meeting.days');
				}//end if

				if($meeting['begin_time'] || $meeting['end_time'])
					$tpl->parse('course_query.results.course.meeting.time');
				$tpl->parse('course_query.results.course.meeting');
			}//end foreach
		}//end if

		if(is_array($course['instructors']))
		{
			foreach($course['instructors'] as $instructor)
			{
				$tpl->assign('instructor',$instructor);
				$tpl->parse('course_query.results.course.instructor');
			}//end foreach
		}//end if

		if($online) 
		{
			if(is_array($course['blended_attr']))
			{
				foreach($course['blended_attr'] as $attr)
				{
					$tpl->assign('attribute',array('name'=>$attr));
					$tpl->parse('course_query.results.course.online.attribute');
				}//end foreach
			}//end if
			$tpl->parse('course_query.results.course.online');
		}//end if

		$tpl->parse('course_query.results.course');
		$count++;
	}//end foreach
	$tpl->assign('count',$count);

	if($_GET['termcode_type'])
	{
		if(!count($courses))
			$tpl->parse('course_query.results.no_results');
		if($online) $tpl->parse('course_query.results.online');
		if($link) 
		{
			$tpl->assign('link',$link);
			$tpl->parse('course_query.results.link');
		}//end if
		if($default_styles) 
		{
			$tpl->parse('course_query.default_styles');
		}//end if
		$tpl->parse('course_query.results');
	}//end if
	$tpl->parse('course_query');
	if($output=='out')
	{
		$tpl->out('course_query');
		return true;
	}//end if
	//else($format=='text')
		//return $tpl->text('course_query');
}//end courseQuery

function courseQueryForm($output,$parameters='',$termcode='',$department='',$crn='',$subj_code='',$number='',$section='',$online='',$session='')
{
	/*******************************
		format: 
			out = out the template
			text = return the text of the template
	********************************/
	if(is_array($parameters))
	{
		$termcode=$parameters['termcode'];
		$level=$parameters['level'];
		$department=$parameters['department'];
		$crn=$parameters['crn'];
		$subj_code=$parameters['subj_code'];
		$number=$parameters['number'];
		$section=$parameters['section'];
		$online=$parameters['online'];
		$session=$parameters['session'];
	}//end if
	
	$tpl=new XTemplate('/web/pscpages/includes/templates/course_query_form.tpl');
	$tpl->assign('termcode',$termcode);
	$tpl->assign('department',$department);
	$tpl->assign('crn',$crn);
	$tpl->assign('subj_code',$subj_code);
	$tpl->assign('number',$number);
	$tpl->assign('section',$section);
	$tpl->assign('level',$level);

	if(!$GLOBALS['QUERY_BANNER']->IsConnected())
	{
		$tpl->parse('course_query.form.database_down');
	}//end if

	$current_ug_termcode=$GLOBALS['QUERY_BANNER']->GetOne("SELECT f_get_currentterm('UG') FROM dual");
	$current_gr_termcode=$GLOBALS['QUERY_BANNER']->GetOne("SELECT f_get_currentterm('GR') FROM dual");

	$selected_ug_termcode=($_GET['termcode_ug'])?$_GET['termcode_ug']:$current_ug_termcode;
	$selected_gr_termcode=($_GET['termcode_gr'])?$_GET['termcode_gr']:$current_gr_termcode;

	$year=date('Y');

	if($online)
	{
		$tpl->parse('course_query.form.online');
	}//end if

	for($i=0;$i<3;$i++)
	{
		$termcode_data=$GLOBALS['QUERY_BANNER']->GetRow("SELECT * FROM stvterm WHERE stvterm_code='".(($year+1)+$i)."10'");
		$termcode_data=array_change_key_case($termcode_data,CASE_LOWER);
		$tpl->assign('termcode',$termcode_data);
		if($termcode_data['stvterm_code']==$selected_ug_termcode) $tpl->parse('course_query.form.termcode_ug.selected');
		$tpl->parse('course_query.form.termcode_ug');

		$termcode_data=$GLOBALS['QUERY_BANNER']->GetRow("SELECT * FROM stvterm WHERE stvterm_code='".($year+$i)."20'");
		$termcode_data=array_change_key_case($termcode_data,CASE_LOWER);
		$tpl->assign('termcode',$termcode_data);
		if($termcode_data['stvterm_code']==$selected_ug_termcode) $tpl->parse('course_query.form.termcode_ug.selected');
		$tpl->parse('course_query.form.termcode_ug');

			$termcode_data=$GLOBALS['QUERY_BANNER']->GetRow("SELECT * FROM stvterm WHERE stvterm_code='".($year+$i)."30'");
		$termcode_data=array_change_key_case($termcode_data,CASE_LOWER);
		$tpl->assign('termcode',$termcode_data);
		if($termcode_data['stvterm_code']==$selected_ug_termcode) $tpl->parse('course_query.form.termcode_ug.selected');
		$tpl->parse('course_query.form.termcode_ug');

		$termcode_data=$GLOBALS['QUERY_BANNER']->GetRow("SELECT * FROM stvterm WHERE stvterm_code='".($year+$i)."40'");
		$termcode_data=array_change_key_case($termcode_data,CASE_LOWER);
		$tpl->assign('termcode',$termcode_data);
		if($termcode_data['stvterm_code']==$selected_ug_termcode) $tpl->parse('course_query.form.termcode_ug.selected');
		$tpl->parse('course_query.form.termcode_ug');

		$termcode_data=$GLOBALS['QUERY_BANNER']->GetRow("SELECT * FROM stvterm WHERE stvterm_code='".(($year+1)+$i)."91'");
		$termcode_data=array_change_key_case($termcode_data,CASE_LOWER);
		$tpl->assign('termcode',$termcode_data);
		if($termcode_data['stvterm_code']==$selected_gr_termcode) $tpl->parse('course_query.form.termcode_gr.selected');
		$tpl->parse('course_query.form.termcode_gr');

		$termcode_data=$GLOBALS['QUERY_BANNER']->GetRow("SELECT * FROM stvterm WHERE stvterm_code='".($year+$i)."92'");
		$termcode_data=array_change_key_case($termcode_data,CASE_LOWER);
		$tpl->assign('termcode',$termcode_data);
		if($termcode_data['stvterm_code']==$selected_gr_termcode) $tpl->parse('course_query.form.termcode_gr.selected');
		$tpl->parse('course_query.form.termcode_gr');

		$termcode_data=$GLOBALS['QUERY_BANNER']->GetRow("SELECT * FROM stvterm WHERE stvterm_code='".($year+$i)."93'");
		$termcode_data=array_change_key_case($termcode_data,CASE_LOWER);
		$tpl->assign('termcode',$termcode_data);
		if($termcode_data['stvterm_code']==$selected_gr_termcode) $tpl->parse('course_query.form.termcode_gr.selected');
		$tpl->parse('course_query.form.termcode_gr');

		$termcode_data=$GLOBALS['QUERY_BANNER']->GetRow("SELECT * FROM stvterm WHERE stvterm_code='".($year+$i)."94'");
		$termcode_data=array_change_key_case($termcode_data,CASE_LOWER);
		$tpl->assign('termcode',$termcode_data);
		if($termcode_data['stvterm_code']==$selected_gr_termcode) $tpl->parse('course_query.form.termcode_gr.selected');
		$tpl->parse('course_query.form.termcode_gr');

		$termcode_data=$GLOBALS['QUERY_BANNER']->GetRow("SELECT * FROM stvterm WHERE stvterm_code='".($year+$i)."80'");
		$termcode_data=array_change_key_case($termcode_data,CASE_LOWER);
		$tpl->assign('termcode',$termcode_data);
		if($termcode_data['stvterm_code']==$selected_gr_termcode) $tpl->parse('course_query.form.termcode_gr.selected');
		$tpl->parse('course_query.form.termcode_gr');
	}//end for

	$tpl->parse('course_query.form.'.strtolower($level).'_selected');
	$tpl->parse('course_query.form.show_'.strtolower($level).'_termcodes');

	$tpl->parse('course_query.form');
	$tpl->parse('course_query');
	if($output=='out')
	{
		$tpl->out('course_query');
		return true;
	}//end if
	//else($format=='text')
		//return $tpl->text('course_query');
}//end courseQueryForm

function getQueryTermcode($default='UG')
{
	if($_GET['termcode_type'])
	{
		if($_GET['termcode_type']=='UG')
		{
			$termcode=$_GET['termcode_ug'];
		}//end if
		else
		{
			$termcode=$_GET['termcode_gr'];
		}//end else
	}//end if
	else
	{
		$current_ug_termcode=$GLOBALS['QUERY_BANNER']->GetOne("SELECT f_get_currentterm('UG') FROM dual");
		$current_gr_termcode=$GLOBALS['QUERY_BANNER']->GetOne("SELECT f_get_currentterm('GR') FROM dual");

		$termcode=(strtoupper($default)=='UG')?$current_ug_termcode:$current_gr_termcode;
	}//end else

	return $termcode;
}//end getQueryTermcode
?>