$(function(){
	var s = document.createElement('script');
	s.type='text/javascript';
	document.body.appendChild(s);
	s.src=HOST + '/webapp/my/js/konami.js';
});

/* DELORT LATER */
var JQ = $;

/* search vars!!!!!!!!!!!!!!!!!!!!!!!!!!!!! !!@! */
var googleSearchIframeName = "results_005322158811873917109:eb5xtxv98mg";
var googleSearchFormName = "searchbox_005322158811873917109:eb5xtxv98mg";
var googleSearchFrameWidth = "100%";
var googleSearchFrameborder = 0;
var googleSearchDomain = "google.com";
var googleSearchPath = "/cse";

var rss = {
	echo: function(text,id)
	{
		$('#'+id).html(text);
		var ul_id=$('#'+id+' ul.rss').attr('id');
		
		$('#'+ul_id+' li:has(div.rss-body) h3 a:not(.toggle)').each(function(){
			var selector = '#'+ul_id+' li:has(div.rss-body) h3 a:not(.toggle)';
			var p=$(this).parent().siblings('.rss-body').slice(0,1);
		});
		
		var column_width = $('#' + id).find('.rss-body').width();
		
		$('#' + id + ' .rss img').each(function(i){
			var img_width = $(this).width();
			
			if(img_width  > column_width){
				$(this).width(column_width);
				var height = Math.round($(this).height() / (img_width / column_width));
				$(this).height(height);
				$(this).closest('.wp-caption').width(column_width);
			}
		});

		$('#'+ul_id+' li:has(div.rss-body) h3 a.toggle').click(function(){
			var p=$(this).parent().siblings('.rss-body').slice(0,1);
			if($(p).css('display')=='none')
			{
				$(p).slideDown('fast').parent().attr('class','expanded');
			}
			else
			{
				$(p).slideUp('fast').parent().attr('class','contracted');
			}

			return false;
		});
	},
	load: function(url,render_id,params)
	{
		document.write('<div id="'+render_id+'"><div style="text-align:center;"><img src="'+my.throbber+'" alt="Loading" class="throbber"/></div></div>');
		var default_options={num:5,expand:1,summary:0,url:escape(url)}
		$.extend(default_options,params);
		var feed_url='https://www.plymouth.edu/webapp/portal/channel/rss/?id='+render_id;
		$.each(default_options,function(param,value){
			feed_url=feed_url+'&'+param+'='+value;
		});

		my.load(feed_url);
	}
};

