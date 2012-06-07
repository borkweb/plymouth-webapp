$(function() {
	if( $('.myplymouth').length <= 0 ) {
		$("html, body").animate({ scrollTop: $(document).height() }, 100);

		$('.remote-channel:not(.waypointed)').each(function() {
			$.my.channelInit($(this).parent(),$(this).attr('href'));
			$(this).closest('.channel').addClass('waypointed');
		});
	}//end if
});
