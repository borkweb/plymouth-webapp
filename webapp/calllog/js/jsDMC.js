/** jsDelorean: a (proof-of-concept and/or buggy) javascript library for browser history and state manipulation
	(c) 2006 dbloom (if you want to use this on your site talk to me, but it's pretty buggy and probably a bad idea...)

	This is a basically some Javascript that allows web pages to read and add history entries
	using the fragment identifier. I'm going to add support for all the major browsers, but, so
	far, only Safari support has been implemented (update: I added support for browsers that handle
	location.hash correctly, such as Firefox and...umm...Netscape 4.)

	The Safari implementation will put your page inside of an <iframe> (sorry W3C nerds, but <object>
	wasn't working quite right), so make sure your code is ready for that. This might mess up some "break out
	of frames" scripts.
	
	I like to use innerHTML a lot, so you might want to make sure your page's character encoding is set to
	UTF-8 to prevent cross-site scripting attacks (<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />)
	
	So, enjoy, and leave feedback. You can get my email by going to:
	http://address-protector.com/urG-GK7ozl_PAju6TFhStWGnDBE8F4vUxnTWNEomhvmHs4BHA28CEk0uyKVh2ceY
	
	(By the way, I haven't even seen all of the Back to the Future movies, and the ones I've seen
	have mostly been on TV. I just want to be one of the cool people that references that movie
	gratuitously. Google, please hire me!)
**/
function dmc_start(listener) {
	switch (dmc_detectBrowser()) {
	
		case "safari":
		case "khtml":	isparent=(top==self);
						dmc_browserAPI = dmc_scroll_API;
						break;
		
		case "opera":	history.navigationMode="compatible";
		case "ns4":		// <-- the irony....netscape4 doesn't need any hacks...
		case "iemac":	
		case "opera":	
		case "gecko":	dmc_browserAPI = dmc_nohacks_API;
						break;
						
		case "ie":		dmc_browserAPI = dmc_iframes_API;
						break;
						
		default:		return false;
	}
	setTimeout("dmc_poll()",100);
	dmc_listener_function=listener;
	dmc_browserAPI.initializeHead();
}

function dmc_change() {
	if (dmc_listener_function != "") {
		setTimeout(dmc_listener_function,50);
	}
}

function dmc_body() {
	/*if (dmc_detectBrowser() == "opera") {
		document.write("<div id=\"dmc_operadiv\" style=\"position:absolute;left:-100px;top:-100px;width:1px;height:1px;visibility:hidden;\"><img src=\"#"+Math.random()+"\" onerror=\"dmc_poll(1);\" /></div>");
	}*/
	if (dmc_browserAPI) {
		dmc_browserAPI.initializeBody();
	}
	return true;
}

function dmc_sanitize(h) {
	var x=dmc_decodeHashCounter(h);
	if ((x == -1) && (dmc_decodeHashValue(h) == "")) return h;
	if (x==-1) var x="00";
	return dmc_encodeHash(dmc_decodeHashValue(h),x);
}

// Loosely based on http://www.quirksmode.org/index.html?/js/detect.html#link10
// (I should improve this a bit...)
function dmc_detectBrowser() {
	var ua = navigator.userAgent.toLowerCase();
	if (dmc_subStr(ua,"icab"))
		return "icab";
	if (dmc_subStr(ua,"konqueror"))
		return "khtml";
	if (dmc_subStr(ua,"safari"))
		return "safari";
	if (dmc_subStr(ua,"opera"))
		return "opera";
	if (dmc_subStr(ua,"msie")) {
		if (dmc_subStr(ua,"win")) {
			return "ie";
		} else {
			if (dmc_subStr(ua,"mac")) {
				return "iemac";
			}
		}
	}
	if (!dmc_subStr(ua,"compatible"))
	{
		return "gecko";
	//	return "ns4";
	}
	return "old";
}

function dmc_poll() {
	dmc_browserAPI.checkValue();
	setTimeout("dmc_poll()",100);
}

