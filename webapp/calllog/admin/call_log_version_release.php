<?php
if($GLOBALS['EMPLOYEE_INFO']['user_privileges'] == 'webguru'){
	include("call_log_admin.html");
	$page_name = 'call_log_version_release';
	$tpl = new XTemplate(TEMPLATE_ADMIN_DIR.'/'.$page_name.'.tpl');

	$version_number = file_get_contents($INCLUDES.'/version.txt');
	$version_array = explode(".", $version_number);
	$major_release = $version_array[0] + 1;
	$new_major_release = $major_release.'.0'.'.0';
	$minor_release = $version_array[1] + 1;
	$new_minor_release = $version_array[0].'.'.$minor_release.'.0';
	$dot_release = $version_array[2] + 1;
	$new_dot_release = $version_array[0].'.'.$version_array[1].'.'.$dot_release;
	$tpl->assign('new_major_release', $new_major_release);
	$tpl->assign('new_minor_release', $new_minor_release);
	$tpl->assign('new_dot_release', $new_dot_release);
	$tpl->parse('main');
	$tpl->out('main');

	echo setUpFooter();
}else{
	PSUHTML::redirect(CALL_LOG_WEB_HOME.'/index.html');
}
?>
