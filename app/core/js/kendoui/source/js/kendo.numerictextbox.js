/*
* Kendo UI Web v2012.2.710 (http://kendoui.com)
* Copyright 2012 Telerik AD. All rights reserved.
*
* Kendo UI Web commercial licenses may be obtained at http://kendoui.com/web-license
* If you do not own a commercial license, this file shall be governed by the
* GNU General Public License (GPL) version 3.
* For GPL requirements, please review: http://www.gnu.org/copyleft/gpl.html
*/
(function($, undefined) {
    /**
     * @name kendo.ui.NumericTextBox.Description
     *
     * @section
     * <p>
     *    The NumericTextBox widget can convert an INPUT element into a numeric, percentage or currency textbox.
     *    The type is defined depending on the specified format. The widget renders spin buttons and with their help you can
     *    increment/decrement the value with a predefined step. The NumericTextBox widget accepts only numeric entries.
     *    The widget uses <em>kendo.culture.current</em> culture in order to determine number precision and other culture
     *    specific properties.
     * </p>
     *
     * <h3>Getting Started</h3>
     *
     * @exampleTitle Creating a NumericTextBox from existing INPUT element
     * @example
     * <input id="textBox" />
     *
     * @exampleTitle NumericTextBox initialization
     * @example
     * $(document).ready(function(){
     *  $("#textBox").kendoNumericTextBox();
     * });
     *
     * @section
     * <p>
     *  When a <b>NumericTextBox</b> is initialized, it will automatically
     *  wraps the input element with span element and will render spin
     *  buttons.
     * </p>
     * <h3>Configuring NumericTextBox behaviors</h3>
     * <p>
     *  The <b>NumericTextBox</b> provides configuration options that can be
     *  easily set during initialization. Among the properties that can be
     *  controlled:
     * </p>
     * <ul>
     *  <li>Value of the <b>NumericTextBox</b></li>
     *  <li>Minimum and/or maximum values</li>
     *  <li>Increment step</li>
     *  <li>Precision of the number</li>
     *  <li>Number format (any valid number format is allowed)</li>
     * </ul>
     * <p>
     *  To see a full list of available properties and values, review the
     *  Slider Configuration API documentation tab.
     * </p>
     * @exampleTitle Customizing NumericTextBox defaults
     * @example
     *  $("#textbox").kendoNumericTextBox({
     *      value: 10,
     *      min: -10,
     *      max: 100,
     *      step: 0.75,
     *      format: "n",
     *      decimals: 3
     *  });
     * @section
     * @exampleTitle Create Currency NumericTextBox widget
     * @example
     *  $("#textbox").kendoNumericTextBox({
     *      format: "c2" //Define currency type and 2 digits precision
     *  });
     * @section
     * @exampleTitle Create Percentage NumericTextBox widget
     * @example
     *  $("#textbox").kendoNumericTextBox({
     *      format: "p",
     *      value: 0.15 // 15 %
     *  });
     *
     * @section
     * <h3>Accessing an Existing NumericTextBox</h3>
     * <p>
     *  You can reference an existing <b>NumericTextBox</b> instance via
     *  <a href="http://api.jquery.com/jQuery.data/">jQuery.data()</a>.
     *  Once a reference has been established, you can use the API to control
     *  its behavior.
     * </p>
     *
     * @exampleTitle Accessing an existing NumericTextBox instance
     * @example
     * var numericTextBox = $("#numericTextBox").data("kendoNumericTextBox");
     *
     */
    var kendo = window.kendo,
        keys = kendo.keys,
        ui = kendo.ui,
        Widget = ui.Widget,
        parse = kendo.parseFloat,
        placeholderSupported = kendo.support.placeholder,
        touch = kendo.support.touch,
        getCulture = kendo.getCulture,
        CHANGE = "change",
        DISABLED = "disabled",
        INPUT = "k-input",
        SPIN = "spin",
        TOUCHEND = "touchend",
        MOUSEDOWN = touch ? "touchstart" : "mousedown",
        MOUSEUP = touch ? "touchmove " + TOUCHEND : "mouseup mouseleave",
        DEFAULT = "k-state-default",
        FOCUSED = "k-state-focused",
        HOVER = "k-state-hover",
        HOVEREVENTS = "mouseenter mouseleave",
        POINT = ".",
        SELECTED = "k-state-selected",
        STATEDISABLED = "k-state-disabled",
        TYPE = touch ? "number" : "text",
        NULL = null,
        proxy = $.proxy,
        decimals = {
            190 : ".",
            188 : ","
        };

    var NumericTextBox = Widget.extend(/** @lends kendo.ui.NumericTextBox.prototype */{
        /**
         * @constructs
         * @extends kendo.ui.Widget
         * @param {Element} element DOM element
         * @param {Object} options Configuration options
         * @option {Number} [value] <null> Specifies the value of the NumericTextBox widget.
         * _example
         *  // specify in the HTML
         * <input id="numeric" value="10" type="number" min="-100" max="100" step="10"/>
         *
         * // specify on widget initialization
         * $("#numeric").kendoNumericTextBox({
         *     min: 0,
         *     max: 100,
         *     value: 50
         * });
         * @option {Number} [min] <null> Specifies the smallest value the user can enter.
         * _example
         *  // specify in the HTML
         * <input id="numeric" value="10" type="number" min="-100" max="100" step="10"/>
         * <br />
         * // specify on widget initialization
         * $("#numeric").kendoNumericTextBox({
         *     min: 0,
         *     max: 100,
         *     value: 50
         * });
         * @option {Number} [max] <null> Specifies the largest value the user can enter.
         * _example
         *  // specify in the HTML
         * <input id="numeric" value="10" type="number" min="-100" max="100" step="10"/>
         * <br />
         * // specify on widget initialization
         * $("#numeric").kendoNumericTextBox({
         *     min: 0,
         *     max: 100,
         *     value: 50
         * });
         * @option {Number} [decimals] <null> Specifies the number precision. If not set precision defined by current culture is used.
         * _example
         * // specify on widget initialization
         * $("#numeric").kendoNumericTextBox({
         *     min: 0,
         *     max: 1,
         *     step: 0.1,
         *     decimals: 1
         * });
         * @option {Number} [step] <1> Specifies the increment/decrement step.
         * _example
         *  // specify in the HTML
         * <input id="numeric" value="10" type="number" />
         * <br />
         * // specify on widget initialization
         * $("#numeric").kendoNumericTextBox({
         *     min: 0,
         *     max: 1,
         *     step: 0.1
         * });
         * @option {String} [format] <n> Specifies the format of the number. Any valid number format is allowed.
         * _example
         * $("#numeric").kendoNumericTextBox({
         *    format: "p0", // format as percentage with % sign
         *    min: 0,
         *    max: 1,
         *    step: 0.01
         * });
         * @option {String} [placeholder] <""> Specifies the text displayed when the input is empty.
         * _example
         * // specify on widget initialization
         * $("#numeric").kendoNumericTextBox({
         *     min: 0,
         *     max: 100,
         *     value: 50,
         *     placeholder: "Select A Value"
         * });
         * @option {String} [upArrowText] <Increase value> Specifies the text of the tooltip on the up arrow.
         * _example
         * // specify on widget initialization
         * $("#numeric").kendoNumericTextBox({
         *     min: 0,
         *     max: 100,
         *     value: 50,
         *     upArrowText: "More",
         *     downArrowText: "Less"
         * });
         * @option {String} [downArrowText] <Decrease value> Specifies the text of the tooltip on the down arrow.
         * _example
         * // specify on widget initialization
         * $("#numeric").kendoNumericTextBox({
         *     min: 0,
         *     max: 100,
         *     value: 50,
         *     upArrowText: "More",
         *     downArrowText: "Less"
         * });
         * @option {String} [culture] <en-US> Specifies the culture info used by the NumericTextBox widget.
         * _example
         *
         * // specify on widget initialization
         * $("#numeric").kendoNumericTextBox({
         *     culture: "de-DE"
         * });
         */
         init: function(element, options) {
             var that = this,
             isStep = options && options.step !== undefined,
             min, max, step, value, format;

             Widget.fn.init.call(that, element, options);

             options = that.options;
             element = that.element.addClass(INPUT)
                           .bind({
                               keydown: proxy(that._keydown, that),
                               paste: proxy(that._paste, that),
                               blur: proxy(that._focusout, that)
                           });

             element.closest("form")
                    .bind("reset", function() {
                        setTimeout(function() {
                            that.value(element[0].value);
                        });
                    });

             options.placeholder = options.placeholder || element.attr("placeholder");

             that._wrapper();
             that._arrows();
             that._input();

             that._text.focus(proxy(that._click, that));

             min = that.min(element.attr("min"));
             max = that.max(element.attr("max"));
             step = that._parse(element.attr("step"));

             if (options.min === NULL && min !== NULL) {
                 options.min = min;
             }

             if (options.max === NULL && max !== NULL) {
                 options.max = max;
             }

             if (!isStep && step !== NULL) {
                 options.step = step;
             }

             format = options.format;
             if (format.slice(0,3) === "{0:") {
                 options.format = format.slice(3, format.length - 1);
             }

             value = options.value;
             that.value(value !== NULL ? value : element.val());

             that.enable(!element.is('[disabled]'));

             kendo.notify(that);
         },

        options: {
            name: "NumericTextBox",
            decimals: NULL,
            min: NULL,
            max: NULL,
            value: NULL,
            step: 1,
            culture: "",
            format: "n",
            spinners: true,
            placeholder: "",
            upArrowText: "Increase value",
            downArrowText: "Decrease value"
        },
        events: [
             /**
             * Fires when the value is changed
             * @name kendo.ui.NumericTextBox#change
             * @event
             * @param {Event} e
             * @example
             * $("#numeric").kendoNumericTextBox({
             *     change: function(e) {
             *         // handle event
             *     }
             * });
             * @exampleTitle To set after initialization
             * @example
             * // get a reference to the numeric textbox widget
             * var numeric = $("#numeric").data("kendoNumericTextBox");
             * // bind to the change event
             * numeric.bind("change", function(e) {
             *     // handle event
             * });
             */
            CHANGE,
             /**
             * Fires when the value is changed from the spin buttons
             * @name kendo.ui.NumericTextBox#spin
             * @event
             * @param {Event} e
             * @example
             * $("#numeric").kendoNumericTextBox({
             *     spin: function(e) {
             *         // handle event
             *     }
             * });
             * @exampleTitle To set after initialization
             * @example
             * // get a reference to the numeric textbox widget
             * var numeric = $("#numeric").data("kendoNumericTextBox");
             * // bind to the spin event
             * numeric.bind("spin", function(e) {
             *     // handle event
             * });
             */
            SPIN
        ],
        /**
        * Enable/Disable the numerictextbox widget.
        * @param {Boolean} enable The argument, which defines whether to enable/disable tha numerictextbox.
        * @example
        * // get a reference to the numeric textbox
        * var textbox = $("#textbox").data("kendoNumericTextBox");
        *
        * // disables the numerictextbox
        * numerictextbox.enable(false);
        *
        * // enables the numerictextbox
        * numerictextbox.enable(true);
        */
        enable: function(enable) {
            var that = this,
                text = that._text.add(that.element),
                wrapper = that._inputWrapper.unbind(HOVEREVENTS),
                upArrow = that._upArrow.unbind(MOUSEDOWN),
                downArrow = that._downArrow.unbind(MOUSEDOWN);

            that._toggleText(true);

            if (enable === false) {
                wrapper
                    .removeClass(DEFAULT)
                    .addClass(STATEDISABLED);

                text.attr(DISABLED, DISABLED);
            } else {
                wrapper
                    .addClass(DEFAULT)
                    .removeClass(STATEDISABLED)
                    .bind(HOVEREVENTS, that._toggleHover);

                text.removeAttr(DISABLED);

                upArrow.bind(MOUSEDOWN, function(e) {
                    e.preventDefault();
                    that._spin(1);
                    that._upArrow.addClass(SELECTED);
                });

                downArrow.bind(MOUSEDOWN, function(e) {
                    e.preventDefault();
                    that._spin(-1);
                    that._downArrow.addClass(SELECTED);
                });
            }
        },

        /**
        * Gets/Sets the min value of the NumericTextBox.
        * @param {Number | String} value The min value to set.
        * @returns {Number} The min value of the NumericTextBox.
        * @example
        * // get a reference to the NumericTextBox widget
        * var numerictextbox = $("#numerictextbox").data("kendoNumericTextBox");
        *
        * // get the min value of the numerictextbox.
        * var min = numerictextbox.min();
        *
        * // set the min value of the numerictextbox.
        * numerictextbox.min(-10);
        */
        min: function(value) {
            return this._option("min", value);
        },

        /**
        * Gets/Sets the max value of the NumericTextBox.
        * @param {Number | String} value The max value to set.
        * @returns {Number} The max value of the NumericTextBox.
        * @example
        * // get a reference to the NumericTextBox widget
        * var numerictextbox = $("#numerictextbox").data("kendoNumericTextBox");
        *
        * // get the max value of the numerictextbox.
        * var max = numerictextbox.max();
        *
        * // set the max value of the numerictextbox.
        * numerictextbox.max(10);
        */
        max: function(value) {
            return this._option("max", value);
        },

        /**
        * Gets/Sets the step value of the NumericTextBox.
        * @param {Number | String} value The step value to set.
        * @returns {Number} The step value of the NumericTextBox.
        * @example
        * // get a reference to the NumericTextBox widget
        * var numerictextbox = $("#numerictextbox").data("kendoNumericTextBox");
        *
        * // get the step value of the numerictextbox.
        * var step = numerictextbox.step();
        *
        * // set the step value of the numerictextbox.
        * numerictextbox.step(0.1);
        */
        step: function(value) {
            return this._option("step", value);
        },

        /**
        * Gets/Sets the value of the numerictextbox.
        * @param {Number | String} value The value to set.
        * @returns {Number} The value of the numerictextbox.
        * @example
        * // get a referene to the numeric textbox
        * var numerictextbox = $("#textbox").data("kendoNumericTextBox");
        *
        * // get the value of the numerictextbox.
        * var value = numerictextbox.value();
        *
        * // set the value of the numerictextbox.
        * numerictextbox.value("10.20");
        */
        value: function(value) {
            var that = this, adjusted;

            if (value === undefined) {
                return that._value;
            }

            value = that._parse(value);
            adjusted = that._adjust(value);

            if (value !== adjusted) {
                return;
            }

            that._update(value);
            that._old = that._value;
        },

        _adjust: function(value) {
            var that = this,
            options = that.options,
            min = options.min,
            max = options.max;

            if (value === NULL) {
                return value;
            }

            if (min !== NULL && value < min) {
                value = min;
            } else if (max !== NULL && value > max) {
                value = max;
            }

            return value;
        },

        _arrows: function() {
            var that = this,
            arrows,
            options = that.options,
            spinners = options.spinners,
            element = that.element;

            arrows = element.siblings(".k-icon");

            if (!arrows[0]) {
                arrows = $(buttonHtml("n", options.upArrowText) + buttonHtml("s", options.downArrowText))
                        .insertAfter(element);

                arrows.wrapAll('<span class="k-select"/>');
            }

            arrows.bind(MOUSEUP, function(e) {
                if (!touch || kendo.eventTarget(e) != e.currentTarget || e.type === TOUCHEND) {
                    clearTimeout( that._spinning );
                }
                arrows.removeClass(SELECTED);
            });

            if (!spinners) {
                arrows.toggle(spinners);
                that._inputWrapper.addClass("k-expand-padding");
            }

            that._upArrow = arrows.eq(0);
            that._downArrow = arrows.eq(1);
        },

        _blur: function() {
            var that = this;

            that._toggleText(true);
            that._change(that.element.val());
        },

        _click: function(e) {
            var that = this;

            clearTimeout(that._focusing);
            that._focusing = setTimeout(function() {
                var input = e.target,
                    idx = caret(input),
                    value = input.value.substring(0, idx),
                    format = that._format(that.options.format),
                    group = format[","],
                    groupRegExp = new RegExp("\\" + group, "g"),
                    extractRegExp = new RegExp("([\\d\\" + group + "]+)(\\" + format[POINT] + ")?(\\d+)?"),
                    result = extractRegExp.exec(value),
                    caretPosition = 0;

                if (result) {
                    caretPosition = result[0].replace(groupRegExp, "").length;

                    if (value.indexOf("(") != -1 && that._value < 0) {
                        caretPosition++;
                    }
                }

                that._focusin();

                caret(that.element[0], caretPosition);
            });
        },

        _change: function(value) {
            var that = this;

            that._update(value);
            value = that._value;

            if (that._old != value) {
                that._old = value;
                that.trigger(CHANGE);

                // trigger the DOM change event so any subscriber gets notified
                that.element.trigger(CHANGE);
            }
        },

        _culture: function(culture) {
            return culture || getCulture(this.options.culture);
        },

        _focusin: function() {
            var that = this;
            that._toggleText(false);
            that.element.focus();
            that._inputWrapper.addClass(FOCUSED);
        },

        _focusout: function() {
            var that = this;

            clearTimeout(that._focusing);
            that._inputWrapper.removeClass(FOCUSED);
            that._blur();
        },

        _format: function(format, culture) {
            var numberFormat = this._culture(culture).numberFormat;

            format = format.toLowerCase();

            if (format.indexOf("c") > -1) {
                numberFormat = numberFormat.currency;
            } else if (format.indexOf("p") > -1) {
                numberFormat = numberFormat.percent;
            }

            return numberFormat;
        },

        _input: function() {
            var that = this,
                CLASSNAME = "k-formatted-value",
                element = that.element.show()[0],
                wrapper = that.wrapper,
                text;


            text = wrapper.find(POINT + CLASSNAME);

            if (!text[0]) {
                text = $("<input />").insertBefore(element).addClass(CLASSNAME);
            }

            element.type = TYPE;
            text[0].type = "text";
            text[0].style.cssText = element.style.cssText;
            text.attr("placeholder", that.options.placeholder);

            that._text = text.attr("readonly", true)
                             .addClass(element.className);
        },

        _keydown: function(e) {
            var that = this,
                key = e.keyCode;

            if (key == keys.DOWN) {
                that._step(-1);
            } else if (key == keys.UP) {
                that._step(1);
            } else if (key == keys.ENTER) {
                that._change(that.element.val());
            }

            if (that._prevent(key) && !e.ctrlKey) {
                e.preventDefault();
            }
        },

        _paste: function(e) {
            var that = this,
                element = e.target,
                value = element.value;

            setTimeout(function() {
                if (that._parse(element.value) === NULL) {
                    that._update(value);
                }
            });
        },

        _prevent: function(key) {
            var that = this,
                element = that.element[0],
                value = element.value,
                options = that.options,
                min = options.min,
                numberFormat = that._format(options.format),
                separator = numberFormat[POINT],
                precision = options.decimals,
                idx = caret(element),
                prevent = true,
                end;

            if (precision === NULL) {
                precision = numberFormat.decimals;
            }

            if ((key > 16 && key < 21) ||
                (key > 32 && key < 37) ||
                (key > 47 && key < 58) ||
                (key > 95 && key < 106) ||
                 key == keys.INSERT ||
                 key == keys.DELETE ||
                 key == keys.LEFT ||
                 key == keys.RIGHT ||
                 key == keys.TAB ||
                 key == keys.BACKSPACE ||
                 key == keys.ENTER) {
                prevent = false;
            } else if (decimals[key] === separator && precision > 0 && value.indexOf(separator) == -1) {
                prevent = false;
            } else if ((min === NULL || min < 0) && value.indexOf("-") == -1 && (key == 189 || key == 109) && idx === 0) { //sign
                prevent = false;
            } else if (key == 110 && precision > 0 && value.indexOf(separator) == -1) {
                end = value.substring(idx);

                element.value = value.substring(0, idx) + separator + end;
            }

            return prevent;
        },

        _option: function(option, value) {
            var that = this,
                options = that.options;

            if (value === undefined) {
                return options[option];
            }

            value = that._parse(value);

            if (!value && option === "step") {
                return;
            }

            options[option] = that._parse(value);
        },

        _spin: function(step, timeout) {
            var that = this;

            timeout = timeout || 500;

            clearTimeout( that._spinning );
            that._spinning = setTimeout(function() {
                that._spin(step, 50);
            }, timeout );

            that._step(step);
        },

        _step: function(step) {
            var that = this,
                element = that.element,
                value = that._parse(element.val()) || 0;

            if (document.activeElement != element[0]) {
                that._focusin();
            }

            value += that.options.step * step;

            that._update(that._adjust(value));

            that.trigger(SPIN);
        },

        _toggleHover: function(e) {
            if (!touch) {
                $(e.currentTarget).toggleClass(HOVER, e.type === "mouseenter");
            }
        },

        _toggleText: function(toggle) {
            var that = this;

            toggle = !!toggle;
            that._text.toggle(toggle);
            that.element.toggle(!toggle);
        },

        _parse: function(value, culture) {
            return parse(value, this._culture(culture), this.options.format);
        },

        _update: function(value) {
            var that = this,
                options = that.options,
                format = options.format,
                decimals = options.decimals,
                culture = that._culture(),
                numberFormat = that._format(format, culture),
                isNotNull;

            if (decimals === NULL) {
                decimals = numberFormat.decimals;
            }

            value = that._parse(value, culture);

            isNotNull = value !== NULL;

            if (isNotNull) {
                value = parseFloat(value.toFixed(decimals));
            }

            that._value = value = that._adjust(value);
            that._placeholder(kendo.toString(value, format, culture));
            that.element.val(isNotNull ? value.toString().replace(POINT, numberFormat[POINT]) : "");
        },

        _placeholder: function(value) {
            this._text.val(value);
            if (!placeholderSupported && !value) {
                this._text.val(this.options.placeholder);
            }
        },

        _wrapper: function() {
            var that = this,
                element = that.element,
                wrapper;

            wrapper = element.parents(".k-numerictextbox");

            if (!wrapper.is("span.k-numerictextbox")) {
                wrapper = element.hide().wrap('<span class="k-numeric-wrap k-state-default" />').parent();
                wrapper = wrapper.wrap("<span/>").parent();
            }

            wrapper[0].style.cssText = element[0].style.cssText;
            element[0].style.width = "";
            that.wrapper = wrapper.addClass("k-widget k-numerictextbox").show();
            that._inputWrapper = $(wrapper[0].firstChild);
        }
    });

    function buttonHtml(className, text) {
        return '<span unselectable="on" class="k-link"><span unselectable="on" class="k-icon k-i-arrow-' + className + '" title="' + text + '">' + text + '</span></span>';
    }

    function caret(element, position) {
        var range,
            isPosition = position !== undefined;

        if (document.selection) {
            if ($(element).is(":visible")) {
                element.focus();
            }
            range = document.selection.createRange();
            if (isPosition) {
                range.move("character", position);
                range.select();
            } else {
                var rangeElement = element.createTextRange(),
                    rangeDuplicated = rangeElement.duplicate();
                    rangeElement.moveToBookmark(range.getBookmark());
                    rangeDuplicated.setEndPoint('EndToStart', rangeElement);

                position = rangeDuplicated.text.length;

            }
        } else if (element.selectionStart !== undefined) {
            if (isPosition) {
                element.focus();
                element.setSelectionRange(position, position);
            } else {
                position = element.selectionStart;
            }
        }

        return position;
    }

    ui.plugin(NumericTextBox);
})(jQuery);
;