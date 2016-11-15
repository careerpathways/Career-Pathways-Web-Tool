/**
 * Extend chview/Charts.js for admin editing purposes.
 */
Object.extend(Charts, {
    getID: function(object, config, oncomplete) {
        chUtil.ajax({
                id: this.id,
                a: 'new',
                content: config
            },
            function(ajax) {
                object.id = ajax.responseText;
                if (oncomplete) {
                    oncomplete();
                }
            });
    },

    //_beginRedraw: function(canvas) {},

    _finishRedraw: function(context) {
        // draw the highlight box
        if (Charts.selectedComponent && !Charts.activeControl) {
            var bounds = Charts.selectedComponent.getShape().getBounds();

            context.lineWidth = 3;
            context.strokeStyle = '#ffffff';
            context.strokeRect(bounds.x, bounds.y, bounds.width, bounds.height);
            context.lineWidth = 2;
            context.strokeStyle = SELECTED_COLOR;
            context.strokeRect(bounds.x, bounds.y, bounds.width, bounds.height);

        }

        // draw control points
        if (Charts.controlPoints) {
            Charts.controlPoints.each(function(p) {
                var rect;
                if (!Charts.activeControl || Charts.activeControl == p) {
                    context.fillStyle = p.color ? p.color : SELECTED_COLOR;
                    rect = [Math.floor(p.x) - CONTROL_POINT_RADIUS, Math.floor(p.y) - CONTROL_POINT_RADIUS, CONTROL_POINT_RADIUS * 2, CONTROL_POINT_RADIUS * 2];
                    context.fillRect.apply(context, rect);
                }
            });
        }
    },

    getShapeContaining: function(position) {
        if (Charts.selectedComponent && Charts.selectedComponent.getShape().getBounds().contains(position)) {
            return Charts.selectedComponent.getShape();
        }

        for (var i = Charts.layers.length - 1; i >= 0; --i) {
            var layer = Charts.layers[i];
            if (layer) {
                // loop over the shapes backwards to find the shape on top
                for (var j = layer.length - 1; j >= 0; --j) {
                    if (layer[j].getBounds().contains(position)) {
                        return layer[j];
                    }
                }
            }
        }
    },

    /** Adds a button to the toolbar. The button will display the name,
     * and invoke the onclick when clicked.
     */
    addToolbarButton: function(name, widgetClass, data) {
        var button = document.createElement('div');
        button.className = 'button';
        button.innerHTML = name;
        Event.observe(button, 'click', function() {
            Charts.createComponent(widgetClass, data, Charts.redraw.bind(Charts));
        });
        Charts.toolbar.appendChild(button);
    },

    debug: function(txt) {
        $('debugDiv').innerHTML = txt;
    },

    // TODO document this
    whichi: function() {
        return 'edit';
    },

    confirmDelete: function(type) {
        return confirm('Are you sure you want to delete this ' + type + '?\n\nYou cannot undo this action.');
    },

    positionWithin: function(pointer) {
        return Geometry.translatedPoint(pointer, -Charts.elementOffset.left, -Charts.elementOffset.top)
    },

    getControlPointContaining: function(point) {
        if (Charts.controlPoints) {
            return Charts.controlPoints.find(function(controlPoint) {
                return Geometry.abs(point, controlPoint) < CONTROL_POINT_RADIUS;
            });
        } else {
            return null;
        }
    },

    createComponent: function(type, options, callback) {
        var newOptions = Object.clone(type.DEFAULT_OPTIONS);
        newOptions.config = Object.clone(type.DEFAULT_OPTIONS.config);

        Object.extend(newOptions, options || {});

        var widget = new type(newOptions);
        newOptions.type = widget.getType();
        Charts.getID(widget, newOptions, function() {
            Charts.registerComponent(widget);
            if (callback) {
                callback();
            }
        });
    },

    unregisterComponent: function(component) {
        Charts.components = Charts._removeArrayElement(Charts.components, component);
        var shape = component.getShape();
        Charts.layers[shape.layerIndex] = Charts._removeArrayElement(Charts.layers[shape.layerIndex], shape);

        if (component.type != 'connection') {
            Charts.widgets.unset(component.id);
        }

        if (Charts.selectedComponent == component) {
            Charts._deselect();
        }
    },

    _deselect: function() {
        Charts.selectedComponent = null;
        Charts.controlPoints = null;
    },

    _select: function(component) {
        Charts.selectedComponent = component;
        if (component) {
            Charts.controlPoints = Charts.selectedComponent.getControlPoints();
        } else {
            Charts.controlPoints = null;
        }
    },

    _removeArrayElement: function(array, element) {
        var index = element.index;

        var newArray = [];

        for (var i = 0; i < index; ++i) {
            newArray[i] = array[i];
        }
        for (var i = index, len = array.length - 1; i < len; ++i) {
            newArray[i] = array[i + 1];
            newArray[i].index = i;
        }

        return newArray;
    }
});
