
$(document).on('click', '.add', function(event){
	var $new = $(this).closest('.person').find('.new');
	var $old = $(this).closest('.person').find('.old');
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
	var $new = $(this).closest('.person').find('.new');
	var $old = $(this).closest('.person').find('.old');

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
	var $el = $(this).closest('.person');
	var $checked = $el.find('input:checked').parent();
	$checked.each(function(){
		var postData = new Array();
		var id = $(this).data('merit-id');
		postData[0] = id;		
		$.ajax({
			type: 'POST',
			url: 'merit/remove',
			data: { data: postData },
			success: function(data) {
				$("[data-merit-id='" + id + "']").remove();
			}
		});
	});
});
function html_escape_quotes(oldString){
	newString = oldString.replace("'", "&#039;");
	newString = oldString.replace('"', "&quot;");
	return newString;
}
function js_escape_quotes(oldString){
	newString = oldString.replace("'", "\'");
	newString = oldString.replace('"', '\"');
	return newString;
}
$(document).on('click', '.confirm', function(event){
	var $el = $(this).closest('.person');
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
		var postComment = $el.find('textarea').val();
		var comment = html_escape_quotes(postComment);
		postData[0] = $el.find('input:checked').val();
		postData[1] = postComment;
		postData[2] = $el.find('.name').data('wpid');

		$.ajax({
			type: 'POST',
			url: 'merit',
			data: { data: postData },
			async: true,
			dataType: 'json',
			success: function(data) {
				if (type == 'Star'){
					$el.find('.current-merit').append("<li data-merit-id='" + data['id'] + "'><input type='checkbox'> " + comment + "</li>");
					var $star = $el.find('.merit').last();
					if ($star.length){
						$star.after("<img title = \"" + comment + "\" data-merit-id='" + data['id'] + "' class='merit left' src='../images/star.png'>");
					}else{
						var $dogHouse = $el.find('.demerit').last();
						if ($dogHouse.length){
							$star.after("<img title = \"" + comment +"\" id='" + data['id'] + "' class='merit left' src='../images/star.png'>");
						}else{
							$el.find('.name').before("<img title = \"" + comment +"\" data-merit-id='" + data['id'] + "' class='merit left' src='../images/star.png'>");
						}
					}
				}
				else{
					$el.find('.current-demerit').append('<li data-merit-id=\'' + data['id'] + '\'><input type="checkbox"> ' + comment + '</li>');
					var $dogHouse = $el.find('.demerit').last();
					if ($dogHouse.length){
						$dogHouse.after("<img title = '" + comment + "' data-merit-id='" + data['id'] + "' class='merit demerit left' src='https://s0.plymouth.edu/images/icons/22x22/status/dialog-warning.png'>");
					}else{
						var $star = $el.find('.merit').first();
						if ($star.length){
							$star.before("<img title = '" + comment + "' id='" + data['id'] + "' class='merit demerit left' src='https://s0.plymouth.edu/images/icons/22x22/status/dialog-warning.png'>");
						}else{
							$el.find('.name').before("<img title = '" + comment + "' data-merit-id='" + data['id'] + "' class='merit demerit left' src='https://s0.plymouth.edu/images/icons/22x22/status/dialog-warning.png'>");
						}
					}
				}	
				// remove old values from the add section.
				$checked.removeAttr("checked");
				$el.find('textarea').val('');
			}
		});
	}
});


