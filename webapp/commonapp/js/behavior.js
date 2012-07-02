psu.commonapp = {
	// Track number of users in the "errors" table who are no longer applicants.
	not_applicants: 0,
	
	noemail_start: function() {
		var pidms = [];

		$('#missing tr.userrow.missing').each(function(o){
			pidms.push( $(this).attr('id').substr(5) );
		});
		
		// bail if all records have been updated
		if( pidms.length == 0 ) {
			return;
		}

		psu.commonapp.noemail_fetch( pidms );
	},

	noemail_fetch: function( pidms ) {
		pidms = pidms.join(",");
		$.post( BASE_URL + "/actions/noemail-fetch.php", {pidms:pidms}, psu.commonapp.noemail_update, 'json' );
	},

	noemail_update: function( data, status ) {
		$.each( data, function( key, value ) {
			var $tr = $('#pidm-' + key);			

			$tr.removeClass('missing');

			if( value.term_code_entry == false || (value.apdc_code != 'ND' && value.apdc_code != 'RD') ) {
				$tr.addClass( 'not-app' );
				psu.commonapp.not_applicants += 1;

				// just do this the first time through
				if( psu.commonapp.not_applicants == 1 ) {
					$('.cleanup').slideDown('slow');
				}
			}

			$.each( value, function( key, value ) {
				$tr.find('.' + key).text( value );
			});
		});

		// trigger the next round of updates
		psu.commonapp.noemail_start();
	}
};

$(function(){

if( $('body.provisioning').length ) {
	psu.commonapp.noemail_start();
}

});