var bookmark = {
	host: HOST + '/webapp/go/go',
	init:function(){
		$('.add-bookmark').click(function(){
			$('#add-bookmark').slideToggle('fast');
			return false;
		});

		$('#add-bookmark input[type=reset]').click(function(){
			$('#add-bookmark').slideToggle('fast');
			return false;
		});

		$('#add-bookmark').submit(function(){
			my.load(bookmark.host+'/sidebar/bookmarks.html?url='+$('#add-bookmark input[name=url]').val()+'&title='+$('#add-bookmark input[name=title]').val());
			$('#add-bookmark').slideToggle('fast');
			return false;
		});

		$('.add-folder').click(function(){
			$('#add-folder').slideToggle('fast');
			return false;
		});

		$('#add-folder input[type=reset]').click(function(){
			$('#add-folder').slideToggle('fast');
			return false;
		});

		$('#add-folder').submit(function(){
			my.load(bookmark.host+'/sidebar/bookmarks.html?folder_title='+$('#add-folder input[name=title]').val());
			$('#add-folder').slideToggle('fast');
			return false;
		});
		
		$('.edit-mode').click(function(){
			$('#widget-bookmarks .bookmark-trash').slideToggle('fast');
			return false;
		});
		
		$('#edit-bookmark input[type=reset]').click(function(){
			$('#edit-bookmark').slideToggle('fast');
			return false;
		});
		
		$('#edit-bookmark').submit(function(){
			my.load(bookmark.host+'/sidebar/bookmarks.html?edit&bookmark_id='+id[1]+'&url='+$('#edit-bookmark input[name=url]').val()+'&title='+$('#edit-bookmark input[name=title]').val());
			$('#edit-bookmark').slideToggle('fast');
			return false;
		});
		
		$('#widget-bookmarks li.treeItem.bookmark a').click(function(){
			if( ! $(this).parent().hasClass('edit-mode') ){
				return true;
			}
			if( ! $('#edit-bookmark').show() ){
				$('#edit-bookmark').slideToggle('fast');
			}
			id = $(this).parent().attr('id').split('-');
			url = $(this).parent().children('a').attr('href');
			title = $(this).parent().children('a').text();
			$('#edit-bookmark input[name=id]').val(id[1]);
			$('#edit-bookmark input[name=url]').val(url);
			$('#edit-bookmark input[name=title]').val(title);
			return false;
		});

		/* begin function to bind clicks in jstree with following behavior:
		 * if the user clicks a folder, toggle it, otherwise we assume
		 * it's a bookmark and open the url in a new window
		*/
		$("#bookmarks").bind("select_node.jstree", function(event,data){	
			url = data.rslt.obj.children("a").attr("href");
			if ( data.rslt.obj.hasClass("bookmark" ) ) {
				window.open(url);
			} else {
				data.inst.toggle_node(data.rslt.obj);
				return false;
			}
		}); //end click binding for bookmarks
	
		$("#bookmarks").bind("move_node.jstree", function(event, data){
			bookmark_id = $(data.rslt.o).attr('id').split('-');
			folder_id = $(data.rslt.o).parent().parent().attr('id').split('-');
			if ( $(data.rslt.o).parent().parent().attr('rel') == 'folder' ) {
				my.load(bookmark.host+'/sidebar/bookmarks.html?move&bookmark_id='+bookmark_id[1]+'&parent_id='+folder_id[1]);
			} else if ( $(data.rslt.o).parent().parent().attr('rel') == 'root' ){
				my.load(bookmark.host+'/sidebar/bookmarks.html?move&bookmark_id='+bookmark_id[1]+'&parent_id=0');
			}	else {
				alert('You cannot move a bookmark into another bookmark. It will cause an unfortunate rift in time and space.');
				return false;
			}
		});

		$(function () { 
			var bookmark_dragging = false;
			$.jstree._themes = HOST + '/js/jquery-plugins/tree_themes/';
			$("#bookmarks").jstree({
					"types" : {
						"valid_children" : ["folder"],
						"types" : {
							// the default type
							"folder" : {
								"max_children"	: -1,
								"max_depth"		: 2,
								"valid_children": ["file"]
							},
							"file" : {
								"icon" : {
									"image" : bookmark.host+"/images/file.png"
								}
							}
						}
					},
					"dnd" : {
						"drop_finish" : function (data) { 
							var dropped = data.o;
							if(confirm('Are you sure you wish to delete this?'))
							{
								var id = dropped.attr('id').split('-');
								var classes= dropped.attr('class').split(' ');
								var is_folder=$.grep(classes,function(n,i){
									if(n=='bookmark-folder' || n=='bookmark-folder-open') return true;
									else return false;
								});
								if(is_folder.length>0)
								{
									my.load(bookmark.host+'/sidebar/bookmarks.html?delete&folder_id='+id[1]);
								}
								else
								{
									my.load(bookmark.host+'/sidebar/bookmarks.html?delete&bookmark_id='+id[1]);
								}
								dropped.remove();
							}	
						},
						"drop_check" : function (data) {
							var $el = data.r;
							$el.addClass('bookmark-trash-hover');
							$.my.root.delegate('.bookmark-trash', 'mouseout', function(event){
								$($el).removeClass('bookmark-trash-hover');
								$.my.root.undelegate('.bookmark-trash', 'mouseout');
							});
							return { 
								after : false, 
								before : false, 
								inside : true 
							};
						}
				},

				"crrm" : {
					"move" : {
						"check_move" : function(m) {
							if(m.r.hasClass("bookmark")){
								return (m.p === "before" || m.p === "after"); 
							}else{
								return !(m.p === "before" || m.p === "after");
							}
						}
					}
				},


				"plugins" : [ "themes", "html_data", "ui", "crrm", "dnd", "types"],
				
				"themes" : {
					"theme" : "apple",
					"dots" : false,
					"icons" : true
				}
				});
			});

		$('#bookmark_list li.treeItem').draggable({
			zIndex: 2700
		});
	
	}
};

