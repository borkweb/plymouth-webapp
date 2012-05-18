/*!
 * AmplifyJS 1.1.0 - Core, Store, Request
 * 
 * Copyright 2011 appendTo LLC. (http://appendto.com/team)
 * Dual licensed under the MIT or GPL licenses.
 * http://appendto.com/open-source-licenses
 * 
 * http://amplifyjs.com
 */
/*!
 * Amplify Core 1.1.0
 * 
 * Copyright 2011 appendTo LLC. (http://appendto.com/team)
 * Dual licensed under the MIT or GPL licenses.
 * http://appendto.com/open-source-licenses
 * 
 * http://amplifyjs.com
 */
(function( global, undefined ) {

var slice = [].slice,
	subscriptions = {};

var amplify = global.amplify = {
	publish: function( topic ) {
		var args = slice.call( arguments, 1 ),
			topicSubscriptions,
			subscription,
			length,
			i = 0,
			ret;

		if ( !subscriptions[ topic ] ) {
			return true;
		}

		topicSubscriptions = subscriptions[ topic ].slice();
		for ( length = topicSubscriptions.length; i < length; i++ ) {
			subscription = topicSubscriptions[ i ];
			ret = subscription.callback.apply( subscription.context, args );
			if ( ret === false ) {
				break;
			}
		}
		return ret !== false;
	},

	subscribe: function( topic, context, callback, priority ) {
		if ( arguments.length === 3 && typeof callback === "number" ) {
			priority = callback;
			callback = context;
			context = null;
		}
		if ( arguments.length === 2 ) {
			callback = context;
			context = null;
		}
		priority = priority || 10;

		var topicIndex = 0,
			topics = topic.split( /\s/ ),
			topicLength = topics.length,
			added;
		for ( ; topicIndex < topicLength; topicIndex++ ) {
			topic = topics[ topicIndex ];
			added = false;
			if ( !subscriptions[ topic ] ) {
				subscriptions[ topic ] = [];
			}
	
			var i = subscriptions[ topic ].length - 1,
				subscriptionInfo = {
					callback: callback,
					context: context,
					priority: priority
				};
	
			for ( ; i >= 0; i-- ) {
				if ( subscriptions[ topic ][ i ].priority <= priority ) {
					subscriptions[ topic ].splice( i + 1, 0, subscriptionInfo );
					added = true;
					break;
				}
			}

			if ( !added ) {
				subscriptions[ topic ].unshift( subscriptionInfo );
			}
		}

		return callback;
	},

	unsubscribe: function( topic, callback ) {
		if ( !subscriptions[ topic ] ) {
			return;
		}

		var length = subscriptions[ topic ].length,
			i = 0;

		for ( ; i < length; i++ ) {
			if ( subscriptions[ topic ][ i ].callback === callback ) {
				subscriptions[ topic ].splice( i, 1 );
				break;
			}
		}
	}
};

}( this ) );
/*!
 * Amplify Store - Persistent Client-Side Storage 1.1.0
 * 
 * Copyright 2011 appendTo LLC. (http://appendto.com/team)
 * Dual licensed under the MIT or GPL licenses.
 * http://appendto.com/open-source-licenses
 * 
 * http://amplifyjs.com
 */
(function( amplify, undefined ) {

var store = amplify.store = function( key, value, options, type ) {
	var type = store.type;
	if ( options && options.type && options.type in store.types ) {
		type = options.type;
	}
	return store.types[ type ]( key, value, options || {} );
};

store.types = {};
store.type = null;
store.addType = function( type, storage ) {
	if ( !store.type ) {
		store.type = type;
	}

	store.types[ type ] = storage;
	store[ type ] = function( key, value, options ) {
		options = options || {};
		options.type = type;
		return store( key, value, options );
	};
}
store.error = function() {
	return "amplify.store quota exceeded"; 
};

var rprefix = /^__amplify__/;
function createFromStorageInterface( storageType, storage ) {
	store.addType( storageType, function( key, value, options ) {
		var storedValue, parsed, i, remove,
			ret = value,
			now = (new Date()).getTime();

		if ( !key ) {
			ret = {};
			remove = [];
			i = 0;
			try {
				// accessing the length property works around a localStorage bug
				// in Firefox 4.0 where the keys don't update cross-page
				// we assign to key just to avoid Closure Compiler from removing
				// the access as "useless code"
				// https://bugzilla.mozilla.org/show_bug.cgi?id=662511
				key = storage.length;

				while ( key = storage.key( i++ ) ) {
					if ( rprefix.test( key ) ) {
						parsed = JSON.parse( storage.getItem( key ) );
						if ( parsed.expires && parsed.expires <= now ) {
							remove.push( key );
						} else {
							ret[ key.replace( rprefix, "" ) ] = parsed.data;
						}
					}
				}
				while ( key = remove.pop() ) {
					storage.removeItem( key );
				}
			} catch ( error ) {}
			return ret;
		}

		// protect against name collisions with direct storage
		key = "__amplify__" + key;

		if ( value === undefined ) {
			storedValue = storage.getItem( key );
			parsed = storedValue ? JSON.parse( storedValue ) : { expires: -1 };
			if ( parsed.expires && parsed.expires <= now ) {
				storage.removeItem( key );
			} else {
				return parsed.data;
			}
		} else {
			if ( value === null ) {
				storage.removeItem( key );
			} else {
				parsed = JSON.stringify({
					data: value,
					expires: options.expires ? now + options.expires : null
				});
				try {
					storage.setItem( key, parsed );
				// quota exceeded
				} catch( error ) {
					// expire old data and try again
					store[ storageType ]();
					try {
						storage.setItem( key, parsed );
					} catch( error ) {
						throw store.error();
					}
				}
			}
		}

		return ret;
	});
}

// localStorage + sessionStorage
// IE 8+, Firefox 3.5+, Safari 4+, Chrome 4+, Opera 10.5+, iPhone 2+, Android 2+
for ( var webStorageType in { localStorage: 1, sessionStorage: 1 } ) {
	// try/catch for file protocol in Firefox
	try {
		if ( window[ webStorageType ].getItem ) {
			createFromStorageInterface( webStorageType, window[ webStorageType ] );
		}
	} catch( e ) {}
}

// globalStorage
// non-standard: Firefox 2+
// https://developer.mozilla.org/en/dom/storage#globalStorage
if ( window.globalStorage ) {
	// try/catch for file protocol in Firefox
	try {
		createFromStorageInterface( "globalStorage",
			window.globalStorage[ window.location.hostname ] );
		// Firefox 2.0 and 3.0 have sessionStorage and globalStorage
		// make sure we default to globalStorage
		// but don't default to globalStorage in 3.5+ which also has localStorage
		if ( store.type === "sessionStorage" ) {
			store.type = "globalStorage";
		}
	} catch( e ) {}
}

// userData
// non-standard: IE 5+
// http://msdn.microsoft.com/en-us/library/ms531424(v=vs.85).aspx
(function() {
	// IE 9 has quirks in userData that are a huge pain
	// rather than finding a way to detect these quirks
	// we just don't register userData if we have localStorage
	if ( store.types.localStorage ) {
		return;
	}

	// append to html instead of body so we can do this from the head
	var div = document.createElement( "div" ),
		attrKey = "amplify";
	div.style.display = "none";
	document.getElementsByTagName( "head" )[ 0 ].appendChild( div );

	// we can't feature detect userData support
	// so just try and see if it fails
	// surprisingly, even just adding the behavior isn't enough for a failure
	// so we need to load the data as well
	try {
		div.addBehavior( "#default#userdata" );
		div.load( attrKey );
	} catch( e ) {
		div.parentNode.removeChild( div );
		return;
	}

	store.addType( "userData", function( key, value, options ) {
		div.load( attrKey );
		var attr, parsed, prevValue, i, remove,
			ret = value,
			now = (new Date()).getTime();

		if ( !key ) {
			ret = {};
			remove = [];
			i = 0;
			while ( attr = div.XMLDocument.documentElement.attributes[ i++ ] ) {
				parsed = JSON.parse( attr.value );
				if ( parsed.expires && parsed.expires <= now ) {
					remove.push( attr.name );
				} else {
					ret[ attr.name ] = parsed.data;
				}
			}
			while ( key = remove.pop() ) {
				div.removeAttribute( key );
			}
			div.save( attrKey );
			return ret;
		}

		// convert invalid characters to dashes
		// http://www.w3.org/TR/REC-xml/#NT-Name
		// simplified to assume the starting character is valid
		// also removed colon as it is invalid in HTML attribute names
		key = key.replace( /[^-._0-9A-Za-z\xb7\xc0-\xd6\xd8-\xf6\xf8-\u037d\u37f-\u1fff\u200c-\u200d\u203f\u2040\u2070-\u218f]/g, "-" );

		if ( value === undefined ) {
			attr = div.getAttribute( key );
			parsed = attr ? JSON.parse( attr ) : { expires: -1 };
			if ( parsed.expires && parsed.expires <= now ) {
				div.removeAttribute( key );
			} else {
				return parsed.data;
			}
		} else {
			if ( value === null ) {
				div.removeAttribute( key );
			} else {
				// we need to get the previous value in case we need to rollback
				prevValue = div.getAttribute( key );
				parsed = JSON.stringify({
					data: value,
					expires: (options.expires ? (now + options.expires) : null)
				});
				div.setAttribute( key, parsed );
			}
		}

		try {
			div.save( attrKey );
		// quota exceeded
		} catch ( error ) {
			// roll the value back to the previous value
			if ( prevValue === null ) {
				div.removeAttribute( key );
			} else {
				div.setAttribute( key, prevValue );
			}

			// expire old data and try again
			store.userData();
			try {
				div.setAttribute( key, parsed );
				div.save( attrKey );
			} catch ( error ) {
				// roll the value back to the previous value
				if ( prevValue === null ) {
					div.removeAttribute( key );
				} else {
					div.setAttribute( key, prevValue );
				}
				throw store.error();
			}
		}
		return ret;
	});
}() );

// in-memory storage
// fallback for all browsers to enable the API even if we can't persist data
(function() {
	var memory = {},
		timeout = {};

	function copy( obj ) {
		return obj === undefined ? undefined : JSON.parse( JSON.stringify( obj ) );
	}

	store.addType( "memory", function( key, value, options ) {
		if ( !key ) {
			return copy( memory );
		}

		if ( value === undefined ) {
			return copy( memory[ key ] );
		}

		if ( timeout[ key ] ) {
			clearTimeout( timeout[ key ] );
			delete timeout[ key ];
		}

		if ( value === null ) {
			delete memory[ key ];
			return null;
		}

		memory[ key ] = value;
		if ( options.expires ) {
			timeout[ key ] = setTimeout(function() {
				delete memory[ key ];
				delete timeout[ key ];
			}, options.expires );
		}

		return value;
	});
}() );

}( this.amplify = this.amplify || {} ) );
/*!
 * Amplify Request 1.1.0
 * 
 * Copyright 2011 appendTo LLC. (http://appendto.com/team)
 * Dual licensed under the MIT or GPL licenses.
 * http://appendto.com/open-source-licenses
 * 
 * http://amplifyjs.com
 */
