$.psu = $.extend($.psu, {
	page_changed: false,

	ae_beforeunload: function() {
		if( $.psu.page_changed ) {
			return 'Click "Cancel" to return to the form and save your changes.';
		}
	},
	ae_changed: function() {
		$.psu.page_changed = true;
	},
	update_semester: function() {
		var $is_summer = $('#summer').val();

		if($is_summer == 1) {
			$('#event').hide();
		} else {
			$('#event').show();
		}
	},
	update_confirms: function() {
		var $editing = $('#editing').val();

		if($editing == 0) {
			$('#confirmed').show();
			$('#confirmed').siblings('span').hide();
			$('#confirmed_cert').show();
			$('#confirmed_cert').siblings('span').hide();
		} else {
			$('#confirmed').hide();
			$('#confirmed').siblings('span').show();
			$('#confirmed_cert').hide();
			$('#confirmed_cert').siblings('span').show();
		}
	}
});

$(document).ready(function(){
	$('body.ae-admin #editing').change( $.psu.update_confirms ).change();
	$('body.ae-admin #summer').change( $.psu.update_semester ).change();

	$('body.ae-student input, body.ae-student textarea').bind( 'keyup', $.psu.ae_changed );
	$('body.ae-student select').bind( 'change', $.psu.ae_changed );

	// enforce default text in #ceremony_needs box
	$('body.ae-student #ceremony_needs').blur(function(){
		if( $(this).val() == '' ) {
		$(this).addClass('default-text').val($.psu.SPECIAL_NEEDS_DEFAULT);
		}
	}).blur();

	// when #ceremony_needs gains focus
	$('body.ae-student #ceremony_needs').focus(function(){
		$(this).removeClass('default-text');

		if( $(this).val() == $.psu.SPECIAL_NEEDS_DEFAULT ) {
		$(this).val('');
		}
	});

	window.onbeforeunload=$.psu.ae_beforeunload;
	$('form').submit(function(){
		$.psu.page_changed = false;
		return true;
	});
});
