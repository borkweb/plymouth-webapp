$(document).on('click', '.add-new', function(event){
	if ($('.remove').attr('hidden') !== undefined) {
			// attribute exists
	} else {
			// attribute does not exist
		$('.remove').hide('slow');
		$('.remove').addClass('hidden');
	}
	$('.new').show('slow');
	$('.new').removeClass('hidden');
});
$(document).on('click', '.remove-old', function(event){
	if ($('.new').attr('hidden') !== undefined) {
			// attribute exists
	} else {
			// attribute does not exist
		$('.new').hide('slow');
		$('.new').addClass('hidden');
	}
	$('.remove').show('slow');
	$('.remove').removeClass('hidden');
});
