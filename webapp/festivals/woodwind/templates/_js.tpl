<script type="text/javascript">
var woodwind_changed = false, woodwind_submitting = false;

// https://developer.mozilla.org/en/DOM/window.onbeforeunload
window.onbeforeunload = function(e) {
	var e = e || window.event;

	// skip if nothing changed, or we're submitting
	if( woodwind_changed == false || woodwind_submitting == true ) {
		return;
	}

	var msg = 'You have unsaved changes. Are you sure you want to leave the registration form?';

	if( e ) {
		e.returnValue = msg;
	}

	return msg;
};

$(function(){

$('input, textarea').bind('keyup', function() { woodwind_changed = true; });
$('input, select').bind('change', function() { woodwind_changed = true; });

$('#woodwind-application').submit(function(){
	woodwind_submitting = true;
});

});
</script>