(function( amplify, undefined ) {

function noop() {}
function isFunction( obj ) {
	return ({}).toString.call( obj ) === "[object Function]";
}

function async( fn ) {
	var isAsync = false;
	setTimeout(function() {
		isAsync = true;
	}, 1 );
	return function() {
		var that = this,
			args = arguments;
		if ( isAsync ) {
			fn.apply( that, args );
		} else {
			setTimeout(function() {
				fn.apply( that, args );
			}, 1 );
		}
	};
}

amplify.request = function( resourceId, data, callback ) {
	// default to an empty hash just so we can handle a missing resourceId
	// in one place
	var settings = resourceId || {};

	if ( typeof settings === "string" ) {
		if ( isFunction( data ) ) {
			callback = data;
			data = {};
		}
		settings = {
			resourceId: resourceId,
			data: data || {},
			success: callback
		};
	}

	var request = { abort: noop },
		resource = amplify.request.resources[ settings.resourceId ],
		success = settings.success || noop,
		error = settings.error || noop;
	settings.success = async( function( data, status ) {
		status = status || "success";
		amplify.publish( "request.success", settings, data, status );
		amplify.publish( "request.complete", settings, data, status );
		success( data, status );
	});
	settings.error = async( function( data, status ) {
		status = status || "error";
		amplify.publish( "request.error", settings, data, status );
		amplify.publish( "request.complete", settings, data, status );
		error( data, status );
	});

	if ( !resource ) {
		if ( !settings.resourceId ) {
			throw "amplify.request: no resourceId provided";
		}
		throw "amplify.request: unknown resourceId: " + settings.resourceId;
	}

	if ( !amplify.publish( "request.before", settings ) ) {
		settings.error( null, "abort" );
		return;
	}

	amplify.request.resources[ settings.resourceId ]( settings, request );
	return request;
};

amplify.request.types = {};
amplify.request.resources = {};
amplify.request.define = function( resourceId, type, settings ) {
	if ( typeof type === "string" ) {
		if ( !( type in amplify.request.types ) ) {
			throw "amplify.request.define: unknown type: " + type;
		}

		settings.resourceId = resourceId;
		amplify.request.resources[ resourceId ] =
			amplify.request.types[ type ]( settings );
	} else {
		// no pre-processor or settings for one-off types (don't invoke)
		amplify.request.resources[ resourceId ] = type;
	}
};

}( amplify ) );





