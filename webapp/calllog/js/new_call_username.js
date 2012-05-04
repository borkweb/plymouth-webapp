var global_caller_username;

function showGroupUsers(){
	var group_number = $F('its_group_assigned_to');
	new Ajax.Updater('showGroupUsersDiv', 'https://'+document.domain+'/webapp/calllog/ajax_backend.html?return=showGroupUsers&group_number='+group_number);
}

function problem_array(){
	new Ajax.Updater('knowledgebase_div', 'https://'+document.domain+'/webapp/calllog/ajax_backend.html?return=knowledgebase_data&searchterm='+$('problem_details').value+'&caller_user_name='+$F('caller_user_name'));
}

function KeyCheck(e, call_log_username){
	var KeyID = (window.event) ? event.keyCode : e.keyCode;
	if (KeyID == 32){ //checks for Spacebar
		getKeywords();
	}
}

function auto_save_info(call_log_username){
	new Ajax.PeriodicalUpdater('displayAutoSave', 'https://'+document.domain+'/webapp/calllog/ajax_backend.html?return=autoSaveCall&call_log_username='+call_log_username+'&call_details='+$F('problem_details')+'&caller_user_name='+$F('caller_user_name'), {frequency:120});
}

function getKeywords(){
	var string = "";
	try{
		string += '&call_id='+$F('call_id');
	}catch (e){}
	new Ajax.Updater('keywordsList', 'https://'+document.domain+'/webapp/calllog/ajax_backend.html?return=keywordList&details='+$F('problem_details')+string, {onComplete: addKeywords});
}

function addKeywords(){
	$('keywords_list').value = $('keywordsList').innerHTML;
}

function viewCallHistoryDetails(call_id){
	new Ajax.Updater('CallHistoryDiv', 'https://'+document.domain+'/webapp/calllog/ajax_backend.html?return=callHistoryDetails&caller_user_name='+$F('caller_user_name')+'&call_id='+call_id);
	Element.toggle('CallHistoryInnerDiv');
}

function viewCallHistorySummary(caller, highlight){
	if(highlight){
		new Ajax.Updater('CallHistoryDiv', 'https://'+document.domain+'/webapp/calllog/ajax_backend.html?return=callHistorySummary&caller='+caller, {onComplete: highlightHistory});
	}else{
		new Ajax.Updater('CallHistoryDiv', 'https://'+document.domain+'/webapp/calllog/ajax_backend.html?return=callHistorySummary&caller='+caller);
	}
}

function highlightHistory(){
	new Effect.Highlight('CallHistoryTD1_1', {startcolor:'#D19275', endcolor:'#FFEFD5', duration: 3.0});
	new Effect.Highlight('CallHistoryTD2_1', {startcolor:'#D19275', endcolor:'#FFEFD5', duration: 3.0});
}

function sendKBEmail(page_id, sentCount, rowNum){
	new Ajax.Updater('sendKBEmailDiv', 'https://'+document.domain+'/webapp/calllog/ajax_backend.html?return=sendKBEmail&caller_user_name='+$F('caller_user_name')+'&KBArticle='+page_id+'&SentCount='+sentCount+'&searchterm='+$F('problem_details'));
	updateKBNum(page_id,sentCount);
	return false;
}

function updateKBNum(page_id,num){
	$('sendKBEmailDiv_'+page_id).innerHTML = "Sent (" + (num+1) + ")";
	$('knowledgebaseTD1_'+page_id).style.backgroundColor = "#FFFF33";
	$('knowledgebaseTD2_'+page_id).style.backgroundColor = "#FFFF33";
}

function restoreRequest(){
	global_caller_username = $F('caller_user_name');
	new Ajax.Updater('restore_request_div', 'https://'+document.domain+'/webapp/calllog/ajax_backend.html?return=restoreRequest&caller_user_name='+$F('caller_user_name')+'&caller_first_name='+$F('caller_first_name')+'&caller_last_name='+$F('caller_last_name')+'&caller_role='+$F('caller_role')+'&restore_system='+$F('restore_system')+'&restore_path='+$F('restore_path')+'&restore_filenames='+$F('restore_filenames')+'&restore_month='+$F('restore_month')+'&restore_date='+$F('restore_date')+'&restore_year='+$F('restore_year')+'&restore_hour='+$F('restore_hour')+'&restore_minute='+$F('restore_minute')+'&restore_details='+$F('restore_details')+'&action=refresh', {onComplete: viewCallHistorySummary(global_caller_username, true)});
}

function restoreRequestError(){
	alert("error");
	new Ajax.Updater('restore_request_div', 'https://'+document.domain+'/webapp/calllog/ajax_backend.html?return=restoreRequest',{onComplete: restoreRequestHighlight});
}