var myPoll = {
	init: function(channel_id)
	{
		$('#'+channel_id+' a').click(function(){
			var url = $(this).attr('href');
			my.channelInit('#'+channel_id, url);
			return false;
		});
		
		$('#'+channel_id+' #qpoll').submit(function(){
			var vote = $('input[name=form_option]:checked', this).val();
			$.my.channelFetch($(this).attr('action')+'?voted_id='+$('#curpollid',this).val()+'&vote='+vote, channel_id);
			return false;
		});
	},
	echo: function(text_to_display, channel_id)
	{
		$('#'+channel_id).html(text_to_display);
		myPoll.init(channel_id);
	}
};

var directory_search = {
	init: function()
	{
		$('input.ds-what').addClass('temp_text');

		$('input.ds-what').focus(function(){
			$(this).val('');
			$(this).removeClass('temp_text');
		});

		$('input.ds-what').blur(function(){
			if($(this).val()=='')
			{
				$(this).addClass('temp_text');
				$(this).val('Type Here To Search');
			}
		});

		$('#ds-person-select').click(function(){
			$('#ds-person-search').show();
			$('#ds-person-select').parent().toggleClass('selected');
			return false;
		});

		$("#ds-person-search").attr('action', $("#ds-person-search").attr('action')+'?ajax=1&width=750&height=500').attr('title','Directory Search').supermodal({
			event: 'submit'
		});
	}
};

