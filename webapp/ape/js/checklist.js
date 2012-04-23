var checklist_admin = {
	toggle_details: function( e ) {
		var $head = $(this);
		var $parent = $(this).parent();
		var $el = $head.next( '.checklist_items' );

		$parent.toggleClass('open');
		$el.toggle();
	},
	email: function(e) {
		e.preventDefault();
		if( confirm( 'Do you really want to email the people responsible for filling out the '+$(this).siblings('.grid_4').text()+' section of the '+$( '.title .primary' ).text()+' checklist?' ) ) {
			var $reminder_sent = $(this).parents('.category').find('.reminder-sent span');
			$reminder_sent.html('<img src="/images/icons/16x16/animations/throbber.gif"/>').load( $(this).attr('href') );
		}
	},
	view: function( e ) {
		e.stopPropagation();
	}
};
$.root.delegate('#checklist_admin .head a', 'click', checklist_admin.view );
$.root.delegate('#checklist_admin .head', 'click', checklist_admin.toggle_details );
$.root.delegate('#checklist_admin .category a', 'click', checklist_admin.email );

$(function(){
	$('#checklist_admin .head:first').click();
});
