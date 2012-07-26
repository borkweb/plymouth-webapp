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
     * @name kendo.fx
     * @namespace This object contains the fx library that is used by all widgets using animation.
     * If this file is not included, all animations will be disabled but the basic functionality preserved.
     */
    var kendo = window.kendo,
        fx = kendo.fx,
        each = $.each,
        extend = $.extend,
        proxy = $.proxy,
        size = kendo.size,
        browser = $.browser,
        support = kendo.support,
        transforms = support.transforms,
        transitions = support.transitions,
        scaleProperties = { scale: 0, scalex: 0, scaley: 0, scale3d: 0 },
        translateProperties = { translate: 0, translatex: 0, translatey: 0, translate3d: 0 },
        hasZoom = (typeof document.documentElement.style.zoom !== "undefined") && !transforms,
        matrix3dRegExp = /matrix3?d?\s*\(.*,\s*([\d\.\-]+)\w*?,\s*([\d\.\-]+)\w*?,\s*([\d\.\-]+)\w*?,\s*([\d\.\-]+)\w*?/i,
        cssParamsRegExp = /^(-?[\d\.\-]+)?[\w\s]*,?\s*(-?[\d\.\-]+)?[\w\s]*/i,
        translateXRegExp = /translatex?$/i,
        oldEffectsRegExp = /(zoom|fade|expand)(\w+)/,
        singleEffectRegExp = /(zoom|fade|expand)/,
        unitRegExp = /[xy]$/i,
        transformProps = ["perspective", "rotate", "rotatex", "rotatey", "rotatez", "rotate3d", "scale", "scalex", "scaley", "scalez", "scale3d", "skew", "skewx", "skewy", "translate", "translatex", "translatey", "translatez", "translate3d", "matrix", "matrix3d"],
        transform2d = ["rotate", "scale", "scalex", "scaley", "skew", "skewx", "skewy", "translate", "translatex", "translatey", "matrix"],
        transform2units = { "rotate": "deg", scale: "", skew: "px", translate: "px" },
        cssPrefix = transforms.css,
        round = Math.round,
        BLANK = "",
        PX = "px",
        NONE = "none",
        AUTO = "auto",
        WIDTH = "width",
        HEIGHT = "height",
        HIDDEN = "hidden",
        ORIGIN = "origin",
        ABORT_ID = "abortId",
        OVERFLOW = "overflow",
        TRANSLATE = "translate",
        TRANSITION = cssPrefix + "transition",
        TRANSFORM = cssPrefix + "transform",
        PERSPECTIVE = cssPrefix + "perspective",
        BACKFACE = cssPrefix + "backface-visibility";

    kendo.directions = {
        left: {
            reverse: "right",
            property: "left",
            transition: "translatex",
            vertical: false,
            modifier: -1
        },
        right: {
            reverse: "left",
            property: "left",
            transition: "translatex",
            vertical: false,
            modifier: 1
        },
        down: {
            reverse: "up",
            property: "top",
            transition: "translatey",
            vertical: true,
            modifier: 1
        },
        up: {
            reverse: "down",
            property: "top",
            transition: "translatey",
            vertical: true,
            modifier: -1
        },
        top: {
            reverse: "bottom"
        },
        bottom: {
            reverse: "top"
        },
        "in": {
            reverse: "out",
            modifier: -1
        },
        out: {
            reverse: "in",
            modifier: 1
        }
    };

    extend($.fn, {
        kendoStop: function(clearQueue, gotoEnd) {
            if (transitions) {
                return kendo.fx.stopQueue(this, clearQueue || false, gotoEnd || false);
            } else {
                return this.stop(clearQueue, gotoEnd);
            }
        }
    });

    /* jQuery support for all transform animations (FF 3.5/3.6, Opera 10.x, IE9 */

    if (transforms && !transitions) {
        each(transform2d, function(idx, value) {
            $.fn[value] = function(val) {
                if (typeof val == "undefined") {
                    return animationProperty(this, value);
                } else {
                    var that = $(this)[0],
                        transformValue = value + "(" + val + transform2units[value.replace(unitRegExp, "")] + ")";

                    if (that.style.cssText.indexOf(TRANSFORM) == -1) {
                        $(this).css(TRANSFORM, transformValue);
                    } else {
                        that.style.cssText = that.style.cssText.replace(new RegExp(value + "\\(.*?\\)", "i"), transformValue);
                    }
                }
                return this;
            };

            $.fx.step[value] = function (fx) {
                $(fx.elem)[value](fx.now);
            };
        });

        var curProxy = $.fx.prototype.cur;
        $.fx.prototype.cur = function () {
            if (transform2d.indexOf(this.prop) != -1) {
                return parseFloat($(this.elem)[this.prop]());
            }

            return curProxy.apply(this, arguments);
        };
    }

    kendo.toggleClass = function(element, classes, options, add) {
        if (classes) {
            classes = classes.split(" ");

            if (transitions) {
                options = extend({
                    exclusive: "all",
                    duration: 400,
                    ease: "ease-out"
                }, options);

                element.css(TRANSITION, options.exclusive + " " + options.duration + "ms " + options.ease);
                setTimeout(function() {
                    element.css(TRANSITION, "").css(HEIGHT);
                }, options.duration); // TODO: this should fire a kendoAnimate session instead.
            }

            each(classes, function(idx, value) {
                element.toggleClass(value, add);
            });
        }

        return element;
    };

    kendo.parseEffects = function(input, mirror) {
        var effects = {};

        if (typeof input === "string") {
            each(input.split(" "), function(idx, value) {
                var redirectedEffect = !singleEffectRegExp.test(value),
                    resolved = value.replace(oldEffectsRegExp, function(match, $1, $2) {
                        return $1 + ":" + $2.toLowerCase();
                    }), // Support for old zoomIn/fadeOut style, now deprecated.
                    effect = resolved.split(":"),
                    direction = effect[1],
                    effectBody = {};

                if (effect.length > 1) {
                    effectBody.direction = (mirror && redirectedEffect ? kendo.directions[direction].reverse : direction);
                }

                effects[effect[0]] = effectBody;
            });
        } else {
            each(input, function(idx) {
                var direction = this.direction;

                if (direction && mirror && !singleEffectRegExp.test(idx)) {
                    this.direction = kendo.directions[direction].reverse;
                }

                effects[idx] = this;
            });
        }

        return effects;
    };

    function parseInteger(value) {
        return parseInt(value, 10);
    }

    function parseCSS(element, property) {
        return parseInteger(element.css(property));
    }

    function getComputedStyles(element, properties) {
        var styles = {};

        if (properties) {
            if (document.defaultView && document.defaultView.getComputedStyle) {
                var computedStyle = document.defaultView.getComputedStyle(element, "");

                each(properties, function(idx, value) {
                    styles[value] = computedStyle.getPropertyValue(value);
                });
            } else
                if (element.currentStyle) { // Not really needed
                    var style = element.currentStyle;

                    each(properties, function(idx, value) {
                        styles[value] = style[value.replace(/\-(\w)/g, function (strMatch, g1) { return g1.toUpperCase(); })];
                    });
                }
        } else {
            styles = document.defaultView.getComputedStyle(element, "");
        }

        return styles;
    }

    function slideToSlideIn(options) {
      options.effects.slideIn = options.effects.slide;
      delete options.effects.slide;
      delete options.complete;
      return options;
    }

    function parseTransitionEffects(options) {
        var effects = options.effects,
            mirror;

        if (effects === "zoom") {
            effects = "zoomIn fadeIn";
        }
        if (effects === "slide") {
            effects = "slide:left";
        }
        if (effects === "fade") {
            effects = "fadeIn";
        }
        if (effects === "overlay") {
            effects = "slideIn:left";
        }
        if (/^overlay:(.+)$/.test(effects)) {
            effects = "slideIn:" + RegExp.$1;
        }

        mirror = options.reverse && /^(slide:)/.test(effects);

        if (mirror) {
            delete options.reverse;
        }

        options.effects = $.extend(kendo.parseEffects(effects, mirror), {show: true});

        return options;
    }

    function keys(obj) {
        var acc = [];
        for (var propertyName in obj) {
            acc.push(propertyName);
        }
        return acc;
    }

    function stopTransition(element, transition) {
        if (element.data(ABORT_ID)) {
            clearTimeout(element.data(ABORT_ID));
            element.removeData(ABORT_ID);
        }

        element.css(TRANSITION, "").css(TRANSITION);
        element.dequeue();
        transition.complete.call(element);
    }

    function activateTask(currentTransition) {
        var element = currentTransition.object, delay = 0;

        if (!currentTransition) {
            return;
        }

        element.css(currentTransition.setup).css(TRANSITION);
        element.css(currentTransition.CSS).css(TRANSFORM);

        if (browser.mozilla) {
            element.one(transitions.event, function () { stopTransition(element, currentTransition); } );
            delay = 50;
        }
        element.data(ABORT_ID, setTimeout(stopTransition, currentTransition.duration + delay, element, currentTransition));
    }

    function strip3DTransforms(properties) {
        for (var key in properties) {
            if (transformProps.indexOf(key) != -1 && transform2d.indexOf(key) == -1) {
                delete properties[key];
            }
        }

        return properties;
    }

    function evaluateCSS(element, properties, options) {
        var key, value;

        for (key in properties) {
            if ($.isFunction(properties[key])) {
                value = properties[key](element, options);
                if (value !== undefined) {
                    properties[key] = value;
                } else {
                    delete properties[key];
                }
            }

        }

        return properties;
    }

    function normalizeCSS(element, properties, options) {
        var transformation = [], cssValues = {}, lowerKey, key, value, exitValue, isTransformed;

        for (key in properties) {
            lowerKey = key.toLowerCase();
            isTransformed = transforms && transformProps.indexOf(lowerKey) != -1;

            if (!support.hasHW3D && isTransformed && transform2d.indexOf(lowerKey) == -1) {
                delete properties[key];
            } else {
                exitValue = false;

                if ($.isFunction(properties[key])) {
                    value = properties[key](element, options);
                    if (value !== undefined) {
                        exitValue = value;
                    }
                } else {
                    exitValue = properties[key];
                }

                if (exitValue !== false) {
                    if (isTransformed) {
                        transformation.push(key + "(" + exitValue + ")");
                    } else {
                        cssValues[key] = exitValue;
                    }
                }
            }
        }

        if (transformation.length) {
            cssValues[TRANSFORM] = transformation.join(" ");
        }

        return cssValues;
    }

    if (transitions) {

        extend(kendo.fx, {
            transition: function(element, properties, options) {

                options = extend({
                        duration: 200,
                        ease: "ease-out",
                        complete: null,
                        exclusive: "all"
                    },
                    options
                );

                options.duration = $.fx ? $.fx.speeds[options.duration] || options.duration : options.duration;

                var css = normalizeCSS(element, properties, options),
                    currentTask = {
                        keys: keys(css),
                        CSS: css,
                        object: element,
                        setup: {},
                        duration: options.duration,
                        complete: options.complete
                    };
                currentTask.setup[TRANSITION] = options.exclusive + " " + options.duration + "ms " + options.ease;

                var oldKeys = element.data("keys") || [];
                $.merge(oldKeys, currentTask.keys);
                element.data("keys", $.unique(oldKeys));

                activateTask(currentTask);
            },

            stopQueue: function(element, clearQueue, gotoEnd) {

                if (element.data(ABORT_ID)) {
                    clearTimeout(element.data(ABORT_ID));
                    element.removeData(ABORT_ID);
                }

                var that = this, cssValues,
                    taskKeys = element.data("keys"),
                    retainPosition = (gotoEnd === false && taskKeys);

                if (retainPosition) {
                    cssValues = getComputedStyles(element[0], taskKeys);
                }

                element.css(TRANSITION, "").css(TRANSITION);

                if (retainPosition) {
                    element.css(cssValues);
                }

                element.removeData("keys");

                if (that.complete) {
                    that.complete.call(element);
                }

                element.stop(clearQueue);
                return element;
            }

        });
    }

    function animationProperty(element, property) {
        if (transforms) {
            var transform = element.css(TRANSFORM);
            if (transform == NONE) {
                return property == "scale" ? 1 : 0;
            }

            var match = transform.match(new RegExp(property + "\\s*\\(([\\d\\w\\.]+)")),
                computed = 0;

            if (match) {
                computed = parseInteger(match[1]);
            } else {
                match = transform.match(matrix3dRegExp) || [0, 0, 0, 0, 0];
                property = property.toLowerCase();

                if (translateXRegExp.test(property)) {
                    computed = parseFloat(match[3] / match[2]);
                } else if (property == "translatey") {
                    computed = parseFloat(match[4] / match[2]);
                } else if (property == "scale") {
                    computed = parseFloat(match[2]);
                } else if (property == "rotate") {
                    computed = parseFloat(Math.atan2(match[2], match[1]));
                }
            }

            return computed;
        } else {
            return parseFloat(element.css(property));
        }
    }

    function initDirection (element, direction, reverse) {
        var real = kendo.directions[direction],
            dir = reverse ? kendo.directions[real.reverse] : real;

        return { direction: dir, offset: -dir.modifier * (dir.vertical ? element.outerHeight() : element.outerWidth()) };
    }

    kendo.fx.promise = function(element, options) {
        var promises = [], effects;

        effects = kendo.parseEffects(options.effects);
        options.effects = effects;

        element.data("animating", true);

        var props = { keep: [], restore: [] }, css = {}, target,
            methods = { setup: [], teardown: [] }, properties = {},

            // create a promise for each effect
            promise = $.Deferred(function(deferred) {
                if (size(effects)) {
                    var opts = extend({}, options, { complete: deferred.resolve });

                    each(effects, function(effectName, settings) {
                        var effect = kendo.fx[effectName];

                        if (effect) {
                            var dir = kendo.directions[settings.direction];
                            if (settings.direction && dir) {
                                settings.direction = (options.reverse ? dir.reverse : settings.direction);
                            }

                            opts = extend(true, opts, settings);

                            each(methods, function (idx) {
                                if (effect[idx]) {
                                    methods[idx].push(effect[idx]);
                                }
                            });

                            each(props, function(idx) {
                                if (effect[idx]) {
                                    $.merge(props[idx], effect[idx]);
                                }
                            });

                            if (effect.css) {
                                css = extend(css, effect.css);
                            }
                        }
                    });

                    if (methods.setup.length) {
                        each ($.unique(props.keep), function(idx, value) {
                            if (!element.data(value)) {
                                element.data(value, element.css(value));
                            }
                        });

                        if (options.show) {
                            css = extend(css, { display: element.data("olddisplay") || "block" }); // Add show to the set
                        }

                        if (transforms && !options.reset) {
                            css = evaluateCSS(element, css, opts);

                            target = element.data("targetTransform");

                            if (target) {
                                css = extend(target, css);
                            }
                        }
                        css = normalizeCSS(element, css, opts);

                        if (transforms && !transitions) {
                            css = strip3DTransforms(css);
                        }

                        element.css(css)
                               .css(TRANSFORM); // Nudge

                        each (methods.setup, function() { properties = extend(properties, this(element, opts)); });

                        if (kendo.fx.animate) {
                            options.init();
                            element.data("targetTransform", properties);
                            kendo.fx.animate(element, properties, opts);
                        }

                        return;
                    }
                } else if (options.show) {
                    element.css({ display: element.data("olddisplay") || "block" }).css("display");
                    options.init();
                }

                deferred.resolve();
            }).promise();

        promises.push(promise);

        //wait for all effects to complete
        $.when.apply(null, promises).then(function() {
            element
                .removeData("animating")
                .dequeue(); // call next animation from the queue

            if (options.hide) {
                element.data("olddisplay", element.css("display")).hide();
            }

            if (size(effects)) {
                var restore = function() {
                    each ($.unique(props.restore), function(idx, value) {
                        element.css(value, element.data(value));
                    });
                };

                restore();
                if (hasZoom && !transforms) {
                    setTimeout(restore, 0); // Again jQuery callback in IE8-.
                }

                each(methods.teardown, function() { this(element, options); }); // call the internal completion callbacks
            }

            if (options.completeCallback) {
                options.completeCallback(element); // call the external complete callback with the element
            }
        });
    };

    kendo.fx.transitionPromise = function(element, destination, options) {
        kendo.fx.animateTo(element, destination, options);
        return element;
    };

    extend(kendo.fx, {
        animate: function(elements, properties, options) {
            var useTransition = options.transition !== false;
            delete options.transition;

            if (transitions && "transition" in fx && useTransition) {
                fx.transition(elements, properties, options);
            } else {
                if (transforms) {
                    elements.animate(strip3DTransforms(properties), { queue: false, show: false, hide: false, duration: options.duration, complete: options.complete }); // Stop animate from showing/hiding the element to be able to hide it later on.
                } else {
                    elements.each(function() {
                        var element = $(this),
                            multiple = {};

                        each(transformProps, function(idx, value) { // remove transforms to avoid IE and older browsers confusion
                            var params,
                                currentValue = properties ? properties[value]+ " " : null; // We need to match

                            if (currentValue) {
                                var single = properties;

                                if (value in scaleProperties && properties[value] !== undefined) {
                                    params = currentValue.match(cssParamsRegExp);
                                    if (hasZoom) {
                                        var half = (1 - params[1]) / 2;
                                        extend(single, {
                                                           zoom: +params[1],
                                                           marginLeft: element.width() * half,
                                                           marginTop: element.height() * half
                                                       });
                                    } else if (transforms) {
                                        extend(single, {
                                                           scale: +params[0]
                                                       });
                                    }
                                } else {
                                    if (value in translateProperties && properties[value] !== undefined) {
                                        var position = element.css("position"),
                                            isFixed = (position == "absolute" || position == "fixed");

                                        if (!element.data(TRANSLATE)) {
                                            if (isFixed) {
                                                element.data(TRANSLATE, {
                                                    top: parseCSS(element, "top") || 0,
                                                    left: parseCSS(element, "left") || 0,
                                                    bottom: parseCSS(element, "bottom"),
                                                    right: parseCSS(element, "right")
                                                });
                                            } else {
                                                element.data(TRANSLATE, {
                                                    top: parseCSS(element, "marginTop") || 0,
                                                    left: parseCSS(element, "marginLeft") || 0
                                                });
                                            }
                                        }

                                        var originalPosition = element.data(TRANSLATE);

                                        params = currentValue.match(cssParamsRegExp);
                                        if (params) {

                                            var dX = value == TRANSLATE + "y" ? +null : +params[1],
                                                dY = value == TRANSLATE + "y" ? +params[1] : +params[2];

                                            if (isFixed) {
                                                if (!isNaN(originalPosition.right)) {
                                                    if (!isNaN(dX)) { extend(single, { right: originalPosition.right - dX }); }
                                                } else {
                                                    if (!isNaN(dX)) { extend(single, { left: originalPosition.left + dX }); }
                                                }

                                                if (!isNaN(originalPosition.bottom)) {
                                                    if (!isNaN(dY)) { extend(single, { bottom: originalPosition.bottom - dY }); }
                                                } else {
                                                    if (!isNaN(dY)) { extend(single, { top: originalPosition.top + dY }); }
                                                }
                                            } else {
                                                if (!isNaN(dX)) { extend(single, { marginLeft: originalPosition.left + dX }); }
                                                if (!isNaN(dY)) { extend(single, { marginTop: originalPosition.top + dY }); }
                                            }
                                        }
                                    }
                                }

                                if (!transforms && value != "scale" && value in single) {
                                    delete single[value];
                                }

                                if (single) {
                                    extend(multiple, single);
                                }
                            }
                        });

                        if (browser.msie) {
                            delete multiple.scale;
                        }

                        element.animate(multiple, { queue: false, show: false, hide: false, duration: options.duration, complete: options.complete }); // Stop animate from showing/hiding the element to be able to hide it later on.
                    });
                }
            }
        },

        animateTo: function(element, destination, options) {
            var direction,
                commonParent = element.parents().filter(destination.parents()).first(),
                originalOverflow;

            options = parseTransitionEffects(options);
            if (!support.mobileOS.android) {
                originalOverflow = commonParent.css(OVERFLOW);
                commonParent.css(OVERFLOW, "hidden");
            }

            $.each(options.effects, function(name, definition) {
                direction = direction || definition.direction;
            });

            function complete(animatedElement) {
                destination[0].style.cssText = "";
                element[0].style.cssText = ""; // Removing the whole style attribute breaks Android.
                if (!support.mobileOS.android) {
                    commonParent.css(OVERFLOW, originalOverflow);
                }
                if (options.completeCallback) {
                    options.completeCallback.call(element, animatedElement);
                }
            }

            options.complete = browser.msie ? function() { setTimeout(complete, 0); } : complete;
            options.reset = true; // Reset transforms if there are any.

            if ("slide" in options.effects) {
                element.kendoAnimate(options);
                destination.kendoAnimate(slideToSlideIn(options));
            } else {
                (options.reverse ? element : destination).kendoAnimate(options);
            }
        },

        fade: {
            keep: [ "opacity" ],
            css: {
                opacity: function(element, options) {
                    var opacity = element[0].style.opacity;
                    return options.effects.fade.direction == "in" && (!opacity || opacity == 1) ? 0 : 1;
                }
            },
            restore: [ "opacity" ],
            setup: function(element, options) {
                return extend({ opacity: options.effects.fade.direction == "out" ? 0 : 1 }, options.properties);
            }
        },
        zoom: {
            css: {
                scale: function(element, options) {
                    var scale = animationProperty(element, "scale");
                    return options.effects.zoom.direction == "in" ? (scale != 1 ? scale : "0.01") : 1;
                },
                zoom: function(element, options) {
                    var zoom = element[0].style.zoom;
                    return options.effects.zoom.direction == "in" && hasZoom ? (zoom ? zoom : "0.01") : undefined;
                }
            },
            setup: function(element, options) {
                var reverse = options.effects.zoom.direction == "out";

                if (hasZoom) {
                    var version = browser.version,
                        style = element[0].currentStyle,
                        width = style.width.indexOf("%") != -1 ? element.parent().width() : element.width(),
                        height = style.height.indexOf("%") != -1 ? element.parent().height() : parseInteger(style.height),
                        half = version < 9 && options.effects.fade ? 0 : (1 - (parseInteger(element.css("zoom")) / 100)) / 2; // Kill margins in IE7/8 if using fade

                    element.css({
                        marginLeft: width * (version < 8 ? 0 : half),
                        marginTop: height * half
                    });
                }

                return extend({ scale: reverse ? 0.01 : 1 }, options.properties);
            }
        },
        slide: {
            setup: function(element, options) {
                var reverse = options.reverse, extender = {},
                    init = initDirection(element, options.effects.slide.direction, reverse),
                    property = transforms && options.transition !== false ? init.direction.transition : init.direction.property;

                init.offset /= -(options.divisor || 1);
                if (!reverse) {
                    var origin = element.data(ORIGIN);
                    if (!origin && origin !== 0) {
                        element.data(ORIGIN, animationProperty(element, property));
                    }
                }

                extender[property] = reverse ? (element.data(ORIGIN) || 0) : (element.data(ORIGIN) || 0) + init.offset + PX;

                return extend(extender, options.properties);
            }
        },
        slideMargin: {
            setup: function(element, options) {
                var origin = element.data(ORIGIN),
                    offset = options.offset, margin,
                    extender = {}, reverse = options.reverse;

                if (!reverse && !origin && origin !== 0) {
                    element.data(ORIGIN, parseFloat(element.css("margin-" + options.axis)));
                }

                margin = (element.data(ORIGIN) || 0);
                extender["margin-" + options.axis] = !reverse ? margin + offset : margin;
                return extend(extender, options.properties);
            }
        },
        slideTo: {
            setup: function(element, options) {
                var offset = (options.offset+"").split(","),
                    extender = {}, reverse = options.reverse;

                if (transforms && options.transition !== false) {
                    extender.translatex = !reverse ? offset[0] : 0;
                    extender.translatey = !reverse ? offset[1] : 0;
                } else {
                    extender.left = !reverse ? offset[0] : 0;
                    extender.top = !reverse ? offset[1] : 0;
                }
                element.css("left");

                return extend(extender, options.properties);
            }
        },
        slideIn: {
            css: {
                translatex: function (element, options) {
                    var init = initDirection(element, options.effects.slideIn.direction, options.reverse);
                    return init.direction.transition == "translatex" ? (!options.reverse ? init.offset : 0) + PX : undefined;
                },
                translatey: function (element, options) {
                    var init = initDirection(element, options.effects.slideIn.direction, options.reverse);
                    return init.direction.transition == "translatey" ? (!options.reverse ? init.offset : 0) + PX : undefined;
                }
            },
            setup: function(element, options) {
                var reverse = options.reverse,
                    init = initDirection(element, options.effects.slideIn.direction, reverse),
                    extender = {};

                if (transforms && options.transition !== false) {
                    extender[init.direction.transition] = (reverse ? init.offset : 0) + PX;
                } else {
                    if (!reverse) {
                        element.css(init.direction.property, init.offset + PX);
                    }
                    extender[init.direction.property] = (reverse ? init.offset : 0) + PX;
                    element.css(init.direction.property);
                }

                return extend(extender, options.properties);
            }
        },
        expand: {
            keep: [ OVERFLOW ],
            css: { overflow: HIDDEN },
            restore: [ OVERFLOW ],
            setup: function(element, options) {
                var reverse = options.reverse,
                    direction = options.effects.expand.direction,
                    property = (direction ? direction == "vertical" : true) ? HEIGHT : WIDTH,
                    setLength = element[0].style[property],
                    oldLength = element.data(property),
                    length = parseFloat(oldLength || setLength) || round(element.css(property, AUTO )[property]()),
                    completion = {};

                completion[property] = (reverse ? 0 : length) + PX;
                element.css(property, reverse ? length : 0).css(property);
                if (oldLength === undefined) {
                    element.data(property, setLength);
                }

                return extend(completion, options.properties);
            },
            teardown: function(element, options) {
                var direction = options.effects.expand.direction,
                    property = (direction ? direction == "vertical" : true) ? HEIGHT : WIDTH,
                    length = element.data(property);
                if (length == AUTO || length === BLANK) {
                    setTimeout(function() { element.css(property, AUTO).css(property); }, 0); // jQuery animate complete callback in IE is called before the last animation step!
                }
            }
        },
        flip: {
            css: {
                rotatex: function (element, options) {
                    return options.effects.flip.direction == "vertical" ? options.reverse ? "180deg" : "0deg" : undefined;
                },
                rotatey: function (element, options) {
                    return options.effects.flip.direction == "horizontal" ? options.reverse ? "180deg" : "0deg" : undefined;
                }
            },
            setup: function(element, options) {
                var rotation = options.effects.flip.direction == "horizontal" ? "rotatey" : "rotatex",
                    reverse = options.reverse, parent = element.parent(),
                    degree = options.degree, face = options.face, back = options.back,
                    faceRotation = rotation + (reverse ? "(180deg)" : "(0deg)"),
                    backRotation = rotation + (reverse ? "(0deg)" : "(180deg)"),
                    completion = {};

                if (support.hasHW3D) {
                    if (parent.css(PERSPECTIVE) == NONE) {
                        parent.css(PERSPECTIVE, 500);
                    }

                    element.css(cssPrefix + "transform-style", "preserve-3d");
                    face.css(BACKFACE, HIDDEN).css(TRANSFORM, faceRotation).css("z-index", reverse ? 0 : -1);
                    back.css(BACKFACE, HIDDEN).css(TRANSFORM, backRotation).css("z-index", reverse ? -1 : 0);
                    completion[rotation] = (reverse ? "-" : "") + (degree ? degree : 180) + "deg";
                } else {
                    if (kendo.size(options.effects) == 1) {
                        options.duration = 0;
                    }
                }
                face.show();
                back.show();

                return extend(completion, options.properties);
            },
            teardown: function(element, options) {
                options[options.reverse ? "back" : "face"].hide();

                if (support.hasHW3D) {
                    $().add(options.face).add(options.back).add(element)
                        .css(BACKFACE, "");
                }
            }
        },
        simple: {
            setup: function(element, options) {
                return options.properties;
            }
        }
    });

    kendo.fx.expandVertical = kendo.fx.expand; // expandVertical is deprecated.

    var animationFrame  = window.requestAnimationFrame       ||
                          window.webkitRequestAnimationFrame ||
                          window.mozRequestAnimationFrame    ||
                          window.oRequestAnimationFrame      ||
                          window.msRequestAnimationFrame     ||
                          function(callback){ setTimeout(callback, 1000 / 60); };

    var Animation = kendo.Class.extend({
        init: function() {
            var that = this;
            that._tickProxy = proxy(that._tick, that);
            that._started = false;
        },

        tick: $.noop,
        done: $.noop,
        onEnd: $.noop,
        onCancel: $.noop,

        start: function() {
            this._started = true;
            animationFrame(this._tickProxy);
        },

        cancel: function() {
            this._started = false;
            this.onCancel();
        },

        _tick: function() {
            var that = this;
            if (!that._started) { return; }

            that.tick();

            if (!that.done()) {
                animationFrame(that._tickProxy);
            } else {
                that._started = false;
                that.onEnd();
            }
        }
    });

    var Transition = Animation.extend({
        init: function(options) {
            var that = this;
            extend(that, options);
            Animation.fn.init.call(that);
        },

        done: function() {
            return this.timePassed() >= this.duration;
        },

        timePassed: function() {
            return Math.min(this.duration, (+new Date()) - this.startDate);
        },

        moveTo: function(options) {
            var that = this,
                movable = that.movable;

            that.initial = movable[that.axis];
            that.delta = options.location - that.initial;

            that.duration = options.duration || 300;

            that.tick = that._easeProxy(options.ease);

            that.startDate = +new Date();
            that.start();
        },

        _easeProxy: function(ease) {
            var that = this;

            return function() {
                that.movable.moveAxis(that.axis, ease(that.timePassed(), that.initial, that.delta, that.duration));
            };
        }
    });

    extend(Transition, {
        easeOutExpo: function (t, b, c, d) {
            return (t==d) ? b+c : c * (-Math.pow(2, -10 * t/d) + 1) + b;
        },

        easeOutBack: function (t, b, c, d, s) {
            s = 1.70158;
            return c*((t=t/d-1)*t*((s+1)*t + s) + 1) + b;
        }
    });

    fx.Animation = Animation;
    fx.Transition = Transition;
})(jQuery);
;