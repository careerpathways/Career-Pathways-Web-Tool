
Charts = {
    whichi: function() {
        return 'view';
    },

    draw: function(canvas_container, toolbar_container) {
        // in IE 8, this has to happen BEFORE we use a canvas
        if (window.G_vmlCanvasManager) {
            window.G_vmlCanvasManager.init_(document);
        }

        if (canvas_container) {
            Charts.element = $(canvas_container);
            Charts._afterOpen = function() {
                var points = Charts.components.invoke('getShape').invoke('getBounds').pluck('bottomRight');
                Charts.bounds = new Geometry.Bounds(points);
                var bottomRight = Charts.bounds.bottomRight;
                if (Charts.drawingStatus !== 'draft') {
                    jQuery('#' + canvas_container).width(bottomRight.x);
                    jQuery('#' + canvas_container).height(bottomRight.y);
                }
            };
        } else {
            Charts.element = $(document.body);
            Charts._afterOpen = function() {
                var points = Charts.components.invoke('getShape').invoke('getBounds').pluck('bottomRight');
                Charts.bounds = new Geometry.Bounds(points);
                var bottomRight = Charts.bounds.bottomRight;
                Charts.createCanvas(bottomRight.x, bottomRight.y);
            };
        }

        Charts.elementOffset = Charts.element.cumulativeOffset();

        if (toolbar_container) {
            Charts.toolbarContainer = $(toolbar_container);
        }

        Charts.textSizeMonitor = new TextSizeMonitor(Charts.element);
        Charts.textSizeMonitor.start();
        Charts.textSizeMultiplier = Charts.textSizeMonitor.getSize() / Charts.textSizeMonitor.getBaseSize();
        Charts.element.observe('text:resized', function(e) {
            Charts.textSizeMultiplier = e.memo.currentSize / Charts.textSizeMonitor.getBaseSize();
            // TODO only reposition text elements
            Charts.components.each(function(component) {
                if (component.repositionElement) {
                    component.repositionElement();
                }
            })
            Charts.redraw();
        });

        Charts.createCanvas(Charts.element.offsetWidth, Charts.element.offsetHeight);

        Charts.element.addClassName('yui-skin-sam');

        Charts.setData({
            versionId: versionId,
            widgets: chData,
            connections: connections,
            drawing_status: drawing_status
        });
    },

    createCanvas: function(width, height) {
        if (Charts.canvas) {
            Charts.element.removeChild(Charts.canvas);
        }

        Charts.canvas = document.createElement('canvas');
        Charts._realCanvas = Charts.canvas.getContext;
        if (Charts._realCanvas && Charts.printing) {
            Charts.canvasScale = 4;
        } else {
            Charts.canvasScale = 1;
        }
        Charts.canvas.setAttribute('width', width * Charts.canvasScale);
        Charts.canvas.setAttribute('height', height * Charts.canvasScale);

        // this makes firefox happy (for printing)
        Charts.canvas.style.width = (width) + 'px';
        Charts.canvas.style.height = (height) + 'px';

        if (!Charts._realCanvas) {
            Charts.canvas.id = Charts.element.id + '_canvas';
            Charts.element.appendChild(Charts.canvas);
            Charts.canvas = window.G_vmlCanvasManager.initElement(Charts.canvas);
        } else {
            Charts.element.appendChild(Charts.canvas);
        }
        Charts.canvas = $(Charts.canvas);

        Charts.canvas.style.position = "absolute";
        Charts.canvas.style.top = "0";
        Charts.canvas.style.left = "0";
        Charts.canvas.style.zIndex = "-100";

        Charts.ctx = Charts.canvas.getContext('2d');
    },

    setData: function(data) {
        Charts.versionId = data.versionId;
        Charts.layers = [];
        Charts.components = [];
        Charts.widgets = new Hash(),
            Charts.connections = new Hash();
        Charts.drawingStatus = data.drawing_status;

        if (Charts.drawingStatus == 'draft') {
            Charts.backgroundImageUrl = '/images/' + Charts.drawingStatus + '-overlay.png';
            // firefox crashes with this backgroung image
            if (!(Charts.printing && Prototype.Browser.Gecko)) {
                Charts.canvas.style.background = 'url(' + Charts.backgroundImageUrl + ')';
            }
        }

        // add all the widgets
        data.widgets.each(function(properties) {
            var component = null;
            switch (properties['type']) {
                case 'line':
                    component = new ChartLine(properties);
                    break;
                case 'arrow':
                    properties.arrowheadAtEnd = true;
                    component = new ChartLine(properties);
                    break;
                case 'box':
                    component = new ChartBox(properties);
                    break;
                case 'circle':
                    component = new ChartCircle(properties);
                    break;
            }
            Charts.registerComponent(component);
        });

        //once all widgets are translated and drawn we can draw links between them (currently only boxes support this feature)
        data.connections.each(function(data) {
            var source = Charts.getWidget(data.source_object_id);
            var destination = Charts.getWidget(data.destination_object_id);
            var connection = new Connection(source, destination, data);
            Charts.registerComponent(connection);
        });

        if (Charts._afterOpen) {
            Charts._afterOpen();
        }
        //Charts.reposition();
        Charts.redraw();

        Charts.element.fire('chart:drawn', {
            chart: Charts
        });
    },

    /** Registers a component with the chart.
     *  A registered component has it's shapes added to the chart. */
    registerComponent: function(component) {
        component.chart = Charts;

        // TODO belongs in component
        component.shape = component.createShape();
        component.shape.widget = component;

        Charts.addShape(component.getShape(), component.getLayer());

        Charts.components.push(component);

        if (component.type != 'connection') {
            Charts.widgets.set(component.id, component);
        }

        component.reposition();
    },

    /** Returns the widget with the specified id. */
    getWidget: function(id) {
        return Charts.widgets.get(id);
    },

    /** Adds a shape to the chart. */
    addShape: function(shape, layerIndex) {
        layerIndex = layerIndex || 0;
        var layer = Charts.layers[layerIndex];
        if (!layer) {
            layer = [];
            Charts.layers[layerIndex] = layer;
        }
        shape.index = layer.length;
        shape.layerIndex = layerIndex;
        layer[shape.index] = shape;
    },

    /** Moves a shape forward in the stacking order. */
    moveShapeForward: function(shape) {
        if (shape.index < Charts.layers[shape.layerIndex].length - 1) {
            Charts._swapShapeOrder(shape.layerIndex, shape.index, shape.index + 1);
            return true;
        } else {
            return false;
        }
    },

    /** Moves a shape backwards in the stacking order. */
    moveShapeBackward: function(shape) {
        if (shape.index > 0) {
            Charts._swapShapeOrder(shape.layerIndex, shape.index, shape.index - 1);
            return true;
        } else {
            return false;
        }
    },

    /* Reverses the order of two shapes. */
    _swapShapeOrder: function(l, i, j) {
        var shapeI = Charts.layers[l][i];
        var shapeJ = Charts.layers[l][j];

        shapeI.index = j;
        shapeJ.index = i;

        Charts.layers[l][i] = shapeJ;
        Charts.layers[l][j] = shapeI;
    },

    /* Repositions all shapes in the chart. */
    reposition: function() {
        Charts.components.invoke('reposition');
        if (Charts.selectedComponent) {
            Charts.controlPoints = Charts.selectedComponent.getControlPoints();
        }
    },

    /** Redraws all shapes in the chart.
    function redraw() */
    redraw: function() {
        var context = Charts.ctx;

        context.save();
        var scale = Charts.textSizeMultiplier * Charts.canvasScale;
        context.scale(scale, scale);

        if (Charts.printing) {
            if (Prototype.Browser.Gecko) {
                context.fillStyle = '#ffffff';
                context.fillRect(0, 0, Charts.canvas.width, Charts.canvas.height);

                if (Charts.backgroundImageUrl) {
                    if (!Charts._backgroundImage) {
                        context.restore();
                        Charts._backgroundImage = new Image();
                        Charts._backgroundImage.src = Charts.backgroundImageUrl;
                        Charts._backgroundImage.onload = Charts.redraw;

                        return;
                    } else {
                        for (var x = 0; x < Charts.bounds.width; x += Charts._backgroundImage.width) {
                            for (var y = 0; y < Charts.bounds.height; y += Charts._backgroundImage.height) {
                                context.drawImage(Charts._backgroundImage, x, y, Charts._backgroundImage.width, Charts._backgroundImage.height);
                            }
                        }
                    }

                }
            }
        } else {
            context.clearRect(0, 0, Charts.canvas.width, Charts.canvas.height);
        }

        if (Charts.drawGrid && Charts.drawingArea) {
            context.strokeStyle = Charts.gridColor;
            context.lineWidth = 1;

            for (var x = .5; x < Charts.drawingArea.bottomRight.x; x += Charts.gridSize) {
                context.beginPath();
                context.moveTo(x, 0);
                context.lineTo(x, Charts.drawingArea.height);
                context.stroke();

            }
            for (var y = .5; y < Charts.drawingArea.bottomRight.y; y += Charts.gridSize) {
                context.beginPath();
                context.moveTo(0, y);
                context.lineTo(Charts.drawingArea.width, y);
                context.stroke();
            }
        }

        var layer;
        for (var i = 0, layerLen = Charts.layers.length; i < layerLen; ++i) {
            layer = Charts.layers[i];

            if (layer) {
                // draw the shapes
                for (var j = 0, len = layer.length; j < len; ++j) {
                    layer[j].draw(context);
                }
            }
        }

        Charts._finishRedraw(context);

        context.restore();
    },

    _finishRedraw: function(context) {},

    redrawNeeded: function(bounds) {
        this.needsRedraw = true;
    }
};
Charts.gridSize = 10;
Charts.drawGrid = false;
Charts.gridColor = 'rgba(20, 20, 20, .2)';
