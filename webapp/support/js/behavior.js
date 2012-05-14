$(function(){
	if( $('#ipdata').length > 0 ) {
		var browser_text = navigator.userAgent;
		$('#ipdata .browser span').append( browser_text ).parent().show();

		var $misc = $('#ipdata .misc ul');
		$misc.append('<li>Cookies are ' + (navigator.cookieEnabled ? '<span class="positive">Enabled</span>' : '<span class="negative">Disabled</span>') + '</li>' );
		$misc.append('<li>' + navigator.platform + ' is your Platform</li>' );
		if( $.trim( $misc.html() ) != '' ) {
			$misc.parent().show();
		}//end if
	}//end if
});
