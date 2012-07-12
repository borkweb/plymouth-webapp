
function redirect(url){
	document.location = url;
}

$jQuery(document).ready(function($){
	$jQuery('.call-age-status').popover({
		placement: 'left'
	});

	$jQuery('.box .replace-toggle').on('click', function(e) {
		e.preventDefault();
		$jQuery(this).next().show();
		$jQuery(this).remove();
	});
	$jQuery('#search_string').focus();
	$jQuery('#its-group-help').colorbox({
		width: 1000,
		height: 800
	});

	$jQuery(document).delegate('.print-balance a', 'click',function(){
		$jQuery('.print-funds .error').hide();
		$jQuery('.print-funds .add-funds').slideDown('fast');
		return false;
	});
	
	$jQuery(document).delegate('.add-funds button', 'click',function(){
		$jQuery('.add-funds').hide();
		$jQuery('.add-funds-throbber').show();
		
		var amount = $jQuery('.add-funds select').val();
		var pidm = $jQuery('#caller_pidm').val();
		
		$jQuery.get( BASE_URL + '/print_balance.php?pidm=' + pidm + '&fund_increase=' + amount + '&action=update', function(data){
			data = $jQuery.trim(data);
			$jQuery('.add-funds-throbber').slideUp('fast');
			if(data == 'no_record' || data == 'invalid_privs' || data == 'too_small') {
				$jQuery('.add-funds-' + data).slideDown('fast');
			}//end if
			else {
				var updated_value = '$' + amount + '.00';
				$jQuery('.add-funds-success span').html(updated_value.replace(/\$\-/,'-$'));
				$jQuery('.add-funds-success').slideDown('fast');
				$jQuery('.print-balance .balance').html(data);
			}//end esle
		});
	});

	// make the Open Call tables clickable
	$jQuery(document).delegate('#open_calls_main_div .call', 'click', function(){
		var $link = $jQuery('.view', this).attr('href');
		$jQuery(this).closest('table').find('.highlight').removeClass('highlight');
		$jQuery(this).addClass('highlight');
		document.location = $link;
	});

	////// BEGIN handle Ticket Checklists
	function getValue( $el, depth ) {
		var val = $el.val();
		var append_text = '';
		var temp_value = '';

		if( $el.is('input[type=radio]:not(:checked)') ) {
			return append_text;
		}

		if( $.trim(val) != '' ) {
			var $label = $el.siblings('label');
			var rel = $label.attr('rel');
			var label_text = rel ? rel : $label.html();
			append_text = append_text + label_text + "  " + val;	

			var $extra = $el.siblings('.sub:visible').children('li select,li textarea,li input');
			if( $extra.length > 0 ) {
				temp_value = getValue( $($extra.get(0)), depth+1 );

				if( $.trim( temp_value ) !== '' ) {
					append_text = append_text + "\r";

					for(var i = 0; i < depth; i++) {
						append_text = append_text + "-";
					}//end for

					append_text = append_text + " " + temp_value;
				}//end if
			}//end if
		}//end if

		return append_text;
	}//end get Value

	$(document).on('click', '#change_call_status', function(){
		console.log($('#call_status'));
		
	});
	$('#new_call,#edit_call').bind('submit', function(){
		console.log(event);
		var append_text = '';
		var $details = $('#problem_details');
		var details = $details.val();
		var temp_value = '';

		$('#checklists ul:visible li > select,#checklists ul:visible li > input,#checklists ul:visible li > textarea').each(function(){
			temp_value = getValue( $(this), 1 );
			if( append_text !== '' && temp_value) {
				append_text = append_text + "\r";
			}//end if
			append_text = append_text + temp_value;
		});

		if( $.trim( append_text ) != '' ) {
			append_text = "*****************\rTicket Details (" + $('select[name=checklist] option:selected').html() + "):\r------------------\r" + append_text + "\r*****************";
			if( $.trim( $details.val() ) != '' ) {
				append_text = "\r\r" + append_text;
			}//end if
		} else if( $('#new_call').length > 0 ) {
			if( !confirm( 'You haven\'t answered the provided questions about your ticket. Are you sure you wish to submit the call?') ) {
				return false;
			}//end if
		}//end else

		if( $.trim( details ) == '' ) {
			if( !confirm('You have not filled out a problem description. Do you still want to submit the call?') ) {
				return false;
			}//end if
		}//end if

		$details.val( $details.val() + append_text );
		return true;
	});

	$('#checklists select').bind('change', function(){
		var select = $(this).val();
		$(this).siblings('.sub').each(function(){
			var $extra = $(this);
			var rel = $extra.attr('rel');
			if( rel !== undefined ) {
				try{
					rel = rel.split('|');
				} catch( e ) {
					rel = [ rel ];
				}
			} else {
				rel = false;
			}//end else

			if( select && (rel === false || $.inArray(select, rel) !== -1) ) {
				$extra.slideDown('fast');
			} else {
				$extra.slideUp('fast');
			}//end else
		});
	});
	/////// end handle ticket checklists

	$jQuery('body').delegate('#call-history', 'hover', function() {
		setTimeout( add_focused_history, 250 );
	});

	$jQuery('body').delegate('#call-history', 'mouseleave', function() {
		setTimeout( remove_focused_history, 500 );
	});
});

function add_focused_history() {
	if( $jQuery('#call-history').is(':hover') ) {
		$jQuery('#call-history').addClass('focused-block');
	}//end if
}//end add_focused_history

function remove_focused_history() {
	if( ! $jQuery('#call-history').is(':hover') ) {
		$jQuery('#call-history').removeClass('focused-block');
	}//end if
}//end add_focused_history
