// Let's listen for when PhoneGap/Cordova has correctly loaded
// THEN we'll run our PhoneGap/Cordova dependent code
document.addEventListener('deviceready', function () { // Don't use a jQuery event listener here. PhoneGap/Cordova will shit itself.
	// Setup some variables
	var nativeFramework = {};

	// Depending on which framework loaded the app, lets grab some info
	if (typeof device.cordova != 'undefined') {
		nativeFramework.name = 'Cordova';
		nativeFramework.version = device.cordova;
	}
	else if (typeof device.phonegap != 'undefined') {
		nativeFramework.name = 'PhoneGap';
		nativeFramework.version = device.phonegap;
	}

	// Let the log know that the framework is working! :)
	psu.log('DEVICEREADY event fired. ' + nativeFramework.name + ' has been initialized');

	// Now that the framework has loaded, let's wait for jQuery to be ready so we can do some more elegant things. :)
	$(document).ready( function() {
		// Function to notify the user of a new app version
		function notifyUpgrade() {
			// Let's get the current version of the webapp
			var webAppVersion = $('[name=app-version]').attr('content');

			// If the version of the framework that loaded the webapp is old
			if (GlobalTools.compareVersionNumbers(clientAppVersion, webAppVersion) < 0) {
				// Use jQuery Mobile to load the new page
				psu.log('The user\'s client (v. ' + clientAppVersion + ') is old. Loading the upgrade page.');
				$.mobile.changePage( UPGRADE_URL );
			}
		}

		// Function to test against a version and run a function if the version requirement is met
		// If notify is set to true, it will notify the user to upgrade
		function requiresVersion(requiredVersion, func, notify) {
			// Let's define some defaults
			notify = typeof notify !== 'undefined' ? notify : false;

			// Let's check if the version requirement is met
			if (GlobalTools.compareVersionNumbers(clientAppVersion, requiredVersion) >= 0) {
				func();
			}
			else {
				psu.log('The user\'s client (v. ' + clientAppVersion + ') doesn\'t meet the required version (v. ' + requiredVersion + ') to run function: ' + func);

				if (notify) {
					notifyUpgrade();
				}

				return false;
			}
		}

		// Let's create some plugin variables
		var childBrowser;

		// Let's install/initialize some plugins
		try {
			// ChildBrowser
			if (window.plugins.childBrowser !== null) {
				childBrowser = window.plugins.childBrowser; // ChildBrowser
			}
			else if (ChildBrowser !== null && ChildBrowser !== undefined) {
				childBrowser = ChildBrowser.install(); // ChildBrowser
			}
		}
		catch (e) {
			psu.log('Couldn\'t load plugin. Died with: ' + e);
		}

		// Add the Framework version to the info panel and show it
		var $infoElement = $('.info-panel .app-frameworks #' + nativeFramework.name.toLowerCase() );
		$infoElement.find('span').text(nativeFramework.version);
		$infoElement.show(0).css('display', 'block !important');

		// When the Android back button is pressed
		// !NOTE!: This PERMANENTLY OVERRIDES the native back button functionality.
		$(document).on('backbutton', function () {
			psu.log('Back button pressed.');

			// Grab the current page as a jQuery object
			var $currentPage = $.mobile.activePage;

			// If the current page is the dashboard
			if ($currentPage.attr('id') == 'page-dashboard') {
				psu.log('Back button pressed on the dashboard');

				// If the hidden info div is visible, the dashboard info slider is open
				if ($('#hidden-info-div.open').is(':visible')) {
					psu.log('Closing the hidden info slider');

					// Let the other event handlers do the work
					$('#footer-info-button').trigger('vclick');
				}
				// Its closed
				else {
					psu.log('Closing the app');

					// Close the app
					navigator.app.exitApp();
				}
			}
			// If none of these match, we need to restore native functionality
			else {
				// Go back 
				window.history.back();
			}
		});

		// When the user clicks the button to add the directory user to their contacts
		$(document).on('vclick', '#add-to-contacts', function(event) {
			// Let's cache their name. We're going to be using it plenty
			var contactsName = $.trim($('#directory-details-name').text());

			// Let's define some success and error functions
			function saveSuccess(contact) {
				psu.log('Contact "' + contactsName + '" saved');

				// Alert the user
				navigator.notification.alert(
					'Contact "' + contactsName + '" saved',	// Message
					null,							// Callback
					'Success!',						// Title
					'OK'								// Button
				);
			}
			function saveError(contactError) {
				psu.log('Contact save failed with error ' + contactError.code);

				// Alert the user
				navigator.notification.alert(
					'Sorry, there was a problem saving the contact',	// Message
					null,										// Callback
					'Whoops',									// Title
					'OK'											// Button
				);
			}

			// The function that create's the new contact
			function createContact() {
				// Create a new contact object
				var newContact = navigator.contacts.create();

				// Create a new name object to correctly store the contact's name
				var nameObj = new ContactName();
				nameObj.givenName = $('#directory-details-name').data('firstname'); // First name
				nameObj.familyName = $('#directory-details-name').data('lastname'); // Last name

				// Add the new name object to the contact
				newContact.name = nameObj;

				// Set the new contacts details
				// Set both the displayName and nickname for maximum device compatibility
				newContact.displayName = contactsName;
				newContact.nickname = contactsName;

				newContact.emails = new Array(
					new ContactField('work', $.trim($('#directory-details-email').text()), false)
				);

				newContact.phoneNumbers = new Array(
					new ContactField('work', $.trim($('#directory-details-phone-office').text()), false),
					new ContactField('other', $.trim($('#directory-details-phone-voicemail').text()), false)
				);

				// Ok, we have the contact object and its properties. Now let's try and save it to the device
				psu.log('Attempting to save contact "' + contactsName + '" to the device');
				newContact.save( saveSuccess, saveError );

			} // End createContact

			// Confirm choice
			function confirmChoice(choice) {
				// Yes is button 2
				if (choice == 2) {
					// Let's create and save the contact
					createContact();
				}
			}

			// Let's make sure that the user really wants to do this
			navigator.notification.confirm(
				'Add "' + contactsName + '" as a contact?',	// Message
				confirmChoice,							// Function to parse choice
				'Add Contact?',						// Title
				'No,Yes'								// Choices
			);

		});

		// Let's remove the webapp authentication logic, we need it to be different for the phonegap app
		$(document).off('vclick.webapp', 'a[data-auth=required]');

		// When a link to an authentication required page is clicked
		$(document).on('vclick', 'a[data-auth=required]', function(event) {
			// Let's keep track of this element
			var self = this;

			// Our authorization logic requires version 1.8.0 of the app
			requiresVersion('0.8.0', function() {
				// Prevent the page from changing normally
				event.preventDefault();

				// Keep jQuery Mobile from removing the href (this was a PITA to figure out. thanks for the help @borkweb)
				event.stopPropagation();

				// jQuery selector and class
				var $htmlTag = $('html');
				var authClass = 'authenticated';

				// Let's grab the link's URL
				var linkUrl = $(self).attr('href');

				// Are we already authenticated?
				var authStatus = $htmlTag.hasClass(authClass);

				// Let's create a function to continue loading the page at the originally intended URL
				var continueLoading = function() {
					// Use jQuery Mobile to load the new page
					psu.log('Ok. Loading the AUTH required page: ' + linkUrl);
					$.mobile.changePage( linkUrl, {
						reloadPage: "true"
					});
				};

				// Function to run on a successful login
				var loginSuccess = function() {
					// Log the information
					psu.log('The user is has logged in!');

					// Close the childBrowser
					psu.log('ChildBrowser closing. We don\'t need it open anymore.');
					childBrowser.close();

					// Add a class to the html, so we don't have to worry about using the childBrowser while the user's session is still alive
					$htmlTag.addClass(authClass);

					// We should probably show the logout button now
					$('#logout-btn').show(0).css('display', 'block');

					// Let's load the page that required login/authentication
					continueLoading();
				};

				// If we're already logged in
				if (authStatus === true) {
					// Let's just load the page
					continueLoading();
				}
				// Otherwise, we need to log in... its required
				else {
					// Let's make sure that the ChildBrowser plugin is ready and available
					if (childBrowser !== null) {
						// Ok, let's load our authentication page
						childBrowser.showWebPage( LOGIN_URL, { showLocationBar: true });

						// Let's setup a function to run when the child browser is closed
						childBrowser.onLocationChange = function(location) {
							// Log the information
							psu.log('ChildBrowser location changed to: ' + location);

							// Let's check if the user has logged in successfully
							if (/login-success/.test(location)) {
								loginSuccess();
							}
						};
					}
				}
			}, true); // End auth logic requiring specific version
		});

		// Let's remove the webapp logout logic, we need it to be different for the phonegap app
		$(document).off('vclick.webapp', '#logout-btn');

		// When the logout button is clicked
		$(document).on('vclick', '#logout-btn', function(event) {
			// Let's keep track of this element
			var self = this;

			// Our authorization logic requires version 1.8.0 of the app
			requiresVersion('0.8.0', function() {
				// Prevent the page from changing normally
				event.preventDefault();

				// Keep jQuery Mobile from removing the href (this was a PITA to figure out. thanks for the help @borkweb)
				event.stopPropagation();

				// jQuery selector and class
				var $htmlTag = $('html');
				var authClass = 'authenticated';

				// Logout url
				var logoutUrl = LOGOUT_URL + 'logout-message';

				// Function to run on a successful login
				var logoutSuccess = function() {
					// Log the information
					psu.log('The user has successfully logged out.');

					// Close the childBrowser
					psu.log('ChildBrowser closing. We don\'t need it open anymore.');
					childBrowser.close();

					// Add a class to the html, so we don't have to worry about using the childBrowser while the user's session is still alive
					$htmlTag.removeClass(authClass);

					// We should probably hide the logout button now
					$(self).hide(0).css('display', 'none');

					// Refresh/repaint
					$(window).trigger('resize');
					$(window).trigger('updatelayout');
				};

				// Let's make sure that the ChildBrowser plugin is ready and available
				if (childBrowser !== null) {
					// Ok, let's load our authentication page
					childBrowser.showWebPage( logoutUrl, { showLocationBar: true });

					// Let's setup a function to run when the child browser is closed
					childBrowser.onLocationChange = function(location) {
						// Log the information
						psu.log('ChildBrowser location changed to: ' + location);

						// Let's check if the user has logged out successfully
						if (/logout-success/.test(location) || /connect/.test(location) || /wp-login/.test(location)) {
							// Delay the finished function for a second, so that we can actually show the message
							window.setTimeout( function() {
								logoutSuccess();
							}, 1500);
						}
					};
				}
			}, true); // End auth logic requiring specific version
		});

	}); // End jQuery dependence
}, false);
