
$(document).on('click', '.add', function(event){
	// $new is the div which contains the add new merit content
	// $old is the div which contains the add Remove a merit content
	var $new = $(this).closest('.person').find('.new');
	var $old = $(this).closest('.person').find('.old');
	// If $new is visible hide it else show it.
	if (!$new.hasClass('hidden')) {
		$new.hide('slow');
		$new.addClass('hidden');
	} 
	else if ($new.hasClass('hidden')){
		$new.show('slow');
		$new.removeClass('hidden');
	}
	// if $old is visible hide it.
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
	// Select the outmost div that holds a person. Including name, Add new merit and Remove a merit buttons etc...
	var $el = $(this).closest('.person');
	// Select all the checked checkbox's parent which is a <li>
	var $checked = $el.find('input:checked').parent();
	// Foreach checkbox delete it from the data base then remove the element from the page
	$checked.each(function(){
		//var postData = new Array();
		var id = $(this).data('merit-id');
		postData = {id: id};		
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
	// First show a gritter notification (http://boedesign.com/blog/2009/07/11/growl-for-jquery-gritter/)
	var $el = $(this).closest('.person');
	var name = $el.find('.name').html();
	var $checked = $el.find('input:checked');
	var type = $checked.data('text');
	// type is either Star or Dog House, selected via radio buttons.
	// if there is no type it will add a notification in the upper right
	if ( _.isUndefined(type) || _.isNull(type) ){
		$.gritter.add({
			title: 'No type selected',
			text: 'Please choose a type (Star or Dog House)',
			time: 3000,
		});	
	}
	else{
		// if there is a type and it is star
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
			// if it a dog house
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
		postData = {type: $el.find('input:checked').val(), comments: postComment, wpid: $el.find('.name').data('wpid')};

		$.ajax({
			type: 'POST',
			url: 'merit',
			data: { data: postData },
			async: true,
			dataType: 'json',
			success: function(data) {
				// Data is returned containing the id number of the item inserted into the database
				// star and dogHouse is the code inserted to show the icons.
				var dogHouse = '<span data-merit-id="' + data['id'] + '" class="merit demerit" title="' + comment + '"><i class="psu-icon icon small red"><span class="icon-ape-home"></span></i></span>';
				var star = '<span data-merit-id="' + data['id'] + '" class="merit" title="' + comment + '"><i class="psu-icon icon small gold"><span class="icon-star"></span></i></span>';
				// type will always equal Star or Dog House this is used to determain what to insert
				if (type == 'Star'){
					// The next line adds the option removal checkbox under the Remove a merit button
					$el.find('.current-merit').append('<li data-merit-id=\'' + data['id'] + '\'><input type="checkbox"> ' + comment + '</li>');
					// Select all the merits and choose the last one.
					var $star = $el.find('.merit').last();
					// If a star exists insert this star after the current one
					if ($star.length){
						$star.after(star);
					}else{
						// If there is no star look for a doghouse to insert it after
						var $dogHouse = $el.find('.demerit').last();
						if ($dogHouse.length){
							$star.after(star);
						}else{
							// If there are no merits associated with this person insert it before their name
							$el.find('.name').before(star);
						}
					}
				}
				else{
					$el.find('.current-demerit').append('<li data-merit-id=\'' + data['id'] + '\'><input type="checkbox"> ' + comment + '</li>');
					var $dogHouse = $el.find('.demerit').last();
					if ($dogHouse.length){
						$dogHouse.after(dogHouse);
					}else{
						var $star = $el.find('.merit').first();
						if ($star.length){
							$star.before(dogHouse);
						}else{
							$el.find('.name').before(dogHouse);
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

