<script type="text/javascript">
var mtecd_changed = false, mtecd_submitting = false;

// https://developer.mozilla.org/en/DOM/window.onbeforeunload
window.onbeforeunload = function(e) {
	var e = e || window.event;

	// skip if nothing changed, or we're submitting
	if( mtecd_changed == false || mtecd_submitting == true ) {
		return;
	}

	var msg = 'You have unsaved changes. Are you sure you want to leave the application form?';

	if( e ) {
		e.returnValue = msg;
	}

	return msg;
};

$(function(){

$('input, textarea').bind('keyup', function() { mtecd_changed = true; });
$('input, select').bind('change', function() { mtecd_changed = true; });

$('#mtecd-application').submit(function(){
	mtecd_submitting = true;
});

});
</script>
