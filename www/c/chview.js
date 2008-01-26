<?
header("Content-type: text/javascript");
?>

    var cssstr = "<?
$css = <<<EOT
.brush_%c {
  background: url('"+base_url+"/i/%c/brush.png') no-repeat;
  filter:progid:DXImageTransform.Microsoft.AlphaImageLoader(src='"+base_url+"/i/%c/brush.png',sizingMethod='image');
  !background: none;
}
.line_%c td.c, .arrow_%c td.c {
  background-color: #%c;
}
.line_%c.v td.n, .arrow_%c.s td.n {
  background: url('"+base_url+"/i/%c/cap_n.png') no-repeat;
  filter:progid:DXImageTransform.Microsoft.AlphaImageLoader(src='"+base_url+"/i/%c/cap_n.png',sizingMethod='image');
  !background: none;
}
.line_%c.v td.s, .arrow_%c.n td.s {
  background: url('"+base_url+"/i/%c/cap_s.png') no-repeat;
  filter:progid:DXImageTransform.Microsoft.AlphaImageLoader(src='"+base_url+"/i/%c/cap_s.png',sizingMethod='image');
  !background: none;
}
.line_%c.h td.e, .arrow_%c.w td.e {
  background: url('"+base_url+"/i/%c/cap_e.png') no-repeat;
  filter:progid:DXImageTransform.Microsoft.AlphaImageLoader(src='"+base_url+"/i/%c/cap_e.png',sizingMethod='image');
  !background: none;
}
.line_%c.h td.w, .arrow_%c.e td.w {
  background: url('"+base_url+"/i/%c/cap_w.png') no-repeat;
  filter:progid:DXImageTransform.Microsoft.AlphaImageLoader(src='"+base_url+"/i/%c/cap_w.png',sizingMethod='image');
  !background: none;
}
.arrow_%c.n td.ne {
  background: url('"+base_url+"/i/%c/arrn_ne.png') no-repeat;
  filter:progid:DXImageTransform.Microsoft.AlphaImageLoader(src='"+base_url+"/i/%c/arrn_ne.png',sizingMethod='image');
  !background: none;
}
.arrow_%c.n td.n {
  background: url('"+base_url+"/i/%c/arrn_n.png') no-repeat;
  filter:progid:DXImageTransform.Microsoft.AlphaImageLoader(src='"+base_url+"/i/%c/arrn_n.png',sizingMethod='image');
  !background: none;
}
.arrow_%c.n td.nw {
  background: url('"+base_url+"/i/%c/arrn_nw.png') no-repeat;
  filter:progid:DXImageTransform.Microsoft.AlphaImageLoader(src='"+base_url+"/i/%c/arrn_nw.png',sizingMethod='image');
  !background: none;
}
.arrow_%c.s td.se {
  background: url('"+base_url+"/i/%c/arrs_se.png') no-repeat;
  filter:progid:DXImageTransform.Microsoft.AlphaImageLoader(src='"+base_url+"/i/%c/arrs_se.png',sizingMethod='image');
  !background: none;
}
.arrow_%c.s td.s {
  background: url('"+base_url+"/i/%c/arrs_s.png') no-repeat;
  filter:progid:DXImageTransform.Microsoft.AlphaImageLoader(src='"+base_url+"/i/%c/arrs_s.png',sizingMethod='image');
  !background: none;
}
.arrow_%c.s td.sw {
  background: url('"+base_url+"/i/%c/arrs_sw.png') no-repeat;
  filter:progid:DXImageTransform.Microsoft.AlphaImageLoader(src='"+base_url+"/i/%c/arrs_sw.png',sizingMethod='image');
  !background: none;
}
.arrow_%c.e td.ne {
  background: url('"+base_url+"/i/%c/arre_ne.png') no-repeat;
  filter:progid:DXImageTransform.Microsoft.AlphaImageLoader(src='"+base_url+"/i/%c/arre_ne.png',sizingMethod='image');
  !background: none;
}
.arrow_%c.e td.e {
  background: url('"+base_url+"/i/%c/arre_e.png') no-repeat;
  filter:progid:DXImageTransform.Microsoft.AlphaImageLoader(src='"+base_url+"/i/%c/arre_e.png',sizingMethod='image');
  !background: none;
}
.arrow_%c.e td.se {
  background: url('"+base_url+"/i/%c/arre_se.png') no-repeat;
  filter:progid:DXImageTransform.Microsoft.AlphaImageLoader(src='"+base_url+"/i/%c/arre_se.png',sizingMethod='image');
  !background: none;
}
.arrow_%c.w td.nw {
  background: url('"+base_url+"/i/%c/arrw_nw.png') no-repeat;
  filter:progid:DXImageTransform.Microsoft.AlphaImageLoader(src='"+base_url+"/i/%c/arrw_nw.png',sizingMethod='image');
  !background: none;
}
.arrow_%c.w td.w {
  background: url('"+base_url+"/i/%c/arrw_w.png') no-repeat;
  filter:progid:DXImageTransform.Microsoft.AlphaImageLoader(src='"+base_url+"/i/%c/arrw_w.png',sizingMethod='image');
  !background: none;
}
.arrow_%c.w td.sw {
  background: url('"+base_url+"/i/%c/arrw_sw.png') no-repeat;
  filter:progid:DXImageTransform.Microsoft.AlphaImageLoader(src='"+base_url+"/i/%c/arrw_sw.png',sizingMethod='image');
  !background: none;
}
.box_%c td.nw {
  background: url('"+base_url+"/i/%c/tbox_nw.png') no-repeat;
  filter:progid:DXImageTransform.Microsoft.AlphaImageLoader(src='"+base_url+"/i/%c/tbox_nw.png',sizingMethod='crop');
  !background: none;
}
.box_%c td.n {
  background-color: #%c;
}
.box_%c td.ne {
  background: url('"+base_url+"/i/%c/tbox_ne.png') no-repeat;
  filter:progid:DXImageTransform.Microsoft.AlphaImageLoader(src='"+base_url+"/i/%c/tbox_ne.png',sizingMethod='crop');
  !background: none;
}
.box_%c td.c {
  background: #fff;
}
.box_%c td.w {
  background: url('"+base_url+"/i/%c/tbox_w.png') repeat-y;
  filter:progid:DXImageTransform.Microsoft.AlphaImageLoader(src='"+base_url+"/i/%c/tbox_w.png',sizingMethod='scale');
  !background: none;
}
.box_%c td.e {
  background: url('"+base_url+"/i/%c/tbox_e.png') right repeat-y;
  filter:progid:DXImageTransform.Microsoft.AlphaImageLoader(src='"+base_url+"/i/%c/tbox_e.png',sizingMethod='scale');
  !background: none;
}
.box_%c td.sw {
  background: url('"+base_url+"/i/%c/tbox_sw.png') no-repeat;
  filter:progid:DXImageTransform.Microsoft.AlphaImageLoader(src='"+base_url+"/i/%c/tbox_sw.png',sizingMethod='image');
  !background: none;
}
.box_%c td.s {
  background: url('"+base_url+"/i/%c/tbox_s.png') bottom repeat-x;
  filter:progid:DXImageTransform.Microsoft.AlphaImageLoader(src='"+base_url+"/i/%c/tbox_s.png',sizingMethod='scale');
  !background: none;
}
.box_%c td.se {
  background: url('"+base_url+"/i/%c/tbox_se.png') no-repeat;
  filter:progid:DXImageTransform.Microsoft.AlphaImageLoader(src='"+base_url+"/i/%c/tbox_se.png',sizingMethod='image');
  !background: none;
}
EOT;
echo preg_replace('/\n\s*/', '', $css);
?>";

    var css = '';
    chColor.push('333333');
    chColor.each(function(color) {
    	css += cssstr.replace(/%c/g, color);
    });
    document.write('<style type="text/css">' + css + '</style>');