function dmc_subStr(str,substr) {
	if (str.indexOf(substr) == -1) return false;
	return true;
}
/** this function is from http://www.worldtimzone.com/res/encode/
	the original author is unclear... **/
function dmc_utf8(wide) {
	if (typeof(encodeURIComponent)=="function")
		return encodeURIComponent(wide);
	if (wide=="")
		return "";
	var c, s;
	var enc = "";
	var i = 0;
	while(i<wide.length) {
		c= wide.charCodeAt(i++);
		// handle UTF-16 surrogates
		if (c>=0xDC00 && c<0xE000) continue;
		if (c>=0xD800 && c<0xDC00) {
			if (i>=wide.length) continue;
			s= wide.charCodeAt(i++);
			if (s<0xDC00 || c>=0xDE00) continue;
			c= ((c-0xD800)<<10)+(s-0xDC00)+0x10000;
		}
		// output value
		if (c<0x80) enc += String.fromCharCode(c);
		else if (c<0x800) enc += String.fromCharCode(0xC0+(c>>6),0x80+(c&0x3F));
		else if (c<0x10000) enc += String.fromCharCode(0xE0+(c>>12),0x80+(c>>6&0x3F),0x80+(c&0x3F));
		else enc += String.fromCharCode(0xF0+(c>>18),0x80+(c>>12&0x3F),0x80+(c>>6&0x3F),0x80+(c&0x3F));
	}
	return enc;
}



function dmc_encodeHash(value,counter) {
	return "#"+dmc_utf8(value+"["+counter+"]");
}

function dmc_lastIndexOf(str,chr) {
	return dmc_lastIndexOfR(str,chr,str.length);
}
// this is a recursive-type thingy....
function dmc_lastIndexOfR(str,chr,pos) {
	if (pos < 0)
		return -1;
	if (str.charAt(pos) != chr)
		return dmc_lastIndexOfR(str,chr,pos-1);
	return pos;
}

function dmc_decodeHashValue(ta) {
	var t=unescape(ta);
	if (t.charAt(t.length - 1) == "]") {
		var l = dmc_lastIndexOf(t,"[");
		//	is there a [?		and, make sure it isn't just [] without a counter inside
		if ((l != -1) && (l+1 < t.length - 1)) {
			var i = t.length-2;
			var g = true;
			while (g && (i > l)) {
				g = "0123456789".indexOf(t.charAt(i))+1;
				i--;
			}
			if (g) { // valid counter, so we should NOT include it in the value
				if (t.substring(0,1) != "#")
					return t.substring(0,l);
				return t.substring(1,l);
			}
		}
	}
	return t.substring(t.indexOf("#")+1);
}

function dmc_decodeHashCounter(ta) {
	var t=unescape(ta);
	if (t.charAt(t.length - 1) == "]") {
		var l = dmc_lastIndexOf(t,"[");
		if ((l != -1) && (l+1 < t.length - 1)) {
			var i = t.length-2;
			var g = true;
			while (g && (i > l)) {
				g = "0123456789".indexOf(t.charAt(i))+1;
				i--;
			}
			if (g) { // valid counter, so we should return it
				// We do Math.floor so we don't return a string (we want to return 42, not "42")...
				return Math.floor(t.substring(l+1,t.length-1));
			}
		}
	}
	return -1;
}

function dmc_debug(msg) {
	if (dmc_browserAPI.showDebug) {
		if (isparent) {	
			document.getElementById("debug").innerHTML=msg+"<hr />"+document.getElementById("debug").innerHTML;
		} else {
			parent.dmc_debug(msg);
		}
	}
}

