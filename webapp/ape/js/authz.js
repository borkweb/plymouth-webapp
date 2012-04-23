var authz = {
	//declaration of an object for selectors that will be accessed in a loop
	selectors : {},

	//initiation of the selectors
	init: function()	{

		authz.selectors = {
			child_container :	$( '#new_child' ),
			child_append_to : [],
			adds_container :	$( '#new_add' ),
			adds_added_li : $( '#authz-management .possible-roles-perms ul li:last' ),
			adds_append_to : [], 
			meta_container : $( '#new_meta'),
			meta_append_to : $( '#meta' ) 
		}; 
		authz.selectors.child_name = authz.selectors.child_container.find( '.child_name' );
		authz.selectors.child_info = authz.selectors.child_container.find( '.child_info' );
		authz.selectors.adds_attr = authz.selectors.adds_container.find( '.add_attr' );
		authz.selectors.adds_name = authz.selectors.adds_container.find( '.add_name');
		authz.selectors.adds_append_to.push([ 
			$( '#authz-management .possible-roles-perms ul:first' ), 
			$( '#authz-management .possible-roles-perms ul:last' ) 
		]); 
		authz.selectors.adds_append_to.push([
			$( '#col1 ul:last' ), 
			$( '#col1, ul.alter' ) 
		]);  
		authz.selectors.child_append_to.push( $( '#col2 .roles-perms ul:first' ) ); 
		authz.selectors.child_append_to.push( $( '#col2 .roles-perms ul:last' ) ); 
	},
	/*
	* Display uses ajax and JSON to retrieve database information. The information is populated to the form at the bottom and
	* the other two columns.
	*/
	display: function(attr){
		var attr = { attr: attr };

		//retreive server information
		$.getJSON( 'authz-management.html', attr, function(info){ 

			//clear the old contents of the form
			authz.reset();

			//populate the top half of the form on the bottom
			var desc = info.desc[0];
			$( '#attr_slug' ).val(desc.attribute).attr( 'disabled', 'disabled' );
			$( '#attr_name' ).val(desc.name);
			$( '#col2_header' ).text('Attribute: '+desc.name);
			$( '#attr_type' ).children( 'option[value='+desc.type_id+']' ).attr( 'selected', 'selected' );
			$( '#description' ).text(desc.description);

			//populate the meta setion of the form
			for( x in info.meta ) {
				authz.display_meta( info.meta[x] );
			}	

			//populate column two
			for( c in info.children ) {
				authz.display_child( info.children[c] );  
			}

			//populate column three
			for( g in info.possible_adds ) {
				authz.display_adds( info.possible_adds[g], 0 );		
			}

			//display the headings for column 2 and 3
			$( '#col3 h3, #col2 h3, #col2 div, #col3 .filter, #col2 h2' ).show();

		});
	},
	/*
	*	clears all form and column information except for column 1
	*/
	reset : function() {
		$( '#col2 .roles-perms ul' ).html( '' );
		$( '#authz-management .possible-roles-perms ul' ).html( '' );
		$( '#meta' ).html( '' );
		$( '#attr_slug' ).val( '' ).removeAttr( 'disabled' );;
		$( '#attr_name' ).val( '' );
		$( '#attr_type' ).children( 'option:first' ).attr( 'selected', 'selected' );
		$( '#description' ).text( '' );
		$( '#col1 .selected' ).removeClass( 'selected' );
		$( '#col3 h3, #col2 h3, #col2 div, #col3 .filter, #col2 h2:first' ).hide();
	},
	/*
	*	populate information at a hidden set of elements and then inserts a copy into column 2
	*/
	display_child : function( desc ) {
		authz.selectors.child_info.attr( 'name', desc.parent_attribute+'--'+desc.child_attribute );	
		authz.selectors.child_name.text( desc.name );

		//decids type of attribute
		if( desc.child_type_id == "2") {
			var list = 0;
		} else {
			var list = 1;
		} 
		authz.selectors.child_append_to[ list ].append( authz.selectors.child_container.html() );

		//decide if the current checkbox needs to be checked
		if( desc.is_default == "Y" ) {
			child_current_checkbox : 	$( ' #authz-management .roles-perms ul li:last .child_info ' ).attr( 'checked', 'checked' );
		} else {
			child_current_checkbox : 	$( ' #authz-management .roles-perms ul li:last .child_info ' ).removeAttr( 'checked' );
		}//end else
	},
	
	/*
	*	populate the information in column 3
	*/
	display_adds : function( desc, col ) {
		//decide which heading to put the attribute under
		if( desc.type_id == 1 ) {
			var placement = 1;
		} else {
			var placement = 0;
		}//end else

		//populate the information
		authz.selectors.adds_attr.attr( 'rel', desc.attribute );
		authz.selectors.adds_name.text( desc.name );
		authz.selectors.adds_append_to[col][placement].append( authz.selectors.adds_container.html() );
		authz.selectors.adds_added_li.removeAttr( 'style' );
	},
	//fills in meta information
	display_meta : function( current ) {
		authz.selectors.meta_append_to.append(authz.selectors.meta_container.html());	
		authz.selectors.meta_append_to.find('li:last input').val( current.meta );
		authz.selectors.meta_append_to.find('li:last input').attr( 'name', current.meta );
	}

};
//the add link is controlled by this bind
$(document).delegate( '#attribute_info a:last', 'click', function(){
	$('#meta').append($('#new_meta').html());	
});

