$(function () {
	$('.person').each( function(){
		var $el = $(this);
		var permission = $el.find('.permission').html();
		if (permission == 'Information Desk Trainee'){
				$el.find('.demote').attr('disabled', 'disabled');
		}
		else if (permission == 'Senior Information Desk Consultant'){
				$el.find('.promote').attr('disabled', 'disabled');
		}
	});
});
$(document).on('click', '.demote', function(){
	var wpid = $(this).data('wpid');
	var $row = $(this).closest('tr'); 
	TrainingTracker.demotionPost(wpid, $row);
});

$(document).on('click', '.promote', function(){
	var wpid = $(this).data('wpid');
	var $row = $(this).closest('tr'); 
	TrainingTracker.promotionPost(wpid, $row);
});
