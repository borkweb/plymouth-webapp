plugins = ../amplify.js \
	../jquery-plugins/jquery.multi-ddm.min.js \
	../behavior.js \
	../jquery-plugins/jquery.ba-dotimeout.js \
	../jquery-plugins/waypoints.js

all: psu-standard.min.js

clean:
	rm -f psu-standard.js psu-standard.min.js

# simple concatenation
psu-standard.js: $(plugins)
	cat $^ > $@

# make separately from psu-standard.js, in order to keep script copyright intact
psu-standard.min.js: psu-standard.js
	rm -f psu-standard.min.js
	for plugin in $(plugins) ; do cat $$plugin | uglifyjs >>$@ ; done