/** Browser hacks... **/
dmc_nohacks_API = { //(...or so it was, until I made it work with IE Mac)
	initializeHead: function() {
		if (dmc_detectBrowser() == "iemac")
			this.liar_API=true;
		if (location.hash && (dmc_decodeHashCounter(location.hash) != -1))
			this.nextcount = dmc_decodeHashCounter(location.hash) + 1;
	},
	initializeBody: function() {
		if (this.liar_API) {
			document.write("<div style=\"position:absolute;top:-100px;left:-100px;visibility:hidden;width:1px;height:1px;border:0px;padding:0px;margin:0px;\" id=\"dmc_script\"></div>");
		}
	},
	setValue: function(value) {
		var x=dmc_encodeHash(value,this.nextcount);
		this.nextcount++;
		if (this.liar_API) {
			document.getElementById("dmc_script").innerHTML="<a name=\""+x.substring(x.indexOf("#")+1)+"\"></a><script type=\"text/javascript\">location.hash=\""+x.substring(x.indexOf("#")+1)+"\";dmc_change();</script>";
			this.fixScroll(x.substring(x.indexOf("#")+1),document.body.scrollTop,50);
		} else {
			location.href=location.href.split("#")[0]+x;
			dmc_change();
		}
		this.prev=x;
	},
	changeValue: function(value) {
		var x=dmc_encodeHash(value,this.nextcount);
		this.nextcount++;
		if (this.liar_API == true) {
			document.getElementById("dmc_script").innerHTML="<a name=\""+x.substring(x.indexOf("#")+1)+"\"></a><script type=\"text/javascript\">location.replace(\""+location.href.split("#")[0]+x+"\");dmc_change();</script>";
			this.fixScroll(x.substring(x.indexOf("#")+1),document.body.scrollTop,50);
		} else {
			location.replace(location.href.split("#")[0]+x);
			dmc_change();
		}
		this.prev=x;
	},
	keepValue: function() {
		this.setValue(this.getValue());
		dmc_change();
	},
	getValue: function() {
		return dmc_decodeHashValue(location.hash);
	},
	checkValue: function() {
		if (location.hash != this.prev) {
			dmc_change();
			if (dmc_decodeHashCounter(location.hash) >= this.nextcount)
				this.nextcount = dmc_decodeHashCounter(location.hash) + 1;
			this.prev=location.hash;
		}
	},
	/**private**/
	//ie mac hack
	fixScroll: function(correctHash,correctScroll,tries) {
		if (location.href.split("#")[1] == correctHash) {
			document.body.scrollTop=correctScroll;
		} else {
			if (tries > 0) {
				tries--;
				setTimeout("dmc_browserAPI.fixScroll(\""+correctHash+"\","+correctScroll+","+tries+")",5);
			}
		}
	},
	liar_API: false, //(True if we need to use a small IE mac hack)
	nextcount: 1,
	prev: location.hash
};

// this code is messy....i kind of figured out how IE history methods work as I went.
// i'll clean it up....

