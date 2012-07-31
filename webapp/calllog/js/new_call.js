var global_caller_username;

function addRow(id, i, totalGroups){
	if(i <= totalGroups+1){
		var j=i;
		j++;
		$('addGroupDiv_'+i).innerHTML = "";
		var group = $("ITSGroup");
		var after = $("junk");
		var row = document.createElement("TR")
		var td1 = document.createElement("TD")
			td1.setAttribute('CLASS','content_head')
			td1.innerHTML = "ITS Group "+j;
		var td2 = document.createElement("TD")
			td2.setAttribute('CLASS','content')
			var td2_1 = document.createElement("SELECT")
				td2_1.setAttribute('onChange','updateITSGroup('+j+', '+totalGroups+');')
				td2_1.setAttribute('name','addUser_groupListing_'+j)
				td2_1.setAttribute('id','addUser_groupListing_'+j)
				td2_1.innerHTML = "<option value='0'>Unassigned</option>"+group_options;
		if (j<totalGroups+1){
			var td2_2 = document.createElement("SPAN")
			td2_2.setAttribute('id','addGroupDiv_'+j)
			td2_2.innerHTML = " <a href='javascript: void(0);' onClick=\"addRow('editUserTable', "+j+", "+totalGroups+");\">Add Group</a> | <a href='javascript: void(0);' onClick=\"removeRow('editUserTable', "+j+", "+totalGroups+");\">Remove Group</a>"
		}
		row.appendChild(td1);
		td2.appendChild(td2_1);
		td2.appendChild(td2_2);
		row.appendChild(td2);
		after.insertBefore(row, $('afterGroupsElement'));
	}else{
		$('addGroupDiv_'+i).innerHTML = "";
	}
}

