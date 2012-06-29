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
							if (demoteText == 'Senior Information Desk Consultant'){
								var demoteTo = 'sta';
								var demoteName = 'Information Desk Consultant';
								$row.find('.promote').removeAttr('disabled');
							}
							else{
								var demoteTo = 'trainee';
								var demoteName = 'Information Desk Trainee';

								$row.find('.demote').attr('disabled', 'disabled');
								$row.find('.promote').removeAttr('disabled');
							}
							$row.find('.permission').text(demoteName);
							var postData = Array();
							postData[0] = demoteTo;
							postData[1] = wpid;
							$.ajax({
									type: 'POST',
									url: '/webapp/training-tracker/staff/fate',
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

	//statistics / checklist page function
	outputDataCheck: function (e){
		var postData = new Array();

		//if it checkbox you clicked was just checked.
		if (e.target.checked){
			//pass complete
			var response = 'complete';
		}else{
			//pass n/a	
			var response = 'incomplete';
		}
		//active user is the person looking at the page
		//current user is the person they are looking at
		postData[0]=e.target.id; //id of the checkbox
		postData[1]=current_user_wpid;
		postData[2]=response;
		$.ajax({
			type: 'POST',
			url: '/webapp/training-tracker/staff/checklist/item',
			data: { data: postData }
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
							else{
								var promoteTo = 'shift_leader';
								var promoteName = 'Senior Information Desk Consultant';
								$row.find('.promote').attr('disabled', 'disabled');
								$row.find('.demote').removeAttr('disabled');
							}
							$row.find('.permission').text(promoteName);
							var postData = Array();
							postData[0] = promoteTo;
							postData[1] = wpid;
							$.ajax({
								type: 'POST',
								url: '/webapp/training-tracker/staff/fate',
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

	recaculateProgress: function (e){
		var $el = $(this).closest('.inner-goals');
		var checked = $el.find('input:checked').length;
		var all = $el.find('input').length;
		var newProgress = Math.round(((checked / all)*100)*100)/100;

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