// known issues:
// IE < 5.5 doesn't work
// * if you manually input an address in the address bar, your previous history entry is duplicated
//    so you have to hit "Back" twice to get past it
// * i *really* want to come up with a way to suppress IE's click sound, etc
dmc_iframes_API = {
	initializeHead: function() {
		return true;
	},
	initializeBody: function() {
		document.writeln("<div style=\"position:absolute;left:-100px;top:0px;visibility:hidden;\">");
		document.writeln("	<iframe name=\"dmc_iframe\" id=\"dmc_iframe\" style=\"width:1px;height:1px;visibility:hidden;\"></iframe>");
		document.writeln("</div>");
		var h=location.hash;
		var v=dmc_decodeHashValue(h);
		var c=dmc_decodeHashCounter(h);
		if (c != -1)
			var x=dmc_encodeHash(v,c);
		this.actualHash = h;
		//alert(location.hash+","+x);
		//if (location.hash==x) alert("eq");
		if (!((x) && (x == h))) {
			if (!(x)) {
				var x=dmc_encodeHash(v,this.counter);
			}
			if ((window.location.hash) && (window.location.hash.split("#")[1].length > 0)) {
				window.location.hash=x;
				this.anticipatedHash=x;
				this.initIframe(x);
				dmc_change();
			} else {
				x="";
				this.iframeInUse=false;
			//	window.location.replace("/."+window.location.pathname+window.location.search+x);
			}
		}
	},
	setValue: function(value) {
		this.counter++;
		var h=dmc_encodeHash(value,this.counter);
		window.location.hash = h;
		//this.prevLocationHash=h;
		this.anticipatedHash=h;
		this.setIframe(h,false);
		dmc_change();
	},
	changeValue: function(value) {
		this.counter++;
		var h=dmc_encodeHash(value,this.counter);
		window.location.hash = h;
		//this.prevLocationHash=h;
		this.anticipatedHash=h;
		//this.actualHash=h;
		this.setIframe(h,true);
		dmc_change();
	},
	keepValue: function() {
		this.setValue(this.getValue());
	},
	getValue: function() {
		if (this.anticipatedHash)
			return dmc_decodeHashValue(this.anticipatedHash);
		return dmc_decodeHashValue(this.actualHash);
	},
	checkValue: function() {
		if (dmc_sanitize(location.hash) != this.prevLocationHash) {
			if (dmc_decodeHashCounter(location.hash) > this.counter) {
				this.counter = dmc_decodeHashCounter(location.hash);
			}
			this.actualHash=dmc_sanitize(location.hash);
			if (this.actualHash != this.anticipatedHash) {
				this.setIframe(this.actualHash,false);
				dmc_change();
			}
			this.anticipatedHash=false;
			this.prevLocationHash=this.actualHash;
		}
	},
	/** private functions **/
	// hist: true/false, don't add history entry?
	initIframe: function(hash) {
		if (frames["dmc_iframe"]) {
			this.iframeInUse=false;
			if (!(frames['dmc_iframe'].document.documentElement)) {
				this.setIframe(hash,true);
			}
		} else {
			setTimeout("dmc_browserAPI.initIframe(\""+hash+"\")",25);
		}
	},
	setIframe: function(hash,hist) {
		if ((!(this.iframeInUse)) && (frames["dmc_iframe"])) {
		//setTimeout("document.getElementById('dd').innerHTML+='IFRAME!'",100);
			this.iframeInUse=true;
			
			var d=document.open("text/html","dmc_iframe","",hist);
			d.document.write("<script>parent.dmc_browserAPI.iframeInUse=false;parent.dmc_browserAPI.reportHash(\""+hash+"\");</script></body>");
			d.document.close();
			
			/**
			document.open("text/html","dmc_iframe","",hist);
			frames['dmc_iframe'].document.write("<script>parent.dmc_browserAPI.iframeInUse=false;parent.dmc_browserAPI.reportHash(\""+hash+"\");</script></body>");
			frames['dmc_iframe'].document.close();
			**/
		} else {
			setTimeout("dmc_browserAPI.setIframe(\""+hash+"\")",25);
		}
	},
	reportHash: function(hash) {
	//alert("X");
		if (location.hash != hash) {
			if (hash != "") {
				if (dmc_sanitize(window.location.hash) != hash)
					window.location.hash = hash;
				this.prevLocationHash=hash;
				this.actualHash=hash;
			} else {
				this.actualHash="#";
				this.prevLocationHash="";
			}
			this.anticipatedHash=false;
			dmc_change();
		}
	},
	iframeInUse: true,
	prevLocationHash: location.hash,
	actualHash: location.hash,
	anticipatedHash: false,
	counter: 1
};