(function( amplify, $, undefined ) {

var xhrProps = [ "status", "statusText", "responseText", "responseXML", "readyState" ],
    rurlData = /\{([^\}]+)\}/g;

amplify.request.types.ajax = function( defnSettings ) {
	defnSettings = $.extend({
		type: "GET"
	}, defnSettings );

	return function( settings, request ) {
		var xhr,
			url = defnSettings.url,
			abort = request.abort,
			ajaxSettings = $.extend( true, {}, defnSettings, { data: settings.data } ),
			aborted = false,
			ampXHR = {
				readyState: 0,
				setRequestHeader: function( name, value ) {
					return xhr.setRequestHeader( name, value );
				},
				getAllResponseHeaders: function() {
					return xhr.getAllResponseHeaders();
				},
				getResponseHeader: function( key ) {
					return xhr.getResponseHeader( key );
				},
				overrideMimeType: function( type ) {
					return xhr.overrideMideType( type );
				},
				abort: function() {
					aborted = true;
					try {
						xhr.abort();
					// IE 7 throws an error when trying to abort
					} catch( e ) {}
					handleResponse( null, "abort" );
				},
				success: function( data, status ) {
					settings.success( data, status );
				},
				error: function( data, status ) {
					settings.error( data, status );
				}
			};

		amplify.publish( "request.ajax.preprocess",
			defnSettings, settings, ajaxSettings, ampXHR );

		$.extend( ajaxSettings, {
			success: function( data, status ) {
				handleResponse( data, status );
			},
			error: function( _xhr, status ) {
				handleResponse( null, status );
			},
			beforeSend: function( _xhr, _ajaxSettings ) {
				xhr = _xhr;
				ajaxSettings = _ajaxSettings;
				var ret = defnSettings.beforeSend ?
					defnSettings.beforeSend.call( this, ampXHR, ajaxSettings ) : true;
				return ret && amplify.publish( "request.before.ajax",
					defnSettings, settings, ajaxSettings, ampXHR );
			}
		});
		$.ajax( ajaxSettings );

		function handleResponse( data, status ) {
			$.each( xhrProps, function( i, key ) {
				try {
					ampXHR[ key ] = xhr[ key ];
				} catch( e ) {}
			});
			// Playbook returns "HTTP/1.1 200 OK"
			// TODO: something also returns "OK", what?
			if ( /OK$/.test( ampXHR.statusText ) ) {
				ampXHR.statusText = "success";
			}
			if ( data === undefined ) {
				// TODO: add support for ajax errors with data
				data = null;
			}
			if ( aborted ) {
				status = "abort";
			}
			if ( /timeout|error|abort/.test( status ) ) {
				ampXHR.error( data, status );
			} else {
				ampXHR.success( data, status );
			}
			// avoid handling a response multiple times
			// this can happen if a request is aborted
			// TODO: figure out if this breaks polling or multi-part responses
			handleResponse = $.noop;
		}

		request.abort = function() {
			ampXHR.abort();
			abort.call( this );
		};
	};
};



amplify.subscribe( "request.ajax.preprocess", function( defnSettings, settings, ajaxSettings ) {
	var mappedKeys = [],
		data = ajaxSettings.data;

	if ( typeof data === "string" ) {
		return;
	}

	data = $.extend( true, {}, defnSettings.data, data );

	ajaxSettings.url = ajaxSettings.url.replace( rurlData, function ( m, key ) {
		if ( key in data ) {
		    mappedKeys.push( key );
		    return data[ key ];
		}
	});

	// We delete the keys later so duplicates are still replaced
	$.each( mappedKeys, function ( i, key ) {
		delete data[ key ];
	});

	ajaxSettings.data = data;
});



amplify.subscribe( "request.ajax.preprocess", function( defnSettings, settings, ajaxSettings ) {
	var data = ajaxSettings.data,
		dataMap = defnSettings.dataMap;

	if ( !dataMap || typeof data === "string" ) {
		return;
	}

	if ( $.isFunction( dataMap ) ) {
		ajaxSettings.data = dataMap( data );
	} else {
		$.each( defnSettings.dataMap, function( orig, replace ) {
			if ( orig in data ) {
				data[ replace ] = data[ orig ];
				delete data[ orig ];
			}
		});
		ajaxSettings.data = data;
	}
});



var cache = amplify.request.cache = {
	_key: function( resourceId, url, data ) {
		data = url + data;
		var length = data.length,
			i = 0,
			checksum = chunk();

		while ( i < length ) {
			checksum ^= chunk();
		}

		function chunk() {
			return data.charCodeAt( i++ ) << 24 |
				data.charCodeAt( i++ ) << 16 |
				data.charCodeAt( i++ ) << 8 |
				data.charCodeAt( i++ ) << 0;
		}

		return "request-" + resourceId + "-" + checksum;
	},

	_default: (function() {
		var memoryStore = {};
		return function( resource, settings, ajaxSettings, ampXHR ) {
			// data is already converted to a string by the time we get here
			var cacheKey = cache._key( settings.resourceId,
					ajaxSettings.url, ajaxSettings.data ),
				duration = resource.cache;

			if ( cacheKey in memoryStore ) {
				ampXHR.success( memoryStore[ cacheKey ] );
				return false;
			}
			var success = ampXHR.success;
			ampXHR.success = function( data ) {
				memoryStore[ cacheKey ] = data;
				if ( typeof duration === "number" ) {
					setTimeout(function() {
						delete memoryStore[ cacheKey ];
					}, duration );
				}
				success.apply( this, arguments );
			};
		};
	}())
};

if ( amplify.store ) {
	$.each( amplify.store.types, function( type ) {
		cache[ type ] = function( resource, settings, ajaxSettings, ampXHR ) {
			var cacheKey = cache._key( settings.resourceId,
					ajaxSettings.url, ajaxSettings.data ),
				cached = amplify.store[ type ]( cacheKey );

			if ( cached ) {
				ajaxSettings.success( cached );
				return false;
			}
			var success = ampXHR.success;
			ampXHR.success = function( data ) {	
				amplify.store[ type ]( cacheKey, data, { expires: resource.cache.expires } );
				success.apply( this, arguments );
			};
		};
	});
	cache.persist = cache[ amplify.store.type ];
}

amplify.subscribe( "request.before.ajax", function( resource ) {
	var cacheType = resource.cache;
	if ( cacheType ) {
		// normalize between objects and strings/booleans/numbers
		cacheType = cacheType.type || cacheType;
		return cache[ cacheType in cache ? cacheType : "_default" ]
			.apply( this, arguments );
	}
});



amplify.request.decoders = {
	// http://labs.omniti.com/labs/jsend
	jsend: function( data, status, ampXHR, success, error ) {
		if ( data.status === "success" ) {
			success( data.data );
		} else if ( data.status === "fail" ) {
			error( data.data, "fail" );
		} else if ( data.status === "error" ) {
			delete data.status;
			error( data, "error" );
		}
	}
};

amplify.subscribe( "request.before.ajax", function( resource, settings, ajaxSettings, ampXHR ) {
	var _success = ampXHR.success,
		_error = ampXHR.error,
		decoder = $.isFunction( resource.decoder )
			? resource.decoder
			: resource.decoder in amplify.request.decoders
				? amplify.request.decoders[ resource.decoder ]
				: amplify.request.decoders._default;

	if ( !decoder ) {
		return;
	}

	function success( data, status ) {
		_success( data, status );
	}
	function error( data, status ) {
		_error( data, status );
	}
	ampXHR.success = function( data, status ) {
		decoder( data, status, ampXHR, success, error );
	};
	ampXHR.error = function( data, status ) {
		decoder( data, status, ampXHR, success, error );
	};
});

}( amplify, jQuery ) );
/**
 * Multi-level Drop Down Menu 2.0
 * August 22, 2009
 * Corey Hart @ http://www.codenothing.com
 */ 
