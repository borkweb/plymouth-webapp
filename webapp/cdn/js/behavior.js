psu.cdn = {
	files_cb: function( data, textStatus ) {
	},

	files_submit: function(e) {
		var $form = $(this), formData = $form.serialize();
		$.post( $form.attr('action'), formData, psu.cdn.files_cb );
		return false;
	},

	init: function() {
		$('#manage-files input:checkbox').shiftSelect();
	},

	path_submit: function() {
		var $form = $(this), p = $form.find('[name=p]').val(), url = $form.attr('action');

		if( '/' != p.substr(0,1) ) {
			p = '/' + p;
		}

		document.location.href = url + p;

		return false;
	},

	select: function() {
		var $link = $(this), theId = $link.attr('id');

		$('#manage-files input:checkbox').removeAttr('checked').change();

		if( theId == 'sel-all' ) {
			$('#manage-files input:checkbox').attr('checked', 'checked').change();
		} else if( theId == 'sel-db' ) {
			$('#manage-files tr.cdn-db input:checkbox').attr('checked', 'checked').change();
		} else if( theId == 'sel-nodb' ) {
			$('#manage-files tr:not(.cdn-db) input:checkbox').attr('checked', 'checked').change();
		} else if( theId == 'sel-nofs' ) {
			$('#manage-files tr:not(.cdn-fs) input:checkbox').attr('checked', 'checked').change();
		}

		return false;
	},

	// Handler for row click event.
	select_row_click: function(e) {
		// Don't double-run if user was clicking 
		if( e.target.tagName == 'INPUT' ) {
			return true;
		}

		var $input = $(this).closest('tr').find(':checkbox');

		if( $input.is(':checked') ) {
			$input.removeAttr('checked').change();
		} else {
			$input.attr('checked', 'checked').change();
		}
	},

	// Handler for the row checkbox click event.
	select_row_checkbox_clicked: function(e) {
	},

	// Handler for the row checkbox change event.
	select_row_checkbox_changed: function(e) {
		psu.cdn.row_highlight( $(this) );
	},

	// (Un)highlight the current row depending on the state of its checkbox.
	row_highlight: function($input) {
		$tr = $input.closest('tr');
		if( $input.is(':checked') ) {
			$tr.addClass('highlight');
		} else {
			$tr.removeClass('highlight');
		}
	}
};

// http://clownsinmycoffee.net/2008/04/18/add-shift-select-with-jquery/
jQuery.fn.shiftSelect = function() {
	var checkboxes = this;
	var lastSelected;
	jQuery(this).click( function(event) {
		if ( !lastSelected ) {
			lastSelected = this;
			return;
		}

		if ( event.shiftKey ) {
			var selIndex = checkboxes.index(this);
			var lastIndex = checkboxes.index(lastSelected);
			/*
			 * if you find the "select/unselect" behavior unseemly,
			 * remove this assignment and replace 'checkValue'
			 * with 'true' below.
			 */
			var checkValue = lastSelected.checked;
			if ( selIndex == lastIndex ) {
				return true;
			}

			var end = Math.max(selIndex,lastIndex);
			var start = Math.min(selIndex,lastIndex);
			for(i=start;i<=end;i++) {
				checkboxes[i].checked = checkValue;
				$( checkboxes[i] ).change();
			}
		}
		lastSelected = this;
	});
};

$( psu.cdn.init );

$('#manage-files tbody tr').live('click', psu.cdn.select_row_click);
$('#manage-files tbody tr :checkbox').live('click', psu.cdn.select_row_checkbox_clicked);
$('#manage-files tbody tr :checkbox').live('change', psu.cdn.select_row_checkbox_changed);
$('#view-path').live('submit', psu.cdn.path_submit);
$('#cdn-actions a').live('click', psu.cdn.select);

// TODO: implement an ajax submit handler for the update form
//$('#manage-files').live('submit', psu.cdn.files_submit);
