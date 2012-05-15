/*! teacher-cert.js -- to be included in the footer. */
(function(){

var ui = {}, ajax = {}, schools = {};

// Cached templates
var data = teacher_cert.data;

// School display
ui.school = {
	cancel: function(e) {
		e.preventDefault();

		ui.school.set_template( this, '#school-ro' );
	},
	edit: function(e) {
		e.preventDefault();

		ui.school.set_template( this, '#school' );
	},
	save: function(e) {
		e.preventDefault();
		debugger;
	},
	set_template: function( node, template ) {
		var view = $.view(node),
			tmpl = view.tmpl;

		view.tmpl = template;
		view.render();
	},
	ready: function(e) {
		$('#school-' + data.school.id )
			.find('#school-wrapper')
			.link( data.school, '#school-ro' );

		// Student page "Edit" button
		$(document).on( 'click', '.school-edit', ui.school.edit );
		$(document).on( 'click', '.school-save', ui.school.save );
		$(document).on( 'click', '.school-cancel', ui.school.cancel );
	}
};

// Student search page
ui.search = {
	// track the active search updater
	active_updater: null,

	// the last updater that ran successfully
	complete_updater: null,

	// Don't let the form submit change the page.
	form: function(e) {
		e.preventDefault();
		ui.search.input.call(this, e);
	},

	input: function(e) {
		var $obj = $(e.currentTarget),
			$text = $obj.closest('form').find('.qry-students-q'),
			signature = $(e.currentTarget.form).serialize();

		e.preventDefault();

		// empty request; just blank the output table
		if( '' == $text.val() ) {
			ui.search.active_updater = null;
			ui.search.complete_updater = null;
			return ui.search.no_results(e);
		}

		var updater = new ui.search.updater(signature);
	},

	no_results: function() {
		$('.qry-container').addClass('s-noquery');
	},

	ready: function() {
		// "keyup" catches input. "click" catches the "x" (clear search) UI element in Chrome.
		$(document).on( 'keyup click', '.qry-students-q', ui.search.input );
		$(document).on( 'submit', '.qry-students', ui.search.form );
	},

	// New instances of ui.search.updater will be created to manage overlap
	// of possible ajax calls
	updater: function(sig) {
		var this_updater = this;
		ui.search.active_updater = this_updater;

		// don't run if the signature is the same (indicates the search
		// options have not changed)
		if( ui.search.complete_updater && sig == ui.search.complete_updater.sig ) {
			return;
		}

		this.sig = sig;

		setTimeout( function(){
			if( ui.search.active_updater == this_updater ) {
				this_updater.get_json();
			}
		}, 500 );
	}
};

ui.search.updater.prototype.get_json = function() {
	var this_updater = this;
	$.getJSON( 'api/students', data, function(data, ts, xhr) {
		if( ui.search.active_updater == this_updater ) {
			this_updater.update_table( data );
		}
	});
};

ui.search.updater.prototype.update_table = function( result ) {
	if( 0 == result.data.students.length ) {
		return ui.search.no_results();
	}

	$('.qry-container').removeClass('s-noquery');

	$('.qry-results tbody').html(
		$( '#qry-results-tmpl' ).render( result.data )
	);
};

// Student gate system and gate display
ui.student_gate = {
	cancel: function(e) {
		e.preventDefault();

		ui.student_gate.set_template( this, '#stu-gate-ro' );
	},
	edit: function(e) {
		ui.student_gate.set_template( this, '#stu-gate' );
	},
	save: function(e) {
		e.preventDefault();
		debugger;
	},
	set_template: function( node, template ) {
		var view = $.view(node),
			tmpl = view.tmpl;

		view.tmpl = template;
		view.render();
	},
	ready: function(e) {
		if( data.student_gate_system.gates ) {
			for( var i = 0, l = data.student_gate_system.gates.length; i < l; i++ ) {
				$( '#stu-gate-' + data.student_gate_system.gates[i].student_gate_id )
					.find('.stu-gate-wrapper')
					.link( data.student_gate_system.gates[i], '#stu-gate-ro' )
			}
		}//end if

		// Student page "Edit" button
		$(document).on( 'click', '.stu-gate-edit', ui.student_gate.edit );
		$(document).on( 'click', '.stu-gate-save', ui.student_gate.save );
		$(document).on( 'click', '.stu-gate-cancel', ui.student_gate.cancel );
	},
	view: function(e) {
		e.preventDefault();
		$(this).closest('.collapse').removeClass('collapse');
	}
};

// expose some things (more will probably end up here)
$.extend(true, teacher_cert, {
	school: {
		ready: ui.school.ready
	},
	search: {
		ready: ui.search.ready
	},
	student_gate: {
		ready: ui.student_gate.ready
	}
});

})();


//
// Much of the above relates to jsRender templates. Putting new, unrelated
// stuff below.
//

$(function(){
	$.datepicker.setDefaults({
		dateFormat: 'yy-mm-dd'
	});

	// checklist date fields: jQuery UI Datepicker
	$('input.ckl-date, .datepicker').attr('placeholder', 'MM/DD/YYYY').datepicker();

	$(document).on( 'click', '.stu-gate-view', function( e ) {
		e.preventDefault();
		var $box = $(this).closest('.box');
		if( $box.hasClass('collapse') ) {
			$box.removeClass('collapse');
			$(this).html( 'Hide' );
		} else {
			$box.addClass('collapse');
			$(this).html( 'View' );
		}//end else
	});
});

$(document).on( 'click', '.stu-delete-gs', function(e){
	if( ! confirm( "Are you sure you wish to close this gate system record? It will be marked as incomplete.") ) {
		e.preventDefault();
	}
});

$(document).on( 'click', '.show-siblings', function(e){
	e.preventDefault();
	
	var $obj = $(e.currentTarget),
		$sib = $obj.siblings('.collapse');
	
	$obj.fadeOut(function(){
		$sib.fadeIn();
	});
});
