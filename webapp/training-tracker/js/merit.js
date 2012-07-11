$(function(){
	var $people = $('.person');
	$('.person').each(function(){
		var wpid = $(this).find('.name').data('wpid');
		var $meritList = $(this).find('.current-merit');
		$.each(merits[wpid]['merits'], function(){
			$meritList.append('<li id=\'' + this['id'] +'\'><input type="checkbox"> ' + this['notes'] + '</li>');
		});
		var $demeritList = $(this).find('.current-demerit');
		$.each(merits[wpid]['demerits'], function(){
			$demeritList.append('<li id=\'' + this['id'] +'\'><input type="checkbox"> ' + this['notes'] + '</li>');
		});
	});
});

$(document).on('click', '.add', function(event){
	var $new = $(this).parent().find('.new');
	var $old = $(this).parent().find('.old');
	if (!$new.hasClass('hidden')) {
		$new.hide('slow');
		$new.addClass('hidden');
	} 
	else if ($new.hasClass('hidden')){
		$new.show('slow');
		$new.removeClass('hidden');
	}
	if (!$old.hasClass('hidden')) {
		$old.hide('slow');
		$old.addClass('hidden');
	}
	
});

$(document).on('click', '.remove', function(event){
	var $new = $(this).parent().find('.new');
	var $old = $(this).parent().find('.old');

	if (!$old.hasClass('hidden')) {
		$old.hide('slow');
		$old.addClass('hidden');
	} 
	else if ($old.hasClass('hidden')){
		$old.show('slow');
		$old.removeClass('hidden');
	}
	if (!$new.hasClass('hidden')) {
		$new.hide('slow');
		$new.addClass('hidden');
	}
});

$(document).on('click', '.remove-old', function(event){
	var $el = $(this).parent().parent();
	var $checked = $el.find('input:checked').parent();
	$checked.each(function(){

		var postData = new Array();
		var id = $(this).attr('id');
		postData[0] = id;		
		console.log(id);
		$.ajax({
			type: 'POST',
			url: 'merit/remove',
			data: { data: postData },
		});
		setTimeout(400);
		$(this).remove()
	});
});
$(document).on('click', '.confirm', function(event){
	var $el = $(this).parent().parent();
	var name = $el.find('.name').html();
	var $checked = $el.find('input:checked');
	var type = $checked.data('text');
		if ( _.isUndefined(type) || _.isNull(type) ){
		$.gritter.add({
			title: 'No type selected',
			text: 'Please choose a type (Star or Dog House)',
			time: 3000,
		});	
	}
	else{
		if (type == 'Star'){
			var title = name + " just recieved a " + type;
			var text = "You just gave " + name + " a " + type + ".";
			$.gritter.add({
				title: title,
				text: text,
				time: 3000,
			});
		}
		else{
			var title = name + " just got put in the " + type;
			var text = "You just put " + name + " into the " + type + ".";
			$.gritter.add({
				title: title,
				text: text,
				time: 3000,
			});

		}
	}

	if ( !_.isUndefined(type) ){

		var postData = new Array();
		
		postData[0] = $el.find('input:checked').val();
		postData[1] = $el.find('textarea').val();
		postData[2] = $el.find('.name').data('wpid');

		$.ajax({
			type: 'POST',
			url: 'merit',
			data: { data: postData },
			async: true,
			dataType: 'json',
			success: function(data) {
				console.log(data['id']);
				console.log(type);
				if (type == 'Star'){
					$el.find('.current-merit').append('<li data-id=\'' + data['id'] + '\'><input type="checkbox"> ' + $el.find('textarea').val() + '</li>');
				}
				else{
					$el.find('.current-demerit').append('<li data-id=\'' + data['id'] + '\'><input type="checkbox"> ' + $el.find('textarea').val() + '</li>');
				}	
				// remove old values from the add section.
				$checked.removeAttr("checked");
				$el.find('textarea').val('');
			}
		});

		
		
			}
});
