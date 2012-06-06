$.root = $.root || $(document);

var apejs = {
	ajaxify: function( obj )
	{
	 	var $a = $(this);
		var href = $a.attr('href') + '&method=js';

		var msg = $a.data("confirmation");

		if( msg && !confirm(msg) )
			return false;

		$.getJSON( href, function(data, status){
			apejs.json2growl(data);

			if( typeof(obj.data.callback) == 'function' )
			{
				obj.data.callback(data, status);
			}
		});

		return false;
	},
	get_lock_reason: function(e) {
		$o = $(e.currentTarget);

		var type = $o.hasClass('do-support-unlocked') ? 'unlocking' : 'locking';
		var reason = prompt("Please enter a reason for " + type + " this user's account.");

		// "null" means "cancel" was pressed. blank string "" means "ok" was pressed without any input.
		if( reason === null ) {
			return false;
		}

		if( reason ) {
			var href = $o.attr('href');
			$o.attr( 'href', href + '&reason=' + escape(reason) );
		}

		return true;
	},
	json2growl: function( data )
	{
		var theme;
		if(data.status == 'success')
		{
			theme = 'messages';
		}
		else
		{
			theme = 'errors';
		}

		$.jGrowl(data.message, {theme: theme});
	},
	list2ul: function(list)
	{
		var item;
		var $ul = $('<ul/>');

		for(var i = 0; i < list.length; i++)
		{
			item = list[i];
			$('<li></li>').text(item).appendTo($ul);
		}

		return $ul;
	},
	schema_datacol_init: function()
	{
		$('#col1').datacolLoading();

		$.getJSON(BASE_URL + '/ajax/idm-attributes.php', {}, function(data, status){
			var $ul = apejs.list2ul(data);
			$ul.children('li').bind('click', apejs.select_role);
			$('#col1').empty().append($ul);
		});
	},
	select_role: function(e)
	{
		$('#col2').datacolLoading();

		var $target = $(this);
		$target.parent().children().removeClass('selected');
		$target.addClass('selected');

		$.getJSON(BASE_URL + '/ajax/idm-attributes.php', {attribute: $target.text()}, function(data, status){
			var $ul = apejs.list2ul(data);
			$('#col2').empty().append($ul);
		});
	},
	distauthz_adduser_handle: function(e)
	{
		// "return"
		if(e.keyCode == 13)
		{
			apejs.distauthz_adduser();
		}
		else
		{
			return true;
		}
	},
	distauthz_adduser: function(e)
	{
		// lock the input form
		$('#adduser').attr('disabled', 'disabled');
		$('#adduser-throbber').show();

		// add the permission to the user
		var args = {
			action: 'add',
			attribute: apejs.selected_attribute,
			type: apejs.selected_type,
			username: $('#adduser').val(),
			start_date: $('#col2 select[name=start_Year]').val()+'-'+$('#col2 select[name=start_Month]').val()+'-'+$('#col2 select[name=start_Day]').val(),
			end_date: $('#col2 select[name=end_Year]').val()+'-'+$('#col2 select[name=end_Month]').val()+'-'+$('#col2 select[name=end_Day]').val(),
			reason: escape($('#col2 .reason').val()),
			method: 'js'
		};

		var url = BASE_URL + '/actions/idm.php';
		$.getJSON(url, args, apejs.distauthz_adduser_json);
	},
	distauthz_adduser_json: function(data, status)
	{
		apejs.json2growl(data);

		$('#adduser-throbber').hide();
		$('#adduser').val('').removeAttr('disabled').blur().focus();

		// update the list only if the view has not changed
		if(apejs.selected_type == data.type && apejs.selected_attribute == data.attribute && data.status == 'success')
		{
			var li = apejs.distauthz_person2li(data).css('background-color', 'orange');
			$('.adduser').after(li);
			li.animate({backgroundColor:'white'}, 2000);
		}
	},
	distauthz_init: function()
	{
		$('#col1 .clickable').bind('click', apejs.distauthz_clickcol1);
	},
	distauthz_clickcol1: function(e)
	{
		var li = $(this);
		var url = BASE_URL + '/actions/authz-col2.php';

		var rel = li.attr('rel').split("--");
		apejs.selected_type = rel[0];
		apejs.selected_attribute = rel[1];

		li.parent().children().removeClass('selected').end().end().addClass('selected').appendTo('test');
		$('#col2').datacolLoading();
		$('#col3').empty();

		$.getJSON(url, {attribute:apejs.selected_attribute, type:apejs.selected_type}, apejs.distauthz_populatecol2);
	},
	distauthz_person2li: function(person)
	{
		li = $('<li/>').attr('rel', person.pid)
			.bind('click', apejs.distauthz_clickcol2)
			.text(person.last_name + ", " + person.first_name + " (" + person.username + ")");

		if(person.source == 'ape')
		{
			li.addClass('clickable');
		}
		else
		{
			li.addClass('locked');
		}

		return li;
	},
	distauthz_populatecol2: function(data, status)
	{
		var li, person;

		var ul = $('.add_user_template > ul').clone();
		$('#col2').empty().append(ul);
		$('#col2 .reason_link').click(function(){
			$('#col2 .reason').toggle();
			return false;
		});

		for(var i = 0; i < data.length; i++)
		{
			li = apejs.distauthz_person2li(data[i]);
			$('#col2 ul').append(li);
		}

		$('.adduser').bind('keydown', apejs.distauthz_adduser_handle);
		$('#col2 button').bind('click', apejs.distauthz_adduser);
	},
	distauthz_clickcol2: function(e)
	{
		$('#col3').datacolLoading();

		var $li = $(this);
		apejs.selected_pidm = $li.attr('rel');

		var url = BASE_URL + "/actions/authz-col3.php";

		var args = {
			attribute:apejs.selected_attribute,
			type:apejs.selected_type,
			pidm:apejs.selected_pidm
		};

		$.get(url, args, apejs.distauthz_populatecol3, 'html');
	},
	distauthz_populatecol3: function(data, status)
	{
		$('#col3').empty().append(data).ajaxify(apejs.distauthz_deleted);
	},
	distauthz_deleted: function(data, status)
	{
		if( apejs.selected_type == data.type && apejs.selected_attribute == data.attribute )
		{
			$('#col2 li[rel=' + data.pidm + ']').remove();

			if( apejs.selected_pidm == data.pidm )
			{
				$('#col3').empty();
				apejs.selected_pidm = null;
			}
		}
	},
	get_connect_linkacct: function()
	{
		var $o = $(this);

		var email = prompt("Please enter the user's new email, or leave blank to only sync username.");

		// "null" means "cancel" was pressed. blank string "" means "ok" was pressed without any input.
		if( email === null ) {
			return false;
		}

		if(email)
		{
			$o.attr('href', $o.attr('href') + '&email=' + encodeURIComponent(email));
		}//end if

		return true;
	},
	get_reset_email: function()
	{
		var $o = $(this);

		var email = prompt("Please enter the user's new email, or leave blank to only sync username.");

		// "null" means "cancel" was pressed. blank string "" means "ok" was pressed without any input.
		if( email === null ) {
			return false;
		}

		if(email)
		{
			$o.attr('href', $o.attr('href') + '&email=' + encodeURIComponent(email));
		}//end if

		return true;
	},
	sl_error_code_keyup: function(e) {
		var value = $(e.target).val();

		var $rows = $('#sl-error-codes tr'), $row, thisVal;

		for( var i = 1; i < $rows.length; i++ ) {
			$row = $( $rows.get(i) );
			thisVal = 1<<(i-1);

			if( value & thisVal ) {
				$row.addClass('highlight');
			} else {
				$row.removeClass('highlight');
			}
		}
	},
	show_copy_roles: function(e) {
		e.preventDefault();

		$('.copy-roles-dialog').dialog({
			title: 'Current user roles',
			width: 500,
			height: 300
		});

		$('.copy-roles-dialog textarea').focus().select();
	}
};

