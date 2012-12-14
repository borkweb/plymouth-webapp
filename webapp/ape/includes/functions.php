<?php

function initializePage($template)
{
	$GLOBALS['tpl']=new XTemplate($template,$GLOBALS['TEMPLATES']);
	$GLOBALS['tpl']->assign('icon',$GLOBALS['ape']->icons);
	if($_REQUEST['type'])
		$GLOBALS['tpl']->assign('search_'.$_REQUEST['type'],'selected="1"');
	else
		$GLOBALS['tpl']->assign('search_name','selected="1"');
	$GLOBALS['tpl']->assign('search_term',$_REQUEST['identifier']);
}//end initializePage

function buildWindow($win,$inline=false)
{
	if($inline)
	{
		$GLOBALS['tpl']->assign('window',$win);
		$GLOBALS['tpl']->parse('main.window');
		return $GLOBALS['tpl']->text('main.window');
	}//end if
	else
	{
		$window=new XTemplate($GLOBALS['TEMPLATES'].'/window.tpl');
		$window->assign('window',$win);
		$window->parse('window');
		return $window->text('window');
	}//end else
}//end buildWindow

function calcDriveQuota($username)
{	

	$quota = PSU::db('systems')->GetRow("SELECT * FROM home_quotas WHERE user = ?", array( $username ));
	if( ! $quota ) {
		// unexpected error! (maybe being run on dev and no data was returned?)
		// return something to fill the <div> on the page:
		return "<em>unexpected error</em>";
	}
	else {
		return formatProgressBar($quota);
	}// end else
}//end calcDriveQuota

function calcMailQuota($username)
{	
	$zimbraAdmin = new zimbraAdmin();
	$zimbraAdmin->connect();
	$quota = $zimbraAdmin->getAccountQuota($username);
	if( ! $quota ) {
		// unexpected error! (maybe being run on dev and no data was returned?)
		// return something to fill the <div> on the page:
		return "<em>unexpected error</em>";
	}
	return formatProgressBar($quota);
}

/**
 * Quota info for display in a (CSS) "progress bar"
 * Input: (not all these fields are currently being used)
 *   [user] => djbramer
 *   [quota_limit] => 2048
 *   [quota_usage] => 589
 *   [percent_usage] => 28.77
 *   [quota_available] => 1459
 *   [update_timestamp] => 2012-12-03 13:18:52
 * Sample Output: a bar with a colored background (green==OK, etc)
 *   "589 MB (28.77%)"
 */
function formatProgressBar($quota) {
	
		$used_label = $quota['quota_usage'].' MB';
		$max_label = $quota['quota_limit'].' MB';

		if($quota['quota_usage']<40) {
			$small_label = '&nbsp;'.$used_label;
			$used_label = '';
		}
		
		$params = array(
			'text' => $used_label,
			'percent' => $quota['percent_usage'],
			'class' => $quota['percent_usage'] >= 90 ? 'progress-red' : ''
		);

		$tpl = new PSUTemplate();
		return $tpl->psu_progress($params, $tpl);
}

function getManualMailingListMembers($list)
{
	$data=array();
	$sql="SELECT * FROM pzrattr,spriden WHERE spriden_pidm=pzrattr_pidm AND spriden_change_ind is null AND pzrattr_attr_code='$list' AND pzrattr_manual='Y' ORDER BY spriden_last_name,spriden_first_name";
	if($results=$GLOBALS['BANNER']->Execute($sql))
	{
		while($row=$results->FetchRow())
		{
			$row=PSUTools::cleanKeys(array('pzvattr_','spriden_'),'',$row);
			$data[]=$row;
		}//end while
	}//end if
	return $data;
}//end getManualMailingListMembers

function grabContent($tpl)
{
	$template=new XTemplate($GLOBALS['TEMPLATES'].$tpl);
	$template->parse('main');
	return $template->text('main');
}//end grabContent

function identity_icons( &$results ) {

	// prepare list of search results
	foreach($results as $key => &$result)
	{
		switch($result['r_ldap_user'])
		{
			default: $result['icon'] = 'person'; break;
		}//end switch
	}//end foreach

	return $results;
}//end identity_icons

function isAdmit($pid)
{
	$query="DECLARE v_exists VARCHAR2(1); BEGIN :v_exists := icgokrol.f_appaccept_ind($pid); END;";
	$stmt=$GLOBALS['BANNER']->PrepareSP($query);
	$GLOBALS['BANNER']->OutParameter($stmt,$exists,'v_exists');
	$GLOBALS['BANNER']->Execute($stmt);

	if($exists=='Y') return true;
	return false;
}//end isAdmit

function isCallLogEmployee($username)
{
	return $GLOBALS['CALLLOG']->GetOne("SELECT 1 FROM call_log_employee WHERE status = 'active' AND user_name='$username'");
}//end isCallLogEmployee

function isEligibleToRegister($pidm)
{
	$global_term_string=getGlobalTermString();
	$query="SELECT 'Y'
           FROM sgbstdn a,
                stvstst
          WHERE a.sgbstdn_pidm = $pidm
                AND a.sgbstdn_stst_code = stvstst_code
                AND stvstst_reg_ind = 'Y'
                AND a.sgbstdn_term_code_eff IN
   (SELECT MAX (b.sgbstdn_term_code_eff)
      FROM sgbstdn b, stvterm c
     WHERE     b.sgbstdn_pidm = a.sgbstdn_pidm
           AND b.sgbstdn_term_code_eff <= c.stvterm_code
           AND INSTR ('$global_term_string', c.stvterm_code) > 0
     GROUP BY c.stvterm_code)";
	$result=$GLOBALS['BANNER']->GetOne($query);
	if($result=='Y') return true;
	else return false;
}//end isEligibleToRegister

function no($text='N')
{
	return '<span class="no">'.$text.'</span>';
}//end no

function unknown($text='Unknown. Search by Name.')
{
	return '<span class="unknown">'.$text.'</span>';
}//end unknown

function yes($text='Y')
{
	return '<span class="yes">'.$text.'</span>';
}//end yes

/**
 * action_cleanup() creates an HTTP response for a page that is responding
 * to a form submission. This response might be a redirect to another page,
 * or outputting a JSON string. This function causes script processing to end.
 *
 * @param			string $url the url to redirect to
 * @param			mixed $response any messages that should be passed to the user
 * @param			bool $is_ajax whether or not the response should be done in json
 */
function action_cleanup($url, $response='', $is_ajax=false)
{
	if($is_ajax)
	{
		PSUTools::jsonAndExit($response);
	}
	else
	{
		$_SESSION['messages'] = array_merge($_SESSION['messages'], $response['messages']);
		$_SESSION['errors'] = array_merge($_SESSION['errors'], $response['errors']);

		PSUHTML::redirect($url);
	}
}//end action_cleanup

/**
 * sort_roles_by_source() is a callback function for uasort, ordering by
 * the attribute key.
 */
function sort_roles_by_source($a, $b)
{
	return strcmp($a['attribute'], $b['attribute']);
}//end sort_roles_by_source
