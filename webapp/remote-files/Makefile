APP_PATH = rf
SIMPLESUB = .htaccess common.php css/style.css

.PHONY: all clean

all: $(SIMPLESUB) css/style.css
	$(MAKE) APP_PATH=$(APP_PATH) -C js

clean:
	-rm -f $(SIMPLESUB) js/swfupload.min.js

%: %.in
	sed "s/%APP_PATH%/$(APP_PATH)/" < $< > $@