function inspect(e) {
  var ret = '';
  for (var n in e) ret += n + ': ' + e[n] + "\n";
  return ret;
}
Charts = {
  boxes: new Hash(),
  widgets: new Hash(),
  connections: new Hash(),
  drawing_status: drawing_status,
  whichi: function() { return 'view'; },
  draw: function(canvas_container, toolbar_container) {
    this.css = css;  
  
    if (canvas_container) {
    	Charts.canvas = $(canvas_container);
    }
    else {
    	Charts.canvas = $(document.body);
    }
    if (toolbar_container) {
    	Charts.toolbarContainer = $(toolbar_container);
    }
    
    Charts.canvas.addClassName('yui-skin-sam');

	if( Charts.drawing_status == "outdated" || Charts.drawing_status == "draft" ) {
		// the whichi method appears to be inherited differently on ie7/firefox 
		//if( this.whichi() == 'view' ) {
			Charts.canvas.style.backgroundImage = "url(/images/" + Charts.drawing_status + "-overlay.png)";
		//}
	}

	// add the title image
    var title = document.createElement('div');
	title.className = 'chTitle';
	title.innerHTML = chTitleImg;
    Charts.canvas.appendChild(title);

	// add all the drawing elements
    chData.each(function(elemObjData) {   
      var elemObj = Charts.createWidget(elemObjData);      
      if(elemObj){
        //register component by ID within this chart
        Charts.widgets.set(elemObj.id, elemObj);
        
        if (elemObj.type == 'box') {
        	Charts.boxes.set(elemObj.id, elemObj);
        } 
      }              
    });
    //once all widgets are translated and drawn we can draw links between them (currently only boxes support this feature)
    connections.each(function(data) {         
   		var source = Charts.widgets.get(data.source_object_id);
   		var destination = Charts.widgets.get(data.destination_object_id);
         var connection = new Connection(source, destination, data);
         Charts.canvas.appendChild( connection.getElem() );
    });
    
    Charts.canvas.fire('chart:drawn', {chart: Charts});
  },
  
  createWidget: function(config) {
    var object = null;
    switch (config['type']) {
    case 'line':
      object = new chLine(config);
      break;
    case 'arrow':
      object = new chArrow(config);
      break;
    case 'box':
      object = new chBox(config);
      break;    
    }
    
    Charts.canvas.appendChild(object.getElem());
    
    if (!config['id'] && this.getID) this.getID(config, object, function() {
    	document.fire('widget:created', {widget: object});
    });
    else {
    	document.fire('widget:created', {widget: object});
    }
    
    if (object.type == 'box') {
    	this.addBox(object);
    }
    
    return object
  },
  
  addBox: function(box) {
  	this.boxes.set(box.id, box);
  },
  
  removeBox: function(box) {
  	this.boxes.unset(box.id);
  }
}

