$(function(){
	function set_price( smval, bgval ) {
		$( '#price' ).val( '$' + smval + ' - $' + bgval );
	}//end set_price
	
	$('#price-slider').slider({
		range: true,
		min: psu_slider.min, 
		max: psu_slider.max,
		step: 5,
		values: [psu_slider.selected_min, psu_slider.selected_max],
		slide: function(event, ui) {
			set_price( ui.values[0], ui.values[1] );
		}
	});

	set_price( psu_slider.selected_min, psu_slider.selected_max );

	$('#filter-reset').click(function(){
		$('#price').val( '$' + psu_slider.min + ' - $' + psu_slider.max );
		$('#price-slider').slider( 'values', 0, psu_slider.min );
		$('#price-slider').slider( 'values', 1, psu_slider.max );
		$('.filter-check').removeAttr('checked');
	});

	$('.item-thumb').closest('a').colorbox();

	$('.item-thumb').each(function(){
		$el = $(this);
		if($el.height() > $el.width()) {
			var h = 130;
			var w = Math.ceil($el.width() / $el.height * 130);
		} else {
			var w = 130;
			var h = Math.ceil($el.height() / $el.width * 130);
		}//end else
		$el.css({height: h, width: w});
	});
});
