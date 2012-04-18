/**
 * @preserve PSU behavior.js
 *
 * Copyright 2010 Plymouth State University
 */

// Cached jQuery object, mostly for use with delegate:
//     $.root.delegate('a', 'click', callback);
$.root = $(document);

// document.ready execution
// ------------------------

$(function(){
	// Konami code FTW.
	if( typeof(HOST) == "string" ) {
		var s = document.createElement('script');
		s.type='text/javascript';
		document.body.appendChild(s);
		s.src = HOST + '/webapp/my/js/konami.js';
	}

	if(typeof $.fn.dropDownMenu == 'function')
	{
		$('.webapp-nav ul:first').dropDownMenu({timer:500, parentMO: 'parent-hover', childMO: 'child-hover1'});
	}//end if

	$('.webapp-nav ul ul ul').parent().children('a').addClass('menu-expand');

	if( $.fn.tablesorter ) {
		$('table.sortable').tablesorter();
	}
});

// Add a nice animation to the "Return to Top" link in the webapp
// style footer.
$('#return-to-top').live('click', function(e){
	e.preventDefault();
	$('html, body').animate({scrollTop:0}, 'fast');
});

// Old top-level namespace for PSU functionality. Use `psu`, instead.
$.psu = $.psu || {};

// Top-level namespace for PSU functionality. Extend with your own app-specific functionality.
(function(){

this.psu = {
	host: null
};

if( typeof(HOST) == "string" ) {
	this.psu.host = HOST;
} else {
	this.psu.host = document.location.protocol + '//';

	if( ".dev.plymouth.edu" == document.location.host.substr(-17) ) {
		this.psu.host = this.psu.host + "www.dev.plymouth.edu";
	} else {
		this.psu.host = this.psu.host + "www.plymouth.edu";
	}
}

})();

// Add a message to the messages block.
//
//     $.psu.addMessage('Hey, something bad happened.', 'error');
$.psu.addMessage = function( msg, type ) {
	var li = $('<li/>').text(msg);

	switch(type) {
		case 'success': type = 'successes'; break;
		case 'warning': type = 'warnings'; break;
		case 'error': type = 'errors'; break;
		default: type = 'messages';
	}

	$('#webapp-avant-body .message-' + type).find('ul').append(li).end().show();
};

// Add a general purpose function we can use instead of console.log, which
// is not supported in Internet Explorer.
window.log = function(){
  log.history = log.history || [];   // store logs to an array for reference
  log.history.push(arguments);
  if(this.console){
    console.log( Array.prototype.slice.call(arguments) );
  }
};
