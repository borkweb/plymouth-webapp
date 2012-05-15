/**
 * @preserve PSU behavior.js
 *
 * Copyright 2010 Plymouth State University
 */
(function($){
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

/**
 * PSU data lazy loading.
 */
(function($){
	var queue = [],
		job_id = 0;

	$.fn.psu_lazyload = function() {
		if( false == $.fn.psu_lazyload.settings.endpoint ) {
			return this;
		}

		var ret = this.each(function(){
			if( $(this).hasClass( 'psu-lazyload-complete' ) ) {
				return;
			}

			queue.push(this);

			if( 0 === job_id ) {
				job_id = setTimeout( lazy_initiate, 500 );
			}
		});

		return ret;
	};

	$.fn.psu_lazyload.settings = {
		batch_count: 20,
		endpoint: false
	};

	function lazy_initiate() {
		var nodes = {},
			ids = [],
			$node;

		while( node = queue.shift() ) {
			$node = $(node);
			var id = $node.data('id');

			if( typeof nodes[id] === 'undefined' ) {
				nodes[id] = $node;
			} else {
				nodes[id] = nodes[id].add( $node );
			}

			ids.push(id);

			if( ids.length >= $.fn.psu_lazyload.settings.batch_count ) {
				break;
			}
		}

		if( ids.length > 0 ) {
			$.getJSON( $.fn.psu_lazyload.settings.endpoint, {id:ids}, lazy_populate.bind(nodes) );
		}
	}

	function lazy_populate( data, ts, xhr ) {
		var nodes_by_id = this;

		// immediately start a new job
		job_id = setTimeout( lazy_initiate, 0 );
		
		$.each(data, function(id, person_data){
			var $nodes = nodes_by_id[id];

			$nodes.addClass( 'psu-lazyload-complete' );

			$nodes.find('.lazy-field').each(function(){
				var field = $(this),
					type = field.data('type');

				field.text( person_data[type] );
			});
		});
	}

	$(function(){
		$('.psu-lazyload').psu_lazyload()
	});
})(jQuery);

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
})(jQuery);
