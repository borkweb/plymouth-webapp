JS_TARGETS = behavior.js
CLEANUP = docs
ALL = docs

docs: docs/behavior.html

docs/behavior.html: behavior.js
	pycco behavior.js

include /web/bin/Makefile.master
