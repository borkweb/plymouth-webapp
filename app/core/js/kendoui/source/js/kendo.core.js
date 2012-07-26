/*
* Kendo UI Web v2012.2.710 (http://kendoui.com)
* Copyright 2012 Telerik AD. All rights reserved.
*
* Kendo UI Web commercial licenses may be obtained at http://kendoui.com/web-license
* If you do not own a commercial license, this file shall be governed by the
* GNU General Public License (GPL) version 3.
* For GPL requirements, please review: http://www.gnu.org/copyleft/gpl.html
*/
;(function($, undefined) {
    /**
     * @name kendo
     * @namespace This object contains all code introduced by the Kendo project, plus helper functions that are used across all widgets.
     */
    var kendo = window.kendo = window.kendo || {},
        extend = $.extend,
        each = $.each,
        proxy = $.proxy,
        isArray = $.isArray,
        noop = $.noop,
        isFunction = $.isFunction,
        math = Math,
        Template,
        JSON = window.JSON || {},
        support = {},
        percentRegExp = /%/,
        formatRegExp = /\{(\d+)(:[^\}]+)?\}/g,
        boxShadowRegExp = /(\d+?)px\s*(\d+?)px\s*(\d+?)px\s*(\d+?)?/i,
        FUNCTION = "function",
        STRING = "string",
        NUMBER = "number",
        OBJECT = "object",
        NULL = "null",
        BOOLEAN = "boolean",
        UNDEFINED = "undefined",
        getterCache = {},
        setterCache = {},
        slice = [].slice,
        globalize = window.Globalize;

    function Class() {}

    Class.extend = function(proto) {
        var base = function() {},
            member,
            that = this,
            subclass = proto && proto.init ? proto.init : function () {
                that.apply(this, arguments);
            },
            fn;

        base.prototype = that.prototype;
        fn = subclass.fn = subclass.prototype = new base();

        for (member in proto) {
            if (typeof proto[member] === OBJECT && !(proto[member] instanceof Array) && proto[member] !== null) {
                // Merge object members
                fn[member] = extend(true, {}, base.prototype[member], proto[member]);
            } else {
                fn[member] = proto[member];
            }
        }

        fn.constructor = subclass;
        subclass.extend = that.extend;

        return subclass;
    };

    var Observable = Class.extend(/** @lends kendo.Observable.prototype */{
        /**
         * Creates an observable instance.
         * @constructs
         * @class Represents a class that can trigger events, along with methods that subscribe handlers to these events.
         */
        init: function() {
            this._events = {};
        },

        bind: function(eventName, handlers, one) {
            var that = this,
                idx,
                eventNames = typeof eventName === STRING ? [eventName] : eventName,
                length,
                original,
                handler,
                handlersIsFunction = typeof handlers === FUNCTION,
                events;

            for (idx = 0, length = eventNames.length; idx < length; idx++) {
                eventName = eventNames[idx];

                handler = handlersIsFunction ? handlers : handlers[eventName];

                if (handler) {
                    if (one) {
                        original = handler;
                        handler = function() {
                            that.unbind(eventName, handler);
                            original.apply(that, arguments);
                        };
                    }
                    events = that._events[eventName] = that._events[eventName] || [];
                    events.push(handler);
                }
            }

            return that;
        },

        one: function(eventNames, handlers) {
            return this.bind(eventNames, handlers, true);
        },

        first: function(eventName, handlers) {
            var that = this,
                idx,
                eventNames = typeof eventName === STRING ? [eventName] : eventName,
                length,
                handler,
                handlersIsFunction = typeof handlers === FUNCTION,
                events;

            for (idx = 0, length = eventNames.length; idx < length; idx++) {
                eventName = eventNames[idx];

                handler = handlersIsFunction ? handlers : handlers[eventName];

                if (handler) {
                    events = that._events[eventName] = that._events[eventName] || [];
                    events.unshift(handler);
                }
            }

            return that;
        },

        trigger: function(eventName, e) {
            var that = this,
                events = that._events[eventName],
                idx,
                length,
                isDefaultPrevented = false;

            if (events) {
                e = e || {};

                e.sender = that;

                e.preventDefault = function () {
                    isDefaultPrevented = true;
                };

                e.isDefaultPrevented = function() {
                    return isDefaultPrevented;
                };

                events = events.slice();

                //Do not cache the length of the events array as removing events attached through one will fail
                for (idx = 0, length = events.length; idx < length; idx++) {
                    events[idx].call(that, e);
                }
            }

            return isDefaultPrevented;
        },

        unbind: function(eventName, handler) {
            var that = this,
                events = that._events[eventName],
                idx,
                length;

            if (events) {
                if (handler) {
                    for (idx = 0, length = events.length; idx < length; idx++) {
                        if (events[idx] === handler) {
                            events.splice(idx, 1);
                        }
                    }
                } else {
                    that._events[eventName] = [];
                }
            }

            return that;
        }
    });

    /**
     * @name kendo.Template.Description
     *
     * @section
     * <p>
     *  Templates offer way of creating HTML chunks. Options such as HTML encoding and compilation for optimal
     *  performance are available.
     * </p>
     *
     * @exampleTitle Basic template
     * @example
     * var inlineTemplate = kendo.template("Hello, #= firstName # #= lastName #");
     * var inlineData = { firstName: "John", lastName: "Doe" };
     * $("#inline").html(inlineTemplate(inlineData));
     *
     * @exampleTitle Output:
     * @example
     * Hello, John Doe!
     *
     * @exampleTitle Encode HTML
     * @example
     * var encodingTemplate = kendo.template("HTML tags are encoded as follows: #:html#");
     * var encodingData = { html: "<strong>lorem ipsum</strong>" };
     * $("#encoding").html(encodingTemplate(encodingData));
     *
     * @exampleTitle Output:
     * @example
     * HTML tags are encoded as follows: <strong>lorem ipsum</strong>
     *
     * @exampleTitle Use javascript in templates
     * @example
     * var encodingTemplate = kendo.template("#if (foo) {# bar #}#");
     * var data = { foo: true};
     * $("#encoding").html(encodingTemplate(data)); // outputs bar
     *
     * @exampleTitle Escape sharp symbols in JavaScript strings
     * @example
     * var encodingTemplate = kendo.template("<a href='\\#'>Link</a>");
     *
     * @exampleTitle Escape sharp symbols in script templates
     * @example
     * <script type="text/x-kendo-template" id="template">
     *  <a href="\#">Link</a>
     * </script>
     *
     * <script>
     * var encodingTemplate = kendo.template($("#template").html());
     * </script>
     */

     function compilePart(part, stringPart) {
         if (stringPart) {
             return "'" +
                 part.split("'").join("\\'")
                     .split('\\"').join('\\\\\\"')
                     .replace(/\n/g, "\\n")
                     .replace(/\r/g, "\\r")
                     .replace(/\t/g, "\\t") + "'";
         } else {
             var first = part.charAt(0),
                 rest = part.substring(1);

             if (first === "=") {
                 return "+(" + rest + ")+";
             } else if (first === ":") {
                 return "+e(" + rest + ")+";
             } else {
                 return ";" + part + ";o+=";
             }
         }
     }

    var argumentNameRegExp = /^\w+/,
        encodeRegExp = /\$\{([^}]*)\}/g,
        escapedCurlyRegExp = /\\\}/g,
        curlyRegExp = /__CURLY__/g,
        escapedSharpRegExp = /\\#/g,
        sharpRegExp = /__SHARP__/g;

    /**
     * @name kendo.Template
     * @namespace
     */
    Template = /** @lends kendo.Template */ {
        paramName: "data", // name of the parameter of the generated template
        useWithBlock: true, // whether to wrap the template in a with() block
        /**
         * Renders a template for each item of the data.
         * @ignore
         * @name kendo.Template.render
         * @static
         * @function
         * @param {String} [template] The template that will be rendered
         * @param {Array} [data] Data items
         * @returns {String} The rendered template
         */
        render: function(template, data) {
            var idx,
                length,
                html = "";

            for (idx = 0, length = data.length; idx < length; idx++) {
                html += template(data[idx]);
            }

            return html;
        },
        /**
         * Compiles a template to a function that builds HTML. Useful when a template will be used several times.
         * @ignore
         * @name kendo.Template.compile
         * @static
         * @function
         * @param {String} [template] The template that will be compiled
         * @param {Object} [options] Compilation options
         * @returns {Function} The compiled template
         */
        compile: function(template, options) {
            var settings = extend({}, this, options),
                paramName = settings.paramName,
                argumentName = paramName.match(argumentNameRegExp)[0],
                useWithBlock = settings.useWithBlock,
                functionBody = "var o,e=kendo.htmlEncode;",
                parts,
                idx;

            if (isFunction(template)) {
                if (template.length === 2) {
                    //looks like jQuery.template
                    return function(d) {
                        return template($, { data: d }).join("");
                    };
                }
                return template;
            }

            functionBody += useWithBlock ? "with(" + paramName + "){" : "";

            functionBody += "o=";

            parts = template
                .replace(escapedCurlyRegExp, "__CURLY__")
                .replace(encodeRegExp, "#=e($1)#")
                .replace(curlyRegExp, "}")
                .replace(escapedSharpRegExp, "__SHARP__")
                .split("#");

            for (idx = 0; idx < parts.length; idx ++) {
                functionBody += compilePart(parts[idx], idx % 2 === 0);
            }

            functionBody += useWithBlock ? ";}" : ";";

            functionBody += "return o;";

            functionBody = functionBody.replace(sharpRegExp, "#");

            try {
                return new Function(argumentName, functionBody);
            } catch(e) {
                throw new Error(kendo.format("Invalid template:'{0}' Generated code:'{1}'", template, functionBody));
            }
        }
    };

function pad(number) {
    return number < 10 ? "0" + number : number;
}

    //JSON stringify
