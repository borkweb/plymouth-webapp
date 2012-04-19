<script type="text/javascript">
var anejf_changed = false, anejf_submitting = false;

// https://developer.mozilla.org/en/DOM/window.onbeforeunload
window.onbeforeunload = function(e) {
	var e = e || window.event;

	// skip if nothing changed, or we're submitting
	if( anejf_changed == false || anejf_submitting == true ) {
		return;
	}

	var msg = 'You have unsaved changes. Are you sure you want to leave the application form?';

	if( e ) {
		e.returnValue = msg;
	}

	return msg;
};

$(function(){

$('input, textarea').bind('keyup', function() { anejf_changed = true; });
$('input, select').bind('change', function() { anejf_changed = true; });

$('#anejf-application').submit(function(){
	anejf_submitting = true;
});

});
</script>
