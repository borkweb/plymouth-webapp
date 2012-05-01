plugins = bootstrap-dropdown.js bootstrap-modal.js bootstrap-tooltip.js bootstrap-popover.js

all: bootstrap.min.js

clean:
	rm -f bootstrap.js bootstrap.min.js

bootstrap.js: $(plugins)
	cat $^ > $@

bootstrap.min.js: bootstrap.js
	php -r 'echo urlencode(file_get_contents("php://stdin"));' < $^ | sed 's/^/js_code=/' | curl -d @- http://marijnhaverbeke.nl/uglifyjs > $@

