$(function() {
	var colorbox_maxwidth = 650;

	$('.message-available').each(function() {
		var id = $(this).attr('id');
		var fund_code = id.split('_');
		fund_code = fund_code[1];

		$(this).find('.view a').colorbox({
			rel: 'award-message',
			inline: true,
			href: '#award-message_' + fund_code,
			maxWidth: colorbox_maxwidth,
			current: 'Award Message {current} of {total}'
		});
	});

	$('.finaid-requirements .instructions').each(function(){
		var code = $(this).closest('tr').data('reqcode');

		$(this).colorbox({
			rel: 'requirement',
			inline: true,
			href: '#requirements_' + code,
			maxWidth: colorbox_maxwidth,
			current: 'Unsatisfied Requirements Instructions, {current} of {total}'
		});
	});

	var hide_empty_col = function( table, theClass ) {
		var col = table.find( theClass );
		if( col.find('a').length === 0 ) {
			col.hide();
		}
	};

	$('.finaid-requirements').each(function(){
		var table = $(this);

		hide_empty_col( table, '.finaid-req-inst' );
		hide_empty_col( table, '.finaid-req-form' );
		hide_empty_col( table, '.finaid-req-website' );
	});

	$('.award-term .details').tooltip({
		position: 'bottom center',
		delay: 0,
		opacity: 0.9,
		offset: [10, 0],
		relative: true
	});

	$('.award-sum').award_sum();

	$('body').PSUFeedback({});
});

// make sure colorbox opens all the way when triggered
$(document).bind('cbox_open', function(){
	$('#colorbox').show();
});

$.fn.award_sum = function() {
	this.each(function() {
		var sum = 0,
			selector = $(this).data('selector');

		$('.' + selector + ' .award-value').each(function() {
			sum += Number( $(this).data('value') );
		});
		$(this).text( String(sum).formatDollars() );
	});
};

String.prototype.formatDollars = function() {
	var dec_part = this % 100,
		int_part = String(Math.floor(this / 100));

	// commas every three digits: http://remysharp.com/2007/10/19/thousand-separator-regex/
	int_part = int_part.replace(/\d{1,3}(?=(\d{3})+(?!\d))/g, '$&,');

	if( dec_part < 10 ) {
		dec_part = '0' + dec_part;
	}

	// currency stuff
	str = '$' + int_part + '.' + dec_part;

	return str;
};