var interrupt = {
	url: HOST + '/webapp/portal/interrupt',
	check: function(){
		my.load(interrupt.url+'/index.html?check=1');
	},
	close_if_empty: function( $message ){
		// hide the message
		if( $message.siblings(':visible').length === 0 ) {
			$message.parents('.message-messages').slideUp('fast');
		} else {
			$message.slideUp('fast');
		}//end else
	},
	response: function(resp){
		if(resp=='null') {
			if( ! $('#cboxWrapper').is(':visible')) {
				$.colorbox({
					href: interrupt.url + '/index.html',
					width: 650,
					height: 550
				});
			}//end if
			setTimeout('interrupt.check();',120000);
		}//end if
	},
	identity_theft: function()
	{
		$('.webapp-avant-body .message-messages ul').append('<li class="identity-theft-interrupt"><map name="id-theft"><area href="mailto:helpdesk@plymouth.edu" alt="Email the Helpdesk" title="Email the Helpdesk" shape="rect" coords="432,221,568,238"></area></map><img src="https://www.plymouth.edu/webapp/images/identity_theft.gif" alt="Identity Theft" usemap="#id-theft"/><br/><a href="#" class="disable">hide this message</a></li>').show();
		
		$('.webapp-avant-body .message-messages .identity-theft-interrupt a.disable').click(function(){
			$.my.load($.my.host + '/interrupt/log.html?log=identity_theft');
			$('.identity-theft-interrupt').slideUp().remove();
			if($('.webapp-avant-body .message-messages li').size() == 0)
			{
				$('.webapp-avant-body .message-messages').hide();
			}//end if
		});
	},
	panther: function()
	{
		$('#global_message').before('<div id="panther-container"><div id="panther-interrupt"><div id="panther-content"><h3>Name the PSU Panther!</h3><p>The Plymouth State Class of 2012 has organized a campaign to name our popular mascot. After gathering more than 100 suggestions from across campus, the choices have been narrowed to 12 names. Which one is worthy of our Panther? <a href="http://www.plymouth.edu/webapp/survey/fillsurvey.php?sid=254" target="_blank">Cast your vote today</a>! The winning name will be announced at the men\'s hockey season opener on November 6.</p><div id="panther-buttons"><a href="#" class="disable">hide this message</a><a id="panther-vote" href="http://www.plymouth.edu/webapp/survey/fillsurvey.php?sid=254" target="_blank">Vote Now!</a></div></div></div></div>');
		$('#panther-container').show();
		
		$('#panther-interrupt a.disable').click(function(){
			$.my.load($.my.host + '/interrupt/log.html?log=panther');
			$('#panther-container').slideUp().remove();
		});
	},
	banner_signoff_showmsg: function(annoy)
	{
		$('.webapp-avant-body .message-messages ul').append('<li class="'+annoy+'">Your department has unreviewed Banner Sign-offs. Please check the <strong>Services</strong> tab to review pending patches.</li>');
		$('.webapp-avant-body .message-messages').show();
    $('.annoy-stage-1').annoy({action: 'highlight', selector: '.webapp-avant-body .message-messages'});
    //$('.annoy-stage-2').annoy({action: 'blink', selector: '.webapp-avant-body .message-messages'});
    //$('.annoy-stage-3').annoy({action: 'shake', selector: '.webapp-avant-body .message-messages'});
	},
	message: function(message, params) {
		var options = {
			hide_icon: false,
			id: ""
		};
		$.extend(options, params);

		if( options.id !== "" ) {
			if( $('#' + options.id).length > 0 ) { 
				return;
			}//end if
		}//end if

		if( options.hide_icon ) {
			$('.webapp-avant-body .message-messages').addClass('message-clean');
		}//end if

    $('.webapp-avant-body .message-messages ul').append('<li id="' + options.id + '">' + message + '</li>').find('.interrupt-hide').bind('click', function(e) {
			e.preventDefault();
			$.getScript($(this).attr('href'));

			var $li = $(this).parents('li');
			interrupt.close_if_empty( $li );

			$li.remove();
		});

    $('.webapp-avant-body .message-messages').show();
	},
	outage: function(message)
	{
    $('.webapp-avant-body .message-messages').parent().before('<div class="message-container message-outage"><div class="message message-errors">' + message +'</div></div>');
	},
	top: function(message) {
		$('<div id="top-message"><div class="container_16"><div class="grid_16">' + message + '</div></div></div>')
			.prependTo('body')
			.slideDown('fast');
	}
};

var toggleLink = {
	display: function(id){
		 $('#'+id).css('display','block');
		 $('#'+id).siblings('h2').find('.marker').addClass('down-arrow').removeClass('right-arrow');
		 my.load('https://www.plymouth.edu/webapp/portal/param.php?param='+id+'&action=set&value=1');
	},
	echo: function(data,id,state){
		$('#'+id).html(data);
		toggleLink.init(id,state);
	},
	hide: function(id){
		$('#'+id).css('display','none');
		$('#'+id).siblings('h2').find('.marker').addClass('right-arrow').removeClass('down-arrow');
		my.load('https://www.plymouth.edu/webapp/portal/param.php?param='+id+'&action=set&value=0');
	},
	init: function(id,state){
		$('#'+id+' h2').click(function(){
			var link_id = $(this).siblings('.links').attr('id');
			toggleLink.toggle(link_id);
		});
		
		$('#'+id+' h2').mouseover(function(){
			$(this).addClass('shade');
		});

		$('#'+id+' h2').mouseout(function(){
			$(this).removeClass('shade');
		});
		
		$.my.load(state);
	},
	toggle: function(id){
	 if($('#'+id).css('display') == 'none') toggleLink.display(id);
	 else toggleLink.hide(id);
	}
};