;(function(a){a.fn.reverse=[].reverse;a.fn.dropDownMenu=function(b){return this.each(function(){var d=a(this),j=[],g,l,c,e,h=a.extend({timer:500,parentMO:null,childMO:null,levels:[],numberOfLevels:5},b||{},a.metadata?d.metadata():{});if(h.levels.length){h.numberOfLevels=h.levels.length}else{h.levels[0]=h.parentMO?h.parentMO:h.childMO;for(var k=1;k<h.numberOfLevels+1;k++){h.levels[k]=h.childMO}}j[0]=d.children("li");for(var k=1;k<h.numberOfLevels+1;k++){g=h.levels[k-1];j[k]=j[k-1].children("ul").children("li");j[k-1].mouseover(function(){c=a(this);e=c.children("a");if(l){clearTimeout(l)}a("a",c.siblings("li")).each(function(){var i=a(this),m=i.data("classname");if(i.hasClass(m)){i.removeClass(m)}});c.siblings("li").find("ul:visible").reverse().hide();e.addClass(e.data("classname")).siblings("ul").show()}).mouseout(function(){if(a(this).children("a").data("classname")==h.levels[0]){l=setTimeout(f,h.timer)}}).children("a").data("classname",g)}a(document).click(f);function f(){a("a",d).each(function(){var i=a(this),m=i.data("classname");if(i.hasClass(m)){i.removeClass(m)}});a("ul:visible",d).reverse().hide();if(l){clearTimeout(l)}}})}})(jQuery);
/**
 * @preserve PSU behavior.js
 *
 * Copyright 2010 Plymouth State University
 */

// Cached jQuery object, mostly for use with delegate:
//     $.root.delegate('a', 'click', callback);
$.root = $(document);

// document.ready execution
// ------------------------

$(function(){
	// Konami code FTW.
	if( typeof(HOST) == "string" ) {
		var s = document.createElement('script');
		s.type='text/javascript';
		document.body.appendChild(s);
		s.src = HOST + '/webapp/my/js/konami.js';
	}

	if(typeof $.fn.dropDownMenu == 'function')
	{
		$('.webapp-nav ul:first').dropDownMenu({timer:500, parentMO: 'parent-hover', childMO: 'child-hover1'});
	}//end if

	$('.webapp-nav ul ul ul').parent().children('a').addClass('menu-expand');

	if( $.fn.tablesorter ) {
		$('table.sortable').tablesorter();
	}
});

// Add a nice animation to the "Return to Top" link in the webapp
// style footer.
$('#return-to-top').live('click', function(e){
	e.preventDefault();
	$('html, body').animate({scrollTop:0}, 'fast');
});

// Old top-level namespace for PSU functionality. Use `psu`, instead.
$.psu = $.psu || {};

// Top-level namespace for PSU functionality. Extend with your own app-specific functionality.
(function(){

this.psu = {
	host: null
};

if( typeof(HOST) == "string" ) {
	this.psu.host = HOST;
} else {
	this.psu.host = document.location.protocol + '//';

	if( ".dev.plymouth.edu" == document.location.host.substr(-17) ) {
		this.psu.host = this.psu.host + "www.dev.plymouth.edu";
	} else {
		this.psu.host = this.psu.host + "www.plymouth.edu";
	}
}

})();

/**
 * PSU data lazy loading.
 */
(function($){
	var queue = [],
		job_id = 0;

	$.fn.psu_lazyload = function() {
		if( false == $.fn.psu_lazyload.settings.endpoint ) {
			return this;
		}

		var ret = this.each(function(){
			if( $(this).hasClass( 'psu-lazyload-complete' ) ) {
				return;
			}

			queue.push(this);

			if( 0 === job_id ) {
				job_id = setTimeout( lazy_initiate, 500 );
			}
		});

		return ret;
	};

	$.fn.psu_lazyload.settings = {
		batch_count: 20,
		endpoint: false
	};

	function lazy_initiate() {
		var nodes = {},
			ids = [],
			$node;

		while( node = queue.shift() ) {
			$node = $(node);
			var id = $node.data('id');

			if( typeof nodes[id] === 'undefined' ) {
				nodes[id] = $node;
			} else {
				nodes[id] = nodes[id].add( $node );
			}

			ids.push(id);

			if( ids.length >= $.fn.psu_lazyload.settings.batch_count ) {
				break;
			}
		}

		if( ids.length > 0 ) {
			$.getJSON( $.fn.psu_lazyload.settings.endpoint, {id:ids}, lazy_populate.bind(nodes) );
		}
	}

	function lazy_populate( data, ts, xhr ) {
		var nodes_by_id = this;

		// immediately start a new job
		job_id = setTimeout( lazy_initiate, 0 );
		
		$.each(data, function(id, person_data){
			var $nodes = nodes_by_id[id];

			$nodes.addClass( 'psu-lazyload-complete' );

			$nodes.find('.lazy-field').each(function(){
				var field = $(this),
					type = field.data('type');

				field.text( person_data[type] );
			});
		});
	}

	$(function(){
		$('.psu-lazyload').psu_lazyload()
	});
})(jQuery);

// Add a message to the messages block.
//
//     $.psu.addMessage('Hey, something bad happened.', 'error');
$.psu.addMessage = function( msg, type ) {
	var li = $('<li/>').text(msg);

	switch(type) {
		case 'success': type = 'successes'; break;
		case 'warning': type = 'warnings'; break;
		case 'error': type = 'errors'; break;
		default: type = 'messages';
	}

	$('#webapp-avant-body .message-' + type).find('ul').append(li).end().show();
};

// Add a general purpose function we can use instead of console.log, which
// is not supported in Internet Explorer.
window.log = function(){
  log.history = log.history || [];   // store logs to an array for reference
  log.history.push(arguments);
  if(this.console){
    console.log( Array.prototype.slice.call(arguments) );
  }
};
/*!
 * jQuery doTimeout: Like setTimeout, but better! - v1.0 - 3/3/2010
 * http://benalman.com/projects/jquery-dotimeout-plugin/
 * 
 * Copyright (c) 2010 "Cowboy" Ben Alman
 * Dual licensed under the MIT and GPL licenses.
 * http://benalman.com/about/license/
 */

// Script: jQuery doTimeout: Like setTimeout, but better!
//
// *Version: 1.0, Last updated: 3/3/2010*
// 
// Project Home - http://benalman.com/projects/jquery-dotimeout-plugin/
// GitHub       - http://github.com/cowboy/jquery-dotimeout/
// Source       - http://github.com/cowboy/jquery-dotimeout/raw/master/jquery.ba-dotimeout.js
// (Minified)   - http://github.com/cowboy/jquery-dotimeout/raw/master/jquery.ba-dotimeout.min.js (1.0kb)
// 
// About: License
// 
// Copyright (c) 2010 "Cowboy" Ben Alman,
// Dual licensed under the MIT and GPL licenses.
// http://benalman.com/about/license/
// 
// About: Examples
// 
// These working examples, complete with fully commented code, illustrate a few
// ways in which this plugin can be used.
// 
// Debouncing      - http://benalman.com/code/projects/jquery-dotimeout/examples/debouncing/
// Delays, Polling - http://benalman.com/code/projects/jquery-dotimeout/examples/delay-poll/
// Hover Intent    - http://benalman.com/code/projects/jquery-dotimeout/examples/hoverintent/
// 
// About: Support and Testing
// 
// Information about what version or versions of jQuery this plugin has been
// tested with, what browsers it has been tested in, and where the unit tests
// reside (so you can test it yourself).
// 
// jQuery Versions - 1.3.2, 1.4.2
// Browsers Tested - Internet Explorer 6-8, Firefox 2-3.6, Safari 3-4, Chrome 4-5, Opera 9.6-10.1.
// Unit Tests      - http://benalman.com/code/projects/jquery-dotimeout/unit/
// 
// About: Release History
// 
// 1.0 - (3/3/2010) Callback can now be a string, in which case it will call
//       the appropriate $.method or $.fn.method, depending on where .doTimeout
//       was called. Callback must now return `true` (not just a truthy value)
//       to poll.
// 0.4 - (7/15/2009) Made the "id" argument optional, some other minor tweaks
// 0.3 - (6/25/2009) Initial release

