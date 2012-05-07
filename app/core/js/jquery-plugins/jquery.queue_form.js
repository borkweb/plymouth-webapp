/*!
 * jQuery Queue Form - v1.0
 * Copyright (c) 2011 Plymouth State University - Matthew Batchelder
 * Dual licensed under the MIT license and GPL license.
 * https://github.com/imakewebthings/jquery-waypoints/blob/master/MIT-license.txt
 * https://github.com/imakewebthings/jquery-waypoints/blob/master/GPL-license.txt
 */

/**
 * Queue Form 
 */
;(function( $ ) {
	$.widget('psu.queue_form', {

		/**
		 * Use the _create method to bind events and inject markup
		 */
		_create: function() {
			this.element.on('submit', function( e ) {
				e.preventDefault();
				$( this ).data('queue_form').submit();
			});

			this.options.button = this.element.find('button[type=submit],input[type=submit');
		},

		/**
		 * default functionality in _init to set up this bad boy
		 */
		_init: function() {
			// reset the queue
			this.options.queue = [];

			// if no url is passed with the options, use the form's action
			if( ! this.options.url ) {
				this.options.url = this.element.attr('action');
			}//end if
		},

		/**
		 * adds parameters to the submission queue. parameters will be formatted like so:
		 * 			field[index] = value   e.g. items[5] = 'bacon'
		 *
		 * @param string field _POST variable name
		 * @param string index _POST variable index
		 * @param string value value to submit
		 */
		queue: function( field, index, value ) {
			// if the queue field doesn't exist, initialize it
			if( ! this.options.queue[ field ] ) {
				this.options.queue[ field ] = {};
			}//end if

			this.options.queue[ field ][ index ] = value;
		},

		/**
		 * execute a submission by notifying a button, building a parameter list
		 * and firing off an ajax call
		 */
		submit: function() {
			var self = this;
			var params = {};

			this._update_button( 'wait' );

			for( field in this.options.queue ) {
				for( index in this.options.queue[ field ] ) {
					param = new Object();
					prop = field +'[' + index + ']';
					param[prop] = this.options.queue[ field ][ index ];

					$.extend( params, param );

					// delete the variable from the queue
					delete this.options.queue[ field ][ index ];
				}//end for
			}//end for

			// don't forget about the other form fields
			$.extend( params, this.element.serialize() );

			// make the request
			var result_xhr = $.ajax( this.options.url, {
				type: this.options.method,
				data: params,
				success: function( data ) {
					if( 'success' === data ) {
						self._update_button( 'saved' );
					} else {
						self._update_button( 'fail' );
					}//end else
				}
			});
		},

		/**
		 * update the button's state
		 */
		_update_button: function( setting ) {
			for( key in this.options.classes ) {
				if( key !== setting ) {
					this.options.button.removeClass( this.options.classes[ key ] );
				}//end if
			}//end for

			this.options.button
				.addClass( this.options.classes[ setting ] )
				.html( this.options.text[ setting ] );

			if( this.options.disabled[ setting ] ) {
				this.options.button.attr('disabled', 'disabled');
			} else {
				this.options.button.removeAttr('disabled');
			}//end else
		},

		/**
		 * here are the options for this widget. Exciting.
		 */
		options: {
			method: 'POST',
			queue: {},
			url: null,
			disabled: {
				wait: true,
				saved: true,
				fail: true,
				save: false,
				await: true
			},
			text: {
				wait: 'Please wait...',
				saved: 'Changes Saved!',
				fail: 'Changes Failed To Save!',
				save: 'Save Changes',
				await: 'Save Changes'
			},
			classes: {
				wait: 'pending',
				saved: 'saved',
				fail: 'danger',
				save: 'primary',
				await: 'disabled'
			}
		}
	});
})( jQuery );