jQuery.fn.datacolLoading = function()
{
	this.empty().append('<ul><li><i>Loading...</i></li></ul>');
	return this;
};

jQuery.fn.ajaxify = function(callback)
{
	this.find('.ajaxify').bind('click', {callback:callback}, apejs.ajaxify);
	return this;
};

$(document).ready(function(){
	if( checklist_admin && checklist_admin.sort_lists && $('#checklist_admin').length > 0 ) {
		checklist_admin.sort_lists();
	}
	$.jGrowl.defaults.position = 'bottom-right';
	$('#search input[name=identifier]').focus();

	$("#delete-vista").data("confirmation", "Are you sure you want to delete the Vista profile?");
	$("#delete-roaming").data("confirmation", "Are you sure you want to delete the roaming profile?");
	
	$('a.list-remove').click(function(){
		return confirm('Are you sure you wish to remove that person?');
	});

	// only make the hardware fields editable with certain roles
	if( typeof( jQuery.fn.editable ) == 'function' && AUTHZ.permission.ape_hardware ) {
		$('body.hardware .edit').editable( BASE_URL + '/actions/hardware-save.php', {
			indicator: 'Saving...',
			placeholder: '<em>none</em>',
			width: 150,
			submitdata: { ajax: 1 }
		});
	}

	$('body.hardware .delete').click(function(){
		return confirm('Click "OK" to continue deleting this record.');
	});

	$('#message').fadeOut(4000);

	$('.ape-section > h3, .ape-section > h4').click(function(){
		var $el = $(this);
		var $parent = $(this).parent();
		var theID = $parent.attr('id');
		var param_url = '/webapp/portal/param.php?action=set&param=' + theID + '&value=';

		if( theID ){
			if( $parent.hasClass('ape-section-hidden') ){
				$.get(param_url + '1');
				$parent.removeClass('ape-section-hidden');
			} else {
				$.get(param_url + '0');
				$parent.addClass('ape-section-hidden');
			}//end else
		}//end if
	});

	$('.do-support-locked').bind('click', apejs.get_lock_reason);
	$('.do-support-unlocked').bind('click', apejs.get_lock_reason);
	$('.reset-email').bind('click', apejs.get_reset_email);
	$('.connect-linkacct').bind('click', apejs.get_connect_linkacct);
	
	$('a.retrieve').click(function(){
		var href = $(this).attr('href');
		var the_id = $(this).attr('id');
		$('#'+the_id+'_out').load(href, function(){
			$('a#'+the_id).hide();
			setTimeout('show("'+the_id+'");',60000);
		});
		return false;
	});

	$('.roles .banner_myp li, .roles .ad li').popover({placement: 'left'});
	$('.idmrole, .idmchild').popover({placement: 'left'});
	$('.username-history').popover();
	$('.tooltip-trigger').tooltip();

	// The above .tooltip() removes the element's title, so we're free to find all unhandled titles here.
	//$('[title]').addClass('title-tooltip').tooltip().contents().wrap('<span class="inherit"/>');

	$('.more').click(function(){
		$(this).siblings('select').attr({size:10, multiple:'multiple'}).end().hide();
	});

	$('.ajaxify').bind('click', apejs.ajaxify);

	var $lh;

	$.root.delegate('.limited', 'mouseover', function(){
		var $o = $(this);
		var pos = $o.offset();

		if( $o.text().trim() == '' ) {
			return;
		}

		$lh.css({
			top: pos.top - 6,
			left: pos.left - 6
		}).text( $o.text() );
		$lh.show();
	});
	
	$('body').append('<div id="limited-hover" style="display:none;">asdfasdf</div>');
	$lh = $('#limited-hover');
	$lh.mouseleave( function() { $(this).hide(); } );

	$('#sl-error-code').bind('keyup', apejs.sl_error_code_keyup);

//checklist binding for 1 checkbox selected per li.checklist
	$( 'ul.checklist li.checklist_item input' ).click(function(){
			$(this).parent().siblings('.check').find('input:checked').removeAttr('checked');
	});
	$('.has_datepicker').datepicker(); 
	$('table.sortable').tablesorter({
		sortList: [[0,1]]
	});
});
function show(the_id)
{
	$('a#'+the_id).show();
	$('#'+the_id+'_out').html('');
}//end show

$.root.delegate( '.copy-roles', 'click', apejs.show_copy_roles );
