<?php
function displayRecoveryData($template_file){
	global $db; 

	$tpl = new XTemplate($template_file);
	// gets saved temp data that is stored every 30 seconds
	$getRecoveryDataQuery = $db->Execute("SELECT * FROM temp_call WHERE call_log_username = '$_SESSION[username]'");
	if ($getRecoveryDataQuery->RecordCount() != 0){
		while($getRecoveryData = $getRecoveryDataQuery->FetchRow()){
			$recovery_data = unserialize($getRecoveryData['recovery_data']);
			$tpl->assign('recovered_data', $recovery_data);
			$tpl->parse("main.recovered.listRecoveredCalls");
		}
		$tpl->assign('call_log_username', $_SESSION['username']);
		$tpl->parse("main.recovered");
	}
	return $tpl->text('main.recovered');
}
?>