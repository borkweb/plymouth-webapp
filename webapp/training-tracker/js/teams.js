$(function(){
	$('.chzn-select').chosen(); 

	$( '.team' ).sortable({
		connectWith: '.team',
		appendTo: 'body',
		placeholder: "ui-state-highlight",
		start: function(e, ui){
        ui.placeholder.height(ui.item.height());
    },
		helper: function(event,$item){
			var $helper = $('<ul class = "styled"><li id="' +  event.originalEvent.target.id + '">' + event.originalEvent.target.innerHTML + '</li></ul>');
			mentee_wpid = event.originalEvent.target.id;
			mentor_wpid_alt = $('#'+event.currentTarget.id).parent().find('select').val();
			var height = $(this).height();
			$('.ui-state-highlight').attr('alt', 'Beijing Brush Seller');
			return $helper;
		},
		receive: function(event, ui){
			//the number of the team it is dropping to is the last character, so this grabs the last character
			var mentor_wpid = $(this).parent().find('select').val();

			if (_.isUndefined(teams[mentor_wpid]) != true){

				if (mentor_wpid != mentor_wpid_alt && mentor_wpid.length != 0){
					//using jquery for a deep copy
					teams[mentor_wpid][mentee_wpid] = $.extend(true, {}, teams[mentor_wpid_alt][mentee_wpid]);
					delete teams[mentor_wpid_alt][mentee_wpid];
					changeTeam(mentor_wpid, mentee_wpid);
					//call ajax
				}
			}
			else{
				if (mentor_wpid != mentor_wpid_alt && mentor_wpid.length != 0){
					teams[mentor_wpid] = { };
					teams[mentor_wpid][mentee_wpid] = $.extend(true, {}, teams[mentor_wpid_alt][mentee_wpid]);
					delete teams[mentor_wpid_alt][mentee_wpid];
					changeTeam(mentor_wpid, mentee_wpid);
					//call ajax
				}
			}
		}
	}).disableSelection();
	function changeTeam(mentor_wpid, mentee_wpid){
		postData = new Array();
		postData[0] = mentee_wpid;
		postData[1] = mentor_wpid;
		$.ajax({
			type: 'POST',
			url: 'builder',
			data: { data: postData }
		});
	}
});

$(document).on( 'change', 'select.list1', function() { 
	$('#team1').empty();

	var mentorWpid = $(this).val();
	for(var i in teams[mentorWpid]){
		$('#team1').append('<li class="ui-state-default" id="' + i + '">' + teams[mentorWpid][i]['name'] + '</li>');
	}	
});

$(document).on( 'change', 'select.list2', function() { 
	$('#team2').empty();
	var mentorWpid = $(this).val();
	for(var i in teams[mentorWpid]){
		$('#team2').append('<li class="ui-state-default" id="' + i + '">' + teams[mentorWpid][i]['name'] + '</li>');
	}
});
