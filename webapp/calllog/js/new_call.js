function KeyCheck(e){
	var KeyID = (window.event) ? event.keyCode : e.keyCode;
	if (KeyID == "13"){ //checks for Carrige Return AKA Enter Key
		searchUser(); 
	}
}

function updateOpenCalls(){
	new Ajax.PeriodicalUpdater('main-open-calls', 'https://'+document.domain+'/webapp/calllog/ajax_backend.html?return=get_employee_group_info', {frequency:30});
}

function searchUser(){ //If you change this change the function name above it
	if ($F('search_string') != ""){
		Element.show('search_results_loading');
		new Ajax.Updater('main-search-results', 'https://'+document.domain+'/webapp/calllog/ajax_backend.html?return=searchResults&search_string='+$('search_string').value+'&search_type='+$('search_type').value, {onComplete: hideLoadingContent});
		Element.hide('main-search-results');
	}else{
		alert("Please Enter A Search Term");
	}
}

function delete_saved_data(caller){
	new Ajax.Updater('recovered_information', 'https://'+document.domain+'/webapp/calllog/ajax_backend.html?return=deleteSavedData&caller='+caller);
}

function switchPageSearchCalls(which){
	new Ajax.Updater('main-search-results', 'https://'+document.domain+'/webapp/calllog/ajax_backend.html?return=searchResults&search_string='+$F('search_string')+'&search_type='+$F('search_type')+'&switch='+which, {onComplete: hideLoadingContent});
}

function hideLoadingContent(){
	Element.hide('search_results_loading');
	Element.show('main-search-results');
	$jQuery("#calllog-search").tablesorter();
}

function view_open_calls(option, group, type){
	Element.hide('search_results_loading');
	Element.hide('main-new-call');
	Element.show('open_calls_loading');
	new Ajax.Updater('open_calls_loading', 'https://'+document.domain+'/webapp/calllog/ajax_backend.html?return=openCalls&option='+option+'&group='+group+'&open_call_type='+type);
}

function submit_new_call(){
	Element.hide('open_calls_loading');
	Element.hide('search_results_loading');
	Element.show('main-new-call');

}

function selectNewUser(caller, search_string, search_type){
	new Ajax.Updater('main-new-call', 'https://'+document.domain+'/webapp/calllog/ajax_backend.html?return=selectNewUser&caller='+caller+'&search_string='+search_string+'&search_type='+search_type);
}

function sortField(option, group, type, sort_by){
	new Ajax.Updater('open_calls_loading', 'https://'+document.domain+'/webapp/calllog/ajax_backend.html?return=openCalls&option='+option+'&group='+group+'&open_call_type='+type+
	'&sort_by='+sort_by);
}
