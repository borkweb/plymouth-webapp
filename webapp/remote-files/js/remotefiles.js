function rf_close_message(o)
{
	var parent_ul = jQuery(o).parent();
	jQuery(o).fadeOut('fast');
}

function rf_unlink(anchor)
{
	var url = jQuery(anchor).attr('href');
	var row_id = jQuery(anchor).parents('tr:first').attr('id');
	var filename = jQuery('#'+row_id+' .name span.rename').text();

	if(!confirm('Are you sure you want to delete ' + filename + '?'))
	{
		return false;
	}

	if(url.indexOf("?") > -1)
	{
		url = url.substring(0, url.indexOf("?"));
	}

	url += "?confirmed=1";

	jQuery('#'+row_id+' .name a').addClass('marked');
	jQuery('#'+row_id+' .size').addClass('marked');
	jQuery('#'+row_id+' .delete').html('<span class="blank"></span>');
	$.getJSON(url, { row_id:row_id }, function(json){
		if(json.success == true)
		{
			json.row_id = '#'+json.row_id;
			jQuery(json.row_id+' td').css('background-color', 'red');
			jQuery(json.row_id+' td').fadeOut(1000, function(){
				jQuery(json.row_id).hide();
			});
		}
		else
		{
			alert('Error deleting file: ' + json.message);
		}
	});
}

function rf_upload()
{
	jQuery('#upload input[type=submit]').hide();
	jQuery('#status').css('display', 'inline');
}

var rfjs = {
	messages: function(m)
	{
		rfjs._message(m, 'messages');
	},

	errors: function(m)
	{
		rfjs._message(m, 'errors');
	},

	_message: function(m, type)
	{
		if(jQuery('#'+type).length == 0)
		{
			jQuery('#header').after('<div id="' + type + '" class="' + type + '"><ul></ul></div>');
		}

		jQuery('#'+type+ ' ul').append('<li title="Click to dismiss message" onclick="rf_close_message(this);"><span class="badge"></span>' + m + '</li>');
	},

	shouldShowServers: 0,
	serverList: null,
	serverSpan: null,

	showSL: function()
	{
		rfjs.updateSL(true);
	},

	hideSL: function()
	{
		rfjs.updateSL(false, 500);
	},

	updateSL: function(show, wait)
	{
		if(wait > 0)
		{
			setTimeout('rfjs.updateSL(' + show + ')', wait);
			return;
		}

		rfjs.shouldShowServers += show ? 1 : -1;

		if(rfjs.shouldShowServers > 0)
		{
			rfjs.serverList.show();
			rfjs.serverSpan.addClass('hovered');
		}
		else
		{
			rfjs.serverList.hide();
			rfjs.serverSpan.removeClass('hovered');
		}
	},

	renameFile: function(o)
	{
		var from = o.from.value;
		var to = o.to.value;

		// no double clicking!
		jQuery(o).find('input[type=submit], button').hide();

		if(from == to)
		{
			return rfjs.renameCancel(o);
		}

		var rf_row = jQuery(o).parents('tr').attr('id').substring(6);

		jQuery(o).find('input[type=text]').attr('disabled','disabled').after(' <em>Saving&hellip;</em>');
		$.get(BASE_URL + "/" + server + ":rename" + dirpath, { from:from, to:to, rf_row:rf_row }, function(data, textStatus){
			var $row = jQuery('#rf_row'+data.rf_row);

			if(data.status == 'success')
			{
				var to = data.name;
				var from = $row.find('td.name span.rename').text();

				files = files.filter(function(e,i,a) { return this != e; }, from);
				files.push(to);

				$row.find('form').remove();
				$row.find('td.name span.rename').text(to).show();
			}
			else
			{
				alert("Error renaming file: " + data.message);
				jQuery(o).find('input[type=submit], button').show();
				jQuery(o).find('input[type=text]').removeAttr('disabled');
				jQuery(o).find('em').remove();
			}
		}, 'json');
	},

	renameCancel: function(o)
	{
		if(o.tagName != 'FORM')
		{
			o = jQuery(o).parents('form').get(0);
		}

		jQuery(o).siblings('span.rename').show();
		jQuery(o).remove();

		return true;
	},

	renameClickCallback: function()
	{
		var t = jQuery(this).text();
		jQuery(this).hide();
		jQuery(this).after('<form onsubmit="rfjs.renameFile(this); return false;"><input type="hidden" name="from" value="' + t +
			'"><input type="text" name="to" value="' + t + '" onkeyup="rfjs.renameKeyupHandler(this, event)"><input type="submit" value="Rename"><button onclick="rfjs.renameCancel(this); return false;">Cancel</button></form>');
	},

	renameKeyupHandler: function(o, e)
	{
		if(e.keyCode == 27)
		{
			rfjs.renameCancel(o);
		}
	},

	chmod: function(o)
	{
		var f = jQuery(o).parents('tr').find('.rename').text();
		var full_path = path + f;

		$.getJSON(BASE_URL + '/' + server + ':chmod' + full_path, { }, function(json){
			if(json.status == 'success')
			{
				rfjs.messages("Permissions on \"" + json.filename + "\" were updated successfully.");
			}
			else
			{
				rfjs.errors("There was an error updating permissions on \"" + json.filename + ":\"\n\n" + json.message);
			}
		});
	},

	/**
	 * Show the flash or html uploader, hiding the other.
	 * @param string type 'swf' or 'html'
	 */
	showUploader: function(type)
	{
		if(type == 'swf')
		{
			jQuery('#upload-swf').show();
			jQuery('#upload-html').hide();
		}
		else
		{
			jQuery('#upload-swf').hide();
			jQuery('#upload-html').show();
		}
	},

	flash_version: function()
	{
		//
		// Copyright (c) 2006 Luke Lutman (http://www.lukelutman.com)
		// Dual licensed under the MIT and GPL licenses.
		// http://www.opensource.org/licenses/mit-license.php
		// http://www.opensource.org/licenses/gpl-license.php
		//
		// http://jquery.lukelutman.com/plugins/flash/
		//

		try {
			try {
				// avoid fp6 minor version lookup issues
				// see: http://blog.deconcept.com/2006/01/11/getvariable-setvariable-crash-internet-explorer-flash-6/
				var axo = new ActiveXObject('ShockwaveFlash.ShockwaveFlash.6');
				try { axo.AllowScriptAccess = 'always'; }
				catch(e) { return '6,0,0'; }
			}
			catch(e){ }
			return new ActiveXObject('ShockwaveFlash.ShockwaveFlash').GetVariable('$version').replace(/\D+/g, ',').match(/^,?(.+),?$/)[1];
			// other browsers
		}
		catch(e)
		{
			try
			{
				if(navigator.mimeTypes["application/x-shockwave-flash"].enabledPlugin)
				{
					return (navigator.plugins["Shockwave Flash 2.0"] || navigator.plugins["Shockwave Flash"]).description.replace(/\D+/g, ",").match(/^,?(.+),?$/)[1];
				}
			}
			catch(e){ }
		}
		return '0,0,0';
	}
};//end rfjs

