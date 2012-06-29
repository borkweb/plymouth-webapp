$(function(){

	var $progress = $('.progressbar');
	$progress.progressbar();

	$progress.each( function(){
		var $el = $(this);

		$el.progressbar('option','value', $el.data('progress') );
	});

});
