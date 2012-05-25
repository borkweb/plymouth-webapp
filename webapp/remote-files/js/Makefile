all: swfupload.min.js

clean:
	rm -f swfupload.min.js

swfupload.min.js: swfupload.js swfupload.swfobject.js swfupload.cookies.js
	jsmin < swfupload.js > swfupload.min.js
	jsmin < swfupload.swfobject.js >> swfupload.min.js
	jsmin < swfupload.cookies.js >> swfupload.min.js