var rfswfup = {
	file_dialog_complete: function(n, q)
	{
		if(n > 0)
		{
			swfu.startUpload();
		}
	},

	upload_success: function(f, sd)
	{
		var data;
		data = eval("(" + sd + ")"); // sd is a json string

		if(data.status == 'success')
		{
			files.push(f.name);
			jQuery('#refresh-page').show();
			rfjs.messages(data.html);
			jQuery('#'+f.id+' .status').text(f.size.toKB() + " of " + f.size.toKB() + " (100%)");
		}
		else
		{
			rfjs.errors(data.message);
		}
	},

	upload_error: function(f, e, m)
	{
		jQuery('#'+f.id+' .status').text('Failed!');
	},

	upload_complete: function(f)
	{
		swfu.startUpload();
	},

	upload_progress: function(f, complete, total)
	{
		var percent = Math.floor(complete / total * 10000) / 100;
		jQuery('#'+f.id+' .status').text(complete.toKB() + " of " + total.toKB() + " (" + percent + "%)");
	},

	file_queued: function(f)
	{
		if(files.indexOf(f.name) > -1)
		{
			var canOverwrite = confirm("There is already a file named " + f.name + ". Overwrite?");
			if(!canOverwrite)
			{
				cancelUpload(f.id);
				return false;
			}
		}
		jQuery('#swflog').append('<li id="' + f.id + '">' + f.name + ': <span class="status">0KB of ' + f.size.toKB() + ' (0%)</span></li>');
	},

	file_queue_error: function(f, e, m)
	{
		jQuery('#swflog').append('<li id="' + f.id + '">' + f.name + ': <span class="status">Failed! ' + m + '</span></li>');
	},

	load_failed: function()
	{
		rfjs.showUploader('html');
	}
};

Number.prototype.toKB = function()
{
	var kb = this / 1024;
	if(kb > 0 && kb < 1)
	{
		kb = 1;
	}
	return Math.floor(kb) + "KB";
};

//This prototype is provided by the Mozilla foundation and
//is distributed under the MIT license.
//http://www.ibiblio.org/pub/Linux/LICENSES/mit.license
if (!Array.prototype.filter)
{
	Array.prototype.filter = function(fun /*, thisp*/)
	{
		var len = this.length;
		if (typeof fun != "function")
			throw new TypeError();
		var res = new Array();
		var thisp = arguments[1];
		for (var i = 0; i < len; i++)
		{
			if (i in this)
			{
				var val = this[i]; // in case fun mutates this
				if (fun.call(thisp, val, i, this))
					res.push(val);
			}
		}
		return res;
	};
}

if (!Array.prototype.indexOf) {
	Array.prototype.indexOf = function(entry) {
		for(var i=0; i<this.length; i++) {
			if (this[i] == entry)
				return i;
		}
		return -1;
	}
}

// vim:ts=2:sw=2:noet:
