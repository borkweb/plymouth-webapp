$(function() { 

	$('.confirm').mouseenter(function(){
		$('.confirm-text').addClass('tt-warning');
	}).mouseleave(function(){
		$('.confirm-text').removeClass('tt-warning');
	});	

	var $progress = $('.progressbar');
	$('#goals').accordion();
	$progress.progressbar();	

	$progress.each( function(){
		$(this).progressbar( 'option', 'value', $(this).data('progress'));
	});

	$('.chkbox-container').each( function(){
		 $(this).mouseenter(function() {
			 $(this).addClass('highlight');
		 }).mouseleave(function() {
			 $(this).removeClass('highlight');
		 });
		$(this).tooltip({
				placement: "right" 
		});
	});

});


// As the coments box is modified send the values to the database
$(document).on('click', '.chkbox', TrainingTracker.checkboxToggle);

$(document).on('click', '.chkbox', TrainingTracker.recaculateProgress);