var computingResources = {
	echo: function (data,id)
	{
		$('#'+id).html(data);
		$('#comp_res_header_antivir').click(function(){
			computingResources.toggle('antivir');
		});
		$('#comp_res_header_antivir').mouseover(function(){
			computingResources.mouseOver('antivir');
		});
		$('#comp_res_header_antivir').mouseout(function(){
			computingResources.mouseOut('antivir');
		});
		$('#comp_res_header_computer').click(function(){
			computingResources.toggle('computer');
		});
		$('#comp_res_header_computer').mouseover(function(){
			computingResources.mouseOver('computer');
		});
		$('#comp_res_header_computer').mouseout(function(){
			computingResources.mouseOut('computer');
		});
		$('#comp_res_header_othsoft').click(function(){
			computingResources.toggle('othsoft');
		});
		$('#comp_res_header_othsoft').mouseover(function(){
			computingResources.mouseOver('othsoft');
		});
		$('#comp_res_header_othsoft').mouseout(function(){
			computingResources.mouseOut('othsoft');
		});
		$('#comp_res_header_policy').click(function(){
			computingResources.toggle('policy');
		});
		$('#comp_res_header_policy').mouseover(function(){
			computingResources.mouseOver('policy');
		});
		$('#comp_res_header_policy').mouseout(function(){
			computingResources.mouseOut('policy');
		});
		my.load('https://www.plymouth.edu/webapp/portal/channel/computing_resources/state.html');
	},
	toggle: function (tag) 
	{
	 var obj;
	 if($('#comp_res_'+tag+'_links').css('display')=='none')
	 {
			this.display(tag);
			my.load('https://www.plymouth.edu/webapp/portal/param.php?param=comp_res_'+tag+'&action=set&value=1');
	 }
	 else
	 {

			this.hide(tag);
			my.load('https://www.plymouth.edu/webapp/portal/param.php?param=comp_res_'+tag+'&action=set&value=0');
	 }
	},
	display: function (tag)
	{
			 $('#comp_res_'+tag+'_links').css('display','block');
			 $('#comp_res_'+tag+'_links_marker').html('<img src="https://www.plymouth.edu/webapp/portal/channel/computing_resources/images/down_arrow.gif"/>');
	},
	hide: function (tag)
	{
		 $('#comp_res_'+tag+'_links').css('display','none');
		 $('#comp_res_'+tag+'_links_marker').html('<img src="https://www.plymouth.edu/webapp/portal/channel/computing_resources/images/right_arrow.gif"/>');
	},
	mouseOver: function (tag)
	{
		 $('#comp_res_header_'+tag).addClass('secondary_table_header_shade');
	},
	mouseOut: function (tag)
	{
		 $('#comp_res_header_'+tag).removeClass('secondary_table_header_shade');
	}
};