var VERTICAL = 'v';
var HORIZONTAL = 'h';
var DEFAULT_COLOR = '333333';

var Widget = Class.create({
    type: '',
    id: 0,
    x: 0,
    y: 0,
    h: 0,
    w: 0,
    elem: null,
    html: '<table cellspacing="0" cellpadding="0">' + 
            '<tr>' + 
              '<td class="nw"></td>' + 
              '<td class="n"></td>' + 
              '<td class="ne"></td>' +
            '</tr>' + 
            '<tr>' + 
              '<td class="w"></td>' + 
              '<td class="c"></td>' + 
              '<td class="e"></td>' + 
            '</tr>' + 
            '<tr>' + 
              '<td class="sw"></td>' + 
              '<td class="s"></td>' + 
              '<td class="se"></td>' + 
            '</tr>' + 
          '</table>',
          
    initialize: function(config) {
      this.config = {color: '333333'};
      this.entity = true;
      for (var c in config) {
           if( c == 'config' ) {
			   if( !in_array(config[c].color, chColor) ) {
				   config[c].color = '333333';
			   }
           }
           this[c] = config[c];
      }
      this.handles = {};
	  this.connectionIDs = [];
	  this.connections = new Hash();
	  this.outgoingConnections = new Hash();
	  this.incomingConnections = new Hash();
    },
    
    getElem: function() {
      this.createElement();
      
      this.setupHandles();
      
      return this.setupElem(this.elem);
    },
    
    setupHandles: function() {
      var handles = this.elem.getElementsByTagName('td');
      for (var h = 0; h < handles.length; h++) {
      	var handle = handles[h];
        this.handles[handle.className] = handle;
        handle.innerHTML = '<img src="' + base_url + '/images/blank.gif" height="1" width="1" class="' + handle.className + '"/>';
        
        this.setupHandle(handle);
      }
    },
    
    setupHandle: function(handle) {
    	
    },
    
    createElement: function() {
      var div = document.createElement('div');
      div.innerHTML = this.html;
      this.elem = $(div.firstChild);
      
      this.elem.className = this.type;
      this.elem.style.top = this.y + 'px';
      this.elem.style.left = this.x + 'px';
      this.assignHeight();
      if (this.w > 0) this.elem.style.width = this.w + 'px';
    },
    
    setColor: function(color) {
      if (this.elem.className.indexOf(this.color) != -1) {
      	this.elem.className = this.elem.className.replace(this.color, color);
      }
      else {
      	this.elem.addClassName('c_' + color);
      }
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
	
    redrawConnections: function(){ }, //do nothing by default to update one's connections
    
    getWidth: function() {
    	return parseInt(this.w);
    },
    
    getHeight: function() {
    	return this.elem.offsetHeight;
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

/** Mixin to allow the height of an object to be resized. */
var HeightResizable = {
    assignHeight: function() {
    	if (this.h > 0) this.elem.style.height = this.h + 'px';
    }
};

chLine = Class.create(Widget, HeightResizable, {
  direction: null,
  setupElem: function(elem) {
    if (this.h > this.w) this.setDirection(VERTICAL);
    else this.setDirection(HORIZONTAL);
    return elem;
  },
  
  setDirection: function(direction) {
    if (direction == 'n' || direction == 's' || direction == VERTICAL) direction = VERTICAL;
    else direction = HORIZONTAL;
    this.direction = direction;
    this.elem.className = 'line ' + direction + ' line_' + (this.config.color ? this.config.color : DEFAULT_COLOR);
    if (direction == VERTICAL) this.elem.style.width = '15px';
    else this.elem.style.height = '15px';
  }
});

chLine.THICKNESS = 3;

/***
  basic arrow functions
**/
chArrow = Class.create(Widget, HeightResizable, {
  direction: null,
  setupElem: function(elem) {
    this.setDirection(this.config.direction);
    return elem;
  },
  
  setDirection: function(direction) {
    this.direction = direction;
    this.elem.className = 'arrow ' + direction + ' arrow_' + (this.config.color ? this.config.color : DEFAULT_COLOR);
    if (direction == 'n' || direction == 's') this.elem.style.width = '15px';
    else this.elem.style.height = '15px';
  }
});

var Side = {
	NORTH: 'n',
	SOUTH: 's',
	EAST: 'e',
	WEST: 'w'
};

var AnchorPoint = {
	TOP_LEFT: {side: Side.NORTH, position: 0},
	TOP_CENTER: {side: Side.NORTH, position: 50},
	TOP_RIGHT: {side: Side.NORTH, position: 100},
	MIDDLE_LEFT: {side: Side.WEST, position: 50},
	MIDDLE_RIGHT: {side: Side.EAST, position: 50},
	BOTTOM_LEFT: {side: Side.SOUTH, position: 0},
	BOTTOM_CENTER: {side: Side.SOUTH, position: 50},
	BOTTOM_RIGHT: {side: Side.SOUTH, position: 100}
};

/***
 basic box functions
***/
chBox = Class.create(Widget, {
  setupElem: function(elem) {
    this.elem.className = 'box ' + (this.config.color ? ' box_' + this.config.color : ' box_333333');    
    this.handles['n'].innerHTML = this.config.title;
    this.handles['c'].innerHTML = this.config.content_html;
    return elem;
  },
    
    /** Removes all connections and adds them again. */
    redrawConnections: function() {
        this.getConnections().invoke('redraw');
    },
    
    /** Returns the position of the anchor point. */
    getAnchorPointPosition: function(anchorPoint) {
    	var result;
    	
    	switch (anchorPoint.side) {
    		case Side.NORTH:
    			result = {y: this.getTop()};
    			break;
			case Side.SOUTH:
				result = {y: this.getBottom()};
				break;
			case Side.WEST:
				result = {x: this.getLeft()};
				break;
			case Side.EAST:
				result = {x: this.getRight()};
				break;
    	}
    	
    	switch (anchorPoint.side) {
    		case Side.NORTH:
    		case Side.SOUTH:
    			result.x = this.getLeft() + this.getWidth() * anchorPoint.position / 100;
    			break;
			case Side.WEST:
			case Side.EAST:
				result.y = this.getTop() + this.getHeight() * anchorPoint.position / 100;
				break;
    	}
    	
    	return result;
    },
    
    assignHeight: function() {
          // don't set the height of boxes!
    }
});

var Geometry = {
	bounds: function(a, b) {
		return new Geometry.Bounds(a, b);
	},
	
	Bounds: Class.create({
		initialize: function(a, b) {
			this.left = Math.min(a.x, b.x);
     		this.top = Math.min(a.y, b.y);
     		this.right = Math.max(a.x, b.x);
     		this.bottom = Math.max(a.y, b.y);
		},
		
		contains: function(point) {
			return point.x >= this.left && point.x <= this.right && point.y >= this.top && point.y <= this.bottom;
		}
	}),
	
	deltas: function(start, end) {
		return {
			x: end.x - start.x,
			y: end.y - start.y
		};
	},
	
	length: function(delta) {
		return Math.sqrt(delta.x * delta.x + delta.y * delta.y);
	},
	
	translatedPoint: function(point, x, y) {
		return {
			x: point.x + x,
			y: point.y + y
		}
	}
};

var ARROW_LENGTH = 8;
var ARROW_THICKNESS = 18;

var Line = Class.create({
	initialize: function(start, end, style) {
		this.start = start;
		this.end = end;
		this.style = style;
		
		this.delta = Geometry.deltas(start, end);
		
		this.bounds = Geometry.bounds(start, end);
		
		this.length = Geometry.length(this.delta);
		
		this.theta = Math.atan2(this.delta.y, this.delta.x);
	},
	
	draw: function(context) {
		var start = this.start;
		var end = this.end;
		var isArrow = this.style.drawArrow;
		
		var length = Math.sqrt(deltaX * deltaX + deltaY * deltaY);
		
		context.save();
		context.translate(start.x, start.y);
		context.rotate(this.theta);
		context.translate(-start.x, -start.y);
		
		context.strokeStyle = this.ctx.fillStyle = this.style.color;
		
		context.beginPath();
		context.moveTo(start.x, start.y);
		
		if (this.style.dashed) {
			var penDown = true;
			for (var i = 0; i < length - (isArrow ? ARROW_LENGTH : 10); i += 10) {
				if (penDown) {
					context.lineTo(start.x + i + 10, start.y);
				}
				else {
					context.moveTo(start.x + i + 10, start.y);
				}
				
				penDown = !penDown;
			}
			if (penDown) {
				context.lineTo(start.x + length - (isArrow ? ARROW_LENGTH : 10), start.y);
			}
		}
		else {
			context.lineTo(start.x + length - (isArrow ? ARROW_LENGTH : 0), start.y);
		}
		
		context.stroke();
		
		if (isArrow) {
			context.beginPath();
			context.moveTo(start.x + length - ARROW_LENGTH, start.y - ARROW_THICKNESS / 2);
			context.lineTo(start.x + length, start.y);
    		context.lineTo(start.x + length - ARROW_LENGTH, start.y + ARROW_THICKNESS / 2);
    		context.fill();
		}
		
		context.restore();
	}/* UNTESTED,
	
	contains: function(point) {
		if (this.bounds.contains(point)) {
			
		}
	},
	
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
	}*/
});

/*
var Path = Class.create({
	initialize: function() {
		this.segments = new Array();
	},
	
	addSegment: function(segment) {
		this.segments.push(segment);
	},
	
	draw: function(context) {
		this.segments.invoke('draw', context);
	},
	
	contains: function(point) {
		return this.segments.any(function(segment) {return segment.contains(point);});
	}
});*/

/**
  Connection -- a linking between two chart widgets, which can be a 
  composite of different view components
  {generic chUtil} source
  {generic chUtil} destination  
****/
var Connection = Class.create({
	sourceAnchorPoint: AnchorPoint.TOP_LEFT,
	destinationAnchorPoint: AnchorPoint.TOP_LEFT,
	numSegments: 3,
	sourceAxis: 'x',
	
	initialize: function(source, destination, data) {
	   this.data = data;
	   this.source = source;
       this.destination = destination;
       this.color = this.source.config.color;
       if (data) {
       		this.id = data.id;
       		this.sourceAnchorPoint = {side: data.source_side, position: data.source_position};
       		this.destinationAnchorPoint = {side: data.destination_side, position: data.destination_position};
       		this.numSegments = parseInt(data.num_segments);
       		this.sourceAxis = data.source_axis;
       		this.color = data.color;
       }
	   this.subWidgets = [];
	   //connections must be registered with thier respective boxes
	   this.source.registerOutgoingConnection(this);
	   this.destination.registerIncomingConnection(this);
	   Charts.connections.set(this.id, this);
	},
	
	/** Returns the connection's widgets' elements. */
	getElements: function() {
		if (this.canvas) {
			return [this.canvas];
		}
		else {
			return this.subWidgets.pluck('elem');
		}
	},
	
	/** remove the visual represenation of this element if it is currently being displayed **/
	removeElement: function(){   
	    if(this.elem) {       
	       if(this.elem.parentNode)
	          this.elem.parentNode.removeChild(this.elem);
	       this.elem = null;
	    }
	    
	    // TODO actually remove widgets
	    this.subWidgets = [];
	},
	
	/** set the visual representation of this element to a specific color **/
	colorElements: function(color){
	   this.subWidgets.invoke('setColor', color);
	},
	
	/** generate a chart item (lines or arrows) that will be a sub-component of the representation of a connection**/
	addConnectionComponent: function(startPoint, endPoint, isArrow){
		if (!this.fancy) { 
		     var xOffset = 0;
		     var xExtend = 0;
		     var yOffset = 0;
		     var yExtend = 0;
		     if(startPoint.x == endPoint.x){ //strait up and down line
		         xOffset = -5;
		     }
		     else if(startPoint.y == endPoint.y){  //strait side to side line
		         yOffset = -5;
		         if(!isArrow)
		           xExtend = 5;
		     }
		     var lineDimensions = calculateDimensionsFromEndPoints(Math.add(startPoint.x, xOffset) - xExtend/2, 
		                                                           Math.add(startPoint.y, yOffset) - yExtend/2, 
		                                                           Math.add(Math.add(endPoint.x, xOffset), xExtend/2), 
		                                                           Math.add(Math.add(endPoint.y, yOffset), yExtend/2) );   
		     var options = {x: lineDimensions['x'], 
		                    y: lineDimensions['y'],  
		                    h: lineDimensions['h'], 
		                    w: lineDimensions['w'],
		                    config: {color: this.color},
		                    entity: false};
		     var component = null;
		     if(isArrow){
		         options['type'] = 'arrow';
		         options['config']['direction'] = lineDimensions['direction'];
		         component = new chArrow(options);
		     }
		     else{
		         options['type'] = 'line';
		         component = new chLine(options);
		     }
		     
		     //remove of one component must disconnect and remove fellow components
		     component.remove = function() { this.disconnect() }.bind(this);
		     component.setColor = function(color) {this.setColor(color)}.bind(this);
		     
		     
		     this.elem.appendChild( component.getElem() ); 
		     component.elem.connection = this;
		     this.subWidgets.push( component ); 
		     return component;
		}
		else {
			var line = new Line(startPoint, endPoint, {drawArrow: isArrow, dashed: this.dashed, color: this.color});
			line.draw(this.ctx);
		}
	},
	
	/** generate the HTML reprsentation of a link **/
	getElem: function(){        
	    this.elem = document.createElement('div');
	    
    	var startPoint = this.source.getAnchorPointPosition(this.sourceAnchorPoint);
    	var endPoint = this.destination.getAnchorPointPosition(this.destinationAnchorPoint);
    	var midPoint;
    	
    	if (this.numSegments < 3) {
    		if (this.sourceAxis == 'x') {
    			midPoint = {x: endPoint.x, y: startPoint.y};
    		}
    		else {
    			var midPoint = {x: startPoint.x, y: endPoint.y};
    		}
    	}
    	
    	if (this.fancy) {
    		var padding = 10;
    		
    		var deltaX = endPoint.x - startPoint.x;
    		var width = Math.abs(deltaX);
    		
    		var deltaY = endPoint.y - startPoint.y;
    		var height = Math.abs(deltaY);
    		
    		var length = Math.sqrt(deltaX * deltaX + deltaY * deltaY);
    		
    		var left = Math.min(startPoint.x, endPoint.x);
    		var top = Math.min(startPoint.y, endPoint.y);
    		
    		var startX = startPoint.x - left;
    		var startY = startPoint.y - top;
    		var endX = startX + length;
    		var endY = startY;
    		
    		this.canvas = document.createElement('canvas');
    		
    		this.canvas.setAttribute('width', width + padding * 2);
    		this.canvas.setAttribute('height', height + padding * 2);
    		
    		if (!this.canvas.getContext) {
    			this.canvas.id = 'canvas' + this.id;
    			this.elem.appendChild(this.canvas);
    			this.canvas = G_vmlCanvasManager.initElement(this.canvas);
    		}
    		else {
    			this.elem.appendChild(this.canvas);
    		}
    		
    		this.canvas.className = 'connection';
    		this.canvas.connection = this;
    		
    		this.canvas.style.left = (left - padding) + 'px';
    		this.canvas.style.top = (top - padding) + 'px';
    		
    		this.ctx = this.canvas.getContext('2d');
    		
    		if (this.gradient) {
    			this.color = this.ctx.createLinearGradient(startPoint.x, startPoint.y, endPoint.x, endPoint.y);
	    		this.color.addColorStop(0, '#' + this.source.config.color);
	    		this.color.addColorStop(1, '#' + this.destination.config.color);
    		}
    		else {
    			this.color = '#' + this.source.config.color;
    		}
    		
    		this.ctx.lineWidth = 5;
    		this.ctx.lineCap = 'round';
    		
    		this.ctx.translate(padding - left, padding - top);
    	}
    	else {
			this.canvas = null;
			if (this.numSegments == 0) {
				this.numSegments = 1;
			}
    	}
		
		if (this.numSegments == 0) {
			this.addConnectionComponent(startPoint, endPoint, true);
		}
    	else if (this.numSegments == 1) {
    		this.addConnectionComponent(startPoint, midPoint, true);
    	}
    	else {
		    if (this.numSegments == 2) {
		    	this.addConnectionComponent(startPoint, midPoint, false);
		    	this.addConnectionComponent(midPoint, endPoint, true);
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
				
			    this.addConnectionComponent(startPoint, jointPoint1, false);
			    this.addConnectionComponent(jointPoint1, jointPoint2, false);
			    this.addConnectionComponent(jointPoint2, endPoint, true);
		    }
    	}
    	
		Charts.canvas.fire('connection:created', {connection: this});
	    return this.elem;
	},
	
	redraw: function() {
		this.removeElement();
	    Charts.canvas.appendChild(this.getElem());
	}
});

Math.add = function(a, b){
   return parseInt(a) + parseInt(b);
}

function calculateDimensionsFromEndPoints(startX, startY, endX, endY){
     var dimensions = {
     	x: Math.min(startX, endX),
     	y: Math.min(startY, endY),
     	w: Math.abs(endX - startX),
     	h: Math.abs(endY - startY)
     };

     if(startY == endY){
          dimensions['direction'] = startX < endX ? 'e' : 'w'; 
     }
     if(startX == endX){
          dimensions['direction'] = startY < endY ? 's' : 'n';          
     }
     return dimensions;
}

function in_array(needle, haystack) {
	for (var i=0; i<haystack.length; i++) {
		if (haystack[i] == needle) {
			return true;
		}
	}
	return false;
};

