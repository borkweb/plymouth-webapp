/*
* Kendo UI Web v2012.2.710 (http://kendoui.com)
* Copyright 2012 Telerik AD. All rights reserved.
*
* Kendo UI Web commercial licenses may be obtained at http://kendoui.com/web-license
* If you do not own a commercial license, this file shall be governed by the
* GNU General Public License (GPL) version 3.
* For GPL requirements, please review: http://www.gnu.org/copyleft/gpl.html
*/
(function ($, undefined) {
    var kendo = window.kendo,
        support = kendo.support,
        pointers = support.pointers,
        document = window.document,
        SURFACE = $(document.documentElement),
        Class = kendo.Class,
        Widget = kendo.ui.Widget,
        Observable = kendo.Observable,
        proxy = $.proxy,
        now = $.now,
        extend = $.extend,
        getOffset = kendo.getOffset,
        draggables = {},
        dropTargets = {},
        lastDropTarget,
        invalidZeroEvents = support.mobileOS && support.mobileOS.android,
        START_EVENTS = "mousedown",
        MOVE_EVENTS = "mousemove",
        END_EVENTS = "mouseup mouseleave",
        KEYUP = "keyup",
        CHANGE = "change",

        // Draggable events
        DRAGSTART = "dragstart",
        DRAG = "drag",
        DRAGEND = "dragend",
        DRAGCANCEL = "dragcancel",

        // DropTarget events
        DRAGENTER = "dragenter",
        DRAGLEAVE = "dragleave",
        DROP = "drop",

        // Drag events
        START = "start",
        MOVE = "move",
        END = "end",
        CANCEL = "cancel",
        TAP = "tap";

    if (support.touch) {
        START_EVENTS = "touchstart";
        MOVE_EVENTS = "touchmove";
        END_EVENTS = "touchend touchcancel";
    }

    if(pointers) {
        START_EVENTS = "MSPointerDown";
        MOVE_EVENTS = "MSPointerMove";
        END_EVENTS = "MSPointerUp MSPointerCancel";
    }

    function contains(parent, child) {
        try {
            return $.contains(parent, child) || parent == child;
        } catch (e) {
            return false;
        }
    }

    function elementUnderCursor(e) {
        return document.elementFromPoint(e.x.client, e.y.client);
    }

    function numericCssPropery(element, property) {
        return parseInt(element.css(property), 10) || 0;
    }

    function within(value, range) {
        return Math.min(Math.max(value, range.min), range.max);
    }

    function containerBoundaries(container, element) {
        var offset = container.offset(),
            minX = offset.left + numericCssPropery(container, "borderLeftWidth") + numericCssPropery(container, "paddingLeft"),
            minY = offset.top + numericCssPropery(container, "borderTopWidth") + numericCssPropery(container, "paddingTop"),
            maxX = minX + container.width() - element.outerWidth(true),
            maxY = minY + container.height() - element.outerHeight(true);

        return {
            x: { min: minX, max: maxX },
            y: { min: minY, max: maxY }
        };
    }

    function addNS(events, ns) {
        return events.replace(/ /g, ns + " ");
    }

    function preventTrigger(e) {
        e.preventDefault();

        var target = $(e.target),   // Determine the correct parent to receive the event and bubble.
            parent = target.closest(".k-widget").parent();

        if (!parent[0]) {
            parent = target.parent();
        }

        parent.trigger(e.type);
    }

    /**
     * @name kendo.DragAxis.Description
     *
     * @section <h4>DragAxis</h4>
     * The DragAxis is used internally by the kendo.Drag component to store and calculate event data.
     * The Drag component contains two DragAxis instances: <code>x</code> for the horizontal coordinates, and <code>y</code> for the vertical.
     * The two DragAxis instances are available in each Drag event parameter.
     * @exampleTitle Access DragAxis information in Drag start event
     * @example
     * new kendo.Drag($("#foo"), {
     *  start: function(e) {
     *      console.log(x); // Horizontal axis
     *      console.log(y); // Vertical axis
     *  }
     * });
     *
     * @section Each axis instance contains the following fields:
     * <ul>
     *   <li><b>location</b> - the offset of the mouse/touch relative to the entire document (pageX/Y);</li>
     *   <li><b>startLocation</b> - the offset of the mouse/touch relative to the document when the drag started;</li>
     *   <li><b>client</b> - the offset of the mouse/touch relative to the viewport (clientX/Y);</li>
     *   <li><b>delta</b> - the change from the previous event location</li>
     *   <li><b>velocity</b> - the pixels per millisecond speed of the current move.</li>
     * </ul>
     */
    var DragAxis = Class.extend(/** @lends kendo.DragAxis.prototype */{
        /**
         * @constructs
         */
        init: function(axis) {
            this.axis = axis;
        },

        start: function(location, timeStamp) {
            var that = this,
                offset = location["page" + that.axis];

            that.startLocation = that.location = offset;
            that.client = location["client" + that.axis];
            that.velocity = that.delta = 0;
            that.timeStamp = timeStamp;
        },

        move: function(location, timeStamp) {
            var that = this,
                offset = location["page" + that.axis];

            if (!offset && invalidZeroEvents) {
                return;
            }

            that.delta = offset - that.location;
            that.location = offset;
            that.client = location["client" + that.axis];
            that.initialDelta = offset - that.startLocation;
            that.velocity = that.delta / (timeStamp - that.timeStamp);
            that.timeStamp = timeStamp;
        }
    });

    /**
     * @name kendo.Drag.Description
     * @section <h4>Drag</h4> The kendo Drag component provides a cross-browser, touch-friendly way to handle mouse and touch drag events.
     * @exampleTitle <b>Drag</b> initialization
     * @example
     * var drag = new kendo.Drag($("#draggable"));
     */
    var Drag = Observable.extend(/** @lends kendo.Drag.prototype */{
        /**
         * @constructs
         * @extends kendo.Observable
         * @param {Element} element the DOM element from which the drag event starts.
         * @param {Object} options Configuration options.
         * @option {Number} [threshold] <0> The minimum distance the mouse/touch should move before the event is triggered.
         * @option {Boolean} [global] <false> If set to true, the drag event will be tracked beyond the element boundaries.
         * @option {Element} [surface]  If set, the drag event will be tracked for the surface boundaries. By default, leaving the element boundaries will end the drag.
         * @option {Boolean} [allowSelection] <false> If set to true, the mousedown and selectstart events will not be prevented.
         * @option {Boolean} [stopPropagation] <false> If set to true, the mousedown event propagation will be stopped, disabling
         * drag capturing at parent elements.
         * If set to false, dragging outside of the element boundaries will trigger the <code>end</code> event.
         * @option {Selector} [filter] If passed, the filter limits the child elements that will trigger the event sequence.
         */
        init: function(element, options) {
            var that = this,
                eventMap = {},
                filter,
                preventIfMoving,
                ns = "." + kendo.guid();

            options = options || {};
            filter = that.filter = options.filter;
            that.threshold = options.threshold || 0;

            element = $(element);
            Observable.fn.init.call(that);

            eventMap[addNS(MOVE_EVENTS, ns)] = proxy(that._move, that);
            eventMap[addNS(END_EVENTS, ns)] = proxy(that._end, that);

            extend(that, {
                x: new DragAxis("X"),
                y: new DragAxis("Y"),
                element: element,
                surface: options.global ? SURFACE : options.surface || element,
                stopPropagation: options.stopPropagation,
                pressed: false,
                eventMap: eventMap,
                ns: ns
            });

            element
                .on(START_EVENTS, filter, proxy(that._start, that))
                .on("dragstart", filter, kendo.preventDefault);

            if (pointers) {
                element.css("-ms-touch-action", "pinch-zoom double-tap-zoom");
            }

            if (!options.allowSelection) {
                var args = ["mousedown selectstart", filter, preventTrigger];

                if (filter instanceof $) {
                    args.splice(2, 0, null);
                }

                element.on.apply(element, args);
            }

            if (support.eventCapture) {
                preventIfMoving = function(e) {
                    if (that.moved) {
                        e.preventDefault();
                    }
                };

                that.surface[0].addEventListener(support.mouseup, preventIfMoving, true);
            }

            that.bind([
            /**
             * Fires when the user presses and releases the element without any movement or with a movement below the <code>threshold</code> specified.
             * @name kendo.Drag#tap
             * @event
             * @param {Event} e
             * @param {DragAxis} e.x Reference to the horizontal drag axis instance.
             * @param {DragAxis} e.y Reference to the vertical drag axis instance.
             * @param {jQueryEvent} e.event Reference to the jQuery event object.
             * @param {Element} e.target Reference to the DOM element from which the Drag started.
             * It is different from the element only if <code>filter</code> option is specified.
             */
            TAP,
            /**
             * Fires when the user starts dragging the element.
             * @name kendo.Drag#start
             * @event
             * @param {Event} e
             * @param {DragAxis} e.x Reference to the horizontal drag axis instance.
             * @param {DragAxis} e.y Reference to the vertical drag axis instance.
             * @param {jQueryEvent} e.event Reference to the jQuery event object.
             * @param {Element} e.target Reference to the DOM element from which the Drag started.
             * It is different from the element only if <code>filter</code> option is specified.
             */
            START,
            /**
             * Fires while dragging.
             * @name kendo.Drag#move
             * @event
             * @param {Event} e
             * @param {DragAxis} e.x Reference to the horizontal drag axis instance.
             * @param {DragAxis} e.y Reference to the vertical drag axis instance.
             * @param {jQueryEvent} e.event Reference to the jQuery event object.
             * @param {Element} e.target Reference to the DOM element from which the Drag started.
             * It is different from the element only if <code>filter</code> option is specified.
             */
            MOVE,
            /**
             * Fires when the drag ends.
             * @name kendo.Drag#end
             * @event
             * @param {Event} e
             * @param {DragAxis} e.x Reference to the horizontal drag axis instance.
             * @param {DragAxis} e.y Reference to the vertical drag axis instance.
             * @param {jQueryEvent} e.event Reference to the jQuery event object.
             * @param {Element} e.target Reference to the DOM element from which the Drag started.
             * It is different from the element only if <code>filter</code> option is specified.
             */
            END,
            /**
             * Fires when the drag is canceled. This  when the <code>cancel</code> method is called.
             * @name kendo.Drag#cancel
             * @event
             * @param {Event} e
             * @param {DragAxis} e.x Reference to the horizontal drag axis instance.
             * @param {DragAxis} e.y Reference to the vertical drag axis instance.
             * @param {jQueryEvent} e.event Reference to the jQuery event object.
             * @param {Element} e.target Reference to the DOM element from which the Drag started.
             * It is different from the element only if <code>filter</code> option is specified.
             */
            CANCEL], options);
        },

        /**
         * Capture the current drag, so that Drag listeners bound to parent elements will not trigger.
         * This method will not have any effect if the current drag instance is instantiated with the <code>global</code> option set to true.
         */
        capture: function() {
            Drag.captured = true;
        },

        /**
         * Discard the current drag. Calling the <code>cancel</code> method will trigger the <code>cancel</code> event.
         * The correct moment to call this method would be in the <code>start</code> event handler.
         * @exampleTitle Cancel the drag event sequence
         * @example
         * new kendo.Drag($("#foo"), {
         *  start: function(e) {
         *      e.cancel();
         *  }
         * });
         */
        cancel: function() {
            this._cancel();
            this.trigger(CANCEL);
        },

        skip: function() {
            this._cancel();
        },

        _cancel: function() {
            var that = this;
            that.moved = that.pressed = false;
            that.surface.off(that.ns);
        },

        _start: function(e) {
            var that = this,
                filter = that.filter,
                originalEvent = e.originalEvent,
                touch,
                location = e;

            if (that.pressed) { return; }

            if (filter) {
                that.target = $(e.target).is(filter) ? $(e.target) : $(e.target).closest(filter);
            } else {
                that.target = that.element;
            }

            if (!that.target.length) {
                return;
            }

            that.currentTarget = e.currentTarget;

            if (that.stopPropagation) {
                e.stopPropagation();
            }

            that.pressed = true;
            that.moved = false;
            that.startTime = null;

            if (support.touch) {
                touch = originalEvent.changedTouches[0];
                that.touchID = touch.identifier;
                location = touch;
            }

            if (pointers) {
                that.touchID = originalEvent.pointerId;
                location = originalEvent;
            }

            that._perAxis(START, location, now());
            that.surface.off(that.eventMap).on(that.eventMap);
            Drag.captured = false;
        },

        _move: function(e) {
            var that = this,
                xDelta,
                yDelta,
                delta;

            if (!that.pressed) { return; }

            that._withEvent(e, function(location) {

                that._perAxis(MOVE, location, now());

                if (!that.moved) {
                    xDelta = that.x.initialDelta;
                    yDelta = that.y.initialDelta;

                    delta = Math.sqrt(xDelta * xDelta + yDelta * yDelta);

                    if (delta <= that.threshold) {
                        return;
                    }

                    if (!Drag.captured) {
                        that.startTime = now();
                        that._trigger(START, e);
                        that.moved = true;
                    } else {
                        return that._cancel();
                    }
                }

                // Event handlers may cancel the swipe in the START event handler, hence the double check for pressed.
                if (that.pressed) {
                    that._trigger(MOVE, e);
                }
            });
        },

        _end: function(e) {
            var that = this;

            if (!that.pressed) { return; }

            that._withEvent(e, function() {
                if (that.moved) {
                    that.endTime = now();
                    that._trigger(END, e);
                    that.moved = false;
                } else {
                    that._trigger(TAP, e);
                }

                that._cancel();
            });
        },

        _perAxis: function(method, location, timeStamp) {
            this.x[method](location, timeStamp);
            this.y[method](location, timeStamp);
        },

        _trigger: function(name, e) {
            var data = {
                x: this.x,
                y: this.y,
                target: this.target,
                event: e
            };

            if(this.trigger(name, data)) {
                e.preventDefault();
            }
        },

        _withEvent: function(e, callback) {
            var that = this,
                touchID = that.touchID,
                originalEvent = e.originalEvent,
                touches,
                idx;

            if (support.touch) {
                touches = originalEvent.changedTouches;
                idx = touches.length;

                while (idx) {
                    idx --;
                    if (touches[idx].identifier === touchID) {
                        return callback(touches[idx]);
                    }
                }
            }
            else if (pointers) {
                if (touchID === originalEvent.pointerId) {
                    return callback(originalEvent);
                }
            } else {
                return callback(e);
            }
        }
    });

    var Tap = Observable.extend({
        init: function(element, options) {
            var that = this,
                domElement = element[0];

            that.capture = false;
            domElement.addEventListener(START_EVENTS, proxy(that._press, that), true);
            $.each(END_EVENTS.split(" "), function() {
                domElement.addEventListener(this, proxy(that._release, that), true);
            });

            Observable.fn.init.call(that);

            that.bind(["press", "release"], options || {});
        },

        _press: function(e) {
            var that = this;
            that.trigger("press");
            if (that.capture) {
                e.preventDefault();
            }
        },

        _release: function(e) {
            var that = this;
            that.trigger("release");

            if (that.capture) {
                e.preventDefault();
                that.cancelCapture();
            }
        },

        captureNext: function() {
            this.capture = true;
        },

        cancelCapture: function() {
            this.capture = false;
        }
    });

    var PaneDimension = Observable.extend({
        init: function(options) {
            var that = this;
            Observable.fn.init.call(that);

            $.extend(that, options);

            that.max = 0;

            if (that.horizontal) {
                that.measure = "width";
                that.scrollSize = "scrollWidth";
                that.axis = "x";
            } else {
                that.measure = "height";
                that.scrollSize = "scrollHeight";
                that.axis = "y";
            }
        },

        outOfBounds: function(offset) {
            return  offset > this.max || offset < this.min;
        },

        present: function() {
            return this.max - this.min;
        },

        getSize: function() {
            return this.container[this.measure]();
        },

        getTotal: function() {
            return this.element[0][this.scrollSize];
        },

        update: function(silent) {
            var that = this;

            that.size = that.getSize();
            that.total = that.getTotal();
            that.min = Math.min(that.max, that.size - that.total);
            if (!silent) {
                that.trigger(CHANGE, that);
            }
        }
    });

    var PaneDimensions = Observable.extend({
        init: function(options) {
            var that = this,
                refresh = proxy(that.refresh, that);

            Observable.fn.init.call(that);

            that.x = new PaneDimension(extend({horizontal: true}, options));
            that.y = new PaneDimension(extend({horizontal: false}, options));

            that.bind(CHANGE, options);

            kendo.onResize(refresh);
        },

        present: function() {
            return this.x.present() || this.y.present();
        },

        refresh: function() {
            this.x.update();
            this.y.update();
            this.trigger(CHANGE);
        }
    });

    var PaneAxis = Observable.extend({
        init: function(options) {
            var that = this;
            extend(that, options);
            Observable.fn.init.call(that);
        },

        dragMove: function(delta) {
            var that = this,
                dimension = that.dimension,
                axis = that.axis,
                movable = that.movable,
                position = movable[axis] + delta;

            if (!dimension.present()) {
                return;
            }

            if ((position < dimension.min && delta < 0) || (position > dimension.max && delta > 0)) {
                delta *= that.resistance;
            }

            movable.translateAxis(axis, delta);
            that.trigger(CHANGE, that);
        }
    });

    var Pane = Class.extend({
        init: function(options) {
            var that = this,
                x,
                y,
                resistance;

            extend(that, {elastic: true}, options);

            resistance = that.elastic ? 0.5 : 0;

            that.x = x = new PaneAxis({
                axis: "x",
                dimension: that.dimensions.x,
                resistance: resistance,
                movable: that.movable
            });

            that.y = y = new PaneAxis({
                axis: "y",
                dimension: that.dimensions.y,
                resistance: resistance,
                movable: that.movable
            });

            that.drag.bind(["move", "end"], {
                move: function(e) {
                    if (x.dimension.present() || y.dimension.present()) {
                        x.dragMove(e.x.delta);
                        y.dragMove(e.y.delta);
                        e.preventDefault();
                    } else {
                        that.drag.skip();
                    }
                },

                end: function(e) {
                    e.preventDefault();
                }
            });
        }
    });

    var TRANSFORM_STYLE = support.transitions.prefix + "Transform",
        round = Math.round,
        translate;

    if (support.hasHW3D) {
        translate = function(x, y) {
            return "translate3d(" + round(x) + "px," + round(y) +"px,0)";
        };
    } else {
        translate = function(x, y) {
            return "translate(" + round(x) + "px," + round(y) +"px)";
        };
    }

    var Movable = Observable.extend({
        init: function(element) {
            var that = this;

            Observable.fn.init.call(that);

            that.element = $(element);
            that.x = 0;
            that.y = 0;
            that._saveCoordinates(translate(that.x, that.y));
        },

        translateAxis: function(axis, by) {
            this[axis] += by;
            this.refresh();
        },

        translate: function(coordinates) {
            this.x += coordinates.x;
            this.y += coordinates.y;
            this.refresh();
        },

        moveAxis: function(axis, value) {
            this[axis] = value;
            this.refresh();
        },

        moveTo: function(coordinates) {
            extend(this, coordinates);
            this.refresh();
        },

        refresh: function() {
            var that = this,
                newCoordinates = translate(that.x, that.y);

            if (newCoordinates != that.coordinates) {
                that.element[0].style[TRANSFORM_STYLE] = newCoordinates;
                that._saveCoordinates(newCoordinates);
                that.trigger(CHANGE);
            }
        },

        _saveCoordinates: function(coordinates) {
            this.coordinates = coordinates;
        }
    });

    var DropTarget = Widget.extend(/** @lends kendo.ui.DropTarget.prototype */ {
        /**
         * @constructs
         * @extends kendo.ui.Widget
         * @param {Element} element DOM element
         * @param {Object} options Configuration options.
         * @option {String} [group] <"default"> Used to group sets of draggable and drop targets. A draggable with the same group value as a drop target will be accepted by the drop target.
         */
        init: function(element, options) {
            var that = this;

            Widget.fn.init.call(that, element, options);

            var group = that.options.group;

            if (!(group in dropTargets)) {
                dropTargets[group] = [ that ];
            } else {
                dropTargets[group].push( that );
            }
        },

        events: [
            /**
             * Fires when draggable moves over the drop target.
             * @name kendo.ui.DropTarget#dragenter
             * @event
             * @param {Event} e
             * @param {jQuery} e.draggable Reference to the draggable that enters the drop target.
             */
            DRAGENTER,
            /**
             * Fires when draggable moves out of the drop target.
             * @name kendo.ui.DropTarget#dragleave
             * @event
             * @param {Event} e
             * @param {jQuery} e.draggable Reference to the draggable that leaves the drop target.
             */
            DRAGLEAVE,
            /**
             * Fires when draggable is dropped over the drop target.
             * @name kendo.ui.DropTarget#drop
             * @event
             * @param {Event} e
             * @param {jQuery} e.draggable Reference to the draggable that is dropped over the drop target.
             * @param {jQuery} e.draggable.currentTarget The element that the drag and drop operation started from.
             */
            DROP
        ],

        options: {
            name: "DropTarget",
            group: "default"
        },

        _trigger: function(eventName, e) {
            var that = this,
                draggable = draggables[that.options.group];

            if (draggable) {
                return that.trigger(eventName, extend({}, e.event, {
                           draggable: draggable
                       }));
            }
        },

        _over: function(e) {
            this._trigger(DRAGENTER, e);
        },

        _out: function(e) {
            this._trigger(DRAGLEAVE, e);
        },

        _drop: function(e) {
            var that = this,
                draggable = draggables[that.options.group];

            if (draggable) {
                draggable.dropped = !that._trigger(DROP, e);
            }
        }
    });

    /**
     * @name kendo.ui.Draggable.Description
     *
     * @section <h4>Draggable</h4>
     * Enable draggable functionality on any DOM element.
     *
     * @exampleTitle <b>Draggable</b> initialization
     * @example
     * var draggable = $("#draggable").kendoDraggable();
     *
     * @name kendo.ui.DropTarget.Description
     *
     * @section <h4>DropTarget</h4>
     * Enable any DOM element to be a target for draggable elements.
     *
     * @exampleTitle <b>DropTarget</b> initialization
     * @example
     * var dropTarget = $("#dropTarget").kendoDropTarget();
     */
    var Draggable = Widget.extend(/** @lends kendo.ui.Draggable.prototype */{
        /**
         * @constructs
         * @extends kendo.ui.Widget
         * @param {Element} element DOM element
         * @param {Object} options Configuration options.
         * @option {Number} [distance] <5> The required distance that the mouse should travel in order to initiate a drag.
         * @option {Selector} [filter] Selects child elements that are draggable if a widget is attached to a container.
         * @option {String} [group] <"default"> Used to group sets of draggable and drop targets. A draggable with the same group value as a drop target will be accepted by the drop target.
         * @option {String} [axis] <null> Constrains the hint movement to either the horizontal (x) or vertical (y) axis. Can be set to either "x" or "y".
         * @option {jQuery} [container] If set, the hint movement is constrained to the container boundaries.
         * @option {Object} [cursorOffset] <null> If set, specifies the offset of the hint relative to the mouse cursor/finger.
         * By default, the hint is initially positioned on top of the draggable source offset. The option accepts an object with two keys: <code>top</code> and <code>left</code>.
         * _exampleTitle Initialize Draggable with cursorOffset
         * _example
         * $("#draggable").kendoDraggable({cursorOffset: {top: 10, left: 10}});
         * @option {Function | jQuery} [hint] Provides a way for customization of the drag indicator. If a function is supplied, it receives one argument - the draggable element's jQuery object.
         * _example
         *  //hint as a function
         *  $("#draggable").kendoDraggable({
         *      hint: function(element) {
         *          return $("#draggable").clone();
         *          // same as
         *          //  return element.clone();
         *      }
         *  });
         *
         * //hint as jQuery object
         *  $("#draggable").kendoDraggable({
         *      hint: $("#draggableHint");
         *  });
         */
        init: function (element, options) {
            var that = this;

            Widget.fn.init.call(that, element, options);

            that.drag = new Drag(that.element, {
                global: true,
                stopPropagation: true,
                filter: that.options.filter,
                threshold: that.options.distance,
                start: proxy(that._start, that),
                move: proxy(that._drag, that),
                end: proxy(that._end, that),
                cancel: proxy(that._cancel, that)
            });

            that.destroy = proxy(that._destroy, that);
            that.captureEscape = function(e) {
                if (e.keyCode === kendo.keys.ESC) {
                    that._trigger(DRAGCANCEL, {event: e});
                    that.drag.cancel();
                }
            };
        },

        events: [
            /**
             * Fires when item drag starts.
             * @name kendo.ui.Draggable#dragstart
             * @event
             * @param {Event} e
             */
            DRAGSTART,
             /**
             * Fires while dragging.
             * @name kendo.ui.Draggable#drag
             * @event
             * @param {Event} e
             */
            DRAG,
             /**
             * Fires when item drag ends.
             * @name kendo.ui.Draggable#dragend
             * @event
             * @param {Event} e
             */
            DRAGEND,
             /**
             * Fires when item drag is canceled by pressing the Escape key.
             * @name kendo.ui.Draggable#dragcancel
             * @event
             * @param {Event} e
             */
            DRAGCANCEL
        ],

        options: {
            name: "Draggable",
            distance: 5,
            group: "default",
            cursorOffset: null,
            axis: null,
            container: null,
            dropped: false
        },

        _start: function(e) {
            var that = this,
                options = that.options,
                container = options.container,
                hint = options.hint;

            that.currentTarget = that.drag.target;
            that.currentTargetOffset = getOffset(that.currentTarget);

            if (hint) {
                that.hint = $.isFunction(hint) ? $(hint(that.currentTarget)) : hint;

                var offset = getOffset(that.currentTarget);
                that.hintOffset = offset;

                that.hint.css( {
                    position: "absolute",
                    zIndex: 20000, // the Window's z-index is 10000 and can be raised because of z-stacking
                    left: offset.left,
                    top: offset.top
                })
                .appendTo(document.body);
            }

            draggables[options.group] = that;

            that.dropped = false;

            if (container) {
                that.boundaries = containerBoundaries(container, that.hint);
            }

            if (that._trigger(DRAGSTART, e)) {
                that.drag.cancel();
                that.destroy();
            }

            $(document).on(KEYUP, that.captureEscape);
        },

        updateHint: function(e) {
            var that = this,
                coordinates,
                options = that.options,
                boundaries = that.boundaries,
                axis = options.axis,
                cursorOffset = that.options.cursorOffset;

            if (cursorOffset) {
               coordinates = { left: e.x.location + cursorOffset.left, top: e.y.location + cursorOffset.top };
            } else {
               that.hintOffset.left += e.x.delta;
               that.hintOffset.top += e.y.delta;
               coordinates = $.extend({}, that.hintOffset);
            }

            if (boundaries) {
                coordinates.top = within(coordinates.top, boundaries.y);
                coordinates.left = within(coordinates.left, boundaries.x);
            }

            if (axis === "x") {
                delete coordinates.top;
            } else if (axis === "y") {
                delete coordinates.left;
            }

            that.hint.css(coordinates);
        },

        _drag: function(e) {
            var that = this;

            e.preventDefault();

            that._withDropTarget(e, function(target) {
                if (!target) {
                    if (lastDropTarget) {
                        lastDropTarget._trigger(DRAGLEAVE, e);
                        lastDropTarget = null;
                    }
                    return;
                }

                if (lastDropTarget) {
                    if (target.element[0] === lastDropTarget.element[0]) {
                        return;
                    }

                    lastDropTarget._trigger(DRAGLEAVE, e);
                }

                target._trigger(DRAGENTER, e);
                lastDropTarget = target;
            });

            that._trigger(DRAG, e);

            if (that.hint) {
                that.updateHint(e);
            }
        },

        _end: function(e) {
            var that = this;

            that._withDropTarget(e, function(target) {
                if (target) {
                    target._drop(e);
                    lastDropTarget = null;
                }
            });

            that._trigger(DRAGEND, e);
            that._cancel(e.event);
        },

        _cancel: function(e) {
            var that = this;

            if (that.hint && !that.dropped) {
                that.hint.animate(that.currentTargetOffset, "fast", that.destroy);
            } else {
                that.destroy();
            }
        },

        _trigger: function(eventName, e) {
            var that = this;

            return that.trigger(
            eventName, extend(
            {},
            e.event,
            {
                x: e.x,
                y: e.y,
                currentTarget: that.currentTarget
            }));
        },

        _withDropTarget: function(e, callback) {
            var that = this,
                target,
                theTarget,
                result,
                options = that.options,
                targets = dropTargets[options.group],
                i = 0,
                length = targets && targets.length;

            if (length) {

                target = elementUnderCursor(e);

                if (that.hint && contains(that.hint, target)) {
                    that.hint.hide();
                    target = elementUnderCursor(e);
                    that.hint.show();
                }

                outer:
                while (target) {
                    for (i = 0; i < length; i ++) {
                        theTarget = targets[i];
                        if (theTarget.element[0] === target) {
                            result = theTarget;
                            break outer;
                        }
                    }

                    target = target.parentNode;
                }

                callback(result);
            }
        },

        _destroy: function() {
            var that = this;

            if (that.hint) {
                that.hint.remove();
            }

            delete draggables[that.options.group];

            that.trigger("destroy");
            $(document).off(KEYUP, that.captureEscape);
        }
    });

    kendo.ui.plugin(DropTarget);
    kendo.ui.plugin(Draggable);
    kendo.Drag = Drag;
    kendo.Tap = Tap;
    kendo.containerBoundaries = containerBoundaries;

    extend(kendo.ui, {
        Pane: Pane,
        PaneDimensions: PaneDimensions,
        Movable: Movable
    });

 })(jQuery);
;