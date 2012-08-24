var swfu; // swfupload object

jQuery(function(){
	rfjs.showUploader('swf');

	// firefox + https breaks the uploader because of a self-signed ssl cert
	if(navigator.userAgent.indexOf("Firefox") > -1 && document.location.protocol == 'https:')
	{
		rfjs.showUploader('html');
		var url = document.location.href;
		url = "http" + url.substr(5);
		rfjs.errors('The multi-file uploader has been disabled because it is incompatible with Firefox over HTTPS. Please <a href="' + encodeURI(url) + '">switch to HTTP</a> (may require VPN) or use a different browser to enable this feature.');
	}

	// flashuploader 2.2 requires flash 9+
	var flash_version = rfjs.flash_version().match(/\d+/g);
	if(flash_version[0] < 9)
	{
		rfjs.showUploader('html');
		rfjs.errors("The multi-file uploader has been disabled because it is incompatible with Flash 8 and below.");
	}

	jQuery('#refresh-page').hide();
	jQuery('#filter-input').click(function(){
		if(jQuery(this).val() == 'enter filter here')
		{
			jQuery(this).val('');
		}
	});

	jQuery('.rename').click(rfjs.renameClickCallback);

	jQuery('input[type=submit]').click(function(){
		var filename = jQuery('input[name=rf_file]').attr('value');
		if($.browser.msie)
		{
			filename = filename.substring(filename.lastIndexOf("\\") + 1);
		}
		if(files.indexOf(filename) > -1)
		{
			return confirm("A file named \"" + filename + "\" exists. Overwrite?");
		}

		return true;
	});

	if(typeof(SWFUpload) == 'undefined'){
		return alert("SWFUpload is not defined. An administrator needs to run \"make\" in the Remote Files directory.");
	}

	// dedicated span so we know how high and wide the invisible flash overlay
	// should be
	var placeholder = jQuery('#upload-placeholder-content');

	swfu = new SWFUpload({
		upload_url: BASE_URL + "/" + server + ":upload" + dirpath + "?swfupload=1",
		post_params: { fullpath: path },

		debug: false,

		file_size_limit : "20 MB",
		flash_url: BASE_URL + "/includes/swfupload.swf",
		file_dialog_complete_handler: rfswfup.file_dialog_complete,
		upload_complete_handler: rfswfup.upload_complete,
		upload_success_handler: rfswfup.upload_success,
		upload_error_handler: rfswfup.upload_error,
		file_queued_handler: rfswfup.file_queued,
		file_queue_error_handler: rfswfup.file_queue_error,
		file_post_name: 'rf_file',
		swfupload_load_failed_handler: rfswfup.load_failed,
		swfupload_pre_load_handler: function(){ },
		file_types: '*',

		// span to replace with our flash obj
		button_placeholder_id: "upload-placeholder",
		button_width: placeholder.width(),
		button_height: placeholder.height(),
		button_window_mode: SWFUpload.WINDOW_MODE.TRANSPARENT,
		button_cursor: SWFUpload.CURSOR.HAND,

		minimum_flash_version : "9.0.28"
	});
});