//The new anchor is tided reset()
$(document).delegate( '#attribute_info a:first', 'click', function(){
	authz.reset();	
});

//Controls the Do It button
$(document).delegate( '#attribute_info', 'submit', function(){
	var meta = [];

	$.each($( '#meta li input'), function( index, value ){
		 meta.push($(this).attr('name')+'--'+$(this).val());
	});

	var params = {
			name : $( '#attr_name' ).val(),
			slug : $( '#attr_slug' ).val(),
			type : $( '#attr_type' ).val(),
			desc : $( '#description' ).val(),
			meta : meta,
			add : true
	};
	
	if( $( '#attr_name' ).val() && $( '#attr_slug' ).val() && $( '#attr_type').val() ) {
		$.getJSON( 'authz-management.html', {is_attr:true, attr:params.slug}, function(is_attr) {
			if(is_attr) {
				if(confirm('The slug '+params.slug+' exsists and will change an attributes information. Proceed?')) {
					if(confirm('Are you sure?')) {
						return true;
					} else {
						return false;
					}//end else
				} else {
					return false;
				}//end else
			}//end if

			$.getJSON( 'authz-management.html' , params );

			desc = {
				name: $( '#attr_name' ).val(),
				attribute: $( '#attr_slug' ).val(),
				type_id: $( '#attr_type').val()
			};

			authz.display_adds( desc, 1 );
			authz.display( desc.attribute );
			$( '#col3 h3').show();

		});	
	} else {
		alert('Name, Slug and Type must be filled in'); 
	}//end else

	return false;
});

//controls column 2 checkbox state change saving
$(document).delegate( '#authz-management .roles-perms ul input', 'click', function(){
	var name = $(this).attr('name');
	var def = 'n';

	if(!$(this).is(':checked')) {
		$(this).attr( 'checked', false );
		def = 'n';
	} else {
		$(this).attr( 'checked', true );
		def='Y';
	}

	$.post( 
		'authz-management.html',
		{
			par: name.slice(0, name.indexOf("--")), 
			child: name.slice(name.indexOf("--")+2, 
			name.length
		), 
		def: def, checkbox: true  } 
	);
});

//controls interaction from column 3 to column 2 a.k.a. attributes moving to a child of the selected attribute 
$(document).delegate( '#authz-management .possible-roles-perms ul li', 'click', function(){

	if($(this).parent().prev('h3').text()=='Role') {
		var child_type = 2;
	} else {
		var child_type = 1;
	}

	if($( '#authz-management select:last' ).val()!=2) {
		return false;
	}
	
	var info = { 
		par: $( '#attr_slug' ).val(),
		child: $(this).find('a').attr( 'rel' ), 
		child_type: child_type,  
		add_child: true 
	};

	$.post( 
		'authz-management.html', 
		info 
	);
	var desc = {
		parent_attribute : info.par, 
		child_attribute : info.child, 
		name : $(this).children(':first').children(':first').text(),
		child_type_id : child_type 
	}

	authz.display_child( desc );
		
	$(this).removeAttr( 'rel' ).hide();

});

//controls the [x] anchor
$(document).delegate( ' #authz-management .roles-perms ul a', 'click', function(){

	var attr = $(this).siblings( 'input[type=checkbox]' ).attr('name');
	attr = attr.slice(attr.indexOf('--')+2, attr.length);
	var attr_par = $( '#attr_slug' ).val();

	$.post( 'authz-management.html', { attr:attr, par_attr:attr_par, del:true } ); 

	$(this).parent().hide();

}); 

$(document).delegate( '#authz-management #col1 ul li', 'click', function(){
	authz.display( $(this).children('a').attr( 'rel' ) );
	$(this).parent().find('.selected').removeClass( 'selected' );
	$(this).addClass('selected');
});

$(document).delegate( '#authz-management .filter input', 'keyup', function(){

	var search = $(this).val();
	$(this).closest( 'div' ).siblings( 'ul' ).find( 'li' ).hide();
	var pattern = new RegExp('.*'+search+'.*', 'i');

	var results = $.grep($(this).closest( 'div' ).siblings( 'ul' ).find( 'li' ), function(el, index){
		return !(pattern.test($(el).find('label').text()) || pattern.test($(el).find('a').attr('rel')));
	}, true);
	$.each( results, function(index, el){
		$(this).closest( 'li' ).show();
	});
});