(function($){
  '$:nomunge'; // Used by YUI compressor.
  
  var cache = {},
    
    // Reused internal string.
    doTimeout = 'doTimeout',
    
    // A convenient shortcut.
    aps = Array.prototype.slice;
  
  // Method: jQuery.doTimeout
  // 
  // Initialize, cancel, or force execution of a callback after a delay.
  // 
  // If delay and callback are specified, a doTimeout is initialized. The
  // callback will execute, asynchronously, after the delay. If an id is
  // specified, this doTimeout will override and cancel any existing doTimeout
  // with the same id. Any additional arguments will be passed into callback
  // when it is executed.
  // 
  // If the callback returns true, the doTimeout loop will execute again, after
  // the delay, creating a polling loop until the callback returns a non-true
  // value.
  // 
  // Note that if an id is not passed as the first argument, this doTimeout will
  // NOT be able to be manually canceled or forced. (for debouncing, be sure to
  // specify an id).
  // 
  // If id is specified, but delay and callback are not, the doTimeout will be
  // canceled without executing the callback. If force_mode is specified, the
  // callback will be executed, synchronously, but will only be allowed to
  // continue a polling loop if force_mode is true (provided the callback
  // returns true, of course). If force_mode is false, no polling loop will
  // continue, even if the callback returns true.
  // 
  // Usage:
  // 
  // > jQuery.doTimeout( [ id, ] delay, callback [, arg ... ] );
  // > jQuery.doTimeout( id [, force_mode ] );
  // 
  // Arguments:
  // 
  //  id - (String) An optional unique identifier for this doTimeout. If id is
  //    not specified, the doTimeout will NOT be able to be manually canceled or
  //    forced.
  //  delay - (Number) A zero-or-greater delay in milliseconds after which
  //    callback will be executed. 
  //  callback - (Function) A function to be executed after delay milliseconds.
  //  callback - (String) A jQuery method to be executed after delay
  //    milliseconds. This method will only poll if it explicitly returns
  //    true.
  //  force_mode - (Boolean) If true, execute that id's doTimeout callback
  //    immediately and synchronously, continuing any callback return-true
  //    polling loop. If false, execute the callback immediately and
  //    synchronously but do NOT continue a callback return-true polling loop.
  //    If omitted, cancel that id's doTimeout.
  // 
  // Returns:
  // 
  //  If force_mode is true, false or undefined and there is a
  //  yet-to-be-executed callback to cancel, true is returned, but if no
  //  callback remains to be executed, undefined is returned.
  
  $[doTimeout] = function() {
    return p_doTimeout.apply( window, [ 0 ].concat( aps.call( arguments ) ) );
  };
  
  // Method: jQuery.fn.doTimeout
  // 
  // Initialize, cancel, or force execution of a callback after a delay.
  // Operates like <jQuery.doTimeout>, but the passed callback executes in the
  // context of the jQuery collection of elements, and the id is stored as data
  // on the first element in that collection.
  // 
  // If delay and callback are specified, a doTimeout is initialized. The
  // callback will execute, asynchronously, after the delay. If an id is
  // specified, this doTimeout will override and cancel any existing doTimeout
  // with the same id. Any additional arguments will be passed into callback
  // when it is executed.
  // 
  // If the callback returns true, the doTimeout loop will execute again, after
  // the delay, creating a polling loop until the callback returns a non-true
  // value.
  // 
  // Note that if an id is not passed as the first argument, this doTimeout will
  // NOT be able to be manually canceled or forced (for debouncing, be sure to
  // specify an id).
  // 
  // If id is specified, but delay and callback are not, the doTimeout will be
  // canceled without executing the callback. If force_mode is specified, the
  // callback will be executed, synchronously, but will only be allowed to
  // continue a polling loop if force_mode is true (provided the callback
  // returns true, of course). If force_mode is false, no polling loop will
  // continue, even if the callback returns true.
  // 
  // Usage:
  // 
  // > jQuery('selector').doTimeout( [ id, ] delay, callback [, arg ... ] );
  // > jQuery('selector').doTimeout( id [, force_mode ] );
  // 
  // Arguments:
  // 
  //  id - (String) An optional unique identifier for this doTimeout, stored as
  //    jQuery data on the element. If id is not specified, the doTimeout will
  //    NOT be able to be manually canceled or forced.
  //  delay - (Number) A zero-or-greater delay in milliseconds after which
  //    callback will be executed. 
  //  callback - (Function) A function to be executed after delay milliseconds.
  //  callback - (String) A jQuery.fn method to be executed after delay
  //    milliseconds. This method will only poll if it explicitly returns
  //    true (most jQuery.fn methods return a jQuery object, and not `true`,
  //    which allows them to be chained and prevents polling).
  //  force_mode - (Boolean) If true, execute that id's doTimeout callback
  //    immediately and synchronously, continuing any callback return-true
  //    polling loop. If false, execute the callback immediately and
  //    synchronously but do NOT continue a callback return-true polling loop.
  //    If omitted, cancel that id's doTimeout.
  // 
  // Returns:
  // 
  //  When creating a <jQuery.fn.doTimeout>, the initial jQuery collection of
  //  elements is returned. Otherwise, if force_mode is true, false or undefined
  //  and there is a yet-to-be-executed callback to cancel, true is returned,
  //  but if no callback remains to be executed, undefined is returned.
  
  $.fn[doTimeout] = function() {
    var args = aps.call( arguments ),
      result = p_doTimeout.apply( this, [ doTimeout + args[0] ].concat( args ) );
    
    return typeof args[0] === 'number' || typeof args[1] === 'number'
      ? this
      : result;
  };
  
  function p_doTimeout( jquery_data_key ) {
    var that = this,
      elem,
      data = {},
      
      // Allows the plugin to call a string callback method.
      method_base = jquery_data_key ? $.fn : $,
      
      // Any additional arguments will be passed to the callback.
      args = arguments,
      slice_args = 4,
      
      id        = args[1],
      delay     = args[2],
      callback  = args[3];
    
    if ( typeof id !== 'string' ) {
      slice_args--;
      
      id        = jquery_data_key = 0;
      delay     = args[1];
      callback  = args[2];
    }
    
    // If id is passed, store a data reference either as .data on the first
    // element in a jQuery collection, or in the internal cache.
    if ( jquery_data_key ) { // Note: key is 'doTimeout' + id
      
      // Get id-object from the first element's data, otherwise initialize it to {}.
      elem = that.eq(0);
      elem.data( jquery_data_key, data = elem.data( jquery_data_key ) || {} );
      
    } else if ( id ) {
      // Get id-object from the cache, otherwise initialize it to {}.
      data = cache[ id ] || ( cache[ id ] = {} );
    }
    
    // Clear any existing timeout for this id.
    data.id && clearTimeout( data.id );
    delete data.id;
    
    // Clean up when necessary.
    function cleanup() {
      if ( jquery_data_key ) {
        elem.removeData( jquery_data_key );
      } else if ( id ) {
        delete cache[ id ];
      }
    };
    
    // Yes, there actually is a setTimeout call in here!
    function actually_setTimeout() {
      data.id = setTimeout( function(){ data.fn(); }, delay );
    };
    
    if ( callback ) {
      // A callback (and delay) were specified. Store the callback reference for
      // possible later use, and then setTimeout.
      data.fn = function( no_polling_loop ) {
        
        // If the callback value is a string, it is assumed to be the name of a
        // method on $ or $.fn depending on where doTimeout was executed.
        if ( typeof callback === 'string' ) {
          callback = method_base[ callback ];
        }
        
        callback.apply( that, aps.call( args, slice_args ) ) === true && !no_polling_loop
          
          // Since the callback returned true, and we're not specifically
          // canceling a polling loop, do it again!
          ? actually_setTimeout()
          
          // Otherwise, clean up and quit.
          : cleanup();
      };
      
      // Set that timeout!
      actually_setTimeout();
      
    } else if ( data.fn ) {
      // No callback passed. If force_mode (delay) is true, execute the data.fn
      // callback immediately, continuing any callback return-true polling loop.
      // If force_mode is false, execute the data.fn callback immediately but do
      // NOT continue a callback return-true polling loop. If force_mode is
      // undefined, simply clean up. Since data.fn was still defined, whatever
      // was supposed to happen hadn't yet, so return true.
      delay === undefined ? cleanup() : data.fn( delay === false );
      return true;
      
    } else {
      // Since no callback was passed, and data.fn isn't defined, it looks like
      // whatever was supposed to happen already did. Clean up and quit!
      cleanup();
    }
    
  };
  
})(jQuery);
/*!
jQuery Waypoints - v1.1.6
Copyright (c) 2011-2012 Caleb Troughton
Dual licensed under the MIT license and GPL license.
https://github.com/imakewebthings/jquery-waypoints/blob/master/MIT-license.txt
https://github.com/imakewebthings/jquery-waypoints/blob/master/GPL-license.txt
*/