$(document).ready(function(){
	if( $.fn.supermodal != undefined ) {
		$.fn.supermodal.defaults.throbber = '/images/throbber.gif';
		$.fn.supermodal.defaults.width = 600;
		$.fn.supermodal.defaults.height = 500;
	}

	$('.change-password').popover();

	//$(document).pngFix();

	var waypoint_cb = function() {
		$(this).closest('.channel').addClass('waypointed');
	};

	var waypoint_options = {
		offset: '100%'
	};

	var waypoint_reached = function() {
		$.my.channelInit($(this).parent(),$(this).attr('href'));
	};

	// set every channel as a waypoint
	$('.remote-channel')
		.bind('waypoint.reached', waypoint_reached)
		.waypoint( waypoint_cb, waypoint_options );

	//$('#tabs').myTabs();

	$('.channel .options a.ci_minimize').click(function(){
		$('.body',$(this).parents('.channel')).children('table').slideToggle('fast');
		return false;
	});

	$('.channel .options a.ci_remove').click(function(){
		if(confirm('Are you sure you wish to remove the '+($(this).parents('.channel').find('.title h2.primary').html())+' Channel?'))
		{
			var href=$(this).attr('href');
			$.get(href);
			$(this).parents('.channel').fadeOut('fast').remove();
		}//end if
		return false;
	});

	$('#mycourses-container,#mycourses-channel-container').each(function(){
		$.my.load("https://www.plymouth.edu/webapp/portal/channel/mycourses/?load=1");
	});

	$('#orientation-container').each(function(){
		$.my.load("https://www.plymouth.edu/webapp/portal/channel/orientation/?load=1");
	});

	$('#reset-portal-layout').click(function(){
		return confirm('Are you sure that you want to remove any customizations you have made to myPlymouth, and restore it to the default layout?');
	});

	$('#search-help').ready(function(){
		var term = $.query().q;
		$('#widget-go-search input[name=q]').val(term);
		$('#search-help').attr('src', 'https://go.plymouth.edu/go/sidebar/search_help.html?q='+term);

	});

	$.mySidebar.load();
	interrupt.check();

	$('.content-tabs a').live('click', function(){
		var $el = $(this);
		var rel = $el.attr('rel');
		var $tabs = $el.parents('.content-tabs');

		$tabs.find('li').removeClass('selected');
		$el.parent().addClass('selected');

		$tabs.siblings().hide();
		$tabs.siblings('.' + rel).show();
		return false;
	});

	$('#search-window iframe').each(function(){
		$(this).attr('width','100%');
		$(this).attr('height','900');
	}); 
	
	if($('#zcal_calendar').length>0)
	{
		zCal.init();
	}
	
	/******* BEGIN Go Search Box ************/
	var go_box_message = 'Search for help';
	var go_box_value = $('.go-box').val();

	if(go_box_value=='' || go_box_value==go_box_message)
	{
		$('.go-box').addClass('go-box-inactive').val(go_box_message);		
	}//end if

	$('.go-box').focus(function(){
		if($(this).val()==go_box_message)
		{
			$(this).val('');
		}
		$(this).removeClass('go-box-inactive');
	});
	
	$('.go-box').blur(function(){
		var value = $(this).val();
		if(value =='' || value==go_box_message)
		{
			if(value=='')
			{
				$(this).val(go_box_message);
			}//end if
			$(this).addClass('go-box-inactive');
		}//end if
	});
	/******* END Go Search Box ************/

	$('.psu_image_dialog, .psu_iframe_dialog, .modal').supermodal();

	
	//make the dialog box go away when the overlay is clicked.
	$('.ui-widget-overlay').live('click',function(){
		$('.supermodal').dialog('close').html('');
		return false;
	});

	$('.webapp-avant-body').append('<ul class="grid_16" id="global_message"></ul>');

	// search width toggling
	$('.channel .icon-expand').bind('click', function(){
		var $channel = $(this).parents('.search-column');
		var $other = $channel.siblings('.search-column');

		if($channel.hasClass('column-expand') || $channel.hasClass('column-shrink'))
		{
			$channel.removeClass('column-expand column-shrink');
			$other.removeClass('column-expand column-shrink');
		}//end if
		else
		{
			$channel.addClass('column-expand');
			$other.addClass('column-shrink');
		}//end else
	});

	$('.channel .title .icon-delete').bind('click', function(){
		if(confirm('Are you sure you wish to remove the '+($(this).parents('.channel').find('.title h2.primary').html())+' Channel?'))
		{
			var el = $(this).parents('.channel');
			var url = BASE_URL + '/channel/delete/' + el.attr('id');

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

			el.remove();
		}//end if
		return false;
	});

	$('#tab-box').bind('change', function(){
		document.location = $(this).find('option:selected').val();	
	});

	$.mydnd.init();

	$('body').PSUFeedback({
		bottom: '26px'
	});

	$('#gravatar-info').colorbox({
		html: $('#gravatar-info-message').html(),
		height: 350,
		width: 600
	});
}); // $(document).ready

// make sure colorbox opens all the way when triggered
$(document).bind('cbox_open', function(){
	$('#colorbox').show();
});


var my_js_loaded = true;
