// Create the Global Tools object
var GlobalTools = {};

// Get the device's OS and add it as a class to the HTML tag
// To be used for OS-specific script loading
// To be used for OS-specific styling (trying to stay more true to the native styling)
GlobalTools.deviceOS = function () {
	var deviceOS = '';

	// Get the data
	var devicePlatform = navigator.userAgent.match(/Android/i)
		|| navigator.userAgent.match(/iPod/i)
		|| navigator.userAgent.match(/iPad/i)
		|| navigator.userAgent.match(/iPhone/i)
		|| navigator.userAgent.match(/webOS/i)
		|| 'Other';

	if (devicePlatform == 'iPod' || devicePlatform == 'iPad' || devicePlatform == 'iPhone') {
		deviceOS = 'ios';
	}
	else if (devicePlatform == 'Android') {
		deviceOS = 'android';
	}
	else {
		deviceOS = devicePlatform.toLowerCase();
	}

	// Add the deviceOS as a CSS class to the HTML tag
	document.documentElement.className += ' ' + deviceOS;

	return deviceOS;
};

// Function to check if a given integer is positive
GlobalTools.isPositiveInteger = function (x) {
    // http://stackoverflow.com/a/1019526/11236
    return /^\d+$/.test(x);
}

/**
 * Compare two software version numbers (e.g. 1.7.1)
 * Returns:
 *
 *  0 if they're identical
 *  negative if v1 < v2
 *  positive if v1 > v2
 *  Nan if they in the wrong format
 *
 *  E.g.:
 *
 *  assert(version_number_compare("1.7.1", "1.6.10") > 0);
 *  assert(version_number_compare("1.7.1", "1.7.10") < 0);
 *
 *  "Unit tests": http://jsfiddle.net/ripper234/Xv9WL/28/
 *
 *  Taken from http://stackoverflow.com/a/6832721/11236
 */
GlobalTools.compareVersionNumbers = function (v1, v2) {
    var v1parts = v1.split('.');
    var v2parts = v2.split('.');

    // First, validate both numbers are true version numbers
    function validateParts(parts) {
        for (var i = 0; i < parts.length; ++i) {
            if (!GlobalTools.isPositiveInteger(parts[i])) {
                return false;
            }
        }
        return true;
    }
    if (!validateParts(v1parts) || !validateParts(v2parts)) {
        return NaN;
    }

    for (var i = 0; i < v1parts.length; ++i) {
        if (v2parts.length === i) {
            return 1;
        }

        if (v1parts[i] === v2parts[i]) {
            continue;
        }
        if (v1parts[i] > v2parts[i]) {
            return 1;
        }
        return -1;
    }

    if (v1parts.length != v2parts.length) {
        return -1;
    }

    return 0;
}

// Create a wrapper function for console.log
GlobalTools.log = function () {
	// Disable this function if we're in production
	if (!isDev) {
		return false;
	}

	// Disable console.log on non-supporting browsers
     if (typeof console == "undefined") {
		return false;
     }

	// If all is well, let's use the browser's native console.log function
	console.log( Array.prototype.slice.call(arguments).toString() );
};

// Alias the GlobalTools.log function for quicker access
var psu = {};
psu.log = GlobalTools.log;