/*
Waypoints is a small jQuery plugin that makes it easy to execute a function
whenever you scroll to an element.

GitHub Repository: https://github.com/imakewebthings/jquery-waypoints
Documentation and Examples: http://imakewebthings.github.com/jquery-waypoints

Changelog:
	v1.1.6
		- Fix potential memory leak by unbinding events on empty context elements.
	v1.1.5
		- Make plugin compatible with Browserify/RequireJS. (Thanks @cjroebuck)
	v1.1.4
		- Add handler option to give alternate binding method. (Issue #34)
	v1.1.3
		- Fix cases where waypoints are added post-load and should be triggered
		  immediately. (Issue #28)
	v1.1.2
		- Fixed error thrown by waypoints with triggerOnce option that were
			triggered via resize refresh.
	v1.1.1
		- Fixed bug in initialization where all offsets were being calculated
		  as if set to 0 initially, causing unwarranted triggers during the
		  subsequent refresh.
		- Added onlyOnScroll, an option for individual waypoints that disables
		  triggers due to an offset refresh that crosses the current scroll
		  point. (All credit to @knuton on this one.)
	v1.1
		- Moved the continuous option out of global settings and into the options
		  object for individual waypoints.
		- Added the context option, which allows for using waypoints within any
		  scrollable element, not just the window.
	v1.0.2
		- Moved scroll and resize handler bindings out of load.  Should play nicer
		  with async loaders like Head JS and LABjs.
		- Fixed a 1px off error when using certain % offsets.
		- Added unit tests.
	v1.0.1
		- Added $.waypoints('viewportHeight').
		- Fixed iOS bug (using the new viewportHeight method).
		- Added offset function alias: 'bottom-in-view'.
	v1.0
		- Initial release.
	
Support:
	- jQuery versions 1.4.3+
	- IE6+, FF3+, Chrome 6+, Safari 4+, Opera 11
	- Other versions and browsers may work, these are just the ones I've looked at.
*/