dmc_scroll_API = {
	initializeHead: function() {
		if (isparent) {
			if (this.init) {return true; }
			this.init=true;
			if (location.hash) {
				if (dmc_decodeHashCounter(location.hash) != -1) {
					this.nextCounter = dmc_decodeHashCounter(location.hash)+1;
					this.actualHash=location.hash;
					
					// If the hash truly is well-formed, then h==location.hash. But, if some characters
					// aren't URL-encoded like they should be, a cross-site-scripting exploit could be
					// created by taking advantage of the extensive use of innerHTML later on in this
					// code. So, we must create a new hash that is identical aside from escaping if this
					// is the case.
					//var h=dmc_encodeHash(dmc_decodeHashValue(location.hash),dmc_decodeHashCounter(location.hash));
					var h=dmc_sanitize(location.hash);
					dmc_change();
				} else {
					var h=dmc_encodeHash(dmc_decodeHashValue(location.hash),this.nextCounter);
					dmc_change();
					//document.writeln("<meta http-equiv=\"refresh\" content=\"0;url="+location.href.split("#")[0]+h+"\" />");
				}
			} else {
				var h=dmc_encodeHash("",this.nextCounter);
				//document.writeln("<meta http-equiv=\"refresh\" content=\"0;url="+location.href.split("#")[0]+h+"\" />");
			}
			// If this is the parent frame, we need to basically override everything. This is why we *must*
			// have our script first in the <head>.
			document.writeln("</head>");
			document.writeln("<body style=\"overflow:hidden;\" ondragenter=\"dmc_browserAPI.dragging=true;dmc_debug('dragging');\" ondragover=\"dmc_browserAPI.fixScroll();\" ondragleave=\"dmc_browserAPI.fixScroll2();\"  onscroll=\"dmc_browserAPI.checkValue();\">");
			document.write("<div id=\"dmc_cache\" style=\"padding:0px;margin:0px;width:1px;height:1px;position:absolute;top:0px;left:-250px;visibility:hidden;\"><form name=\"dmc_frmCache\" id=\"dmc_frmCache\" method=\"GET\" action=\"#\" target=\"dmc_childObject\" onsubmit=\"return false;\"><textarea style=\"width:10px;height:10px;\" name=\"dmc_txtCache\" id=\"dmc_txtCache\"></textarea></form></div>");
			document.write("<div style=\"padding:0px;margin:0px;width:1px;height:100%;position:absolute;left:-100px;top:0px;overflow:visible;visibility:hidden;\"><div id=\"dmc_anchors\" style=\"padding:0px;margin:0px;width:1px;border:0px;\" >");
			if (document.getElementById("dmc_txtCache").value.length > 0) {
		//		alert(document.getElementById("dmc_txtCache").value);
				document.write(document.getElementById("dmc_txtCache").value);
				setTimeout("dmc_debug(\"history!\")",1000);
			} else {
				var l="<div><img alt=\"[nothing]\"  style=\"display:block;width:1px;height:1px;visibility:hidden;border:0px;margin:0px;padding:0px;\" /></div><div><img alt=\"[nothing]\"  style=\"display:block;width:1px;height:1px;visibility:hidden;border:0px;margin:0px;padding:0px;\" /><a name=\""+h.substring(h.indexOf("#")+1)+"\" title=\"anchor_2\"></a></div>";
				document.write(l);
				document.getElementById("dmc_txtCache").value=l;
			}
			document.write("</div><img style=\"width:1px;height:200%;display:block;\" alt=\"[nothing]\" /></div>");
			document.writeln("	<iframe onblur=\"frames['dmc_childObject'].focus();\"  name=\"dmc_childObject\" src=\""+location.href.split("#")[0]+"\" id=\"dmc_childObject\" type=\"text/html\" style=\"display:block;width:100%;height:100%;position:fixed;top:0px;left:0px;border:0px;visibility:visible;\"></iframe>");
			if (this.showDebug) document.writeln("	<div id=\"debug\" style=\"position:fixed;top:0px;left:100%;margin-left:-240px;width:240px;height:240px;opacity:.9;background-color:black;color:white;overflow:auto;z-index:999;\"></div>");
			
			document.writeln("	<div id=\"dmc_form\" style=\"padding:0px;margin:0px;width:1px;height:1px;position:absolute;top:0px;left:-50px;visibility:hidden;\"></div>");			
			document.writeln("</body>");
			// Stop loading...we'll save the rest of the page for the child frame.
			//window.stop;
			document.write("<noscript>");
		//	setTimeout("frames['dmc_childObject'].location.replace(location.href)",100);
			if (location.hash != h) {
				this.anticipatedHash=h;
				this.doReplace(this.anticipatedHash);
				this.nextCounter++;
			}
			return true;
		}
	},
	initializeBody: function() {
		return;
	},
	setValue: function(value) {
		if (isparent) {
			this.anticipatedHash=dmc_encodeHash(value,this.nextCounter);
			this.addAnchor(this.anticipatedHash,"if (dmc_browserAPI.anticipatedHash == '"+this.anticipatedHash+"') { dmc_browserAPI.formSubmit('"+this.anticipatedHash+"');}");
			this.nextCounter++;
			this.nextScroll++;
			dmc_change();
			return true;
		} else {
			return parent.dmc_browserAPI.setValue(value);
		}
	},
	changeValue: function(value) {
		if (isparent) {
			this.anticipatedHash=dmc_encodeHash(value,this.nextCounter);
			this.addAnchor(this.anticipatedHash,"if (dmc_browserAPI.anticipatedHash == '"+this.anticipatedHash+"') { dmc_browserAPI.doReplace('"+this.anticipatedHash+"');}");
			this.nextCounter++;
			this.nextScroll++;
			dmc_change();
			return true;
		} else {
			return parent.dmc_browserAPI.changeValue(value);
		}
	},
	keepValue: function() {
		return this.setValue(this.getValue());
	},
	getValue: function() {
		if (isparent) {
			this.checkValue();
			if (this.anticipatedHash)
				return dmc_decodeHashValue(this.anticipatedHash);
			return dmc_decodeHashValue(this.actualHash);
		} else {
			return parent.dmc_browserAPI.getValue(this.actualHash);
		}
	},
	checkValue: function() {
		if (isparent) {
			if (!this.dragging) {
				// TODO: raw location.hash change checking
				// Even though Safari will reload the page if you change the fragment URL using
				// the address bar, there are always situations where the *page itself* will initiate
				// a change (for example, a link to a page on the site that someone made in a comment
				// using a PHP script for blog comments, etc).
				if ((this.anticipatedHash) &&
					(document.body.scrollTop == this.nextScroll-1)) {
					this.correctScroll = document.body.scrollTop;
					this.actualHash=this.anticipatedHash;
					this.anticipatedHash=false;
					return true;
				}
				var o = 0;
				if (document.body)
					if (document.body.scrollTop)
						var o=document.body.scrollTop;
				//window.status=o;
				if (o == this.correctScroll ) 
					return true;
				if (document.getElementById("dmc_anchors")) {
					var anchors = document.getElementById("dmc_anchors").getElementsByTagName("a");
					var i = anchors.length-1;
					var g = true;
				//	window.status=o+", anchors:";
					while (g && (i >= 0)) {
				//	window.status+=anchors[i].title.split("_")[1];
						if (anchors[i].title.split("_")[1] == document.body.scrollTop) {
							if (this.anticipatedHash == "#"+anchors[i].name) {
								// This shouldn't happen...
								//alert("something baaaad happened...");
								this.anticipatedHash=false;
							}
							this.actualHash = "#"+anchors[i].name;
							this.correctScroll=document.body.scrollTop;
							g=false;
							dmc_change();
						}
						i--;
					}
					if (g) {return true;}
					//if this is an unknown scroll position, we should ignore it and scroll back to the last scroll position.
					if (this.correctScroll != document.body.scrollTop) {
						dmc_debug(document.body.scrollTop+" is not valid scroll, reverting to "+this.correctScroll);
						document.body.scrollTop=this.correctScroll;
					}
				}
				return false;
			}
		} else {
			// keep the window's title correct since we're kind of trapped in an <iframe> here.
			if (parent.document.title != document.title) {
				parent.document.title = document.title;
			}
		}
	},
	
	/** Private functions **/
	addAnchor: function(hash,nextStep) {
		// That guy from QuirksMode says it's faster to use innerHTML....plus, I'm lazy.
		if (nextStep)
			nextStep=" onerror=\""+nextStep+"\"";
		else
			nextStep=" ";
		document.getElementById("dmc_anchors").innerHTML+="<div><img alt=\"[nothing]\" src=\"#\" style=\"display:block;width:1px;height:1px;visibility:hidden;border:0px;margin:0px;padding:0px;\""+nextStep+" /><a name=\""+hash.substring(hash.indexOf("#")+1)+"\" title=\"anchor_"+this.nextScroll+"\"></a></div>";
	
		document.getElementById("dmc_txtCache").value+="<div><img alt=\"[nothing]\" src=\"#\" style=\"display:block;width:1px;height:1px;visibility:hidden;border:0px;margin:0px;padding:0px;\" /><a name=\""+hash.substring(hash.indexOf("#")+1)+"\" title=\"anchor_"+this.nextScroll+"\"></a></div>";
	},
	
	// safari won't normally add a change to the location.hash to the back/forward
	// hsitory, unless it is done by a user manually clicking a link. but, thanks to
	// a loophole in how <form method=get> is handled, we can set the action of the 
	// form to the new hash and safari will NOT reload the page (which is probably
	// the correct behavior, and is what most other browsers will do). Anyway, this is
	// a "tight squeeze" so I'm not sure about the longetivity of this trick, but that's
	// kind of the nature of the beast when it comes to AJAX...
	formSubmit: function(hash) {
		if (document.getElementsByName(hash.substring(hash.indexOf("#")+1)).length) {
			dmc_debug("formSubmit to "+hash);
			//window.status+=hash+",";
			var f="f"+Math.floor(Math.random()*100000);
			if (hash.charAt(0) != "#")
				hash="#"+hash;
			// We can't submit() here because the form might not have been added to the
			// page immediately, so we use the <img onerror> trick.
			document.getElementById("dmc_form").innerHTML="<form name=\""+f+"\" method=\"GET\" action=\""+hash+"\" onsubmit=\"dmc_browserAPI.actualHash='"+hash+"';\"></form><img src=\"#\" alt=\"[nothing]\" onerror=\"document.forms."+f+".submit();this.onerror='';\" />";
		} else {
			setTimeout("dmc_browserAPI.formSubmit(\""+hash+"\")",10);
		}
	},
	
	doReplace: function(hash) {
		if (document.getElementsByName(hash.substring(hash.indexOf("#")+1)).length) {
			dmc_debug("doReplace to "+hash);
			if (hash.charAt(0) != "#")
				hash="#"+hash;
			location.replace(hash);
		} else {
			setTimeout("dmc_browserAPI.doReplace(\""+hash+"\")",10);
		}
	},
	
	fixScroll: function() {	
		if (!((this.anticipatedHash) &&
			(document.body.scrollTop == this.nextScroll-1))) {
			if (!(document.body.scrollTop == this.correctScroll)) {
				if (this.anticipatedHash) {
					this.fixdrag = this.nextScroll - 1;
				} else {
					this.fixdrag = this.correctScroll;
				}
			}
		}
	},
	
	fixScroll2: function() {
		if (this.fixdrag) {
			dmc_debug("Corrected scroll position ("+document.body.scrollTop+", corrected to: "+this.fixdrag+")");
			document.body.scrollTop = this.fixdrag;
			this.fixdrag=false;
		}
		this.dragging=false;
	},
	
	
	
	showDebug: false,
	dragging: false,
	fixdrag: false,
	anticipatedHash: false,
	actualHash: location.hash,
	correctScroll: 2,
	nextScroll: 3,
	nextCounter: 1,
	init: false
};