function restoreRequestHighlight(){
	new Effect.Highlight('restore_request_error', {startcolor:'#D19275', endcolor:'#FFEFD5', restorecolor:'#FFEFD5'});
}

function newRestoreRequest(){
	new Ajax.Updater('restore_request_div', 'https://'+document.domain+'/webapp/calllog/ajax_backend.html?return=restoreRequest');
}

function viewMediaHistoryDetails(media_id){
	new Ajax.Updater('media_loans_div', 'https://'+document.domain+'/webapp/calllog/ajax_backend.html?return=mediaHistoryDetails&caller_user_name='+$F('caller_user_name')+'&media_id='+media_id);
	Element.toggle('MediaHistoryInnerDiv');
}

function viewMediaHistorySummary(){
	new Ajax.Updater('media_loans_div', 'https://'+document.domain+'/webapp/calllog/ajax_backend.html?return=mediaHistorySummary&caller_user_name='+$F('caller_user_name')+'');
}

function addNewAttachment(){
	var file = document.createElement("INPUT")
		file.setAttribute('type','file')
		file.setAttribute('name','attachment_file_1')
		file.setAttribute('id','attachment_file_1')
	attach-div.appendChild(file);
}

function hideLoadingContent(){
	Element.hide('userStatusLoading');
}

function symbol_toggle(id){
	var element = $(id);
	if (element.innerHTML == "+"){
		element.innerHTML = "-";
		$('userStatusLoadingText').innerHTML = "Loading Content Please Wait...";
	}else{
		element.innerHTML = "+";
		$('userStatusLoadingText').innerHTML = "Closing Content Please Wait...";
	}
}

function change_status(id){
	var element = $(id);
	if((element.value != 0) || (element.value != 'unassigned') || (element.value != null)){
		$('call_status').value = "open";
	}
	if(($('tlc_assigned_to').value == 'unassigned') && ($('its_assigned_group').value == 0)){
		$('call_status').value = "closed";
	}
}

function calllog_toggle(element){
	var status = $(element+'_div').style.display;
	if(status == ''||status == 'none'){
		$(element+'_div').style.display='block';
	}else{
		$(element+'_div').style.display='none';
		symbol_toggle(element+'_symbol');
	}
}

function goBack(where, option, group, find_type, page){
	if(find_type){
		window.location = page+"?action="+where+"&option="+option+"&group="+group+"&find_type="+find_type;
	}else{
		window.location = "index.html?action="+where+"&option="+option+"&group="+group+"&find_type="+find_type;
	}
}

function checkNewCallForm(){
	var no_details;
	if($F('problem_details')==""){
	   no_details = confirm("Call has no problem details, proceed?"); 
	}
	if(no_details != false){
		document.new_call.submit();
	}

}

function sendHelpDeskMail(call_id, its_group, action){
	if(action == 'send' || action == 'queue'){
		var string = "";
		try{
			string += '&caller_class='+$F('email_caller_class');
		}
		catch (e){}
		try{
			string += '&call_id='+call_id;
		}
		catch (e){}
		try{
			string += '&its_group='+$F('its_assigned_group');
		}
		catch (e){}
		new Ajax.Updater('caller_email_div', 'https://'+document.domain+'/webapp/calllog/ajax_backend.html?return=sendCallerEmail&caller_first_name='+$F('email_first_name')+'&caller_last_name='+$F('email_last_name')+'&user_name='+$F('email_user_name')+'&message='+escape($F('email_message'))+'&action=queue'+string);
	}else{
		if($('caller_email_legend').innerHTML == 'Caller Information'){
			Element.hide('caller_information_div');
			$('caller_email_legend').innerHTML = "Caller Email";
			Element.show('caller_email_div');
		}else{
			Element.show('caller_information_div');
			$('caller_email_legend').innerHTML = "Caller Information";
			Element.hide('caller_email_div');
		}
	}
}

function reorder_assign_history(order, caller_user_name, call_id){
	if(order == 'old'){
		new Ajax.Updater('call_assignment_history_reorder', 'https://'+document.domain+'/webapp/calllog/ajax_backend.html?return=assign_reorder&order=old&call_id='+call_id+'&caller_user_name='+caller_user_name);
		new Ajax.Updater('reorder-assign-state', 'https://'+document.domain+'/webapp/calllog/ajax_backend.html?return=change_assign_state&order=old');
	}else if(order == 'new'){
		new Ajax.Updater('call_assignment_history_reorder', 'https://'+document.domain+'/webapp/calllog/ajax_backend.html?return=assign_reorder&order=new&call_id='+call_id+'&caller_user_name='+caller_user_name);
		new Ajax.Updater('reorder-assign-state', 'https://'+document.domain+'/webapp/calllog/ajax_backend.html?return=change_assign_state&order=new');
	}
}