(function($, wp, wps, window, undefined){
	'$:nomunge';
	
	var $w = $(window),
	
	// Keeping common strings as variables = better minification
	eventName = 'waypoint.reached',
	
	/*
	For the waypoint and direction passed in, trigger the waypoint.reached
	event and deal with the triggerOnce option.
	*/
	triggerWaypoint = function(way, dir) {
		way.element.trigger(eventName, dir);
		if (way.options.triggerOnce) {
			way.element[wp]('destroy');
		}
	},
	
	/*
	Given a jQuery element and Context, returns the index of that element in the waypoints
	array.  Returns the index, or -1 if the element is not a waypoint.
	*/
	waypointIndex = function(el, context) {
		if (!context) return -1;
		var i = context.waypoints.length - 1;
		while (i >= 0 && context.waypoints[i].element[0] !== el[0]) {
			i -= 1;
		}
		return i;
	},
	
	// Private list of all elements used as scrolling contexts for waypoints.
	contexts = [],
	
	/*
	Context Class - represents a scrolling context.  Properties include:
		element: jQuery object containing a single HTML element.
		waypoints: Array of waypoints operating under this scroll context.
		oldScroll: Keeps the previous scroll position to determine scroll direction.
		didScroll: Flag used in scrolling the context's scroll event.
		didResize: Flag used in scrolling the context's resize event.
		doScroll: Function that checks for crossed waypoints. Called from throttler.
	*/
	Context = function(context) {
		$.extend(this, {
			element: $(context),
			oldScroll: 0,
			
			/*
			List of all elements that have been registered as waypoints.
			Each object in the array contains:
				element: jQuery object containing a single HTML element.
				offset: The window scroll offset, in px, that triggers the waypoint event.
				options: Options object that was passed to the waypoint fn function.
			*/
			'waypoints': [],
			
			didScroll: false,
			didResize: false,
	
			doScroll: $.proxy(function() {
				var newScroll = this.element.scrollTop(),
				
				// Are we scrolling up or down? Used for direction argument in callback.
				isDown = newScroll > this.oldScroll,
				that = this,

				// Get a list of all waypoints that were crossed since last scroll move.
				pointsHit = $.grep(this.waypoints, function(el, i) {
					return isDown ?
						(el.offset > that.oldScroll && el.offset <= newScroll) :
						(el.offset <= that.oldScroll && el.offset > newScroll);
				}),
				len = pointsHit.length;
				
				// iOS adjustment
				if (!this.oldScroll || !newScroll) {
					$[wps]('refresh');
				}

				// Done with scroll comparisons, store new scroll before ejection
				this.oldScroll = newScroll;

				// No waypoints crossed? Eject.
				if (!len) return;

				// If several waypoints triggered, need to do so in reverse order going up
				if (!isDown) pointsHit.reverse();

				/*
				One scroll move may cross several waypoints.  If the waypoint's continuous
				option is true it should fire even if it isn't the last waypoint.  If false,
				it will only fire if it's the last one.
				*/
				$.each(pointsHit, function(i, point) {
					if (point.options.continuous || i === len - 1) {
						triggerWaypoint(point, [isDown ? 'down' : 'up']);
					}
				});
			}, this)
		});
		
		// Setup scroll and resize handlers.  Throttled at the settings-defined rate limits.
		$(context).bind('scroll.waypoints', $.proxy(function() {
			if (!this.didScroll) {
				this.didScroll = true;
				window.setTimeout($.proxy(function() {
					this.doScroll();
					this.didScroll = false;
				}, this), $[wps].settings.scrollThrottle);
			}
		}, this)).bind('resize.waypoints', $.proxy(function() {
			if (!this.didResize) {
				this.didResize = true;
				window.setTimeout($.proxy(function() {
					$[wps]('refresh');
					this.didResize = false;
				}, this), $[wps].settings.resizeThrottle);
			}
		}, this));
		
		$w.load($.proxy(function() {
			/*
			Fire a scroll check, should the page be loaded at a non-zero scroll value,
			as with a fragment id link or a page refresh.
			*/
			this.doScroll();
		}, this));
	},
	
	/* Returns a Context object from the contexts array, given the raw HTML element
	for that context. */
	getContextByElement = function(element) {
		var found = null;
		
		$.each(contexts, function(i, c) {
			if (c.element[0] === element) {
				found = c;
				return false;
			}
		});
		
		return found;
	},
	
	// Methods exposed to the effin' object 
	methods = {
		/*
		jQuery.fn.waypoint([handler], [options])
		
		handler
			function, optional
			A callback function called when the user scrolls past the element.
			The function signature is function(event, direction) where event is
			a standard jQuery Event Object and direction is a string, either 'down'
			or 'up' indicating which direction the user is scrolling.
			
		options
			object, optional
			A map of options to apply to this set of waypoints, including where on
			the browser window the waypoint is triggered. For a full list of
			options and their defaults, see $.fn.waypoint.defaults.
			
		This is how you register an element as a waypoint. When the user scrolls past
		that element it triggers waypoint.reached, a custom event. Since the
		parameters for creating a waypoint are optional, we have a few different
		possible signatures. Let’s look at each of them.

		someElements.waypoint();
			
		Calling .waypoint with no parameters will register the elements as waypoints
		using the default options. The elements will fire the waypoint.reached event,
		but calling it in this way does not bind any handler to the event. You can
		bind to the event yourself, as with any other event, like so:

		someElements.bind('waypoint.reached', function(event, direction) {
		   // make it rain
		});
			
		You will usually want to create a waypoint and immediately bind a function to
		waypoint.reached, and can do so by passing a handler as the first argument to
		.waypoint:

		someElements.waypoint(function(event, direction) {
		   if (direction === 'down') {
		      // do this on the way down
		   }
		   else {
		      // do this on the way back up through the waypoint
		   }
		});
			
		This will still use the default options, which will trigger the waypoint when
		the top of the element hits the top of the window. We can pass .waypoint an
		options object to customize things:

		someElements.waypoint(function(event, direction) {
		   // do something amazing
		}, {
		   offset: '50%'  // middle of the page
		});
			
		You can also pass just an options object.

		someElements.waypoint({
		   offset: 100  // 100px from the top
		});
			
		This behaves like .waypoint(), in that it registers the elements as waypoints
		but binds no event handlers.

		Calling .waypoint on an existing waypoint will extend the previous options.
		If the call includes a handler, it will be bound to waypoint.reached without
		unbinding any other handlers.
		*/
		init: function(f, options) {
			// Register each element as a waypoint, add to array.
			this.each(function() {
				var cElement = $.fn[wp].defaults.context,
				context,
				$this = $(this);

				// Default window context or a specific element?
				if (options && options.context) {
					cElement = options.context;
				}

				// Find the closest element that matches the context
				if (!$.isWindow(cElement)) {
					cElement = $this.closest(cElement)[0];
				}
				context = getContextByElement(cElement);

				// Not a context yet? Create and push.
				if (!context) {
					context = new Context(cElement);
					contexts.push(context);
				}
				
				// Extend default and preexisting options
				var ndx = waypointIndex($this, context),
				base = ndx < 0 ? $.fn[wp].defaults : context.waypoints[ndx].options,
				opts = $.extend({}, base, options);
				
				// Offset aliases
				opts.offset = opts.offset === "bottom-in-view" ?
					function() {
						var cHeight = $.isWindow(cElement) ? $[wps]('viewportHeight')
							: $(cElement).height();
						return cHeight - $(this).outerHeight();
					} : opts.offset;

				// Update, or create new waypoint
				if (ndx < 0) {
					context.waypoints.push({
						'element': $this,
						'offset': null,
						'options': opts
					});
				}
				else {
					context.waypoints[ndx].options = opts;
				}
				
				// Bind the function if it was passed in.
				if (f) {
					$this.bind(eventName, f);
				}
				// Bind the function in the handler option if it exists.
				if (options && options.handler) {
					$this.bind(eventName, options.handler);
				}
			});
			
			// Need to re-sort+refresh the waypoints array after new elements are added.
			$[wps]('refresh');
			
			return this;
		},
		
		
		/*
		jQuery.fn.waypoint('remove')
		
		Passing the string 'remove' to .waypoint unregisters the elements as waypoints
		and wipes any custom options, but leaves the waypoint.reached events bound.
		Calling .waypoint again in the future would reregister the waypoint and the old
		handlers would continue to work.
		*/
		remove: function() {
			return this.each(function(i, el) {
				var $el = $(el);
				
				$.each(contexts, function(i, c) {
					var ndx = waypointIndex($el, c);

					if (ndx >= 0) {
						c.waypoints.splice(ndx, 1);

						if (!c.waypoints.length) {
							c.element.unbind('scroll.waypoints resize.waypoints');
							contexts.splice(i, 1);
						}
					}
				});
			});
		},
		
		/*
		jQuery.fn.waypoint('destroy')
		
		Passing the string 'destroy' to .waypoint will unbind all waypoint.reached
		event handlers on those elements and unregisters them as waypoints.
		*/
		destroy: function() {
			return this.unbind(eventName)[wp]('remove');
		}
	},
	
	/*
	Methods used by the jQuery object extension.
	*/
	jQMethods = {
		
		/*
		jQuery.waypoints('refresh')
		
		This will force a recalculation of each waypoint’s trigger point based on
		its offset option and context. This is called automatically whenever the window
		(or other defined context) is resized, new waypoints are added, or a waypoint’s
		options are modified. If your project is changing the DOM or page layout without
		doing one of these things, you may want to manually call this refresh.
		*/
		refresh: function() {
			$.each(contexts, function(i, c) {
				var isWin = $.isWindow(c.element[0]),
				contextOffset = isWin ? 0 : c.element.offset().top,
				contextHeight = isWin ? $[wps]('viewportHeight') : c.element.height(),
				contextScroll = isWin ? 0 : c.element.scrollTop();
				
				$.each(c.waypoints, function(j, o) {
					/* $.each isn't safe from element removal due to triggerOnce.
					Should rewrite the loop but this is way easier. */
					if (!o) return;

					// Adjustment is just the offset if it's a px value
					var adjustment = o.options.offset,
					oldOffset = o.offset;
					
					// Set adjustment to the return value if offset is a function.
					if (typeof o.options.offset === "function") {
						adjustment = o.options.offset.apply(o.element);
					}
					// Calculate the adjustment if offset is a percentage.
					else if (typeof o.options.offset === "string") {
						var amount = parseFloat(o.options.offset);
						adjustment = o.options.offset.indexOf("%") ?
							Math.ceil(contextHeight * (amount / 100)) : amount;
					}

					/* 
					Set the element offset to the window scroll offset, less
					all our adjustments.
					*/
					o.offset = o.element.offset().top - contextOffset
						+ contextScroll - adjustment;

					/*
					An element offset change across the current scroll point triggers
					the event, just as if we scrolled past it unless prevented by an
					optional flag.
					*/
					if (o.options.onlyOnScroll) return;

					if (oldOffset !== null && c.oldScroll > oldOffset && c.oldScroll <= o.offset) {
						triggerWaypoint(o, ['up']);
					}
					else if (oldOffset !== null && c.oldScroll < oldOffset && c.oldScroll >= o.offset) {
						triggerWaypoint(o, ['down']);
					}
					/* For new waypoints added after load, check that down should have
					already been triggered */
					else if (!oldOffset && c.element.scrollTop() > o.offset) {
						triggerWaypoint(o, ['down']);
					}
				});
				
				// Keep waypoints sorted by offset value.
				c.waypoints.sort(function(a, b) {
					return a.offset - b.offset;
				});
			});
		},
		
		
		/*
		jQuery.waypoints('viewportHeight')
		
		This will return the height of the viewport, adjusting for inconsistencies
		that come with calling $(window).height() in iOS. Recommended for use
		within any offset functions.
		*/
		viewportHeight: function() {
			return (window.innerHeight ? window.innerHeight : $w.height());
		},
		
		
		/*
		jQuery.waypoints()
		
		This will return a jQuery object with a collection of all registered waypoint
		elements.

		$('.post').waypoint();
		$('.ad-unit').waypoint(function(event, direction) {
		   // Passed an ad unit
		});
		console.log($.waypoints());
		
		The example above would log a jQuery object containing all .post and .ad-unit
		elements.
		*/
		aggregate: function() {
			var points = $();
			$.each(contexts, function(i, c) {
				$.each(c.waypoints, function(i, e) {
					points = points.add(e.element);
				});
			});
			return points;
		}
	};

	
	/*
	fn extension.  Delegates to appropriate method.
	*/
	$.fn[wp] = function(method) {
		
		if (methods[method]) {
			return methods[method].apply(this, Array.prototype.slice.call(arguments, 1));
		}
		else if (typeof method === "function" || !method) {
			return methods.init.apply(this, arguments);
		}
		else if (typeof method === "object") {
			return methods.init.apply(this, [null, method]);
		}
		else {
			$.error( 'Method ' + method + ' does not exist on jQuery ' + wp );
		}
	};
	
	
	/*
	The default options object that is extended when calling .waypoint. It has the
	following properties:
	
	context
		string | element | jQuery*
		default: window
		The context defines which scrollable element the waypoint belongs to and acts
		within. The default, window, means the waypoint offset is calculated with relation
		to the whole viewport.  You can set this to another element to use the waypoints
		within that element.  Accepts a selector string, *but if you use jQuery 1.6+ it
		also accepts a raw HTML element or jQuery object.
	
	continuous
		boolean
		default: true
		If true, and multiple waypoints are triggered in one scroll, this waypoint will
		trigger even if it is not the last waypoint reached.  If false, it will only
		trigger if it is the last waypoint.
		
	handler
		function
		default: undefined
		An alternative way to bind functions to the waypoint, without using the function
		as the first argument to the waypoint function.

	offset
		number | string | function
		default: 0
		Determines how far the top of the element must be from the top of the browser
		window to trigger a waypoint. It can be a number, which is taken as a number
		of pixels, a string representing a percentage of the viewport height, or a
		function that will return a number of pixels.
		
	onlyOnScroll
		boolean
		default: false
		If true, this waypoint will not trigger if an offset change during a refresh
		causes it to pass the current scroll point.
		
	triggerOnce
		boolean
		default: false
		If true, the waypoint will be destroyed when triggered.
	
	An offset of 250 would trigger the waypoint when the top of the element is 250px
	from the top of the viewport. Negative values for any offset work as you might
	expect. A value of -100 would trigger the waypoint when the element is 100px above
	the top of the window.

	offset: '100%'
	
	A string percentage will determine the pixel offset based on the height of the
	window. When resizing the window, this offset will automatically be recalculated
	without needing to call $.waypoints('refresh').

	// The bottom of the element is in view
	offset: function() {
	   return $.waypoints('viewportHeight') - $(this).outerHeight();
	}
	
	Offset can take a function, which must return a number of pixels from the top of
	the window. The this value will always refer to the raw HTML element of the
	waypoint. As with % values, functions are recalculated automatically when the
	window resizes. For more on recalculating offsets, see $.waypoints('refresh').
	
	An offset value of 'bottom-in-view' will act as an alias for the function in the
	example above, as this is a common usage.
	
	offset: 'bottom-in-view'
	
	You can see this alias in use on the Scroll Analytics example page.

	The triggerOnce flag, if true, will destroy the waypoint after the first trigger.
	This is just a shortcut for calling .waypoint('destroy') within the waypoint
	handler. This is useful in situations such as scroll analytics, where you only
	want to record an event once for each page visit.
	
	The context option lets you use Waypoints within an element other than the window.
	You can define the context with a selector string and the waypoint will act within
	the nearest ancestor that matches this selector.
	
	$('.something-scrollable .waypoint').waypoint({
	   context: '.something-scrollable'
	});
	
	You can see this in action on the Dial Controls example.
	
	The handler option gives authors an alternative way to bind functions when
	creating a waypoint.  In place of:
	
	$('.item').waypoint(function(event, direction) {
	   // make things happen
	});
	
	You may instead write:
	
	$('.item').waypoint({
	   handler: function(event, direction) {
	      // make things happen
	   }
	});
	
	*/
	$.fn[wp].defaults = {
		continuous: true,
		offset: 0,
		triggerOnce: false,
		context: window
	};
	
	
	
	
	
	/*
	jQuery object extension. Delegates to appropriate methods above.
	*/
	$[wps] = function(method) {
		if (jQMethods[method]) {
			return jQMethods[method].apply(this);
		}
		else {
			return jQMethods['aggregate']();
		}
	};
	
	
	/*
	$.waypoints.settings
	
	Settings object that determines some of the plugin’s behavior.
		
	resizeThrottle
		number
		default: 200
		For performance reasons, the refresh performed during resizes is
		throttled. This value is the rate-limit in milliseconds between resize
		refreshes. For more information on throttling, check out Ben Alman’s
		throttle / debounce plugin.
		http://benalman.com/projects/jquery-throttle-debounce-plugin/
		
	scrollThrottle
		number
		default: 100
		For performance reasons, checking for any crossed waypoints during a
		scroll event is throttled. This value is the rate-limit in milliseconds
		between scroll checks. For more information on throttling, check out Ben
		Alman’s throttle / debounce plugin.
		http://benalman.com/projects/jquery-throttle-debounce-plugin/
	*/
	$[wps].settings = {
		resizeThrottle: 200,
		scrollThrottle: 100
	};
	
	$w.load(function() {
		// Calculate everything once on load.
		$[wps]('refresh');
	});
})(jQuery, 'waypoint', 'waypoints', window);