function addUserProfile(){
	new Ajax.Updater('update_add_message', 'https://'+document.domain+'/webapp/calllog/admin/admin_ajax.html?return=userProfileAction&action=add&calllog_username='+$F('calllog_username')+'&user_id='+$F('user_id')+'&last_name='+$F('last_name')+'&first_name='+$F('first_name')+'&position='+$F('position')+'&work_phone='+$F('work_phone')+'&cell_phone='+$F('cell_phone')+'&home_phone='+$F('home_phone')+'&class_options='+$F('class_options')+'&comments='+$F('comments')+'&user_privileges='+$F('user_privileges')+'&status='+$F('status')+'&ferpa='+$F('ferpa_select'), {onComplete: redirectManageUsers});
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


function getEmployeeCalls(employee_calls_username){
	Element.show('display_calls_loading');
	if(employee_calls_username != ""){
		new Ajax.Updater('display_calls', 'https://'+document.domain+'/webapp/calllog/admin/admin_ajax.html?return=getEmployeeCalls&user_name='+employee_calls_username, {onComplete: hideCallsLoading});
	}else{
		new Ajax.Updater('display_calls', 'https://'+document.domain+'/webapp/calllog/admin/admin_ajax.html?return=getEmployeeCalls&user_name='+$F('employee_calls_username'), {onComplete: hideCallsLoading});
	}
}

function hideCallsLoading(){
	Element.hide('display_calls_loading');
}

function hideLoadingContent(){
	$jQuery('#search_results_loading').hide();
	$jQuery('#main-search-results').show();
	$jQuery("#calllog-search").tablesorter();
}

function highlightHistory(){
	new Effect.Highlight('CallHistoryTD1_1', {startcolor:'#D19275', endcolor:'#FFEFD5', duration: 3.0});
	new Effect.Highlight('CallHistoryTD2_1', {startcolor:'#D19275', endcolor:'#FFEFD5', duration: 3.0});
}

function keyCheck(e){
	var KeyID = (window.event) ? event.keyCode : e.keyCode;
	if (KeyID == "13"){ //checks for Carrige Return AKA Enter Key
		searchUser(); 
	}
}

function newRestoreRequest(){
	new Ajax.Updater('restore_request_div', 'https://'+document.domain+'/webapp/calllog/ajax_backend.html?return=restoreRequest');
}


function redirectManageUsers(){
	window.location="manage_users.html?action=edittlcuser&user_name="+$F('calllog_username');
}

function removeRow(id, i, totalGroups){
	var tes = $F('removeGroupLink_5');
	var group = $("ITSGroup");
	//group.removeChild(row);
}

function reorder_assign_history(order, caller_user_name, call_id){
	new Ajax.Updater('call_assignment_history', 'https://'+document.domain+'/webapp/calllog/ajax_backend.html?return=assign_reorder&order='+order+'&call_id='+call_id+'&caller_user_name='+caller_user_name);
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

function searchUser(){ //If you change this change the function name above it
	if ($F('search_string') != ""){
		Element.show('search_results_loading');
		Element.hide('main-search-results');
		new Ajax.Updater('main-search-results', 
			'https://'+document.domain+'/webapp/calllog/ajax_backend.html?return=searchResults&search_string='+$('search_string').value+'&search_type='+$('search_type').value, 
			{onComplete: hideLoadingContent}
		);
	}else{
		alert("Please Enter A Search Term");
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


function updateITSGroup(i, totalGroups){
	var string = "";
	try{
		var group0 = $F('addUser_groupListing_0');
		string += "&group0="+group0;
	}catch (e){}
	try{
		var group1 = $F('addUser_groupListing_1');
		string += "&group1="+group1;
	}catch (e){}
	try{
		var group2 = $F('addUser_groupListing_2');
		string += "&group2="+group2;
	}catch (e){}
	try{
		var group3 = $F('addUser_groupListing_3');
		string += "&group3="+group3;
	}catch (e){}
	try{
		var group4 = $F('addUser_groupListing_4');
		string += "&group4="+group4;
	}catch (e){}
	try{
		var group5 = $F('addUser_groupListing_5');
		string += "&group5="+group5;
	}catch (e){}
	try{
		var group6 = $F('addUser_groupListing_6');
		string += "&group6="+group6;
	}catch (e){}
	try{
		var group7 = $F('addUser_groupListing_7');
		string += "&group7="+group7;
	}catch (e){}
	try{
		var group8 = $F('addUser_groupListing_8');
		string += "&group8="+group8;
	}catch (e){}
	try{
		var group9 = $F('addUser_groupListing_9');
		string += "&group9="+group9;
	}catch (e){}
	try{
		var group10 = $F('addUser_groupListing_10');
		string += "&group10="+group10;
	}catch (e){}
	try{
		var group11 = $F('addUser_groupListing_11');
		string += "&group11="+group11;
	}catch (e){}
	try{
		var group12 = $F('addUser_groupListing_12');
		string += "&group12="+group12;
	}catch (e){}
	try{
		var group13 = $F('addUser_groupListing_13');
		string += "&group13="+group13;
	}catch (e){}
	try{
		var group14 = $F('addUser_groupListing_14');
		string += "&group14="+group14;
	}catch (e){}

	new Ajax.Updater('update_add_message', 'https://'+document.domain+'/webapp/calllog/admin/admin_ajax.html?return=updateITSGroup&user_id='+$F('user_id')+string);
}


function updateUserProfile(i){
	new Ajax.Updater('update_add_message', 'https://'+document.domain+'/webapp/calllog/admin/admin_ajax.html?return=userProfileAction&action=update&calllog_username='+$F('calllog_username')+'&user_id='+$F('user_id')+'&last_name='+$F('last_name')+'&first_name='+$F('first_name')+'&position='+$F('position')+'&work_phone='+$F('work_phone')+'&cell_phone='+$F('cell_phone')+'&home_phone='+$F('home_phone')+'&class_options='+$F('class_options')+'&comments='+$F('comments')+'&user_privileges='+$F('user_privileges')+'&status='+$F('status')+'&ferpa='+$F('ferpa_select'));
}


function viewCallHistoryDetails(call_id){
	new Ajax.Updater('call-history', 'https://'+document.domain+'/webapp/calllog/ajax_backend.html?return=callHistoryDetails&caller_user_name='+$F('caller_user_name')+'&call_id='+call_id);
	Element.toggle('CallHistoryInnerDiv');
}

function viewCallHistorySummary(caller, highlight){
	if(highlight){
		new Ajax.Updater('call-history', 'https://'+document.domain+'/webapp/calllog/ajax_backend.html?return=callHistorySummary&caller='+caller, {onComplete: highlightHistory});
	}else{
		new Ajax.Updater('call-history', 'https://'+document.domain+'/webapp/calllog/ajax_backend.html?return=callHistorySummary&caller='+caller);
	}
}
