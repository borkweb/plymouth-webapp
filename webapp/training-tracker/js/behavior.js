
//TrainingTracker object to hold functions
var TrainingTracker = {

	//functions from the admin page
	demotionPost: function(wpid, $row){
		var name = $row.find('.name').html();
		$('.popup_text').text('Are sure you want to demote ' + name + '?');
		$( '#confirmation' ).dialog({
					resizable: false,
					height:200,
					modal: true,
					buttons: {
						'Yes': function() {
							var demoteText = $row.find('.permission').html();
						 if (demoteText == 'Junior Shift Supervisor'){
								var demoteTo = 'shift_leader';
								var demoteName = 'Senior Information Desk Consultant';
								$row.find('.promote').removeAttr('disabled');
							}else	if (demoteText == 'Senior Information Desk Consultant'){
								var demoteTo = 'sta';
								var demoteName = 'Information Desk Consultant';
							}else if (demoteText == 'Information Desk Consultant'){
								var demoteTo = 'trainee';
								var demoteName = 'Information Desk Trainee';
								$row.find('.demote').attr('disabled', 'disabled');
							}
							$row.find('.permission').text(demoteName);
							var postData = Array();
							postData = {permission: demoteTo, wpid: wpid};

							$.ajax({
									type: 'POST',
									url: 'fate',
									data: { data: postData }
							}); 
							$.gritter.add({
								title: 'You just demoted ' + name,
								text: name + ' was just demoted to a ' + demoteName + '.',
							});
							$( this ).dialog( 'close' );
						},
						'No': function() {
							$( this ).dialog( 'close' );
						}
				}
		});
	},
 
	promotionPost: function (wpid, $row){
		var name = $row.find('.name').html();
		$('.popup_text').text('Are sure you want to promote ' + name + '?');
		$( '#confirmation' ).dialog({
					resizable: false,
					height:200,
					modal: true,
					buttons: {
						'Yes': function() {
						var promoteText = $row.find('.permission').html();
						if (promoteText == 'Information Desk Trainee'){
							var promoteTo = 'sta';
							var promoteName = 'Information Desk Consultant';
							$row.find('.demote').removeAttr('disabled');
						}
						else if (promoteText == 'Information Desk Consultant'){
							var promoteTo = 'shift_leader';
							var promoteName = 'Senior Information Desk Consultant';
						}
						else if (promoteText == 'Senior Information Desk Consultant'){
							var promoteTo = 'supervisor';
							var promoteName = 'Junior Shift Supervisor';
							$row.find('.promote').attr('disabled', 'disabled');
						}else{
							var promoteTo = 'supervisor';
							var promoteName = 'Junior Shift Supervisor';
							$row.find('.promote').attr('disabled', 'disabled');
						}
						$row.find('.permission').text(promoteName);
						var postData = Array();
						postData = {permission: promoteTo, wpid: wpid};

						$.ajax({
							type: 'POST',
							url: 'fate',
							data: { data: postData }
						});  
						$( this ).dialog( 'close' );
						$.gritter.add({
							title: 'You just promoted ' + name,
							text: name + ' was just promoted to a ' + promoteName + '.',
						});
					},
						'No': function() {
							$( this ).dialog( 'close' );
						}
					}
			});
	},

	// Statistics / checklist page function
	checkboxToggle: function (e){
		// If it checkbox you clicked was just checked.
		if (e.target.checked){
			//pass complete
			var response = 'complete';
		}else{
			//pass n/a	
			var response = 'incomplete';
		}
		// Active user is the person looking at the page
		// Current user is the person they are looking at
		postData = { checkboxId: e.target.id, wpid: current_user_wpid, response: response};
		$.ajax({
			type: 'POST',
			url: '../checklist/checkbox',
			data: { data: postData }
		});	
	},

	recaculateProgress: function (e){
		var $el = $(this).closest('.inner-goals');
		var divisor = $el.data('divisor');
		var checked = $el.find('input:checked').length;
		var all = $el.find('input').length;

		if (!_.isUndefined(divisor)){
			all = divisor;
		}

		var newProgress = Math.round(((checked / all)*100)*100)/100;

		if (newProgress > 100){
			newProgress = 100;
		}

		var $progress = $el.prev();
		var $progressbar = $progress.find('.progressbar');
		$progressbar.progressbar( 'option', 'value', newProgress);
		
		$progress.find('.progress').text(newProgress);

		var $allProgress = $('.progress');
		var total = 0;
		$allProgress.each(function(){
			total += parseFloat($(this).text());
		});

		var overallProgress = Math.round((total / $allProgress.length)*100)/100;
		$('#overall').progressbar( 'option', 'value', overallProgress);
		$('#total-progress').text(overallProgress);
	}
};