(function() {
    var escapable = /[\\\"\x00-\x1f\x7f-\x9f\u00ad\u0600-\u0604\u070f\u17b4\u17b5\u200c-\u200f\u2028-\u202f\u2060-\u206f\ufeff\ufff0-\uffff]/g,
        gap,
        indent,
        meta = {
            "\b": "\\b",
            "\t": "\\t",
            "\n": "\\n",
            "\f": "\\f",
            "\r": "\\r",
            "\"" : '\\"',
            "\\": "\\\\"
        },
        rep,
        toString = {}.toString;

    if (typeof Date.prototype.toJSON !== FUNCTION) {

        /** @ignore */
        Date.prototype.toJSON = function (key) {
            var that = this;

            return isFinite(that.valueOf()) ?
                that.getUTCFullYear()     + "-" +
                pad(that.getUTCMonth() + 1) + "-" +
                pad(that.getUTCDate())      + "T" +
                pad(that.getUTCHours())     + ":" +
                pad(that.getUTCMinutes())   + ":" +
                pad(that.getUTCSeconds())   + "Z" : null;
        };

        String.prototype.toJSON = Number.prototype.toJSON = /** @ignore */ Boolean.prototype.toJSON = function (key) {
            return this.valueOf();
        };
    }

    function quote(string) {
        escapable.lastIndex = 0;
        return escapable.test(string) ? "\"" + string.replace(escapable, function (a) {
            var c = meta[a];
            return typeof c === STRING ? c :
                "\\u" + ("0000" + a.charCodeAt(0).toString(16)).slice(-4);
        }) + "\"" : "\"" + string + "\"";
    }

    function str(key, holder) {
        var i,
            k,
            v,
            length,
            mind = gap,
            partial,
            value = holder[key],
            type;

        if (value && typeof value === OBJECT && typeof value.toJSON === FUNCTION) {
            value = value.toJSON(key);
        }

        if (typeof rep === FUNCTION) {
            value = rep.call(holder, key, value);
        }

        type = typeof value;
        if (type === STRING) {
            return quote(value);
        } else if (type === NUMBER) {
            return isFinite(value) ? String(value) : NULL;
        } else if (type === BOOLEAN || type === NULL) {
            return String(value);
        } else if (type === OBJECT) {
            if (!value) {
                return NULL;
            }
            gap += indent;
            partial = [];
            if (toString.apply(value) === "[object Array]") {
                length = value.length;
                for (i = 0; i < length; i++) {
                    partial[i] = str(i, value) || NULL;
                }
                v = partial.length === 0 ? "[]" : gap ?
                    "[\n" + gap + partial.join(",\n" + gap) + "\n" + mind + "]" :
                    "[" + partial.join(",") + "]";
                gap = mind;
                return v;
            }
            if (rep && typeof rep === OBJECT) {
                length = rep.length;
                for (i = 0; i < length; i++) {
                    if (typeof rep[i] === STRING) {
                        k = rep[i];
                        v = str(k, value);
                        if (v) {
                            partial.push(quote(k) + (gap ? ": " : ":") + v);
                        }
                    }
                }
            } else {
                for (k in value) {
                    if (Object.hasOwnProperty.call(value, k)) {
                        v = str(k, value);
                        if (v) {
                            partial.push(quote(k) + (gap ? ": " : ":") + v);
                        }
                    }
                }
            }

            v = partial.length === 0 ? "{}" : gap ?
                "{\n" + gap + partial.join(",\n" + gap) + "\n" + mind + "}" :
                "{" + partial.join(",") + "}";
            gap = mind;
            return v;
        }
    }

    if (typeof JSON.stringify !== FUNCTION) {
        JSON.stringify = function (value, replacer, space) {
            var i;
            gap = "";
            indent = "";

            if (typeof space === NUMBER) {
                for (i = 0; i < space; i += 1) {
                    indent += " ";
                }

            } else if (typeof space === STRING) {
                indent = space;
            }

            rep = replacer;
            if (replacer && typeof replacer !== FUNCTION && (typeof replacer !== OBJECT || typeof replacer.length !== NUMBER)) {
                throw new Error("JSON.stringify");
            }

            return str("", {"": value});
        };
    }
})();

// Date and Number formatting
(function() {
    var dateFormatRegExp = /dddd|ddd|dd|d|MMMM|MMM|MM|M|yyyy|yy|HH|H|hh|h|mm|m|fff|ff|f|tt|ss|s|"[^"]*"|'[^']*'/g,
        standardFormatRegExp =  /^(n|c|p|e)(\d*)$/i,
        literalRegExp = /["'].*?["']/g,
        commaRegExp = /\,/g,
        EMPTY = "",
        POINT = ".",
        COMMA = ",",
        SHARP = "#",
        ZERO = "0",
        PLACEHOLDER = "??",
        EN = "en-US";

    //cultures
    kendo.cultures = {"en-US" : {
        name: EN,
        numberFormat: {
            pattern: ["-n"],
            decimals: 2,
            ",": ",",
            ".": ".",
            groupSize: [3],
            percent: {
                pattern: ["-n %", "n %"],
                decimals: 2,
                ",": ",",
                ".": ".",
                groupSize: [3],
                symbol: "%"
            },
            currency: {
                pattern: ["($n)", "$n"],
                decimals: 2,
                ",": ",",
                ".": ".",
                groupSize: [3],
                symbol: "$"
            }
        },
        calendars: {
            standard: {
                days: {
                    names: ["Sunday", "Monday", "Tuesday", "Wednesday", "Thursday", "Friday", "Saturday"],
                    namesAbbr: ["Sun", "Mon", "Tue", "Wed", "Thu", "Fri", "Sat"],
                    namesShort: [ "Su", "Mo", "Tu", "We", "Th", "Fr", "Sa" ]
                },
                months: {
                    names: ["January", "February", "March", "April", "May", "June", "July", "August", "September", "October", "November", "December"],
                    namesAbbr: ["Jan", "Feb", "Mar", "Apr", "May", "Jun", "Jul", "Aug", "Sep", "Oct", "Nov", "Dec"]
                },
                AM: [ "AM", "am", "AM" ],
                PM: [ "PM", "pm", "PM" ],
                patterns: {
                    d: "M/d/yyyy",
                    D: "dddd, MMMM dd, yyyy",
                    F: "dddd, MMMM dd, yyyy h:mm:ss tt",
                    g: "M/d/yyyy h:mm tt",
                    G: "M/d/yyyy h:mm:ss tt",
                    m: "MMMM dd",
                    M: "MMMM dd",
                    s: "yyyy'-'MM'-'ddTHH':'mm':'ss",
                    t: "h:mm tt",
                    T: "h:mm:ss tt",
                    u: "yyyy'-'MM'-'dd HH':'mm':'ss'Z'",
                    y: "MMMM, yyyy",
                    Y: "MMMM, yyyy"
                },
                "/": "/",
                ":": ":",
                firstDay: 0
            }
        }
    }};

    /**
     * @name kendo.Globalization
     * @namespace
     */
     /**
     * @name kendo.Globalization.Description
     *
     * @section Globalization is the process of designing and developing an
     * application that works in multiple cultures. The culture defines specific information
     * for the number formats, week and month names, date and time formats and etc.
     *
     * @section Kendo exposes <strong><em>culture(cultureName)</em></strong> method which allows to select the culture
     * script corresponding to the "culture name". kendo.culture() method uses the passed culture name
     * to select a culture from the culture scripts that you have included and then sets the current culture. If there is no
     * corresponding culture then the method will try to find culture which is equal to the country part of the culture name.
     * If no culture is found the default one is used.
     *
     * <h3>Define current culture settings</h3>
     *
     * @exampleTitle Include culture scripts and select culture
     * @example
     *
     * <script src="jquery.js" ></script>
     * <script src="kendo.all.min.js"></script>
     * <script src="kendo.culture.en-GB.js"></script>
     * <script type="text/javascript">
     *    //set current culture to the "en-GB" culture script.
     *    kendo.culture("en-GB");
     * </script>
     *
     * @exampleTitle Select closest culture
     * @example
     *
     * <script src="jquery.js" ></script>
     * <script src="kendo.all.min.js"></script>
     * <script src="kendo.culture.fr.js"></script>
     * <script type="text/javascript">
     *    //set current culture to the "fr" culture script.
     *    kendo.culture("fr-FR");
     * </script>
     *
     * @exampleTitle Get current culture
     * @example
     * var cultureInfo = kendo.culture();
     *
     * <h3>Find culture object</h3>
     *
     * @section Kendo also exposes <strong><em>findCulture(cultureName)</em></strong> method which returns a culture object which corresponds to
     * the passed culture name. If there is no such culture in the registered culture scripts, the method will try to find a culture object
     * which corresponds to the country part of the culture name. If no culture is found, the result will be <strong>null</strong>.
     *
     * @exampleTitle Find a culture object
     * @example
     *
     * <script src="jquery.js" ></script>
     * <script src="kendo.all.min.js"></script>
     * <script src="kendo.culture.fr.js"></script>
     * <script type="text/javascript">
     *    //finds the "fr-FR" culture object.
     *    var culture = kendo.findCulture("fr-FR");
     * </script>
     *
     * @section
     * <h3>Format number or date object</h3>
     *
     * Kendo exposes methods which can format number or date object using specific format string and the current specified culture:
     * @section
     * <h4><code>kendo.toString(object, format)</code> - returns a string representation of the current object using specific format.</h4>
     * @exampleTitle Formats number and date objects
     * @example
     * //format number using standard number format
     * kendo.toString(10.12, "n"); //10.12
     * kendo.toString(10.12, "n0"); //10
     * kendo.toString(10.12, "n5"); //10.12000
     * kendo.toString(10.12, "c"); //$10.12
     * kendo.toString(0.12, "p"); //12.00 %
     * //format number using custom number format
     * kendo.toString(19.12, "00##"); //0019
     * //format date
     * kendo.toString(new Date(2010, 9, 5), "yyyy/MM/dd" ); // "2010/10/05"
     * kendo.toString(new Date(2010, 9, 5), "dddd MMMM d, yyyy" ); // "Tuesday October 5, 2010"
     * kendo.toString(new Date(2010, 10, 10, 22, 12), "hh:mm tt" ); // "10:12 PM"
     *
     * @section
     * <h4>kendo.format - replaces each format item in a specified string with the text equivalent of a corresponding object's value.</h4>
     *  @exampleTitle String format
     *  @example
     *  kendo.format("{0} - {1}", 12, 24); //12 - 24
     *  kendo.format("{0:c} - {1:c}", 12, 24); //$12.00 - $24.00
     *
     * @section
     * <h3>Parsing a string</h3>
     *
     * Kendo exposes methods which converts the specified string to date or number object:
     * <ol>
     *    <li>
     *       <code>kendo.parseInt(string, [culture])</code> - converts a string to a whole number using the specified culture (current culture by default).
     *        @exampleTitle Parse string to integer
     *        @example
     *
     *        //assumes that current culture defines decimal separator as "."
     *        kendo.parseInt("12.22"); //12
     *
     *        //assumes that current culture defines decimal separator as ",", group separator as "." and currency symbol as "€"
     *        kendo.parseInt("1.212,22 €"); //1212
     *    </li>
     *    <li>
     *       <code>kendo.parseFloat(string, [culture])</code> - converts a string to a number with floating point using the specified culture (current culture by default).
     *        @exampleTitle Parse string to float
     *        @example
     *
     *        //assumes that current culture defines decimal separator as "."
     *        kendo.parseFloat("12.22"); //12.22
     *
     *        //assumes that current culture defines decimal separator as ",", group separator as "." and currency symbol as "€"
     *        kendo.parseFloat("1.212,22 €"); //1212.22
     *    </li>
     *    <li>
     *       <code>kendo.parseDate(string, [formats], [culture])</code> - converts a string to a JavaScript Date object, taking into account the given format/formats (or the given culture's set of default formats).
     *       Current culture is used if one is not specified.
     *        @exampleTitle Parse string to float
     *        @example
     *
     *        //current culture is "en-US"
     *        kendo.parseDate("12/22/2000"); //Fri Dec 22 2000
     *        kendo.parseDate("2000/12/22", "yyyy/MM/dd"); //Fri Dec 22 2000
     *    </li>
     * </ol>
     *
     * @section
     * <h3>Number formatting</h3>
     * The purpose of number formatting is to convert Number object to a human readable string using culture's specific settings. <code>kendo.format</code> and <code>kendo.toString</code>
     * methods support standard and custom numeric formats:
     * <h4>Standard numeric formats</h4>
     *<strong>n</strong> for number
     *       @exampleTitle Formatting using "n" format
     *       @example
     *       kendo.culture("en-US");
     *       kendo.toString(1234.567, "n"); //1,234.57
     *
     *       kendo.culture("de-DE");
     *       kendo.toString(1234.567, "n3"); //1.234,567
     *@section
     *<strong>c</strong> for currency
     *       @exampleTitle Formatting using "c" format
     *       @example
     *       kendo.culture("en-US");
     *       kendo.toString(1234.567, "c"); //$1,234.57
     *
     *       kendo.culture("de-DE");
     *       kendo.toString(1234.567, "c3"); //1.234,567 €
     *@section
     *<strong>p</strong> for percentage (number is multiplied by 100)
     *       @exampleTitle Formatting using "p" format
     *       @example
     *       kendo.culture("en-US");
     *       kendo.toString(0.222, "p"); //22.20 %
     *
     *       kendo.culture("de-DE");
     *       kendo.toString(0.22, "p3"); //22.000 %
     *@section
     *<strong>e</strong> for exponential
     *       @exampleTitle Formatting using "e" format
     *       @example
     *       kendo.toString(0.122, "e"); //1.22e-1
     *       kendo.toString(0.122, "e4"); //1.2200e-1
     *
     * @section
     * <h4>Custom numeric formats</h4>
     * You can create custom numeric format string using one or more custom numeric specifiers. Custom numeric format string is any tha is not a standard numeric format.
     * <div class="details-list">
     *   <h4 class="details-title">Format specifiers</h4>
     *   <dl>
     *     <dt>
     *       "0" - zero placeholder
     *     </dt>
     *     <dd>Replaces the zero with the corresponding digit if one is present; otherwise, zero appears in the result string - <code>kendo.toString(1234.5678, "00000") -> 01235</code></dd>
     *     <dt>
     *       "#" - digit placeholder
     *     </dt>
     *     <dd>Replaces the pound sign with the corresponding digit if one is present; otherwise, no digit appears in the result string - <code>kendo.toString(1234.5678, "#####") -> 1235</code></dd>
     *     <dt>
     *       "." - Decimal placeholder
     *     </dt>
     *     <dd>Determines the location of the decimal separator in the result string - <code>kendo.tostring(0.45678, "0.00") -> 0.46 </code>(en-us)</dd>
     *     <dt>
     *       "," - group separator placeholder
     *     </dt>
     *     <dd>Insert localized group separator between each group - <code>kendo.tostring(12345678, "##,#") -> 12,345,678</code>(en-us)</dd>
     *     <dt>
     *       "%" - percentage placeholder
     *     </dt>
     *     <dd>Multiplies a number by 100 and inserts a localized percentage symbol in the result string</dd>
     *     <dt>
     *       "e" - exponential notation
     *     </dt>
     *     <dd><code>kendo.toString(0.45678, "e0") -> 5e-1</code></dd>
     *     <dt>
     *       ";" - section separator
     *     </dt>
     *     <dd>Defines sections wih separate format strings for positive, negative, and zero numbers</dd>
     *     <dt>
     *       "string"/'string' - Literal string delimiter
     *     </dt>
     *     <dd>Indicates that the enclosed characters should be copied to the result string</dd>
     *   </dl>
     * </div>
     *
     * @section
     * <h3>Date formatting</h3>
     * The purpose of date formatting is to convert Date object to a human readable string using culture's specific settings. <code>kendo.format</code> and <code>kendo.toString</code>
     * methods support standard and custom date formats:
     * <h4>Standard date formats</h4>
     * <div class="details-list">
     *   <h4 class="details-title">Format specifiers</h4>
     *   <dl>
     *     <dt>
     *       "d" - short date pattern
     *     </dt>
     *     <dd><code>kendo.toString(new Date(2000, 10, 6), "d") -> 11/6/2000</code></dd>
     *     <dt>
     *       "D" - long date pattern
     *     </dt>
     *     <dd><code>kendo.toString(new Date(2000, 10, 6), "D") -> Monday, November 06, 2000</code></dd>
     *     <dt>
     *       "F" - Full date/time pattern
     *     </dt>
     *     <dd><code>kendo.toString(new Date(2000, 10, 6), "D") -> Monday, November 06, 2000 12:00:00 AM</code></dd>
     *     <dt>
     *       "g" - General date/time pattern (short time)
     *     </dt>
     *     <dd><code>kendo.toString(new Date(2000, 10, 6), "g") -> 11/6/2000 12:00 AM</code></dd>
     *     <dt>
     *       "G" - General date/time pattern (long time)
     *     </dt>
     *     <dd><code>kendo.toString(new Date(2000, 10, 6), "G") -> 11/6/2000 12:00:00 AM</code></dd>
     *     <dt>
     *       "M/m" - Month/day pattern
     *     </dt>
     *     <dd><code>kendo.toString(new Date(2000, 10, 6), "m") -> November 06</code></dd>
     *     <dt>
     *       "u" - Universal sortable date/time pattern
     *     </dt>
     *     <dd><code>kendo.toString(new Date(2000, 10, 6), "u") -> 2000-11-06 00:00:00Z</code></dd>
     *     <dt>
     *       "Y/y" - Year/month pattern
     *     </dt>
     *     <dd><code>kendo.toString(new Date(2000, 10, 6), "y") -> November, 2000</code></dd>
     *   </dl>
     * </div>
     *
     *@section
     * <h4>Custom date formats</h4>
     * <div class="details-list">
     *   <h4 class="details-title">Format specifiers</h4>
     *   <dl>
     *     <dt>
     *       "d"
     *     </dt>
     *     <dd>The day of the month, from 1 through 31</dd>
     *     <dt>
     *       "dd"
     *     </dt>
     *     <dd>The day of the month, from 01 through 31.</dd>
     *     <dt>
     *       "ddd"
     *     </dt>
     *     <dd>iThe abbreviated name of the day of the week</dd>
     *     <dt>
     *       "dddd"
     *     </dt>
     *     <dd>The full name of the day of the week</dd>
     *     <dt>
     *       "f"
     *     </dt>
     *     <dd>The tenths of a second in a date and time value</dd>
     *     <dt>
     *       "ff"
     *     </dt>
     *     <dd>The hundredths of a second in a date and time value</dd>
     *     <dt>
     *       "fff"
     *     </dt>
     *     <dd>The milliseconds in a date and time value</dd>
     *     <dt>
     *       "M"
     *     </dt>
     *     <dd>The month, from 1 through 12</dd>
     *     <dt>
     *       "MM"
     *     </dt>
     *     <dd>The month, from 01 through 12</dd>
     *     <dt>
     *       "MMM"
     *     </dt>
     *     <dd>The abbreviated name of the month</dd>
     *     <dt>
     *       "MMMM"
     *     </dt>
     *     <dd>The full name of the month</dd>
     *     <dt>
     *       "h"
     *     </dt>
     *     <dd>The hour, using a 12-hour clock from 1 to 12</dd>
     *     <dt>
     *       "hh"
     *     </dt>
     *     <dd>The hour, using a 12-hour clock from 01 to 12</dd>
     *     <dt>
     *       "H"
     *     </dt>
     *     <dd>The hour, using a 24-hour clock from 1 to 23</dd>
     *     <dt>
     *       "HH"
     *     </dt>
     *     <dd>The hour, using a 24-hour clock from 01 to 23</dd>
     *     <dt>
     *       "m"
     *     </dt>
     *     <dd>The minute, from 0 through 59</dd>
     *     <dt>
     *       "mm"
     *     </dt>
     *     <dd>The minute, from 00 through 59</dd>
     *     <dt>
     *       "s"
     *     </dt>
     *     <dd>The second, from 0 through 59</dd>
     *     <dt>
     *       "ss"
     *     </dt>
     *     <dd>The second, from 00 through 59</dd>
     *     <dt>
     *       "tt"
     *     </dt>
     *     <dd>The AM/PM designator</dd>
     *   </dl>
     * </div>
     *
     * @section
     * <p><h3>Widgets that depend on current culture are:</h3>
     *    <ul>
     *        <li> Calendar </li>
     *        <li> DatePicker </li>
     *        <li> TimePicker </li>
     *        <li> NumericTextBox </li>
     *    </ul>
     * </p>
     */

     function findCulture(culture) {
        if (culture) {
            if (culture.numberFormat) {
                return culture;
            }

            if (typeof culture === STRING) {
                var cultures = kendo.cultures;
                return cultures[culture] || cultures[culture.split("-")[0]] || null;
            }

            return null;
        }

        return null;
    }

    function getCulture(culture) {
        if (culture) {
            culture = findCulture(culture);
        }

        return culture || kendo.cultures.current;
    }

    kendo.culture = function(cultureName) {
        var cultures = kendo.cultures, culture;

        if (cultureName !== undefined) {
            culture = findCulture(cultureName) || cultures[EN];
            culture.calendar = culture.calendars.standard;
            cultures.current = culture;
        } else {
            return cultures.current;
        }
    };

    kendo.findCulture = findCulture;
    kendo.getCulture = getCulture;

    //set current culture to en-US.
    kendo.culture(EN);

    function formatDate(date, format, culture) {
        culture = getCulture(culture);

        var calendar = culture.calendars.standard,
            days = calendar.days,
            months = calendar.months;

        format = calendar.patterns[format] || format;

        return format.replace(dateFormatRegExp, function (match) {
            var result;

            if (match === "d") {
                result = date.getDate();
            } else if (match === "dd") {
                result = pad(date.getDate());
            } else if (match === "ddd") {
                result = days.namesAbbr[date.getDay()];
            } else if (match === "dddd") {
                result = days.names[date.getDay()];
            } else if (match === "M") {
                result = date.getMonth() + 1;
            } else if (match === "MM") {
                result = pad(date.getMonth() + 1);
            } else if (match === "MMM") {
                result = months.namesAbbr[date.getMonth()];
            } else if (match === "MMMM") {
                result = months.names[date.getMonth()];
            } else if (match === "yy") {
                result = pad(date.getFullYear() % 100);
            } else if (match === "yyyy") {
                result = date.getFullYear();
            } else if (match === "h" ) {
                result = date.getHours() % 12 || 12;
            } else if (match === "hh") {
                result = pad(date.getHours() % 12 || 12);
            } else if (match === "H") {
                result = date.getHours();
            } else if (match === "HH") {
                result = pad(date.getHours());
            } else if (match === "m") {
                result = date.getMinutes();
            } else if (match === "mm") {
                result = pad(date.getMinutes());
            } else if (match === "s") {
                result = date.getSeconds();
            } else if (match === "ss") {
                result = pad(date.getSeconds());
            } else if (match === "f") {
                result = math.floor(date.getMilliseconds() / 100);
            } else if (match === "ff") {
                result = math.floor(date.getMilliseconds() / 10);
            } else if (match === "fff") {
                result = date.getMilliseconds();
            } else if (match === "tt") {
                result = date.getHours() < 12 ? calendar.AM[0] : calendar.PM[0];
            }

            return result !== undefined ? result : match.slice(1, match.length - 1);
        });
    }

    //number formatting
    function formatNumber(number, format, culture) {
        culture = getCulture(culture);

        var numberFormat = culture.numberFormat,
            groupSize = numberFormat.groupSize[0],
            groupSeparator = numberFormat[COMMA],
            decimal = numberFormat[POINT],
            precision = numberFormat.decimals,
            pattern = numberFormat.pattern[0],
            literals = [],
            symbol,
            isCurrency, isPercent,
            customPrecision,
            formatAndPrecision,
            negative = number < 0,
            integer,
            fraction,
            integerLength,
            fractionLength,
            replacement = EMPTY,
            value = EMPTY,
            idx,
            length,
            ch,
            hasGroup,
            hasNegativeFormat,
            decimalIndex,
            sharpIndex,
            zeroIndex,
            startZeroIndex,
            start = -1,
            end;

        //return empty string if no number
        if (number === undefined) {
            return EMPTY;
        }

        if (!isFinite(number)) {
            return number;
        }

        //if no format then return number.toString() or number.toLocaleString() if culture.name is not defined
        if (!format) {
            return culture.name.length ? number.toLocaleString() : number.toString();
        }

        formatAndPrecision = standardFormatRegExp.exec(format);

        /* standard formatting */
        if (formatAndPrecision) {
            format = formatAndPrecision[1].toLowerCase();

            isCurrency = format === "c";
            isPercent = format === "p";

            if (isCurrency || isPercent) {
                //get specific number format information if format is currency or percent
                numberFormat = isCurrency ? numberFormat.currency : numberFormat.percent;
                groupSize = numberFormat.groupSize[0];
                groupSeparator = numberFormat[COMMA];
                decimal = numberFormat[POINT];
                precision = numberFormat.decimals;
                symbol = numberFormat.symbol;
                pattern = numberFormat.pattern[negative ? 0 : 1];
            }

            customPrecision = formatAndPrecision[2];

            if (customPrecision) {
                precision = +customPrecision;
            }

            //return number in exponential format
            if (format === "e") {
                return customPrecision ? number.toExponential(precision) : number.toExponential(); // toExponential() and toExponential(undefined) differ in FF #653438.
            }

            // multiply if format is percent
            if (isPercent) {
                number *= 100;
            }

            number = number.toFixed(precision);
            number = number.split(POINT);

            integer = number[0];
            fraction = number[1];

            //exclude "-" if number is negative.
            if (negative) {
                integer = integer.substring(1);
            }

            value = integer;
            integerLength = integer.length;

            //add group separator to the number if it is longer enough
            if (integerLength >= groupSize) {
                value = EMPTY;
                for (idx = 0; idx < integerLength; idx++) {
                    if (idx > 0 && (integerLength - idx) % groupSize === 0) {
                        value += groupSeparator;
                    }
                    value += integer.charAt(idx);
                }
            }

            if (fraction) {
                value += decimal + fraction;
            }

            if (format === "n" && !negative) {
                return value;
            }

            number = EMPTY;

            for (idx = 0, length = pattern.length; idx < length; idx++) {
                ch = pattern.charAt(idx);

                if (ch === "n") {
                    number += value;
                } else if (ch === "$" || ch === "%") {
                    number += symbol;
                } else {
                    number += ch;
                }
            }

            return number;
        }

        //custom formatting
        //
        //separate format by sections.

        //make number positive
        if (negative) {
            number = -number;
        }

        format = format.split(";");
        if (negative && format[1]) {
            //get negative format
            format = format[1];
            hasNegativeFormat = true;
        } else if (number === 0) {
            //format for zeros
            format = format[2] || format[0];
            if (format.indexOf(SHARP) == -1 && format.indexOf(ZERO) == -1) {
                //return format if it is string constant.
                return format;
            }
        } else {
            format = format[0];
        }

        if (format.indexOf("'") > -1 || format.indexOf("\"") > -1) {
            format = format.replace(literalRegExp, function(match) {
                literals.push(match);
                return PLACEHOLDER;
            });
        }

        isCurrency = format.indexOf("$") != -1;
        isPercent = format.indexOf("%") != -1;

        //multiply number if the format has percent
        if (isPercent) {
            number *= 100;
        }

        if (isCurrency || isPercent) {
            //get specific number format information if format is currency or percent
            numberFormat = isCurrency ? numberFormat.currency : numberFormat.percent;
            groupSize = numberFormat.groupSize[0];
            groupSeparator = numberFormat[COMMA];
            decimal = numberFormat[POINT];
            precision = numberFormat.decimals;
            symbol = numberFormat.symbol;
        }

        hasGroup = format.indexOf(COMMA) > -1;
        if (hasGroup) {
            format = format.replace(commaRegExp, EMPTY);
        }

        decimalIndex = format.indexOf(POINT);
        length = format.length;

        if (decimalIndex != -1) {
            zeroIndex = format.lastIndexOf(ZERO);
            sharpIndex = format.lastIndexOf(SHARP);
            fraction = number.toString().split(POINT)[1] || EMPTY;

            if (sharpIndex > zeroIndex && fraction.length > (sharpIndex - zeroIndex)) {
                idx = sharpIndex;
            } else if (zeroIndex != -1 && zeroIndex >= decimalIndex) {
                idx = zeroIndex;
            }

            if (idx) {
                number = number.toFixed(idx - decimalIndex);
            }

        } else {
            number = number.toFixed(0);
        }

        sharpIndex = format.indexOf(SHARP);
        startZeroIndex = zeroIndex = format.indexOf(ZERO);

        //define the index of the first digit placeholder
        if (sharpIndex == -1 && zeroIndex != -1) {
            start = zeroIndex;
        } else if (sharpIndex != -1 && zeroIndex == -1) {
            start = sharpIndex;
        } else {
            start = sharpIndex > zeroIndex ? zeroIndex : sharpIndex;
        }

        sharpIndex = format.lastIndexOf(SHARP);
        zeroIndex = format.lastIndexOf(ZERO);

        //define the index of the last digit placeholder
        if (sharpIndex == -1 && zeroIndex != -1) {
            end = zeroIndex;
        } else if (sharpIndex != -1 && zeroIndex == -1) {
            end = sharpIndex;
        } else {
            end = sharpIndex > zeroIndex ? sharpIndex : zeroIndex;
        }

        if (start == length) {
            end = start;
        }

        if (start != -1) {
            value = number.toString().split(POINT);
            integer = value[0];
            fraction = value[1] || EMPTY;

            integerLength = integer.length;
            fractionLength = fraction.length;

            //add group separator to the number if it is longer enough
            if (hasGroup) {
                if (integerLength === groupSize && integerLength < decimalIndex - startZeroIndex) {
                    integer = groupSeparator + integer;
                } else if (integerLength > groupSize) {
                    value = EMPTY;
                    for (idx = 0; idx < integerLength; idx++) {
                        if (idx > 0 && (integerLength - idx) % groupSize === 0) {
                            value += groupSeparator;
                        }
                        value += integer.charAt(idx);
                    }

                    integer = value;
                }
            }

            number = format.substring(0, start);

            if (negative && !hasNegativeFormat) {
                number += "-";
            }

            for (idx = start; idx < length; idx++) {
                ch = format.charAt(idx);

                if (decimalIndex == -1) {
                    if (end - idx < integerLength) {
                        number += integer;
                        break;
                    }
                } else {
                    if (zeroIndex != -1 && zeroIndex < idx) {
                        replacement = EMPTY;
                    }

                    if ((decimalIndex - idx) <= integerLength && decimalIndex - idx > -1) {
                        number += integer;
                        idx = decimalIndex;
                    }

                    if (decimalIndex === idx) {
                        number += (fraction ? decimal : EMPTY) + fraction;
                        idx += end - decimalIndex + 1;
                        continue;
                    }
                }

                if (ch === ZERO) {
                    number += ch;
                    replacement = ch;
                } else if (ch === SHARP) {
                    number += replacement;
                }
            }

            if (end >= start) {
                number += format.substring(end + 1);
            }

            //replace symbol placeholders
            if (isCurrency || isPercent) {
                value = EMPTY;
                for (idx = 0, length = number.length; idx < length; idx++) {
                    ch = number.charAt(idx);
                    value += (ch === "$" || ch === "%") ? symbol : ch;
                }
                number = value;
            }

            if (literals[0]) {
                length = literals.length;
                for (idx = 0; idx < length; idx++) {
                    number = number.replace(PLACEHOLDER, literals[idx]);
                }
            }
        }

        return number;
    }

    var toString = function(value, fmt, culture) {
        if (fmt) {
            if (value instanceof Date) {
                return formatDate(value, fmt, culture);
            } else if (typeof value === NUMBER) {
                return formatNumber(value, fmt, culture);
            }
        }

        return value !== undefined ? value : "";
    };

    if (globalize) {
        toString = proxy(globalize.format, globalize);
    }

    kendo.format = function(fmt) {
        var values = arguments;

        return fmt.replace(formatRegExp, function(match, index, placeholderFormat) {
            var value = values[parseInt(index, 10) + 1];

            return toString(value, placeholderFormat ? placeholderFormat.substring(1) : "");
        });
    };

    kendo._extractFormat = function (format) {
        if (format.slice(0,3) === "{0:") {
            format = format.slice(3, format.length - 1);
        }

        return format;
    };

    kendo.toString = toString;
    })();


(function() {
    var nonBreakingSpaceRegExp = /\u00A0/g,
        exponentRegExp = /[eE][\-+]?[0-9]+/,
        shortTimeZoneRegExp = /[+|\-]\d{1,2}/,
        longTimeZoneRegExp = /[+|\-]\d{1,2}:\d{2}/,
        formatsSequence = ["G", "g", "d", "F", "D", "y", "m", "T", "t"];

    function outOfRange(value, start, end) {
        return !(value >= start && value <= end);
    }

    function designatorPredicate(designator) {
        return designator.charAt(0);
    }

    function mapDesignators(designators) {
        return $.map(designators, designatorPredicate);
    }

    function parseExact(value, format, culture) {
        if (!value) {
            return null;
        }

        var lookAhead = function (match) {
                var i = 0;
                while (format[idx] === match) {
                    i++;
                    idx++;
                }
                if (i > 0) {
                    idx -= 1;
                }
                return i;
            },
            getNumber = function(size) {
                var rg = new RegExp('^\\d{1,' + size + '}'),
                    match = value.substr(valueIdx, size).match(rg);

                if (match) {
                    match = match[0];
                    valueIdx += match.length;
                    return parseInt(match, 10);
                }
                return null;
            },
            getIndexByName = function (names) {
                var i = 0,
                    length = names.length,
                    name, nameLength;

                for (; i < length; i++) {
                    name = names[i];
                    nameLength = name.length;

                    if (value.substr(valueIdx, nameLength) == name) {
                        valueIdx += nameLength;
                        return i + 1;
                    }
                }
                return null;
            },
            checkLiteral = function() {
                var result = false;
                if (value.charAt(valueIdx) === format[idx]) {
                    valueIdx++;
                    result = true;
                }
                return result;
            },
            calendar = culture.calendars.standard,
            year = null,
            month = null,
            day = null,
            hours = null,
            minutes = null,
            seconds = null,
            milliseconds = null,
            idx = 0,
            valueIdx = 0,
            literal = false,
            date = new Date(),
            shortYearCutOff = 30,
            defaultYear = date.getFullYear(),
            ch, count, length, pattern,
            pmHour, UTC, ISO8601, matches,
            amDesignators, pmDesignators,
            hoursOffset, minutesOffset;

        if (!format) {
            format = "d"; //shord date format
        }

        //if format is part of the patterns get real format
        pattern = calendar.patterns[format];
        if (pattern) {
            format = pattern;
        }

        format = format.split("");
        length = format.length;

        for (; idx < length; idx++) {
            ch = format[idx];

            if (literal) {
                if (ch === "'") {
                    literal = false;
                } else {
                    checkLiteral();
                }
            } else {
                if (ch === "d") {
                    count = lookAhead("d");
                    day = count < 3 ? getNumber(2) : getIndexByName(calendar.days[count == 3 ? "namesAbbr" : "names"]);

                    if (day === null || outOfRange(day, 1, 31)) {
                        return null;
                    }
                } else if (ch === "M") {
                    count = lookAhead("M");
                    month = count < 3 ? getNumber(2) : getIndexByName(calendar.months[count == 3 ? 'namesAbbr' : 'names']);

                    if (month === null || outOfRange(month, 1, 12)) {
                        return null;
                    }
                    month -= 1; //because month is zero based
                } else if (ch === "y") {
                    count = lookAhead("y");
                    year = getNumber(count < 3 ? 2 : 4);
                    if (year === null) {
                        year = defaultYear;
                    }
                    if (year < shortYearCutOff) {
                        year = (defaultYear - defaultYear % 100) + year;
                    }
                } else if (ch === "h" ) {
                    lookAhead("h");
                    hours = getNumber(2);
                    if (hours == 12) {
                        hours = 0;
                    }
                    if (hours === null || outOfRange(hours, 0, 11)) {
                        return null;
                    }
                } else if (ch === "H") {
                    lookAhead("H");
                    hours = getNumber(2);
                    if (hours === null || outOfRange(hours, 0, 23)) {
                        return null;
                    }
                } else if (ch === "m") {
                    lookAhead("m");
                    minutes = getNumber(2);
                    if (minutes === null || outOfRange(minutes, 0, 59)) {
                        return null;
                    }
                } else if (ch === "s") {
                    lookAhead("s");
                    seconds = getNumber(2);
                    if (seconds === null || outOfRange(seconds, 0, 59)) {
                        return null;
                    }
                } else if (ch === "f") {
                    count = lookAhead("f");
                    milliseconds = getNumber(count);
                    if (milliseconds === null || outOfRange(milliseconds, 0, 999)) {
                        return null;
                    }
                } else if (ch === "t") {
                    count = lookAhead("t");
                    amDesignators = calendar.AM;
                    pmDesignators = calendar.PM;

                    if (count === 1) {
                        amDesignators = mapDesignators(amDesignators);
                        pmDesignators = mapDesignators(pmDesignators);
                    }

                    pmHour = getIndexByName(pmDesignators);
                    if (!pmHour && !getIndexByName(amDesignators)) {
                        return null;
                    }
                }
                else if (ch === "z") {
                    UTC = true;
                    count = lookAhead("z");

                    if (value.substr(valueIdx, 1) === "Z") {
                        if (!ISO8601) {
                            return null;
                        }

                        checkLiteral();
                        continue;
                    }

                    matches = value.substr(valueIdx, 6)
                                   .match(count > 2 ? longTimeZoneRegExp : shortTimeZoneRegExp);

                    if (!matches) {
                        return null;
                    }

                    matches = matches[0];
                    valueIdx = matches.length;
                    matches = matches.split(":");

                    hoursOffset = parseInt(matches[0], 10);
                    if (outOfRange(hoursOffset, -12, 13)) {
                        return null;
                    }

                    if (count > 2) {
                        minutesOffset = parseInt(matches[1], 10);
                        if (isNaN(minutesOffset) || outOfRange(minutesOffset, 0, 59)) {
                            return null;
                        }
                    }
                } else if (ch === "T") {
                    ISO8601 = checkLiteral();
                } else if (ch === "'") {
                    literal = true;
                    checkLiteral();
                } else {
                    checkLiteral();
                }
            }
        }

        if (pmHour && hours < 12) {
            hours += 12;
        }

        if (day === null) {
            day = 1;
        }

        if (UTC) {
            if (hoursOffset) {
                hours += -hoursOffset;
            }

            if (minutesOffset) {
                minutes += -minutesOffset;
            }

            return new Date(Date.UTC(year, month, day, hours, minutes, seconds, milliseconds));
        }

        return new Date(year, month, day, hours, minutes, seconds, milliseconds);
    }

    kendo.parseDate = function(value, formats, culture) {
        if (value instanceof Date) {
            return value;
        }

        var idx = 0,
            date = null,
            length, patterns;

        culture = kendo.getCulture(culture);

        if (!formats) {
            formats = [];
            patterns = culture.calendar.patterns;
            length = formatsSequence.length;

            for (; idx < length; idx++) {
                formats[idx] = patterns[formatsSequence[idx]];
            }
            formats[idx] = "ddd MMM dd yyyy HH:mm:ss";
            formats[++idx] = "yyyy-MM-ddTHH:mm:ss.fffzzz";
            formats[++idx] = "yyyy-MM-ddTHH:mm:sszzz";
            formats[++idx] = "yyyy-MM-ddTHH:mmzzz";
            formats[++idx] = "yyyy-MM-ddTHH:mmzz";
            formats[++idx] = "yyyy-MM-dd";

            idx = 0;
        }

        formats = isArray(formats) ? formats: [formats];
        length = formats.length;

        for (; idx < length; idx++) {
            date = parseExact(value, formats[idx], culture);
            if (date) {
                return date;
            }
        }

        return date;
    };

    kendo.parseInt = function(value, culture) {
        var result = kendo.parseFloat(value, culture);
        if (result) {
            result = result | 0;
        }
        return result;
    };

    kendo.parseFloat = function(value, culture, format) {
        if (!value && value !== 0) {
           return null;
        }

        if (typeof value === NUMBER) {
           return value;
        }

        value = value.toString();
        culture = kendo.getCulture(culture);

        var number = culture.numberFormat,
            percent = number.percent,
            currency = number.currency,
            symbol = currency.symbol,
            percentSymbol = percent.symbol,
            negative = value.indexOf("-") > -1,
            parts, isPercent;

        //handle exponential number
        if (exponentRegExp.test(value)) {
            value = parseFloat(value);
            if (isNaN(value)) {
                value = null;
            }
            return value;
        }

        if (value.indexOf(symbol) > -1 || (format && format.toLowerCase().indexOf("c") > -1)) {
            number = currency;
            parts = number.pattern[0].replace("$", symbol).split("n");
            if (value.indexOf(parts[0]) > -1 && value.indexOf(parts[1]) > -1) {
                value = value.replace(parts[0], "").replace(parts[1], "");
                negative = true;
            }
        } else if (value.indexOf(percentSymbol) > -1) {
            isPercent = true;
            number = percent;
            symbol = percentSymbol;
        }

        value = value.replace("-", "")
                     .replace(symbol, "")
                     .replace(nonBreakingSpaceRegExp, " ")
                     .split(number[","].replace(nonBreakingSpaceRegExp, " ")).join("")
                     .replace(number["."], ".");

        value = parseFloat(value);

        if (isNaN(value)) {
            value = null;
        } else if (negative) {
            value *= -1;
        }

        if (value && isPercent) {
            value /= 100;
        }

        return value;
    };

    if (globalize) {
        kendo.parseDate = proxy(globalize.parseDate, globalize);
        kendo.parseFloat = proxy(globalize.parseFloat, globalize);
    }
})();

    function wrap(element) {
        var browser = $.browser, percentage;

        if (!element.parent().hasClass("k-animation-container")) {
            var shadow = element.css(kendo.support.transitions.css + "box-shadow") || element.css("box-shadow"),
                radius = shadow ? shadow.match(boxShadowRegExp) || [ 0, 0, 0, 0, 0 ] : [ 0, 0, 0, 0, 0 ],
                blur = math.max((+radius[3]), +(radius[4] || 0)),
                left = (-radius[1]) + blur,
                right = (+radius[1]) + blur,
                bottom = (+radius[2]) + blur,
                width = element[0].style.width,
                height = element[0].style.height,
                percentWidth = percentRegExp.test(width),
                percentHeight = percentRegExp.test(height);

            if (browser.opera) { // Box shadow can't be retrieved in Opera
                left = right = bottom = 5;
            }

            percentage = percentWidth || percentHeight;

            if (!percentWidth) { width = element.outerWidth(); }
            if (!percentHeight) { height = element.outerHeight(); }

            element.wrap(
                         $("<div/>")
                         .addClass("k-animation-container")
                         .css({
                             width: width,
                             height: height,
                             marginLeft: -left,
                             paddingLeft: left,
                             paddingRight: right,
                             paddingBottom: bottom
                         }));

            if (percentage) {
                element.css({
                    width: "100%",
                    height: "100%",
                    boxSizing: "border-box",
                    mozBoxSizing: "border-box",
                    webkitBoxSizing: "border-box"
                });
            }
        } else {
            var wrapper = element.parent(".k-animation-container"),
                wrapperStyle = wrapper[0].style;

            if (wrapper.is(":hidden")) {
                wrapper.show();
            }

            percentage = percentRegExp.test(wrapperStyle.width) || percentRegExp.test(wrapperStyle.height);

            if (!percentage) {
                wrapper.css({
                    width: element.outerWidth(),
                    height: element.outerHeight()
                });
            }
        }

        if (browser.msie && math.floor(browser.version) <= 7) {
            element.css({
                zoom: 1
            });
        }

        return element.parent();
    }

    function deepExtend(destination) {
        var i = 1,
            length = arguments.length;

        for (i = 1; i < length; i++) {
            deepExtendOne(destination, arguments[i]);
        }

        return destination;
    }

    function deepExtendOne(destination, source) {
        var property,
            propValue,
            propType,
            destProp;

        for (property in source) {
            propValue = source[property];
            propType = typeof propValue;
            if (propType === OBJECT && propValue !== null && propValue.constructor !== Array) {
                if (propValue instanceof Date) {
                    destination[property] = new Date(propValue.getTime());
                } else {
                    destProp = destination[property];
                    if (typeof (destProp) === OBJECT) {
                        destination[property] = destProp || {};
                    } else {
                        destination[property] = {};
                    }
                    deepExtendOne(destination[property], propValue);
                }
            } else if (propType !== UNDEFINED) {
                destination[property] = propValue;
            }
        }

        return destination;
    }

    /**
     * Contains results from feature detection.
     * @name kendo.support
     * @namespace Contains results from feature detection.
     */
    (function() {
        /**
         * Indicates the width of the browser scrollbar. A value of zero means that the browser does not show a visual representation of a scrollbar (i.e. mobile browsers).
         * @name kendo.support.scrollbar
         * @property {Boolean}
         */
        support.scrollbar = function() {
            var div = document.createElement("div"),
                result;

            div.style.cssText = "overflow:scroll;overflow-x:hidden;zoom:1;clear:both";
            div.innerHTML = "&nbsp;";
            document.body.appendChild(div);

            result = div.offsetWidth - div.scrollWidth;

            document.body.removeChild(div);
            return result;
        };

        var table = document.createElement("table");

        // Internet Explorer does not support setting the innerHTML of TBODY and TABLE elements
        try {
            table.innerHTML = "<tr><td></td></tr>";

            /**
             * Indicates whether the browser supports setting of the <tbody> innerHtml.
             * @name kendo.support.tbodyInnerHtml
             * @property {Boolean}
             */
            support.tbodyInnerHtml = true;
        } catch (e) {
            support.tbodyInnerHtml = false;
        }

        /**
         * Indicates whether the browser supports touch events.
         * @name kendo.support.touch
         * @property {Boolean}
         */
        support.touch = "ontouchstart" in window;
        support.pointers = navigator.msPointerEnabled;

        /**
         * Indicates whether the browser supports CSS transitions.
         * @name kendo.support.transitions
         * @property {Boolean}
         */
        var transitions = support.transitions = false;
        var transforms = support.transforms = false;

        /**
         * Indicates whether the browser supports hardware 3d transitions.
         * @name kendo.support.hasHW3D
         * @property {Boolean}
         */
        support.hasHW3D = ("WebKitCSSMatrix" in window && "m11" in new window.WebKitCSSMatrix()) || "MozPerspective" in document.documentElement.style || "msPerspective" in document.documentElement.style;
        support.hasNativeScrolling = typeof document.documentElement.style.webkitOverflowScrolling == "string";

        each([ "Moz", "webkit", "O", "ms" ], function () {
            var prefix = this.toString(),
                hasTransitions = typeof table.style[prefix + "Transition"] === STRING;

            if (hasTransitions || typeof table.style[prefix + "Transform"] === STRING) {
                var lowPrefix = prefix.toLowerCase();

                transforms = {
                    css: "-" + lowPrefix + "-",
                    prefix: prefix,
                    event: (lowPrefix === "o" || lowPrefix === "webkit") ? lowPrefix : ""
                };

                if (hasTransitions) {
                    transitions = transforms;
                    transitions.event = transitions.event ? transitions.event + "TransitionEnd" : "transitionend";
                }

                return false;
            }
        });

        support.transforms = transforms;
        support.transitions = transitions;

        /**
         * Indicates the browser device pixel ratio.
         * @name kendo.support.devicePixelRatio
         * @property {Float}
         */
        support.devicePixelRatio = window.devicePixelRatio === undefined ? 1 : window.devicePixelRatio;

        support.detectOS = function (ua) {
            var os = false, minorVersion, match = [],
                agentRxs = {
                    fire: /(Silk)\/(\d+)\.(\d+(\.\d+)?)/,
                    android: /(Android|Android.*(?:Opera|Firefox).*?\/)\s*(\d+)\.(\d+(\.\d+)?)/,
                    iphone: /(iPhone|iPod).*OS\s+(\d+)[\._]([\d\._]+)/,
                    ipad: /(iPad).*OS\s+(\d+)[\._]([\d_]+)/,
                    meego: /(MeeGo).+NokiaBrowser\/(\d+)\.([\d\._]+)/,
                    webos: /(webOS)\/(\d+)\.(\d+(\.\d+)?)/,
                    blackberry: /(BlackBerry).*?Version\/(\d+)\.(\d+(\.\d+)?)/,
                    playbook: /(PlayBook).*?Tablet\s*OS\s*(\d+)\.(\d+(\.\d+)?)/,
                    winphone: /(IEMobile)\/(\d+)\.(\d+(\.\d+)?)/,
                    windows: /(MSIE)\s+(\d+)\.(\d+(\.\d+)?)/
                },
                osRxs = {
                    ios: /^i(phone|pad|pod)$/i,
                    android: /^android|fire$/i,
                    blackberry: /^blackberry|playbook/i,
                    windows: /windows|winphone/
                },
                formFactorRxs = {
                    tablet: /playbook|ipad|fire/i
                },
                browserRxs = {
                    omini: /Opera\sMini/i,
                    omobile: /Opera\sMobi/i,
                    firefox: /Firefox|Fennec/i,
                    mobilesafari: /version\/.*safari/i,
                    webkit: /webkit/i,
                    ie: /MSIE|Windows\sPhone/i
                },
                testRx = function (agent, rxs, dflt) {
                    for (var rx in rxs) {
                        if (rxs.hasOwnProperty(rx) && rxs[rx].test(agent)) {
                            return rx;
                        }
                    }
                    return dflt !== undefined ? dflt : agent;
                };

            for (var agent in agentRxs) {
                if (agentRxs.hasOwnProperty(agent)) {
                    match = ua.match(agentRxs[agent]);
                    if (match) {
                        if (agent == "windows" && "plugins" in navigator) { return false; } // Break if not Metro/Mobile Windows

                        os = {};
                        os.device = agent;
                        os.tablet = testRx(agent, formFactorRxs, false);
                        os.browser = testRx(ua, browserRxs, "default");
                        os.name = testRx(agent, osRxs);
                        os[os.name] = true;
                        os.majorVersion = match[2];
                        os.minorVersion = match[3].replace("_", ".");
                        minorVersion = os.minorVersion.replace(".", "").substr(0, 2);
                        os.flatVersion = os.majorVersion + minorVersion + (new Array(3 - (minorVersion.length < 3 ? minorVersion.length : 2)).join("0"));
                        os.appMode = window.navigator.standalone || (/file|local/).test(window.location.protocol) || typeof window.PhoneGap !== UNDEFINED || typeof window.cordova !== UNDEFINED; // Use file protocol to detect appModes.

                        if (os.android && support.devicePixelRatio < 1.5 && (window.outerWidth > 800 || window.outerHeight > 800)) {
                            os.tablet = agent;
                        }

                        break;
                    }
                }
            }
            return os;
        };

        /**
         * Parses the mobile OS type and version from the browser user agent.
         * @name kendo.support.mobileOS
         */
        support.mobileOS = support.detectOS(navigator.userAgent);

        support.zoomLevel = function() {
            return support.touch ? (document.documentElement.clientWidth / window.innerWidth) : 1;
        };

        /**
         * Indicates the browser support for event capturing
         * @name kendo.support.eventCapture
         * @property {Boolean}
         */
        support.eventCapture = document.documentElement.addEventListener;

        /**
         * Indicates whether the browser supports input placeholder.
         * @name kendo.support.placeholder
         * @property {Boolean}
         */
        support.placeholder = "placeholder" in document.createElement("input");
        support.stableSort = (function() {
            var sorted = [0,1,2,3,4,5,6,7,8,9,10,11,12].sort(function() { return 0; } );
            return sorted[0] === 0 && sorted[1] === 1 && sorted[2] === 2 && sorted[3] === 3 && sorted[4] === 4 &&
                sorted[5] === 5 && sorted[6] === 6 && sorted[7] === 7 && sorted[8] === 8 &&
                sorted[9] === 9 && sorted[10] === 10 && sorted[11] === 11 && sorted[12] === 12;
        })();

    })();

    /**
     * Exposed by jQuery.
     * @ignore
     * @name jQuery.fn
     * @namespace Handy jQuery plug-ins that are used by all Kendo widgets.
     */

    function size(obj) {
        var result = 0, key;
        for (key in obj) {
            if (obj.hasOwnProperty(key) && key != "toJSON") { // Ignore fake IE7 toJSON.
                result++;
            }
        }

        return result;
    }

    function isNodeEmpty(element) {
        return $.trim($(element).contents().filter(function () { return this.nodeType != 8; }).html()) === "";
    }

    function getOffset(element, type) {
        if (!type) {
            type = "offset";
        }

        var result = element[type](),
            mobileOS = support.mobileOS;

        if (support.touch && mobileOS.ios && mobileOS.flatVersion < 410) { // Extra processing only in broken iOS'
            var offset = type == "offset" ? result : element.offset(),
                positioned = (result.left == offset.left && result.top == offset.top);

            if (positioned) {
                return {
                    top: result.top - window.scrollY,
                    left: result.left - window.scrollX
                };
            }
        }

        return result;
    }

    var directions = {
        left: { reverse: "right" },
        right: { reverse: "left" },
        down: { reverse: "up" },
        up: { reverse: "down" },
        top: { reverse: "bottom" },
        bottom: { reverse: "top" },
        "in": { reverse: "out" },
        out: { reverse: "in" }
    };

    function parseEffects(input) {
        var effects = {};

        each((typeof input === "string" ? input.split(" ") : input), function(idx) {
            effects[idx] = this;
        });

        return effects;
    }

    var fx = {
        promise: function (element, options) {
            if (options.show) {
                element.css({ display: element.data("olddisplay") || "block" }).css("display");
            }

            if (options.hide) {
                element.data("olddisplay", element.css("display")).hide();
            }

            if (options.init) {
                options.init();
            }

            if (options.completeCallback) {
                options.completeCallback(element); // call the external complete callback with the element
            }

            element.dequeue();
        },

        transitionPromise: function(element, destination, options) {
            var container = kendo.wrap(element);
            container.append(destination);

            element.hide();
            destination.show();

            if (options.completeCallback) {
                options.completeCallback(element); // call the external complete callback with the element
            }

            return element;
        }
    };

    function prepareAnimationOptions(options, duration, reverse, complete) {
        if (typeof options === STRING) {
            // options is the list of effect names separated by space e.g. animate(element, "fadeIn slideDown")

            // only callback is provided e.g. animate(element, options, function() {});
            if (isFunction(duration)) {
                complete = duration;
                duration = 400;
                reverse = false;
            }

            if (isFunction(reverse)) {
                complete = reverse;
                reverse = false;
            }

            if (typeof duration === BOOLEAN){
                reverse = duration;
                duration = 400;
            }

            options = {
                effects: options,
                duration: duration,
                reverse: reverse,
                complete: complete
            };
        }

        return extend({
            //default options
            effects: {},
            duration: 400, //jQuery default duration
            reverse: false,
            init: noop,
            teardown: noop,
            hide: false,
            show: false
        }, options, { completeCallback: options.complete, complete: noop }); // Move external complete callback, so deferred.resolve can be always executed.

    }

    function animate(element, options, duration, reverse, complete) {
        element.each(function (idx, el) { // fire separate queues on every element to separate the callback elements
            el = $(el);
            el.queue(function () {
                fx.promise(el, prepareAnimationOptions(options, duration, reverse, complete));
            });
        });

        return element;
    }

    function animateTo(element, destination, options, duration, reverse, complete) {
        return fx.transitionPromise(element, destination, prepareAnimationOptions(options, duration, reverse, complete));
    }

    extend($.fn, /** @lends jQuery.fn */{
        kendoStop: function(clearQueue, gotoEnd) {
            return this.stop(clearQueue, gotoEnd);
        },

        kendoAnimate: function(options, duration, reverse, complete) {
            return animate(this, options, duration, reverse, complete);
        },

        kendoAnimateTo: function(destination, options, duration, reverse, complete) {
            return animateTo(this, destination, options, duration, reverse, complete);
        }
    });

    function toggleClass(element, classes, options, add) {
        if (classes) {
            classes = classes.split(" ");

            each(classes, function(idx, value) {
                element.toggleClass(value, add);
            });
        }

        return element;
    }

    extend($.fn, /** @lends jQuery.fn */{
        kendoAddClass: function(classes, options){
            return toggleClass(this, classes, options, true);
        },
        kendoRemoveClass: function(classes, options){
            return toggleClass(this, classes, options, false);
        },
        kendoToggleClass: function(classes, options, toggle){
            return toggleClass(this, classes, options, toggle);
        }
    });

    var ampRegExp = /&/g,
        ltRegExp = /</g,
        gtRegExp = />/g;
    /**
     * Encodes HTML characters to entities.
     * @name kendo.htmlEncode
     * @function
     * @param {String} value The string that needs to be HTML encoded.
     * @returns {String} The encoded string.
     */
    function htmlEncode(value) {
        return ("" + value).replace(ampRegExp, "&amp;").replace(ltRegExp, "&lt;").replace(gtRegExp, "&gt;");
    }

    var touchLocation = function(e) {
        var originalEvent = typeof e.pageX == UNDEFINED ? e.originalEvent : e;
        return {
            idx: support.pointers ? originalEvent.pointerId : 0,
            x: originalEvent.pageX,
            y: originalEvent.pageY
        };
    };

    var eventTarget = function (e) {
        return e.target;
    };

    if (support.touch) {
        /** @ignore */
        touchLocation = function(e, id) {
            var changedTouches = e.changedTouches || e.originalEvent.changedTouches;

            if (id) {
                var output = null;
                each(changedTouches, function(idx, value) {
                    if (id == value.identifier) {
                        output = {
                            idx: value.identifier,
                            x: value.pageX,
                            y: value.pageY
                        };
                    }
                });
                return output;
            } else {
                return {
                    idx: changedTouches[0].identifier,
                    x: changedTouches[0].pageX,
                    y: changedTouches[0].pageY
                };
            }
        };

        eventTarget = function(e) {
            var touches = "originalEvent" in e ? e.originalEvent.changedTouches : "changedTouches" in e ? e.changedTouches : null;

            return touches ? document.elementFromPoint(touches[0].clientX, touches[0].clientY) : null;
        };

        each(["swipe", "swipeLeft", "swipeRight", "swipeUp", "swipeDown", "doubleTap", "tap"], function(m, value) {
            $.fn[value] = function(callback) {
                return this.bind(value, callback);
            };
        });
    }

    if (support.touch) {
        support.mousedown = "touchstart";
        support.mouseup = "touchend";
        support.mousemove = "touchmove";
        support.mousecancel = "touchcancel";
        support.resize = "orientationchange";
    } else if (support.pointers) {
        support.mousemove = "MSPointerMove";
        support.mousedown = "MSPointerDown";
        support.mouseup = "MSPointerUp";
        support.mousecancel = "MSPointerCancel";
        support.resize = "orientationchange resize";
    } else {
        support.mousemove = "mousemove";
        support.mousedown = "mousedown";
        support.mouseup = "mouseup";
        support.mousecancel = "mouseleave";
        support.resize = "resize";
    }


    var wrapExpression = function(members) {
        var result = "d",
            index,
            idx,
            length,
            member,
            count = 1;

        for (idx = 0, length = members.length; idx < length; idx++) {
            member = members[idx];
            if (member !== "") {
                index = member.indexOf("[");

                if (index !== 0) {
                    if (index == -1) {
                        member = "." + member;
                    } else {
                        count++;
                        member = "." + member.substring(0, index) + " || {})" + member.substring(index);
                    }
                }

                count++;
                result += member + ((idx < length - 1) ? " || {})" : ")");
            }
        }
        return new Array(count).join("(") + result;
    },
    localUrlRe = /^([a-z]+:)?\/\//i;

    extend(kendo, /** @lends kendo */ {
        /**
         * @name kendo.ui
         * @namespace Contains all classes for the Kendo UI widgets.
         */
        ui: kendo.ui || {},
        fx: kendo.fx || fx,
        data: kendo.data || {},
        mobile: kendo.mobile || {},
        dataviz: kendo.dataviz || {ui: {}},
        keys: {
            INSERT: 45,
            DELETE: 46,
            BACKSPACE: 8,
            TAB: 9,
            ENTER: 13,
            ESC: 27,
            LEFT: 37,
            UP: 38,
            RIGHT: 39,
            DOWN: 40,
            END: 35,
            HOME: 36,
            SPACEBAR: 32,
            PAGEUP: 33,
            PAGEDOWN: 34,
            F2: 113,
            F10: 121,
            F12: 123
        },
        support: support,
        animate: animate,
        ns: "",
        attr: function(value) {
            return "data-" + kendo.ns + value;
        },
        wrap: wrap,
        deepExtend: deepExtend,
        size: size,
        isNodeEmpty: isNodeEmpty,
        getOffset: getOffset,
        parseEffects: parseEffects,
        toggleClass: toggleClass,
        directions: directions,
        Observable: Observable,
        Class: Class,
        Template: Template,
        /**
         * Shorthand for {@link kendo.Template.compile}.
         * @name kendo.template
         * @function
         */
        template: proxy(Template.compile, Template),
        /**
         * Shorthand for {@link kendo.Template.render}.
         * @name kendo.render
         * @function
         */
        render: proxy(Template.render, Template),
        stringify: proxy(JSON.stringify, JSON),
        touchLocation: touchLocation,
        eventTarget: eventTarget,
        htmlEncode: htmlEncode,
        isLocalUrl: function(url) {
            return url && !localUrlRe.test(url);
        },
        /** @ignore */
        expr: function(expression, safe) {
            expression = expression || "";

            if (expression && expression.charAt(0) !== "[") {
                expression = "." + expression;
            }

            if (safe) {
                expression =  wrapExpression(expression.split("."));
            } else {
                expression = "d" + expression;
            }

            return expression;
        },
        /** @ignore */
        getter: function(expression, safe) {
            return getterCache[expression] = getterCache[expression] || new Function("d", "return " + kendo.expr(expression, safe));
        },
        /** @ignore */
        setter: function(expression) {
            return setterCache[expression] = setterCache[expression] || new Function("d,value", "d." + expression + "=value");
        },
        /** @ignore */
        accessor: function(expression) {
            return {
                get: kendo.getter(expression),
                set: kendo.setter(expression)
            };
        },
        /** @ignore */
        guid: function() {
            var id = "", i, random;

            for (i = 0; i < 32; i++) {
                random = math.random() * 16 | 0;

                if (i == 8 || i == 12 || i == 16 || i == 20) {
                    id += "-";
                }
                id += (i == 12 ? 4 : (i == 16 ? (random & 3 | 8) : random)).toString(16);
            }

            return id;
        },

        roleSelector: function(role) {
            return role.replace(/(\S+)/g, "[" + kendo.attr("role") + "=$1],").slice(0, -1);
        },

        /** @ignore */
        logToConsole: function(message) {
            var console = window.console;

            if (typeof(console) != "undefined" && console.log) {
                console.log(message);
            }
        }
    });

    var Widget = Observable.extend( /** @lends kendo.ui.Widget.prototype */ {
        /**
         * Initializes widget. Sets `element` and `options` properties.
         * @constructs
         * @class Represents a UI widget. Base class for all Kendo widgets
         * @extends kendo.Observable
         */
        init: function(element, options) {
            var that = this;

            that.element = $(element);

            Observable.fn.init.call(that);

            that.options = extend(true, {}, that.options, options);

            if (!that.element.attr(kendo.attr("role"))) {
                that.element.attr(kendo.attr("role"), (that.options.name || "").toLowerCase());
            }

            that.element.data("kendo" + that.options.prefix + that.options.name, that);

            that.bind(that.events, that.options);
        },

        events: [],

        options: {
            prefix: ""
        },

        setOptions: function(options) {
            $.extend(this.options, options);

            this.bind(this.events, options);
        }
    });

    kendo.notify = noop;

    var templateRegExp = /template$/i,
        jsonRegExp = /^\s*(?:\{(?:.|\n)*\}|\[(?:.|\n)*\])\s*$/,
        jsonFormatRegExp = /^\{(\d+)(:[^\}]+)?\}/,
        dashRegExp = /([A-Z])/g;

    function parseOption(element, option) {
        var value;

        if (option.indexOf("data") === 0) {
            option = option.substring(4);
            option = option.charAt(0).toLowerCase() + option.substring(1);
        }

        option = option.replace(dashRegExp, "-$1");
        value = element.getAttribute("data-" + kendo.ns + option);

        if (value === null) {
            value = undefined;
        } else if (value === "null") {
            value = null;
        } else if (value === "true") {
            value = true;
        } else if (value === "false") {
            value = false;
        } else if (!isNaN(parseFloat(value))) {
            value = parseFloat(value);
        } else if (jsonRegExp.test(value) && !jsonFormatRegExp.test(value)) {
            value = $.parseJSON(value);
        }

        return value;
    }

    function parseOptions(element, options) {
        var result = {},
            option,
            value;

        for (option in options) {
            value = parseOption(element, option);

            if (value !== undefined) {

                if (templateRegExp.test(option)) {
                    value = kendo.template($("#" + value).html());
                }

                result[option] = value;
            }
        }

        return result;
    }

    kendo.initWidget = function(element, options, roles) {
        var result,
            option,
            widget,
            idx,
            length,
            role,
            value,
            dataSource;

        // Preserve backwards compatibility with (element, options, namespace) signature, where namespace was kendo.ui
        if (!roles) {
            roles = kendo.ui.roles;
        } else if (roles.roles) {
            roles = roles.roles;
        }

        element = element.nodeType ? element : element[0];

        role = element.getAttribute("data-" + kendo.ns + "role");

        if (!role) {
            return;
        }

        widget = roles[role];

        if (!widget) {
            return;
        }

        dataSource = parseOption(element, "dataSource");

        options = $.extend({}, parseOptions(element, widget.fn.options), options);

        if (dataSource) {
            if (typeof dataSource === STRING) {
                options.dataSource = kendo.getter(dataSource)(window);
            } else {
                options.dataSource = dataSource;
            }
        }

        for (idx = 0, length = widget.fn.events.length; idx < length; idx++) {
            option = widget.fn.events[idx];

            value = parseOption(element, option);

            if (value !== undefined) {
                options[option] = kendo.getter(value)(window);
            }
        }

        result = $(element).data("kendo" + widget.fn.options.name);

        if (!result) {
            result = new widget(element, options);
        } else {
            result.setOptions(options);
        }

        return result;
    };

    kendo.init = function(element) {
        var namespaces = slice.call(arguments, 1),
            roles;

        if (!namespaces[0]) {
            namespaces = [kendo.ui, kendo.dataviz.ui];
        }

        roles = $.map(namespaces, function(namespace) { return namespace.roles; }).reverse();

        roles = extend.apply(null, [{}].concat(roles));

        $(element).find("[data-" + kendo.ns + "role]").andSelf().each(function(){
            kendo.initWidget(this, {}, roles);
        });
    };

    kendo.parseOptions = parseOptions;

    extend(kendo.ui, /** @lends kendo.ui */{
        Widget: Widget,
        roles: {},
        /**
         * Shows an overlay with a loading message, indicating that an action is in progress.
         * @name kendo.ui.progress
         * @function
         * @param {jQuery} container The container that will hold the overlay
         * @param {Boolean} toggle Whether the overlay should be shown or hidden
         */
        progress: function(container, toggle) {
            var mask = container.find(".k-loading-mask");

            if (toggle) {
                if (!mask.length) {
                    mask = $("<div class='k-loading-mask'><span class='k-loading-text'>Loading...</span><div class='k-loading-image'/><div class='k-loading-color'/></div>")
                        .width("100%").height("100%")
                        .prependTo(container)
                        .css({ top: container.scrollTop(), left: container.scrollLeft() });
                }
            } else if (mask) {
                mask.remove();
            }
        },
        /**
         * Helper method for writing new widgets.
         * Exposes a jQuery plug-in that will handle the widget creation and attach its client-side object in the appropriate data-* attribute.
         * @name kendo.ui.plugin
         * @function
         * @param {kendo.ui.Widget} widget The widget function.
         * @param {Object} register <kendo.ui> The object where the reference to the widget is recorded.
         * @param {Object} prefix <""> The plugin function prefix, e.g. "Mobile" will register "kendoMobileFoo".
         * @example
         * function TextBox(element, options);
         * kendo.ui.plugin(TextBox);
         *
         * // initialize a new TextBox for each input, with the given options object.
         * $("input").kendoTextBox({ });
         * // get the TextBox object and call the value API method
         * $("input").data("kendoTextBox").value();
         */
        plugin: function(widget, register, prefix) {
            var name = widget.fn.options.name,
                getter;

            register = register || kendo.ui;
            prefix = prefix || "";

            register[name] = widget;

            register.roles[name.toLowerCase()] = widget;

            getter = "getKendo" + prefix + name;
            name = "kendo" + prefix + name;

            $.fn[name] = function(options) {
                var value = this,
                    args;

                if (typeof options === STRING) {
                    args = slice.call(arguments, 1);

                    this.each(function(){
                        var widget = $.data(this, name),
                            method,
                            result;

                        if (!widget) {
                            throw new Error(kendo.format("Cannot call method '{0}' of {1} before it is initialized", options, name));
                        }

                        method = widget[options];

                        if (typeof method !== FUNCTION) {
                            throw new Error(kendo.format("Cannot find method '{0}' of {1}", options, name));
                        }

                        result = method.apply(widget, args);

                        if (result !== undefined) {
                            value = result;
                            return false;
                        }
                    });
                } else {
                    this.each(function() {
                        new widget(this, options);
                    });
                }

                return value;
            };

            $.fn[getter] = function() {
                return this.data(name);
            };
        }
    });

    var MobileWidget = Widget.extend(/** @lends kendo.mobile.ui.Widget.prototype */{
        /**
         * Initializes mobile widget. Sets `element` and `options` properties.
         * @constructs
         * @class Represents a mobile UI widget. Base class for all Kendo mobile widgets.
         * @extends kendo.ui.Widget
         */
        init: function(element, options) {
            Widget.fn.init.call(this, element, options);
            this.wrapper = this.element;
        },

        options: {
            prefix: "Mobile"
        },

        events: [],

        viewShow: $.noop,

        view: function() {
            var viewElement = this.element.closest(kendo.roleSelector("view") + "," + kendo.roleSelector("splitview"));
            return viewElement.data("kendoMobileView") || viewElement.data("kendoMobileSplitView");
        }
    });

    /**
     * @name kendo.mobile
     * @namespace This object contains all code introduced by the Kendo mobile suite, plus helper functions that are used across all mobile widgets.
     */
    extend(kendo.mobile, {
        init: function(element) {
            kendo.init(element, kendo.mobile.ui, kendo.ui, kendo.dataviz.ui);
        },

        /**
         * @name kendo.mobile.ui
         * @namespace Contains all classes for the Kendo Mobile UI widgets.
         */
        ui: {
            Widget: MobileWidget,
            roles: {},
            plugin: function(widget) {
                kendo.ui.plugin(widget, kendo.mobile.ui, "Mobile");
            }
        }
    });

    /**
     * Enables kinetic scrolling on touch devices
     * @name kendo.touchScroller
     * @function
     * @param {Selector} element The container element to enable scrolling for.
     */
    kendo.touchScroller = function(element, options) {
        if (support.touch && kendo.mobile.ui.Scroller && !element.data("kendoMobileScroller")) {
            element.kendoMobileScroller(options);
            return element.data("kendoMobileScroller");
        } else {
            return false;
        }
    };

    /**
     * Prevents the default event action. Should be supplied as an event callback
     * @name kendo.preventDefault
     * @function
     */
    kendo.preventDefault = function(e) {
        e.preventDefault();
    };

    /**
     * Retrieves the widget for a given element (if any)
     */
    kendo.widgetInstance = function(element, suite) {
        var widget = suite.roles[element.data(kendo.ns + "role")];

        if (widget) {
            return element.data("kendo" + widget.fn.options.prefix + widget.fn.options.name);
        }
    };

    /**
     * Binds to orientation change or resize (depending on the platform)
     * Abstracts problem with android triggering event before the dimensions have changed.
     * @function
     * @param {Function} callback The callback to be executed
     */
    kendo.onResize = function(callback) {
        var handler = callback;
        if (support.mobileOS.android) {
            handler = function() { setTimeout(callback, 200); };
        }

        $(window).on(support.resize, handler);
    };

    kendo.data = function(element, key) {
        return element.data(kendo.ns + key);
    };
})(jQuery);
;