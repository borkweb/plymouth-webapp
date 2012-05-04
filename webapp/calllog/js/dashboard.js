function showAuthed(){
	new Ajax.Updater('showGroupUsersDiv', 'https://'+document.domain+'/webapp/calllog/ajax_backend.html?return=showGroupUsers&group_number='+group_number);
}