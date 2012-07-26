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
     * @name kendo.ui.DateTimePicker.Description
     *
     * @section
     * <p>
     *  The <b>DateTimePicker</b> allows the end user to select a value from a
     *  calendar or a time drop-down list. Direct input is also allowed.
     *  It supports configurable options for minimum and maximum value, the format,
     *  the interval between predefined hours in the time view, custom templates for "month" view
     *  of the calendar, start view and the depth of the navigation.
     * </p>
     * <h3>Getting Started</h3>
     *
     * @exampleTitle Creating a DateTimePicker from existing input element
     * @example
     * <input id="dateTimePicker" />
     *
     * @exampleTitle DateTimePicker initialization
     * @example
     * $(document).ready(function(){
     *  $("#dateTimePicker").kendoDateTimePicker();
     * });
     *
     * @section
     * <p>
     *  When a <b>DateTimePicker</b> is initialized, it will be displayed at the
     *  location of the target HTML element.
     * </p>
     * <h3>Configuring DateTimePicker Behaviors</h3>
     * <p>
     *  The <b>DateTimePicker</b> provides configuration options that can be set
     *  during initialization. Among the properties that can be controlled:
     * </p>
     * <ul>
     *  <li>Selected datetime</li>
     *  <li>Minimum/Maximum datetime</li>
     *  <li>Define format</li>
     *  <li>Start view</li>
     *  <li>Navigation depth (last view to which end user can navigate)</li>
     *  <li>Define interval between predefined values in the time drop-down list</li>
     * </ul>
     *
     * @exampleTitle Create DateTimePicker with a selected value and a defined
     * minimum and maximum datetime
     * @example
     * $(document).ready(function(){
     *  $("#dateTimePicker").kendoDateTimePicker({
     *     value: new Date(2000, 10, 10, 10, 0, 0),
     *     min: new Date(1950, 0, 1, 8, 0, 0),
     *     max: new Date(2049, 11, 31, 18, 0, 0)
     *  })
     * });
     *
     * @section
     * <p>
     *  DateTimePicker will set the value only if the entered datetime is valid and
     *  within the defined range.
     * </p>
     *
     * @exampleTitle Define the format
     * @example
     * $("#dateTimePicker").kendoDateTimePicker({
     *     format: "MM/dd/yyyy hh:mm tt" //format is used to format the value of the widget and to parse the input.
     * });
     *
     * @exampleTitle Define the time format
     * @example
     * $("#dateTimePicker").kendoDateTimePicker({
     *     timeFormat: "hh:mm:ss tt" //this format will be used to format the predefined values in the time list.
     * });
     *
     * @section
     * <h3>Defining a Start View and Navigation Depth</h3>
     * <p>
     *  The first rendered view can be defined with "start" option.
     *  Navigation depth can be controlled with "depth" option. Predefined
     *  views are:
     * </p>
     * <ul>
     *  <li>"month" - shows the days from the month</li>
     *  <li>"year" - shows the months of the year</li>
     *  <li>"decade" - shows the years from the decade</li>
     *  <li>"century" - shows the decades from the century</li>
     * </ul>
     *
     * @exampleTitle Create a DateTimePicker for selecting a month
     * @example
     * $("#dateTimePicker").kendoDateTimePicker({
     *  start: "year",
     *  depth: "year"
     * });
     *
     * @exampleTitle Define the interval (in minutes) between values in the time drop-down list
     * @example
     * $("#dateTimePicker").kendoDateTimePicker({
     *     interval: 15
     * })
     *
     * @section
     * <h3>Accessing an Existing DateTimePicker</h3>
     * <p>
     *  You can reference an existing <b>DateTimePicker</b> instance via
     *  <a href="http://api.jquery.com/jQuery.data/">jQuery.data()</a>.
     *  Once a reference has been established, you can use the API to control
     *  its behavior.
     * </p>
     *
     * @exampleTitle Accessing an existing DateTimePicker instance
     * @example
     * var dateTimePicker = $("#dateTimePicker").data("kendoDateTimePicker");
     *
     */

    var kendo = window.kendo,
        TimeView = kendo.TimeView,
        touch = kendo.support.touch,
        parse = kendo.parseDate,
        extractFormat = kendo._extractFormat,
        calendar = kendo.calendar,
        isInRange = calendar.isInRange,
        restrictValue = calendar.restrictValue,
        isEqualDatePart = calendar.isEqualDatePart,
        getMilliseconds = TimeView.getMilliseconds,
        ui = kendo.ui,
        Widget = ui.Widget,
        OPEN = "open",
        CLOSE = "close",
        CHANGE = "change",
        CLICK = (touch ? "touchend" : "click"),
        DISABLED = "disabled",
        DEFAULT = "k-state-default",
        FOCUSED = "k-state-focused",
        HOVER = "k-state-hover",
        STATEDISABLED = "k-state-disabled",
        HOVEREVENTS = "mouseenter mouseleave",
        MOUSEDOWN = (touch ? "touchstart" : "mousedown"),
        ICONEVENTS = CLICK + " " + MOUSEDOWN,
        MONTH = "month",
        SPAN = "<span/>",
        DATE = Date,
        MIN = new DATE(1900, 0, 1),
        MAX = new DATE(2099, 11, 31),
        dateViewParams = { view: "date" },
        timeViewParams = { view: "time" },
        extend = $.extend;

    var DateTimePicker = Widget.extend(/** @lends kendo.ui.DateTimePicker.prototype */{
        /**
         * @constructs
         * @extends kendo.ui.Widget
         * @param {Element} element DOM element
         * @param {Object} options Configuration options.
         * @option {Date} [value] <null> Specifies the selected value.
         * _example
         * // set the selected value to January 1st, 2011 12:00 AM
         * $("#dateTimePicker").kendoDateTimePicker({
         *  value: new Date(2011, 0, 1)
         * });
         * _exampleTitle To set after initialization
         * _example
         * // get a reference to the dateTimePicker widget
         * var dateTimePicker = $("#dateTimePicker").data("kendoDateTimePicker");
         * // set the selected value on the dateTimePicker to January 1st, 2011
         * dateTimePicker.value(new Date(2011, 0, 1));
         * @option {Date} [min] <Date(1900, 0, 1)> Specifies the minimum date that the calendar can show.
         * _example
         * // set the min date to Jan 1st, 2011
         * $("#dateTimePicker").kendoDateTimePicker({
         *  min: new Date(2011, 0, 1)
         * });
         * _exampleTitle To set after initialization
         * _example
         * // get a reference to the dateTimePicker widget
         * var dateTimePicker = $("#dateTimePicker").data("kendoDateTimePicker");
         * // set the min date to Jan 1st, 2011 12:00 AM
         * dateTimePicker.min(new Date(2011, 0, 1));
         * @option {Date} [max] <Date(2099, 11, 31)> Specifies the maximum date, which the calendar can show.
         * _example
         * $("#dateTimePicker").kendoDateTimePicker({
         *  max: new Date(2013, 0, 1) // sets max date to Jan 1st, 2013 12:00 AM
         * });
         * _exampleTitle To set after initialization
         * _example
         * var dateTimePicker = $("#dateTimePicker").data("kendoDateTimePicker");
         * // set the max date to Jan 1st, 2013 12:00 AM
         * dateTimePicker.max(new Date(2013,0, 1));
         * @option {String} [format] <MM/dd/yyyy h:mm tt> Specifies the format, which is used to format the value of the DateTimePicker displayed in the input.
         * _example
         * $("#dateTimePicker").kendoDateTimePicker({
         *     format: "yyyy/MM/dd hh:mm tt"
         * });
         * @option {String} [timeFormat] <h:mm tt> Specifies the format, which is used to format the values in the time drop-down list.
         * _example
         * $("#dateTimePicker").kendoDateTimePicker({
         *     timeFormat: "HH:mm" //24 hours format
         * });
         * @option {Array} [parseFormats] <> Specifies the formats, which are used to parse the value set with value() method or by direct input. If not set the value of the options.format and options.timeFormat will be used.
         * _example
         * $("#datePicker").kendoDatePicker({
         *     format: "yyyy/MM/dd hh:mm tt",
         *     parseFormats: ["MMMM yyyy", "HH:mm"] //format also will be added to parseFormats
         * });
         * @option {Array} [dates] <> Specifies a list of dates, which are shown in the time drop-down list. If not set, the DateTimePicker will auto-generate the available times.
         *  _example
         * $("#dateTimePicker").kendoDateTimePicker({
         *     dates: [new Date(2000, 10, 10, 10, 0, 0), new Date(2000, 10, 10, 30, 0)] //the drop-down list will consist only two entries - "10:00 AM" and "10:30 AM"
         * });
         * @option {Number} [interval] <30> Specifies the interval, between values in the popup list, in minutes.
         * _example
         * $("#dateTimePicker").kendoDateTimePicker({
         *     interval: 15
         * });
         * @option {String} [start] <month> Specifies the start view of the calendar.
         * The following settings are available for the <b>start</b> value:
         * <div class="details-list">
         *    <dl>
         *         <dt>
         *              <code>"month"</code>
         *         </dt>
         *         <dd>
         *             shows the days of the month
         *         </dd>
         *         <dt>
         *              <code>"year"</code>
         *         </dt>
         *         <dd>
         *              shows the months of the year
         *         </dd>
         *         <dt>
         *              <code>"decade"</code>
         *         </dt>
         *         <dd>
         *              shows the years of the decade
         *         </dd>
         *         <dt>
         *              <code>"century"</code>
         *         </dt>
         *         <dd>
         *              shows the decades from the centery
         *         </dd>
         *    </dl>
         * </div>
         * _example
         * $("#dateTimePicker").kendoDateTimePicker({
         *     start: "decade" // the dateTimePicker will start with a decade display
         * });
         * @option {String} [depth] Specifies the navigation depth of the calendar. The following
         * settings are available for the <b>depth</b> value:
         * <div class="details-list">
         *    <dl>
         *         <dt>
         *              <code>"month"</code>
         *         </dt>
         *         <dd>
         *             shows the days of the month
         *         </dd>
         *         <dt>
         *              <code>"year"</code>
         *         </dt>
         *         <dd>
         *              shows the months of the year
         *         </dd>
         *         <dt>
         *              <code>"decade"</code>
         *         </dt>
         *         <dd>
         *              shows the years of the decade
         *         </dd>
         *         <dt>
         *              <code>"century"</code>
         *         </dt>
         *         <dd>
         *              shows the decades from the centery
         *         </dd>
         *    </dl>
         * </div>
         * _example
         * $("#dateTimePicker").kendoDateTimePicker({
         *     start: "decade",
         *     depth: "year" // the dateTimePicker will only go to the year level
         * });
         * @option {String} [footer] <> Template to be used for rendering the footer of the calendar.
         * _example
         *  // DateTimePicker initialization
         *  <script>
         *      $("#dateTimePicker").kendoDateTimePicker({
         *          footer: kendo.template("Today - #=kendo.toString(data, 'd') #")
         *      });
         *  </script>
         * @option {Object} [month] <> Templates for the cells rendered in the calendar "month" view.
         * @option {String} [month.content] <> Template to be used for rendering the cells in the calendar "month" view, which are in range.
         * _example
         *  //template
         * <script id="cellTemplate" type="text/x-kendo-tmpl">
         *      <div class="${ data.value < 10 ? exhibition : party }">
         *      </div>
         *      ${ data.value }
         *  </script>
         *
         *  //dateTimePicker initialization
         *  <script>
         *      $("#dateTimePicker").kendoDateTimePicker({
         *          month: {
         *             content:  kendo.template($("#cellTemplate").html()),
         *          }
         *      });
         *  </script>
         *
         * @option {String} [month.empty]
         * The template used for rendering the cells in the calendar "month" view, which are not in the range between
         * the minimum and maximum values.
         *
         * @option {Object} [animation]
         * The animation(s) used for opening and/or closing the pop-ups. Setting this value to <strong>false</strong>
         * will disable the animation(s).
         *
         * @option {Object} [animation.open]
         * The animation(s) used for displaying of the pop-up.
         *
         * _exampleTitle Fade-in the pop-up over 300 milliseconds
         * _example
         * $("#dateTimePicker").kendoDateTimePicker({
         *     animation: {
         *         open: {
         *             effects: "fadeIn",
         *             duration: 300,
         *             show: true
         *         }
         *     }
         * });
         *
         * @option {Object} [animation.close]
         * The animation(s) used for hiding of the pop-up.
         *
         * _exampleTitle Fade-out the pop-up over 300 milliseconds
         * _example
         * $("#dateTimePicker").kendoDateTimePicker({
         *     animation: {
         *         close: {
         *             effects: "fadeOut",
         *             duration: 300,
         *             show: false,
         *             hide: true
         *         }
         *     }
         * });
         *
         * @option {String} [culture] <en-US> Specifies the culture info used by the widget.
         * _example
         *
         * // specify on widget initialization
         * $("#datetimepicker").kendoDateTimePicker({
         *     culture: "de-DE"
         * });
         */
        init: function(element, options) {
            var that = this;

            Widget.fn.init.call(that, element, options);

            element = that.element;
            options = that.options;

            normalize(options);

            that._wrapper();

            that._icons();

            that._views();

            if (!touch) {
                element[0].type = "text";
            }

            element.addClass("k-input")
                    .bind({
                        keydown: $.proxy(that._keydown, that),
                        focus: function() {
                            that._inputWrapper.addClass(FOCUSED);
                        },
                        blur: function() {
                            that._inputWrapper.removeClass(FOCUSED);
                            that._change(element.val());
                            that.close("date");
                            that.close("time");
                        }
                    })
                   .closest("form")
                   .bind("reset", function() {
                       that.value(element[0].defaultValue);
                   });

            that._midnight = getMilliseconds(options.min) + getMilliseconds(options.max) === 0;

            that.enable(!element.is('[disabled]'));
            that.value(options.value || element.val());

            kendo.notify(that);
        },

        options: {
            name: "DateTimePicker",
            value: null,
            format: "",
            timeFormat: "",
            culture: "",
            parseFormats: [],
            dates: [],
            min: new DATE(MIN),
            max: new DATE(MAX),
            interval: 30,
            height: 200,
            footer: "",
            start: MONTH,
            depth: MONTH,
            animation: {},
            month : {}
    },

    events: [
        /**
        *
        * Triggered when the underlying value of a DateTimePicker is changed.
        *
        * @name kendo.ui.DateTimePicker#change
        * @event
        *
        * @param {Event} e
        *
        * @exampleTitle Attach change event handler during initialization; detach via unbind()
        * @example
        * // event change for expand
        * var onChange = function(e) {
        *     // ...
        * };
        *
        * // attach change event handler during initialization
        * var dateTimePicker = $("#dateTimePicker").kendoDateTimePicker({
        *     change: onChange
        * });
        *
        * // detach change event handler via unbind()
        * dateTimePicker.data("kendoDateTimePicker").unbind("change", onChange);
        *
        * @exampleTitle Attach change event handler via bind(); detach via unbind()
        * @example
        * // event change for expand
        * var onChange = function(e) {
        *     // ...
        * };
        *
        * // attach change event handler via bind()
        * $("#dateTimePicker").data("kendoDateTimePicker").bind("change", onChange);
        *
        * // detach change event handler via unbind()
        * $("#dateTimePicker").data("kendoDateTimePicker").unbind("change", onChange);
        *
        */
        /**
        * Fires when the calendar or the time drop-down list is opened
        * @name kendo.ui.DateTimePicker#open
        * @event
        * @param {Event} e
        *
        * @param {String} e.view
        * The view which is opened. Possible values are "date" and "time".
        *
        * @example
        * $("#dateTimePicker").kendoDateTimePicker({
        *     open: function(e) {
        *         // handle event
        *     }
        * });
        * @exampleTitle To set after initialization
        * @example
        * // get a reference to the dateTimePicker widget
        * var dateTimePicker = $("#dateTimePicker").data("kendoDateTimePicker");
        * // bind to the open event
        * dateTimePicker.bind("open", function(e) {
        *     // handle event
        * });
        */
        /**
        * Fires when the calendar or the time drop-down list is closed
        * @name kendo.ui.DateTimePicker#close
        * @event
        * @param {Event} e
        *
        * @param {String} e.view
        * The view which is closed. Possible values are "date" and "time".
        *
        * @example
        * $("#dateTimePicker").kendoDateTimePicker({
        *     close: function(e) {
        *         // handle event
        *     }
        * });
        * @exampleTitle To set after initialization
        * @example
        * // get a reference to the dateTimePicker widget
        * var dateTimePicker = $("#dateTimePicker").data("kendoDateTimePicker");
        * // bind to the close event
        * dateTimePicker.bind("close", function(e) {
        *     // handle event
        * });
        */
        OPEN,
        CLOSE,
        CHANGE
    ],

        setOptions: function(options) {
            var that = this;

            Widget.fn.setOptions.call(that, options);

            normalize(that.options);

            extend(that.dateView.options, that.options);
            extend(that.timeView.options, that.options);

            that.timeView.ul[0].innerHTML = "";
        },

        /**
         *
         * Enables or disables a DateTimePicker.
         *
         * @param {Boolean} enable
         * Enables (<strong>true</strong> or undefined) or disables (<strong>false</strong>) a DateTimePicker.
         *
         * @exampleTitle Enable a DateTimePicker
         * @example
         * $("dateTimePicker").data("kendoDateTimePicker").enable();
         *
         * @exampleTitle Enable a dateTimePicker
         * @example
         * $("dateTimePicker").data("kendoDateTimePicker").enable(true);
         *
         * @exampleTitle Disable a dateTimePicker
         * @example
         * $("dateTimePicker").data("kendoDateTimePicker").enable(false);
         *
         */
        enable: function(enable) {
            var that = this,
                dateIcon = that._dateIcon.unbind(ICONEVENTS),
                timeIcon = that._timeIcon.unbind(ICONEVENTS),
                wrapper = that._inputWrapper.unbind(HOVEREVENTS),
                element = that.element;

            if (enable === false) {
                wrapper
                    .removeClass(DEFAULT)
                    .addClass(STATEDISABLED);

                element.attr(DISABLED, DISABLED);
            } else {
                wrapper
                    .addClass(DEFAULT)
                    .removeClass(STATEDISABLED)
                    .bind(HOVEREVENTS, that._toggleHover);

                element
                    .removeAttr(DISABLED);

                dateIcon.bind({
                    click: function() {
                        that.toggle("date");

                        if (!touch && element[0] !== document.activeElement) {
                            element.focus();
                        }
                    },
                    mousedown: preventDefault
                });

                timeIcon.bind({
                    click: function() {
                        that.toggle("time");

                        if (!touch && element[0] !== document.activeElement) {
                            element.focus();
                        }
                    },
                    mousedown: preventDefault
                });
            }
        },

        /**
         *
         * Closes the calendar or the time drop-down list.
         *
         * @param {String} view
         * The view of the DateTimePicker, expressed as a string.
         * Available views are "time" and "date".
         *
         * @exampleTitle Close the calendar of the DateTimePicker
         * @example
         * $("dateTimePicker").data("kendoDateTimePicker").close();
         *
         * @exampleTitle Close the calendar of the DateTimePicker
         * @example
         * $("dateTimePicker").data("kendoDateTimePicker").close("date");
         *
         * @exampleTitle Close the time drop-down list of a DateTimePicker.
         * @example
         * $("dateTimePicker").data("kendoDateTimePicker").close("time");
         *
         */
        close: function(view) {
            if (view !== "time") {
                view = "date";
            }

            this[view + "View"].close();
        },

        /**
         *
         * Opens the calendar or the time drop-down list.
         *
         * @param {String} view
         * The view of the DateTimePicker, expressed as a string.
         * Available views are "time" and "date".
         *
         * @exampleTitle Open the calendar of the DateTimePicker
         * @example
         * $("dateTimePicker").data("kendoDateTimePicker").open();
         *
         * @exampleTitle Open the calendar of the DateTimePicker
         * @example
         * $("dateTimePicker").data("kendoDateTimePicker").open("date");
         *
         * @exampleTitle Open the time drop-down list of a DateTimePicker.
         * @example
         * $("dateTimePicker").data("kendoDateTimePicker").open("time");
         *
         */
        open: function(view) {
            if (view !== "time") {
                view = "date";
            }

            this[view + "View"].open();
        },

        /**
         *
         * Gets or sets the minimum value of the DateTimePicker.
         *
         * @param {Date|String} value
         * The minimum time value to set for a DateTimePicker, expressed as a Date object or as a string.
         *
         * @returns {Date}
         * The minimum time value of a DateTimePicker.
         *
         * @exampleTitle Get the minimum value of a DateTimePicker
         * @example
         * var dateTimePicker = $("#dateTimePicker").data("kendoDateTimePicker");
         * var minimum = dateTimePicker.min();
         *
         * @exampleTitle Set the minimum value of a DateTimePicker
         * @example
         * var dateTimePicker = $("#dateTimePicker").data("kendoDateTimePicker");
         * dateTimePicker.min(new Date(1900, 0, 1, 10, 0, 0));
         *
         */
        min: function(value) {
            return this._option("min", value);
        },

        /**
         *
         * Gets or sets the maximum value of the DateTimePicker.
         *
         * @param {Date|String} value
         * The maximum time value to set for a DateTimePicker, expressed as a Date object or as a string.
         *
         * @returns {Date}
         * The maximum time value of a DateTimePicker.
         *
         * @exampleTitle Get the maximum value of a DateTimePicker
         * @example
         * var dateTimePicker = $("#dateTimePicker").data("kendoDateTimePicker");
         * var maximum = dateTimePicker.max();
         *
         * @exampleTitle Set the maximum value of a DateTimePicker
         * @example
         * var dateTimePicker = $("#dateTimePicker").data("kendoDateTimePicker");
         * dateTimePicker.max(new Date(1900, 0, 1, 10, 0, 0));
         *
         */
        max: function(value) {
            return this._option("max", value);
        },

        /**
         *
         * Toggles the calendar or the time drop-down list.
         *
         * @param {String} view
         * The view of the DateTimePicker, expressed as a string.
         * Available views are "time" and "date".
         *
         * @exampleTitle Toggle the calendar of the DateTimePicker
         * @example
         * $("dateTimePicker").data("kendoDateTimePicker").toggle();
         *
         * @exampleTitle Toggle the calendar of the DateTimePicker
         * @example
         * $("dateTimePicker").data("kendoDateTimePicker").toggle("date");
         *
         * @exampleTitle Toggle the time drop-down list of a DateTimePicker.
         * @example
         * $("dateTimePicker").data("kendoDateTimePicker").toggle("time");
         *
         */
        toggle: function(view) {
            var secondView = "timeView";

            if (view !== "time") {
                view = "date";
            } else {
                secondView = "dateView";
            }

            this[view + "View"].toggle();
            this[secondView].close();
        },

        /**
         *
         * Gets or sets the value of the DateTimePicker.
         *
         * @param {Date|String} value
         * The time value to set for a DateTimePicker, expressed as a Date object or as a string.
         *
         * @returns {Date}
         * The time value of a DateTimePicker.
         *
         * @exampleTitle Get the value of a DateTimePicker
         * @example
         * var dateTimePicker = $("#dateTimePicker").data("kendoDateTimePicker");
         * var timePickerValue = dateTimePicker.value();
         *
         * @exampleTitle Set the value of a DateTimePicker
         * @example
         * var dateTimePicker = $("#dateTimePicker").data("kendoDateTimePicker");
         * dateTimePicker.value("2/23/2000 10:00 AM");
         *
         */
        value: function(value) {
            var that = this;

            if (value === undefined) {
                return that._value;
            }

            that._old = that._update(value);
        },

        _change: function(value) {
            var that = this;

            value = that._update(value);

            if (+that._old != +value) {
                that._old = value;
                that.trigger(CHANGE);

                // trigger the DOM change event so any subscriber gets notified
                that.element.trigger(CHANGE);
            }
        },

        _option: function(option, value) {
            var that = this,
                options = that.options,
                timeView = that.timeView,
                timeViewOptions = timeView.options,
                current = that._value || that._old;

            if (value === undefined) {
                return options[option];
            }

            value = parse(value, options.parseFormats, options.culture);

            if (!value) {
                return;
            }

            options[option] = new DATE(value);
            that.dateView[option](value);

            that._midnight = getMilliseconds(options.min) + getMilliseconds(options.max) === 0;

            if (current && isEqualDatePart(value, current)) {
                if (that._midnight && option == "max") {
                    timeViewOptions[option] = MAX;
                    timeView.dataBind([MAX]);
                    return;
                }

                timeViewOptions[option] = value;
            } else {
                timeViewOptions.max = MAX;
                timeViewOptions.min = MIN;
            }

            timeView.bind();
        },

        _toggleHover: function(e) {
            if (!touch) {
                $(e.currentTarget).toggleClass(HOVER, e.type === "mouseenter");
            }
        },

        _update: function(value) {
            var that = this,
                options = that.options,
                min = options.min,
                max = options.max,
                timeView = that.timeView,
                date = parse(value, options.parseFormats, options.culture),
                rebind, timeViewOptions, old, skip;

            if (+date === +that._value) {
                return date;
            }

            if (date !== null && isEqualDatePart(date, min)) {
                date = restrictValue(date, min, max);
            } else if (!isInRange(date, min, max)) {
                date = null;
            }

            that._value = date;
            timeView.value(date);
            that.dateView.value(date);

            if (date) {
                old = that._old;
                timeViewOptions = timeView.options;

                if (isEqualDatePart(date, min)) {
                    timeViewOptions.min = min;
                    timeViewOptions.max = MAX;
                    rebind = true;
                }

                if (isEqualDatePart(date, max)) {
                    if (that._midnight) {
                        timeView.dataBind([MAX]);
                        skip = true;
                    } else {
                        timeViewOptions.max = max;
                        if (!rebind) {
                            timeViewOptions.min = MIN;
                        }
                        rebind = true;
                    }
                }

                if (!skip && ((!old && rebind) || (old && !isEqualDatePart(old, date)))) {
                    if (!rebind) {
                        timeViewOptions.max = MAX;
                        timeViewOptions.min = MIN;
                    }

                    timeView.bind();
                }
            }

            that.element.val(date ? kendo.toString(date, options.format, options.culture) : value);

            return date;
        },

        _keydown: function(e) {
            var that = this,
                dateView = that.dateView,
                timeView = that.timeView,
                isDateViewVisible = dateView.popup.visible();

            if (e.altKey && e.keyCode === kendo.keys.DOWN) {
                that.toggle(isDateViewVisible ? "time" : "date");
            } else if (isDateViewVisible) {
                dateView.move(e);
            } else if (timeView.popup.visible()) {
                timeView.move(e);
            } else if (e.keyCode === kendo.keys.ENTER) {
                that._change(that.element.val());
            }
        },

        _views: function() {
            var that = this,
                options = that.options;

            that.dateView = new kendo.DateView(extend({}, options, {
                anchor: that.wrapper,
                change: function() {
                    // calendar is the current scope
                    var value = this.value(),
                        msValue = +value,
                        msMin = +options.min,
                        msMax = +options.max,
                        current;

                    if (msValue === msMin || msValue === msMax) {
                        current = new DATE(that._value);
                        current.setFullYear(value.getFullYear());
                        current.setMonth(value.getMonth());
                        current.setDate(value.getDate());

                        if (isInRange(current, msMin, msMax)) {
                            value = current;
                        }
                    }

                    that._change(value);
                    that.close("date");
                },
                close: function(e) {
                    if (that.trigger(CLOSE, dateViewParams)) {
                        e.preventDefault();
                    }
                },
                open:  function(e) {
                    if (that.trigger(OPEN, dateViewParams)) {
                        e.preventDefault();
                    }
                }
            }));

            that.timeView = new TimeView({
                anchor: that.wrapper,
                animation: options.animation,
                dates: options.dates,
                format: options.timeFormat,
                culture: options.culture,
                height: options.height,
                interval: options.interval,
                min: new DATE(MIN),
                max: new DATE(MAX),
                parseFormats: options.parseFormats,
                value: options.value,
                change: function(value, trigger) {
                    value = that.timeView._parse(value);

                    if (value < options.min) {
                        value = new DATE(options.min);
                        that.timeView.options.min = value;
                    } else if (value > options.max) {
                        value = new DATE(options.max);
                        that.timeView.options.max = value;
                    }

                    if (trigger) {
                        that._timeSelected = true;
                        that._change(value);
                    } else {
                        that.element.val(kendo.toString(value, options.format, options.culture));
                    }
                },
                close: function(e) {
                    if (that.trigger(CLOSE, timeViewParams)) {
                        e.preventDefault();
                    }
                },
                open:  function(e) {
                    if (that.trigger(OPEN, timeViewParams)) {
                        e.preventDefault();
                    }
                }
            });
        },

        _icons: function() {
            var that = this,
                element = that.element,
                icons;

            icons = element.next("span.k-select");

            if (!icons[0]) {
                icons = $('<span unselectable="on" class="k-select"><span unselectable="on" class="k-icon k-i-calendar">select</span><span unselectable="on" class="k-icon k-i-clock">select</span></span>').insertAfter(element);
                icons = icons.children();
            }

            that._dateIcon = icons.eq(0);
            that._timeIcon = icons.eq(1);
        },

        _wrapper: function() {
            var that = this,
            element = that.element,
            wrapper;

            wrapper = element.parents(".k-datetimepicker");

            if (!wrapper[0]) {
                wrapper = element.wrap(SPAN).parent().addClass("k-picker-wrap k-state-default");
                wrapper = wrapper.wrap(SPAN).parent();
            }

            wrapper[0].style.cssText = element[0].style.cssText;
            element.css({
                width: "100%",
                height: element[0].style.height
            });

            that.wrapper = wrapper.addClass("k-widget k-datetimepicker k-header");
            that._inputWrapper = $(wrapper[0].firstChild);
        }
    });

    function preventDefault(e) {
        e.preventDefault();
    }

    function normalize(options) {
        var patterns = kendo.getCulture(options.culture).calendars.standard.patterns;

        options.format = extractFormat(options.format || patterns.g);
        options.timeFormat = extractFormat(options.timeFormat || patterns.t);
        kendo.DateView.normalize(options);
        options.parseFormats.splice(1, 0, options.timeFormat);
    }

    ui.plugin(DateTimePicker);

})(jQuery);
;