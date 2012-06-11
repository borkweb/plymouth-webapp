/*
 * mydnd -- channel drag and drop for myPlymouth
 *
 * By Matthew Batchelder (http://borkweb.com)
 * Copyright (c) 2010 Plymouth State Univeristy
 * Licensed under the MIT License: http://www.opensource.org/licenses/mit-license.php
 *
 * Version: 1.0
 */
;(function($){

$.mydnd = {
	host: BASE_URL,
	init: function(){
		// store the column selection to a mydnd var
		$.mydnd.columns = $('.column');

		// enable sorting on columns
		$.mydnd.columns.sortable({
			cancel: '.options',
			cursor: 'move',
			connectWith: '.column',
			handle: 'div.title',
			items: '.channel:not(.channel-lock-move)',
			opacity: 0.2,
			placeholder: 'channel-placeholder',
			tolerance: 'pointer',
			start: function(e, ui){
				// when dragging starts, prepare the columns for
			  // dropping.
				$.mydnd.prepDropCols(e, ui);
			},
			stop: function(e, ui){
				// when an element has been dropped, handle
				// the drop event
				$.mydnd.updateChannelLocation('channel_to_col', $(this), ui);
			}
		});

		// allow items to be dropped onto tabs
		$('#tabs li').droppable({
			accept: '.channel,.channel-info-body',
			hoverClass: 'drop-over-tab',
			drop: function(event, ui){
				// are they dragging from the channel list into their layout? (rather than moving
				// a channel already in the layout.)
				if(ui.draggable.hasClass('channel-info-body'))
				{
					// yes!  Hand off to the addChannel function for processing
					$.mydnd.addChannel(ui.draggable.parent(), $(this));
				}//end if
				else
				{
					// nope!  This channel already existed on the layout so
					// we have to update its position.  Pass to the updateChannelLocation
					// for processing
					$.mydnd.updateChannelLocation('channel_to_tab', $(this), ui);
				}//end else
			},
			tolerance: 'pointer'
		});

		// prepare the channel browser channels for dragging
		$('.channel-info-body').draggable({
			revert: true,
			revertDuration: 100,
			opacity: 0.2
		});

		// bind a click event to the channel browser's add link
		$('.add-channel').bind('click', function() {
			// user clicked an add-channel link.  Hand off to updateChannelLocation
			// for processing
			$.mydnd.addChannel($(this).parents('.channel-info'), $('#tabs li:first'));
			return false;
		});

	},
	addChannel: function(channel, tab){
		/**
		 * Handles the adding of new channels to the layout
		 */

		// cancel add if channel is null
		if(channel === null) return false;

		// grab the ids
		$channel_id = channel.attr('id');

		// error out if channel doesn't have an id
		if($channel_id === null) return false;

		// if the tab was not set, grab the first tab
		if(tab === null) tab = $('#tabs li:first');
		
		$tab_id = tab.attr('id');

		// error out if tab doesn't have an id
		if($tab_id === null) return false;

		var $url = $.mydnd.host + '/channel/add/'+$channel_id+'/'+$tab_id;

		// add the channel to the tab
		$.mydnd.save($url);

		// update some UI elements to give the user some feedback
		$('.add-channel', channel).replaceWith('<span class="add-channel">Added!</span>');
		
		var wrapper = '<ul class="message message-successes" />';
		if($('.channel-added', channel).length === 0)
		{
			channel.append('<ul class="message message-successes channel-added"></ul>');
		}//end if

		$('.channel-added', channel).append('<li>'+$('h4', channel).html()+' added to '+$('a',tab).html()+'!</li>');
	},
	fixTitleWidths: function(){
		$('.column.grid_6 .channel .title .primary.grid_11').addClass('grid_5').removeClass('grid_11');
		$('.column.grid_12 .channel .title .primary.grid_5').addClass('grid_11').removeClass('grid_5');
	},
	prepDropCols: function(event, ui){
		/**
		 * prepares columns for drag and drop by displaying
		 * a helper bar if a hidden column exists and expands
		 * a hidden column if a channel is dragged over it
		 */

		// determine if one of the columns is empty
		var $empty = $('#column_1 .channel').length === 0 ? 1 : ($('#column_2 .channel').length === 0 ? 2 : 0)

		if($empty > 0)
		{
			// yup.  a column is empty.  Create a drop bar
			$('<div class="drop-column drop-column-' +$empty+'"></div>').insertAfter('#column_2').css('minHeight', $('.webapp-body').height());
			$('.drop-column').droppable({
					tolerance: 'pointer',
					over: function(){
						// if a channel is dragged over the top of this hidden drop bar
						// adjust the grids of the columns so two columns display
						$('#column_1, #column_2').addClass('grid_6').removeClass('grid_12');
						$('#column_'+$empty).show().addClass('drop-shade');
						$.mydnd.fixTitleWidths();

						// refresh the column layout so sorting works as it should.  This
						// is a necessary step to reduce the bugginess of sorting during
						// element resize
						$.mydnd.columns.sortable('refresh');
					}
			});
		}//end if

		// add a class to the parent column to allow for column shading via theme
		if(ui.item.parent().find('.channel').length <= 1)
			ui.item.parent().addClass('drop-shade');

		// adjust the min height of the columns to be the height of the page's body
		// for easier sort handling
		$('.column').css('minHeight', $('.webapp-body').height());

		// make sure the helper object (the thing being dragged) is the appropriate
		// width
		ui.helper.width(ui.item.parent().width());
	},
	save: function(url){
		if(MY_DEFAULT_LAYOUT) {
			$.mydnd.blocked = true;
			$.blockUI({
				message: '<h1><img src="/webapp/my/templates/images/throbber.gif"/> Just a moment...</h1>'
			});

			$.get(url, $.mydnd.unblock);
		}//end if
		else {
			$.get(url);
		}//end else
	},
	unblock: function(data){
		if(data == 'user' && MY_DEFAULT_LAYOUT) {
			MY_DEFAULT_LAYOUT = false;
			history.go(0); // force a page reload

			// remain blocked so they don't move another channel
		}//end if

		else {
			$.unblockUI();
		}//end else
	},
	updateChannelLocation: function(dnd_event, target, ui){
		// prepare some basic variables
		var $url = $.mydnd.host + '/channel/move/';
		var $method = '';
		var $id = '';

		// detect event for appropriate handling
		if(dnd_event == 'channel_to_tab') {
			// the channel is being dragged to a new tab
			$method = 'tab';

			// grab the tab's id
			$id = target.attr('id').replace('tab-', '');

			// prepare the URL
			$url += ui.draggable.attr('id') + '/' + $method + '/' + $id;

			// remove the dragged item from the layout.  This is done
			// here as ui.draggable is not a valid object in the sortable
			// ui object
			ui.draggable.remove();	
		}//end if
		else if(dnd_event == 'channel_to_col') {
			// the channel is being dragged to a new location on the current tab

			// figure out what elements are around it
			var next = ui.item.next();
			var prev = ui.item.prev();

			if(next.length > 0)
			{
				// a channel follows the location this channel is being dragged to
				$method = 'before';
				$id = next.attr('id');
			}//end if
			else if(prev.length > 0)
			{
				// a channel precedes the location this channel is being dragged to
				$id = prev.attr('id');
				$method = 'after';
			}//end elseif
			else
			{
				// this channel is being dragged to an empty column
				$method = 'col';
			}//end if

			$url += ui.item.attr('id') + '/' + $method + '/';
			if($method == 'col') 
			{
				if(ui.item.parent().length > 0)
				{
					$url += ui.item.parent().attr('id').replace(/column_/, '');
				}//end if
				else
				{
					return;
				}//end else
			}//end if
			else
			{
				$url += $id;
			}//end else
		}//end elseif

		// communicate new location to the server
		$.mydnd.save($url);

		// remove shade class on all columns
		$('.column').removeClass('drop-shade').css('minHeight', 'auto');

		// if a column is empty, hide it.
		var $empty = $('#column_1 .channel').length === 0 ? 1 : ($('#column_2 .channel').length === 0 ? 2 : 0);
		if($empty > 0)
		{
			$('#column_1, #column_2').addClass('grid_12').removeClass('grid_6');
			$('#column_'+$empty).hide();
			$.mydnd.fixTitleWidths();
		}//end if
		
		// remove the helper column that displays when a column was originally hidden
		$('.drop-column').remove();
	}
};

})(jQuery);
