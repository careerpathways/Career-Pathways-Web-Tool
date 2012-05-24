Charts = {
  whichi: function() { return 'view'; },
  draw: function(canvas_container, toolbar_container) {
	// in IE 8, this has to happen BEFORE we use a canvas
	if (window.G_vmlCanvasManager) {
		window.G_vmlCanvasManager.init_(document);
	}
		
    if (canvas_container) {
    	Charts.element = $(canvas_container);
    }
    else {
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
		titleImg: chTitleImg,
		skillset: chSkillset,
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
		}
		else {
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
		}
		else {
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

		if(Charts.drawingStatus == 'draft') {
			Charts.backgroundImageUrl = '/images/' + Charts.drawingStatus + '-overlay.png';
			// firefox crashes with this backgroung image
			if (!(Charts.printing && Prototype.Browser.Gecko)) {
				Charts.element.style.background = 'url(' + Charts.backgroundImageUrl + ')';
			}
		}
		
		// add the title image
		var title = document.createElement('div');
		title.className = 'chTitle';
		title.innerHTML = data.titleImg;
		title.style.zIndex = 100;
		title.style.position = 'absolute';
		title.style.top = 0;
		title.style.left = 0;
		Charts.element.appendChild(title);
		
		if(data.skillset) {
		  var skillset = document.createElement('div');
		  skillset.className = 'chSkillset';
		  skillset.innerHTML = data.skillset;
		  skillset.style.zIndex = 101;
		  skillset.style.position = 'absolute';
		  skillset.style.top = "19px";
		  skillset.style.left = 0;
		  Charts.element.appendChild(skillset);
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
    
    Charts.element.fire('chart:drawn', {chart: Charts});
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
		}
		else {
			return false;
		}
	},
	
	/** Moves a shape backwards in the stacking order. */
	moveShapeBackward: function(shape) {
		if (shape.index > 0) {
			Charts._swapShapeOrder(shape.layerIndex, shape.index, shape.index - 1);
			return true;
		}
		else {
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
	
	/** Redraws all shapes in the chart. */
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
					}
					else {
						for (var x = 0; x < Charts.bounds.width; x += Charts._backgroundImage.width) {
							for (var y = 0; y < Charts.bounds.height; y += Charts._backgroundImage.height) {
								context.drawImage(Charts._backgroundImage, x, y, Charts._backgroundImage.width, Charts._backgroundImage.height);
							}
						}
					}
					
				}
			}
		}
		else {
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

var Component = Class.create({
    getShape: function() {
    	return this.shape;
    }, 
    
    getLayer: function() {
    	return 0;
    }
});
Charts.gridSize = 10;
Charts.drawGrid = false;
Charts.gridColor = 'rgba(20, 20, 20, .2)';

var VERTICAL = 'v';
var HORIZONTAL = 'h';
var DEFAULT_COLOR = '333333';

var Widget = Class.create(Component, {
    type: '',
    id: 0,
    x: 0,
    y: 0,
    h: 0,
    w: 0,
          
	initialize: function(options) {
		this.config = {color: DEFAULT_COLOR};
		this.entity = true;
		Object.extend(this, options || {});
		
		this.type = this.getType();
		
		if (!chColor.include(this.config.color)) {
			this.config.color = DEFAULT_COLOR;
		}
		
		this.connectionIDs = [];
		this.outgoingConnections = new Hash();
		this.incomingConnections = new Hash();
		
		if (this.getElem) {
			Charts.element.appendChild(this.getElem());
			this.repositionElement();
			this._onContentChange();
		}
		
		if (this.setup) {
			this.setup();
		}
	},
    
    setColor: function(color) {
      this.color = color;       
    },
	
	/** Returns all connections (both incoming and outgoing). */
	getConnections: function() {
		// Array.concat isn't working in ie7
	   return [this.getIncomingConnections(), this.getOutgoingConnections()].flatten();
	},
	
	/** Registers an outgoing connection (a connection from this widget to another). */
	registerOutgoingConnection: function(connection) {
		this.outgoingConnections.set(connection.destination.id, connection);
	},
	
	/** Returns all outoing connections. */
	getOutgoingConnections: function() {
		return this.outgoingConnections.values();
	},
	
	/** Removes an outgoing connection. */
	unregisterOutgoingConnection: function(connection) {
		this.outgoingConnections.unset(connection.destination.id);
	},
	
	/** Registers an incoming connection (a connection from another widget to this widget). */
	registerIncomingConnection: function(connection) {
		this.incomingConnections.set(connection.source.id, connection);
	},
	
	/** Returns all incoming connections. */
	getIncomingConnections: function() {
		return this.incomingConnections.values();
	},
	
	/** Removes an incoming connection. */
	unregisterIncomingConnection: function(connection) {
		this.incomingConnections.unset(connection.source.id);
	},
	
	/** Returns true if this widget has a connection to the destination. */
	outgoingConnectionExists: function(destination) {
		return this.outgoingConnections.get(destination.id) != null;
	},
	
    reposition: function() {},
    
    getWidth: function() {
    	return parseInt(this.w);
    },
    
    getHeight: function() {
    	return parseInt(this.h);
    },
    
    getTop: function() {
    	return parseInt(this.y);
    },
    
    getLeft: function() {
    	return parseInt(this.x);
    },
    
    getBottom: function() {
    	return this.getTop() + this.getHeight();
    },
    
    getRight: function() {
    	return this.getLeft() + this.getWidth();
    }
});

Widget.toIntegerPoint = function(point) {
	return {
		x: parseInt(point.x),
		y: parseInt(point.y)
	};
};

ChartLine = Class.create(Widget, {
	getType: function() {
		return 'line';
	},
	
	setup: function() {
		var isVertical = this.config.direction && (this.config.direction == VERTICAL || this.config.direction == 'n' || this.config.direction == 's');
		var isOldVersion = false;
		if (!this.startPoint) {
			var a = {x: this.getLeft() + (isVertical ? 7 : 0), y: this.getTop() + (isVertical ? 0 : 7)};
			var b = Geometry.translatedPoint(a, isVertical ? 0 : this.getWidth(), isVertical ? this.getHeight() : 0);
			
			if (this.config.direction == 'n' || this.config.direction == 'w') {
				this.startPoint = b;
				this.endPoint = a;
			}
			else {
				this.startPoint = a;
				this.endPoint = b;
			}
		}
		else {
			this.startPoint = Widget.toIntegerPoint(this.startPoint);
			this.endPoint = Widget.toIntegerPoint(this.endPoint);
		}
	},
  
	getStartPoint: function() {
		return this.startPoint;
	},
  
	getEndPoint: function() {
		return this.endPoint;
	},
	
	createShape: function() {
		return new Path(
			[Geometry.ORIGIN, Geometry.ORIGIN],
			{
				color: '#' + this.config.color,
				lineWidth: 5,
				arrowheadAtEnd: this.arrowheadAtEnd
			}
		);
	},
  
	reposition: function() {
		this.shape.setPoints([
			this.getStartPoint(),
			this.getEndPoint()
		]);
		
		var bounds = this.shape.getBounds();
		this.x = bounds.x;
		this.y = bounds.y;
	}
});

ChartLine.THICKNESS = 3;

var Side = {
	TOP: 'n',
	BOTTOM: 's',
	RIGHT: 'e',
	LEFT: 'w'
};

var AnchorPoint = {
	TOP_LEFT: {side: Side.TOP, position: 0},
	TOP_CENTER: {side: Side.TOP, position: 50},
	TOP_RIGHT: {side: Side.TOP, position: 100},
	MIDDLE_LEFT: {side: Side.LEFT, position: 50},
	MIDDLE_RIGHT: {side: Side.RIGHT, position: 50},
	BOTTOM_LEFT: {side: Side.BOTTOM, position: 0},
	BOTTOM_CENTER: {side: Side.BOTTOM, position: 50},
	BOTTOM_RIGHT: {side: Side.BOTTOM, position: 100}
};

/***
 basic box functions
***/
ChartBox = Class.create(Widget, {
	getType: function() {
		return 'box';
	},
	
    elem: null,
    
	getElem: function() {
		/* NOTE: putting the class in the constructor, i.e. 
		 * new Element('div', {'class': 'ctepathwaysBox'}); doesn't work in IE
		 * 8 with Prototype earlier than v1.6.1. See
		 * https://prototype.lighthouseapp.com/projects/8886/tickets/529 */
		this.elem = new Element('div').addClassName('ctepathwaysBox');
		
		if (this.w > 0) this.elem.style.width = (this.getWidth() - 20) + 'px';

		this.titleElement = new Element('div').addClassName('ctepathwaysBoxTitle');
	    this.contentElement = new Element('div').addClassName('ctepathwaysBoxContent');
	    
	    this.elem.appendChild(this.titleElement);
	    this.elem.appendChild(this.contentElement);
	    
	    this.titleElement.update(this.config.title || '&nbsp;');
	    this.contentElement.update(this.config.content_html);
		
		
		this.elem.style.zIndex = "0";
		return this.elem;
    },
  
  createShape: function() {
  	this.borderThickness = 5;
  	this.innerRectangle = new Rectangle(
  		{x: 0, y: 0}, 0, 0,
  		{
  			fillColor: '#ffffff',
  			fill: true,
  			bottomLeftRadius: this.borderThickness,
  			bottomRightRadius: this.borderThickness
  		}
  	);
  	this.outerRectangle =  new Rectangle(
  		{x: 0, y: 0}, 0, 0,
  		{
  			fillColor: '#' + this.config.color,
  			strokeColor: '#F00000',
  			fill: true,
  			strokeWidth: 2,
  			radius: this.borderThickness * 2
  		}
  	);
  	
  	return new CompoundShape([this.outerRectangle, this.innerRectangle]);
  },
	
	getLayer: function() {
		return 1;
	},
	
    getHeight: function() {
    	return this.height;
    },
    
    /** Returns the position of the anchor point. */
    getAnchorPointPosition: function(anchorPoint) {
    	var result;
    	
    	switch (anchorPoint.side) {
    		case Side.TOP:
    			result = {y: this.getTop()};
    			break;
			case Side.BOTTOM:
				result = {y: this.getBottom()};
				break;
			case Side.LEFT:
				result = {x: this.getLeft()};
				break;
			case Side.RIGHT:
				result = {x: this.getRight()};
				break;
    	}
    	
    	switch (anchorPoint.side) {
    		case Side.TOP:
    		case Side.BOTTOM:
    			result.x = this.getLeft() + this.getWidth() * anchorPoint.position / 100;
    			break;
			case Side.LEFT:
			case Side.RIGHT:
				result.y = this.getTop() + this.getHeight() * anchorPoint.position / 100;
				break;
    	}
    	
    	return result;
    },
    
	repositionElement: function() {
		this.elem.style.top = (this.getTop()) * Charts.textSizeMultiplier + 'px';
		this.elem.style.left = (this.getLeft() + 10) * Charts.textSizeMultiplier + 'px';
		this.elem.style.width = (this.getWidth() - 20) * Charts.textSizeMultiplier + 'px';
		var titlePadding = 4 * Charts.textSizeMultiplier + 'px';
		this.titleElement.style.paddingTop = titlePadding;
		this.titleElement.style.paddingBottom = titlePadding;
	},
	
	_onContentChange: function() {
		this.titleHeight = this.titleElement.offsetHeight / Charts.textSizeMultiplier;
		this.contentHeight = this.contentElement.offsetHeight / Charts.textSizeMultiplier;
		this.height = this.titleHeight + this.contentHeight + 10;
	},
    
    reposition: function() {
    	var pos = {
    		x: this.getLeft(),
    		y: this.getTop()
    	};
    	
    	var thickness = this.borderThickness;
    	this.outerRectangle.reposition(pos, this.getWidth(), this.getHeight());
    	
    	this.innerRectangle.reposition(
    		Geometry.translatedPoint(pos, thickness, this.titleHeight),
    		this.getWidth() - thickness * 2,
    		this.getHeight() - thickness - this.titleHeight
    	);
    	
    	this.innerRectangle.setStyles({
    		bottomLeftRadius: this.borderThickness,
  			bottomRightRadius: this.borderThickness
    	});
    	
    	this.outerRectangle.setStyle('radius', thickness * 2);
    	
    	this.shape.recalculateBounds();
    	
    	this.getConnections().invoke('reposition');
    }
});

/**
  Connection -- a linking between two chart widgets, which can be a 
  composite of different view components
  {generic chUtil} source
  {generic chUtil} destination  
*/
var Connection = Class.create(Component, {
	sourceAnchorPoint: AnchorPoint.TOP_LEFT,
	destinationAnchorPoint: AnchorPoint.TOP_LEFT,
	numSegments: 3,
	sourceAxis: 'x',
	
	getType: function() {
		return 'connection';
	},
	
	initialize: function(source, destination, data) {
		this.data = data;
		this.source = source;
		this.destination = destination;
		this.color = this.source.config.color;
		
		this.startPoint = {};
		this.endPoint = {};
		
		this.chart = Charts;
		
		if (data) {
			this.id = data.id;
			this.sourceAnchorPoint = {side: data.source_side, position: data.source_position};
			this.destinationAnchorPoint = {side: data.destination_side, position: data.destination_position};
			this.numSegments = parseInt(data.num_segments);
			this.sourceAxis = data.source_axis;
			this.color = data.color;
		}
		
		//connections must be registered with thier respective boxes
		this.source.registerOutgoingConnection(this);
		this.destination.registerIncomingConnection(this);
		
		this.type = 'connection';
	},
	
	createShape: function() {
		return new Path(
			[Geometry.ORIGIN, Geometry.ORIGIN],
			{
				arrowheadAtEnd: true,
				lineWidth: 5,
				color: '#' + this.color
			}
		);
	},
	
	reposition: function() {
		var startPoint = this.source.getAnchorPointPosition(this.sourceAnchorPoint);
    	var endPoint = this.destination.getAnchorPointPosition(this.destinationAnchorPoint);
    	var midPoint;
    	
    	if (this.numSegments < 3 && this.numSegments > 0) {
    		if (this.sourceAxis == 'x') {
    			midPoint = {x: endPoint.x, y: startPoint.y};
    		}
    		else {
    			var midPoint = {x: startPoint.x, y: endPoint.y};
    		}
    	}
		
		var points;
		
		if (this.numSegments == 0) {
			points = [startPoint, endPoint];
		}
    	else if (this.numSegments == 1) {
    		points = [startPoint, midPoint];
    	}
    	else {
		    if (this.numSegments == 2) {
		    	points = [startPoint, midPoint, endPoint];
		    }
		    else if (this.numSegments == 3) {
			    if (this.sourceAxis == 'x') {
				    var jointPoint1 = {x: (startPoint.x + endPoint.x) / 2, y: startPoint.y};
				    var jointPoint2 = {x: jointPoint1.x, y: endPoint.y};
			    }
			    else {
			    	var jointPoint1 = {x: startPoint.x, y: (startPoint.y + endPoint.y) / 2};
			    	var jointPoint2 = {x: endPoint.x, y: jointPoint1.y};
			    }
				points = [startPoint, jointPoint1, jointPoint2, endPoint];
		    }
    	}
    	
    	this.shape.setPoints(points);
		
		this.bounds = this.shape.getBounds();
		
		this.startPoint.x = startPoint.x;
		this.startPoint.y = startPoint.y;
		
		this.endPoint.x = endPoint.x;
		this.endPoint.y = endPoint.y;
	}
});

/* MATH
******************************************************************************/
Object.extend(Math, {
	minArray: function(array) {
		return Math.min.apply(Math, array);
	},
	
	maxArray: function(array) {
		return Math.max.apply(Math, array);
	}
});


/* GEOMETRY
******************************************************************************/
var Geometry = {
	ORIGIN: {x: 0, y: 0},
	
	bounds: function(a, b) {
		var points = $A(arguments);
		return new Geometry.Bounds(points);
	},
	
	/** A rectangle that surrounds a set of points. */
	Bounds: Class.create({
		/** Creates a boundary object for a set of points, with an optional 
		 *  fudge factor for containment checking.
		 */
		initialize: function(points, fudge) {
			this.fudge = fudge || 0; 
			var xCoords = points.pluck('x');
			var yCoords = points.pluck('y');
			
			this.left = this.x = Math.minArray(xCoords);
     		this.top = this.y = Math.minArray(yCoords);
     		
     		this.topLeft = {
     			x: this.x,
     			y: this.y
     		}
     		
     		this.right = Math.maxArray(xCoords);
     		this.bottom = Math.maxArray(yCoords);
     		
     		this.bottomRight = {
     			x: this.right,
     			y: this.bottom
     		}
     		
     		this.width = this.right - this.left;
     		this.height = this.bottom - this.top;
		},
		
		/** Returns whether the point is within the fudge factor of the bounds. */
		contains: function(point) {
			if (this.fudge <= 0) {
				return Geometry.gte(point, this.topLeft) && Geometry.lte(point, this.bottomRight);
			}
			else {
				return Geometry.gte(point, Geometry.translatedPoint(this.topLeft, -this.fudge, -this.fudge)) && Geometry.lte(point, Geometry.translatedPoint(this.bottomRight, this.fudge, this.fudge));
			}
		},
		
		/** Returns a new bounds the includes the current bounds and the parameter. */
		compound: function(that) {
			return new Geometry.Bounds([
				this.topLeft,
				that.topLeft,
				this.bottomRight,
				that.bottomRight
			]);
		},
		
		/** Returns a new bounds that include the current points and the new points. */
		expanded: function(points) {
			return new Geometry.Bounds(points).compound(this);
		},
		
		/** Returns the top left and bottom right points of the boundary. */
		getPoints: function() {
			return [this.topLeft, this.bottomRight];
		}
	}),
	
	/** Returns the difference between the start and and end points, as an x,y tuple. */
	deltas: function(start, end) {
		return {
			x: end.x - start.x,
			y: end.y - start.y
		};
	},
	
	/** Returns the distance from the point to the origin. */
	length: function(delta) {
		return Math.sqrt(delta.x * delta.x + delta.y * delta.y);
	},
	
	/** Returns the distance between two points. */
	abs: function(a, b) {
		return Geometry.length(Geometry.deltas(a, b));
	},
	
	/** Returns a point translated by another point or x and y amounts. */
	translatedPoint: function(point) {
		if (arguments.length == 3) {
			return {
				x: point.x + arguments[1],
				y: point.y + arguments[2]
			}
		}
		else {
			return {
				x: point.x + arguments[1].x,
				y: point.y + arguments[1].y
			}
		}
	},
	
	/** Returns a point scaled by a factor. */
	scaledPoint: function(point, factor) {
		return {
			x: point.x * factor,
			y: point.y * factor
		};
	},
	
	/** Returns whether the first argument is positioned the same or to the
	 *  bottom right of the second argument.
	 */
	gte: function(lhs, rhs) {
		return lhs.x >= rhs.x && lhs.y >= rhs.y;
	},
	
	/** Returns whether the first argument is positioned the same or to the
	 *  top left of the second argument.
	 */
	lte: function(lhs, rhs) {
		return lhs.x <= rhs.x && lhs.y <= rhs.y;
	}
};

var ARROW_LENGTH_MULTIPLIER = 1.5;
var ARROW_THICKNESS_MULTIPLIER = 3.5;

/* ABSTRACT SHAPE
******************************************************************************/
var AbstractShape = Class.create({
	/** Returns the shape's boundary rectangle. */
	getBounds: function() {
		return this.bounds;
	},
	
	/** Resets all styles or a sets single style property of the shape. */
	setStyle: function(style) {
		if (arguments.length == 2) {
			var previousValue = this.style[arguments[0]];
			this.style[arguments[0]] = arguments[1];
			return previousValue;
		}
		else {
			this.style = style;
		}
		
		this.onStyleChange(this.style);
	},
	
	/** Sets a set of styles on the shape. */
	setStyles: function(styles) {
		$H(styles).each(function(entry) {
			this.style[entry.key] = entry.value;
		}.bind(this));
		
		this.onStyleChange(this.style);
	},
	
	/** Called when the style of the shape changes. */
	onStyleChange: function() {}
});

/* PATH
******************************************************************************/
var Path = Class.create(AbstractShape, {
	initialize: function(points, style) {
		this.setPoints(points);
		this.style = style;
	},
	
	/** Sets the points that comprise the path. */
	setPoints: function(points) {
		this.points = points;
		
		this.refreshPoints();
	},
	
	/** Refreshes the points if they have changed outside of the path. */
	refreshPoints: function() {
		var points = this.points;
		if (points.length > 0) {
			var previousPoint = points[0];
			
			var point;
			
			for (var i = 1, len = points.length; i < len; ++i) {
				point = points[i];
				point.delta = Geometry.deltas(previousPoint, point);
				point.length = Geometry.length(point.delta);
				point.theta = Math.atan2(point.delta.y, point.delta.x);
				
				previousPoint = point;
			}
		}
		
		this.bounds = new Geometry.Bounds(points, 6);
	},
	
	/** Returns the path's points. */
	getPoints: function() {
		return this.points;
	},
	
	draw: function(context) {
		var arrowheadAtEnd = this.style.arrowheadAtEnd;
		var arrowheadAtStart = this.style.arrowheadAtStart;
		
		context.lineWidth = this.style.lineWidth || 1;
		context.lineCap = 'round';
		context.strokeStyle = context.fillStyle = this.style.color;
		
		context.beginPath();
		
		var previousPoint = this.points[0];
		var point;
		
		context.save();
		if (context.lineWidth % 2 == 1) {
			context.translate(-.5, -.5);
		}
		
		context.moveTo(Math.round(previousPoint.x), Math.round(previousPoint.y));
		
		if (arrowheadAtEnd) {
			var arrowLength = context.lineWidth * ARROW_LENGTH_MULTIPLIER;
			var arrowThickness = context.lineWidth * ARROW_THICKNESS_MULTIPLIER;
		}
		
		var arrowheadThisSegment;
		
		for (var k = 1, len = this.points.length; k < len; ++k) {
			point = this.points[k];
			arrowheadThisSegment = (k == len - 1 && arrowheadAtEnd);
			
			var endPoint;
			if (arrowheadThisSegment) {
				endPoint = {
					x: previousPoint.x + Math.cos(point.theta) * (point.length - arrowLength),
					y: previousPoint.y + Math.sin(point.theta) * (point.length - arrowLength)
				}
			}
			else {
				endPoint = point;
			}
			context.lineTo(Math.round(endPoint.x), Math.round(endPoint.y));
			
			previousPoint = point;
		}
		
		context.stroke();
		
		if (arrowheadAtEnd) {
			context.translate(previousPoint.x, previousPoint.y);
			context.rotate(point.theta);
			context.translate(-previousPoint.x, -previousPoint.y);
			
			context.beginPath();
			context.moveTo(point.x - arrowLength, point.y - arrowThickness / 2);
			context.lineTo(point.x, point.y);
    		context.lineTo(point.x - arrowLength, point.y + arrowThickness / 2);
    		context.fill();
		}
		
		context.restore();
	},
	
	contains: function(point) {
		if (this.bounds.contains(point)) {
			// FIXME implement
		}
	},
	
	// TODO test
	intersects: function(line) {
		if (this.start.x == line.start.x && this.start.y == line.start.y ||
		    this.start.x == line.end.y && this.start.y == line.end.y ||
		    this.end.x == line.start.x && this.start.y == line.start.y ||
		    this.end.x == line.end.y && this.start.y == line.end.y) {
			return false;
		}
		
		var a = this.start;
		var b = Geometry.translatedPoint(this.end, -a.x, -a.y);
		var c = Geometry.translatedPoint(line.start, -a.x, -a.y);
		var d = Geometry.translatedPoint(line.end, -a.x, -a.y);
		
		var cos = b.x / this.length;
		var sin = b.y / this.length;
		
		c = {
			x: c.x * cos + c.y * sin,
			y: c.y * cos - c.x * sin
		};
		
		d = {
			x: d.x * cos + d.y * sin,
			y: d.y * cos + d.x * sin
		};
		
		if (c.x < 0 && d.x < 0 || c.x > 0 && d.x > 0) {
			return false;
		}
		
		var position = d.x + (c.x - d.x) * d.y / (d.y - c.y);
		
		if (position < 0 || position > this.length) {
			return false;
		}
	}
});

/* COMPOUND SHAPE
******************************************************************************/
var CompoundShape = Class.create(AbstractShape, {
	initialize: function(components) {
		this.bounds = null;
		this.resetShapes(components);
	},
	
	/** Returns the points that make up all of the components. */
	getPoints: function() {
		return this.components.invoke('getPoints').flatten();
	},
	
	/** Adds a component to the compound shape, expanding the bounding
	 *  rectangle to include the new shape.
	 */
	addShape: function(component) {
		this.components.push(component);
		if (this.bounds == null) {
			this.bounds = component.getBounds();
		}
		else {
			this.bounds = this.bounds.compound(component.getBounds());
		}
	},
	
	/** Replaces all current shapes in the compound shape. */
	resetShapes: function(components) {
		this.components = components || [];
		
		this.recalculateBounds();
	},
	
	/** Recalculates the boundary rectangle. Used if the shapes that comprise
	 *  the compound shape are repositioned.
	 */
	recalculateBounds: function() {
		this.bounds = new Geometry.Bounds(this.components.invoke('getBounds').invoke('getPoints').flatten());
	},
	
	draw: function(context) {
		this.components.invoke('draw', context);
	},
	
	/** Returns true if any of the shapes that comprise the compound shape
	 *  contain the point.
	 */
	contains: function(point) {
		return
			this.bounds.contains(point) &&
			this.components.any(function(component) {return component.contains(point);});
	}
});

/* RECTANGLE
******************************************************************************/
var Rectangle = Class.create(AbstractShape, {
	initialize: function(position, width, height, style) {
		this.reposition(position, width, height);
		this.setStyle(style);
	},
	
	reposition: function(position, width, height) {
		this.position = position;
		this.width = width;
		this.height = height;
		
		this.bounds = Geometry.bounds(position, Geometry.translatedPoint(position, width, height));
	},
	
	onStyleChange: function(style) {
		if (style.radius) {
			style.topLeftRadius = style.topRightRadius = style.bottomLeftRadius = style.bottomRightRadius = style.radius;
		}
		else {
			style.topLeftRadius = style.topLeftRadius || 0;
			style.topRightRadius = style.topRightRadius || 0;
			style.bottomLeftRadius = style.bottomLeftRadius || 0;
			style.bottomRightRadius = style.bottomRightRadius || 0;
		}
	},
	
	draw: function(context) {
		if (this.style.strokeWidth){
			context.lineWidth = this.style.strokeWidth;
		}

		if (this.style.fillColor) {
			context.fillStyle = this.style.fillColor;
		}
		if (this.style.strokeColor) {
			context.strokeStyle = this.style.strokeColor;
		}
		
		var x = this.position.x;
		var y = this.position.y;
		var width = this.width;
		var height = this.height;
		var topLeftRadius = this.style.topLeftRadius;
		var topRightRadius = this.style.topRightRadius;
		var bottomLeftRadius = this.style.bottomLeftRadius;
		var bottomRightRadius = this.style.bottomRightRadius;
		context.beginPath();
		context.moveTo(x, y + topLeftRadius);
		context.lineTo(x, y + height - bottomLeftRadius);
		context.quadraticCurveTo(x, y + height, x + bottomLeftRadius, y + height);
		context.lineTo(x + width - bottomRightRadius, y + height);
		context.quadraticCurveTo(x + width, y + height, x + width, y + height - bottomRightRadius);
		context.lineTo(x + width, y + topRightRadius);
		context.quadraticCurveTo(x + width, y, x + width - topRightRadius, y);
		context.lineTo(x + topLeftRadius, y);
		context.quadraticCurveTo(x, y, x, y + topLeftRadius);
		if (this.style.fill) {
			context.fill();
		}
		if (this.style.stroke) {
			context.stroke();
		}
		context.lineWidth = 1;
	}
});

/* TEXT SIZE MONITOR
******************************************************************************/

var TextSizeMonitor = Class.create({
	initialize: function(parentElement) {
		this.parentElement = $(parentElement);
		
		this.element = document.createElement('span');
		this.element.id = 'textSizeMonitor' + TextSizeMonitor.index++;
		this.element.innerHTML = '&nbsp;';
		this.element.style.position = 'absolute';
		this.element.style.left = '-10000px';
		
		this.parentElement.insertBefore(this.element, this.parentElement.firstChild);
		this.currentSize = this.getSize();
		this.baseSize = this.currentSize;
	},
	
	start: function() {
		if (!this.interval) {
			this.interval = window.setInterval(this._check.bind(this), TextSizeMonitor.DELAY);
		}
	},
	
	stop: function() {
		if (this.interval) {
			window.clearInterval(this.interval);
			this.interval = null;
		}
	},
	
	getBaseSize: function() {
		return this.baseSize;
	},
	
	getSize: function() {
		return this.element.offsetHeight;
	},
	
	_check: function() {
		var newSize = this.getSize();
		if (newSize !== this.currentSize) {
			var previousSize = this.currentSize;
			this.currentSize = newSize;
			this.parentElement.fire('text:resized', {
				previousSize: previousSize,
				currentSize: newSize,
				baseSize: this.baseSize,
				monitor: this
			});
		}
	}
});

TextSizeMonitor.index = 1;
TextSizeMonitor.DELAY = 500;
