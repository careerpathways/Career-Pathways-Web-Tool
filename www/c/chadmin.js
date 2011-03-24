/****************************************************************
Charts -- the full canvas for a particular drawing, on which chart items can 
be placed
 ****************************************************************/

document.observe('chart:drawn', function(e) {
	var toolbar = document.createElement('div');
	Charts.toolbar = toolbar;
	toolbar.className = 'toolbar';
  
	//Charts.addToolbarButton('line', ChartLine);
	//Charts.addToolbarButton('arrow', ChartLine, {arrowheadAtEnd: true});
	//Charts.addToolbarButton('box', ChartBox);
	
	// firefox needs the 'return false' to cancel the delete
	document.onkeydown = function(e) {
		if (e.keyCode == Event.KEY_BACKSPACE && !document.editingBox && !document.editingTitle) {
			/* TODO something like this is needed for firefox on mac
			if (Prototype.Browser.Gecko && navigator.appVersion.indexOf("Mac") !=- 1) {
				document.location.href += '#';
			}
			*/
			return false;
		}
	}.bindAsEventListener();
  
	document.observe('keydown', function(evt) {
		var captured = true;
		//deletion request
		if(evt.keyCode == Event.KEY_DELETE && Charts.selectedComponent && !document.editingBox && !document.editingTitle ) {
			Charts.selectedComponent.remove();
			Charts.selectedComponent = null;
		}
		//clipboard request (cut, copy, paste, etc.) 
		else if((evt.ctrlKey || evt.metaKey) && Charts.selectedComponent && !document.editingBox && !document.editingTitle ) {
			switch(evt.keyCode) {
			case 67: // ctrl+c
					document.chClipboard = Charts.selectedComponent;
					break;
				case 86: // ctrl+v
					if( document.chClipboard ) {
						document.chClipboard.duplicate(Charts.redraw);
					}
					break;
				case 88: // ctrl+x
					document.chClipboard = Charts.selectedComponent;
					document.chClipboard.remove();
					break;
				default:
					captured = false;										        
			}
		} else if( evt.keyCode == 27 && Charts.selectedComponent ) {
			Charts.selectedComponent.mUp();
		} else {
			captured = false;
		}
		
		if( captured ) {
			Event.stop(evt);
		}
	});
  
  chColor.each(function(color) {
    var cButton = document.createElement('div');
    cButton.className = 'button color';
    cButton.color = color;
    cButton.style.backgroundColor = '#' + color;
    cButton.title = '#' + color;
    Event.observe(cButton, 'click', function(e) {
		if (Charts.selectedComponent && Charts.selectedComponent.setColor) {
			Charts.selectedComponent.setColor(color);
			Charts.redraw();
		}
		e.stop();
		return false;
	});
    toolbar.appendChild(cButton);
  });
  
  var clear = document.createElement('div');
  clear.style.clear = 'both';
  toolbar.appendChild(clear);

  var ddhelp = document.createElement('div');
  ddhelp.className = "tiny";
  ddhelp.innerHTML = "Select a box, line, or connection and click a color";
  toolbar.appendChild(ddhelp);
  
  Charts.toolbarContainer.appendChild(toolbar);

  // greybox overlay for tinymce (replaced fckeditor)

  Charts.editor = document.createElement('div');
  Charts.editor.className = "editorWindow";

  Charts.editor.myfck = document.createElement('textarea');
  Charts.editor.myfck.id = "mceBox";
  Charts.editor.myfck.name = "mceBox";
  Charts.editor.appendChild(Charts.editor.myfck);

  var okbtn = document.createElement('div');
  okbtn.className = "fckOK";
  okbtn.innerHTML = "OK";
  Charts.editor.appendChild(okbtn);
  var self = this;
  Event.observe(okbtn, 'mousedown', function() {Charts.insertFCKcontent(Charts);});

	// add the vertical page divider
	var pageVDivider = document.createElement('div');
	pageVDivider.className = 'chVDivider';
	pageVDivider.innerHTML = '';
	Charts.element.appendChild(pageVDivider);
	
	// add the horizontal page divider
	var pageHDivider = document.createElement('div');
	pageHDivider.className = 'chHDivider';
	pageHDivider.innerHTML = '';
	Charts.element.appendChild(pageHDivider);

  

  Charts.fck = {Config: {}}; //new FCKeditor("PathwaysEditor");
  
	Charts.element.observe('contextmenu', function(e) {
		var pointer = e.pointer();
		var position = Charts.positionWithin(pointer);

		var shape = Charts.getShapeContaining(position);
		if (shape && shape.widget) {
			Charts.contextMenuTarget = shape.widget;
		}
		else {
			Charts.contextMenuTarget = null;
		}
		
		var menu;
		
		if (Charts.contextMenuTarget) {
			switch (Charts.contextMenuTarget.type) {
				case 'box':
					menu = ChartBox.contextMenu;
					break;
				case 'line':
					menu = widgetContextMenu;
					break;
				case 'connection':
					menu = Connection.contextMenu;
					break;
			}
		}
		else {
			menu = Charts.contextMenu;
		}
		
		Charts.contextMenuPosition = {
			x: pointer.x,
			y: pointer.y
		};
		
		menu.cfg.setProperty('x', Charts.contextMenuPosition.x);
		menu.cfg.setProperty('y', Charts.contextMenuPosition.y);
		menu.show();
		
		e.stop();
	});
	
	Charts.element.observe('mousedown', function(e) {
		if (document.editingTitle || !e.isLeftClick()) {
			return;	
		}
		
		var pointer = e.pointer();
		var position = Charts.positionWithin(pointer);
		
		Charts.activeControl = Charts.getControlPointContaining(position);
		Charts.mouseDownPosition = position;
		
		if (Charts.activeControl) {
			Charts.positionDeltas = Geometry.deltas(position, Charts.activeControl);
			e.stop();
			return;
		}
  		var shape = Charts.getShapeContaining(position);
  		
		if (!shape || (shape.widget && shape.widget != Charts.selectedComponent)) {
			if (shape) {
				Charts._select(shape.widget);
				
			}
			else {
				Charts._deselect();
			}
			
			Charts.redraw();
		}
		
		if (Charts.selectedComponent) {
			Charts.activeControl = Charts.selectedComponent;
			Charts.positionDeltas = Geometry.deltas(position, Charts.activeControl);
		}
		
  		if (shape){
  			e.stop();
  		}
	});

	// TODO should only be registered when selected
	Charts.element.observe('mousemove', function(e) {
		// if there is an active control and the control can be moved
		if (Charts.activeControl && Charts.activeControl.applyPosition) {
			Charts.controlReshaped = true;
			
			var position = Charts.positionWithin(e.pointer());
			var offsetPosition = Geometry.translatedPoint(position, Charts.positionDeltas);
			
			if (Charts.snapToGrid && !e.altKey) {
				offsetPosition.x = Charts.gridSize * Math.round(offsetPosition.x / Charts.gridSize);
				offsetPosition.y = Charts.gridSize * Math.round(offsetPosition.y / Charts.gridSize);
			}
			
			offsetPosition.x = Math.max(Charts.drawingArea.x, offsetPosition.x);
			offsetPosition.y = Math.max(Charts.drawingArea.y, offsetPosition.y);
			
			var maxX = Charts.drawingArea.bottomRight.x;
			var maxY = Charts.drawingArea.bottomRight.y;
			if (Charts.activeControl.getShape) {
				maxX -= Charts.activeControl.getShape().getBounds().width;
				maxY -= Charts.activeControl.getShape().getBounds().height;
			}
			
			offsetPosition.x = Math.min(maxX, offsetPosition.x);
			offsetPosition.y = Math.min(maxY, offsetPosition.y);
			
			Charts.activeControl.applyPosition(offsetPosition, e.shiftKey);
			Charts.activeControl.x = offsetPosition.x;
			Charts.activeControl.y = offsetPosition.y;
			Charts.redraw();
		}
		/*else {
			var oldHoveredControlPoint = Charts.hoveredControlPoint;
			Charts.hoveredControlPoint = Charts.getControlPointContaining(position);
			if (Charts.hoveredControlPoint != oldHoveredControlPoint) {
				Charts.redraw();
			}
		}*/
	});
	
	Charts.element.observe('mouseup', function(e) {
		if (Charts.activeControl) {
			if (Charts.controlReshaped) {
				Charts.selectedComponent.onReshape();
			}
			Charts.activeControl = null;
			
			// the selected component could have moved, so update the control points
			Charts._select(Charts.selectedComponent);
			
			Charts.redraw();
		}
		
		Charts.controlReshaped = false;
	});
	
	Charts.drawingArea = new Geometry.Bounds([Geometry.ORIGIN, Geometry.translatedPoint(Geometry.ORIGIN, Charts.element.offsetWidth, Charts.element.offsetHeight)]);
	
	Charts.snapToGrid = true;
});

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
		}});
	},
	
	//_beginRedraw: function(canvas) {},
	
	_finishRedraw: function(context) {
		// draw the highlight box
		if (Charts.selectedComponent && ! Charts.activeControl) {
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
		}
		else {
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
		}
		else {
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

// prevents internet explorer from selecting text
document.onselectstart = function() {
	return false;
};



/**************************************************************
Chart Utilities/Items -- the actual widgets that live on the chart
***************************************************************/
var SELECTED_COLOR = 'rgb(0, 0, 255)';
var HOVERED_COLOR = 'rgb(0, 255, 0)';
var CONTROL_POINT_RADIUS = 5;

var WidgetAdmin = {
	remove: function() {
    	if (!Charts.confirmDelete(this.type)) {
    		return;
    	}
      if (this.mMoveHandler) document.stopObserving('mousemove', this.mMoveHandler);
      if (this.mUpHandler) document.stopObserving('mouseup', this.mUpHandler);
      
      if (this.elem) {
      	Charts.element.removeChild(this.elem);
      }
      chUtil.ajax({id: this.id,
                   a: 'remove'});
                   
      // remove all connections to and from this widget
      this.getConnections().invoke('disconnect', true);
      
      Charts.unregisterComponent(this);
      Charts.redraw();
    },
    setColor: function(color) {
      /*if(this.config.program > 0){
          color = chColor[this.config.program % chColor.length];
      }*/
      if(!color){
          color = '333333';
      }
      this.config.color = color;
      chUtil.ajax({id: this.id,
                   a: 'update',
                   content: { config: {color: color}}});
                   
      //any connections should inherit the same color
      this.getOutgoingConnections().invoke('setColor', color);
      
      this._onSetColor();
    },
    
    _onSetColor: function() {
    	this.shape.setStyle('color', '#' + this.config.color);
    }
}

var START_COLOR = '#0f0';
var END_COLOR = '#f00';

ChartLine.addMethods(WidgetAdmin);
ChartBox.addMethods(WidgetAdmin);

ChartLine.DEFAULT_OPTIONS = {
	startPoint: {x: 100, y: 100},
	endPoint: {x: 200, y: 200}
 };
ChartLine.addMethods({
  getControlPoints: function() {
  	var startControlPoint = Object.clone(this.getStartPoint());
  	startControlPoint.applyPosition = this.applyPointPosition.bind(this, this.startPoint, this.endPoint);
  	startControlPoint.color = START_COLOR;
  	
  	var endControlPoint = Object.clone(this.getEndPoint());
  	endControlPoint.applyPosition = this.applyPointPosition.bind(this, this.endPoint, this.startPoint);
  	endControlPoint.color = END_COLOR;
  	return [
  		startControlPoint,
  		endControlPoint
	];
  },
  
  applyPointPosition: function(point, otherPoint, position, constrain) {
  	if (constrain) {
  		var deltas = Geometry.deltas(position, otherPoint);
  		if (Math.abs(deltas.x) < Math.abs(deltas.y)) {
  			position.x = otherPoint.x;
  		}
  		else {
  			position.y = otherPoint.y;
  		}
  	}
  	
  	point.x = position.x;
  	point.y = position.y;
  	
  	this.reposition();
  },
  
	duplicate: function(callback) {
		return Charts.createComponent(ChartLine, {
			startPoint: Geometry.translatedPoint(this.startPoint, 0, 30),
			endPoint: Geometry.translatedPoint(this.endPoint, 0, 30),
			arrowheadAtEnd: this.arrowheadAtEnd,
			config: {
				color: this.config.color
          	}
		}, callback);      
    },
    
	onReshape: function() {
		chUtil.ajax({
			id: this.id,
			a: 'update',
			content: {
				startPoint: {
					x: this.startPoint.x,
					y: this.startPoint.y
				},
				endPoint: {
					x: this.endPoint.x,
					y: this.endPoint.y
				}
			}
		});      
    },
	
	applyPosition: function(position) {
		var deltas = Geometry.deltas(this, position);
		this.startPoint = Geometry.translatedPoint(this.startPoint, deltas);;
		this.endPoint = Geometry.translatedPoint(this.endPoint, deltas);
		
		this.reposition();
	}
});

ChartBox.ABSOLUTE_MINIMUM_WIDTH = 20;
ChartBox.DEFAULT_OPTIONS = {
	x: 0,
	y: 100,
	h: 100,
	w: 150,
	config: {
		title: 'title',
		content: 'content',
		content_html: 'content'
	}
};

ChartBox.addMethods({  
    setProgram: function(program){
      this.config.program = program;
      chUtil.ajax({a: 'setProgram',
                   object_id: this.id,
                   program_id: program });
      
      this.setColor();     
    },    
    changeTitle: function() {
	  if( document.editingTitle ) return;
      var self = this;
      var input = document.createElement('input');
      input.value = this.config.title;
      input.style.width = (this.w - 20) + 'px';
      Event.observe(input, 'blur', function() {self.saveTitle(input);}, false);
      this.titleElement.innerHTML = '';
      this.titleElement.appendChild(input);
      input.focus();
	  input.select();
	  document.editingTitle = true;
    },
    saveTitle: function(input) {
      this.titleElement.innerHTML = input.value || '&nbsp;';
      this.config.title = input.value;
      chUtil.ajax({id: this.id,
                   a: 'update',
                   content: { config: {title: input.value}}});
      document.editingTitle = false;
      this._onContentChange();
      this.reposition();
      Charts.redraw();
    },
    changeContent: function() {
	  if( document.editingBox ) return;
	  Charts.showEditor(this);
	  document.editingBox = true;
    },
    
    duplicate: function(callback) {
		return Charts.createComponent(ChartBox, {
			x: parseInt(this.x),
			y: parseInt(this.getTop() + this.getHeight()) + 30,
			h: this.h,
			w: this.w,
			config: {
				color: this.config.color,
				title: this.config.title,
				content: this.config.content,
				content_html: this.config.content_html
			}
		}, callback);
	},
  
    /** Handles a connection action (click, etc.) for a box */
    connect: function(){
        //first click means we are setting the connection source and waiting for more info
        if(Charts.waitingConnectionSource == null) {
          Charts.waitingConnectionSource = this;
          linkBoxesMenuItem.cfg.setProperty('text', LINK_TO_HERE_LABEL);
          return;
        }
        
        //don't allow links to objects we have already linked to        
        if(Charts.waitingConnectionSource.outgoingConnectionExists(this)) {
            return;
        }
        
        linkBoxesMenuItem.cfg.setProperty('text', LINK_TO_LABEL);
        
        //clicking self toggles connection source off and on
        if(Charts.waitingConnectionSource.id == this.id){                           
          Charts.waitingConnectionSource = null;
          return;
        }
    	
        //second click means we are setting the connection destination               
        var connection = this.connectFrom(Charts.waitingConnectionSource);
        
        //remove half-link-waiting indicators regardless of what connection attempt has been made
        Charts.waitingConnectionSource = null;
    },
    
    connectFrom: function(beginning) {
    	var data = Connection.determineDefaultConnectionData(beginning, this);
    	var params = Object.clone(data);
    	Object.extend(params, {
    		source_id: Charts.waitingConnectionSource.id,
			destination_id: this.id,
			a: 'connect'
		});
		
		var connection = new Connection(beginning, this, data);
		
		chUtil.ajax(params, function(ajax) {
			connection.id = ajax.responseText;
			
			Charts.registerComponent(connection);
			
			// TODO does this really belong here? should be in event-handling code
			Charts.redraw();
		}.bind(this));
		
		return connection;
    },
    
    getControlPoints: function() {
    	var left = this.getAnchorPointPosition({side: Side.LEFT, position: 50});
    	left.applyPosition = this.applyLeftPosition.bind(this);
    	
    	var right = this.getAnchorPointPosition({side: Side.RIGHT, position: 50});
    	right.applyPosition = this.applyRightPosition.bind(this);
    	
    	if (this.w < this.getMinimumContentWidth()) {
    		left.color = '#ff0000';
    		right.color = '#ff0000';
    	}
    	return [
    		left,
    		right
    	];
    },
    
    getMinimumContentWidth: function() {
		this.contentElement.style.overflow = 'visible';
		this.titleElement.style.overflow = 'visible';
		var result = Math.max(this.titleElement.offsetWidth, this.contentElement.offsetWidth) / Charts.textSizeMultiplier + 20;
		
		this.contentElement.style.overflow = 'hidden';
		this.titleElement.style.overflow = 'hidden';
		
		return result;
    },
    
    applyRightPosition: function(position) {
    	this.w = Math.max(position.x - this.getLeft(), ChartBox.ABSOLUTE_MINIMUM_WIDTH);
    	this._onWidthChange(position, Side.RIGHT);
    },
    
    applyLeftPosition: function(position) {
    	this.w = Math.max(this.getRight() - position.x, ChartBox.ABSOLUTE_MINIMUM_WIDTH);
    	this.x = position.x;
    	this._onWidthChange(position, Side.LEFT);
    },
    
    _onWidthChange: function(position, side) {
    	this.repositionElement();
    	this._onContentChange();
    	this.reposition();
    	
    	var newPosition = this.getAnchorPointPosition({side: side, position: 50});
    	position.x = newPosition.x;
    	position.y = newPosition.y;
    },
    
    applyPosition: function(position) {
    	this.x = position.x;
    	this.y = position.y;
    	this.repositionElement();
    	this.reposition();
    },
    
    _onSetColor: function() {
    	this.outerRectangle.setStyle('fillColor', '#' + this.config.color);
    },
    
    onReshape: function() {
		chUtil.ajax({
			id: this.id,
			a: 'update',
			content: {
				x: this.x,
				y: this.y,
				h: this.h,
				w: this.w
			}
		});
	}
});

var chUtil = {};
chUtil.ajax = function(post, callback) {
	post.version_id = Charts.versionId;
  var params = chUtil.toPost(post);
  
  if (window.XMLHttpRequest) var ajax = new XMLHttpRequest();
  else if (window.ActiveXObject) var ajax = new ActiveXObject("Microsoft.XMLHTTP");  
  
  ajax.onreadystatechange = function () {
                              chUtil.ajaxRsc(ajax, callback);
                            }
  
  ajax.open('POST', 'chserv.php', true);
  ajax.setRequestHeader('Content-type', 'application/x-www-form-urlencoded');
  if (document.cookie) ajax.setRequestHeader('Cookie', document.cookie);
  ajax.setRequestHeader('Content-length', params.length);
  ajax.setRequestHeader('Connection', 'close');
  ajax.send(params);
}

chUtil.ajaxRsc = function(ajax, callback) {
  if (ajax.readyState == 4) {
    if (typeof(callback) !== 'undefined') {
      if (ajax.status == 200) callback(ajax);
      else callback(false);
    }
  }
}

chUtil.toPost = function(obj,path,new_path) {
  if (typeof(path) == 'undefined') var path=[];
  if (typeof(new_path) != 'undefined') path.push(new_path);
  var post_str=[];
  if (typeof(obj) == 'array' || typeof(obj) == 'object') for (var n in obj) post_str.push(chUtil.toPost(obj[n],path,n));
  else {
    var base = path.shift();
    post_str.push(base + (path.length > 0 ? '[' + path.join('][') + ']' : '') + '=' + encodeURIComponent(obj).replace(/&/g, '%26'));
    path.unshift(base);
  }
  path.pop();
  return post_str.join('&');
}

Charts.showEditor = function(mychUtil) {
    this.mychUtil = mychUtil;
	//this.editor.myfck.innerHTML = mychUtil.config.content;

	//this.fck.Value = mychUtil.config.content;
	//this.editor.myfck.innerHTML = this.fck.CreateHtml();

	// Load tinyMCE in place of the object
	tinyMCE.init({
		mode : "none",
		theme : "advanced",
		plugins : "spellchecker,style,table,fullscreen",
		theme_advanced_buttons1 : "bold,italic,underline,strikethrough,|,justifyleft,justifycenter,justifyright,justifyfull,|,formatselect,fontsizeselect",
		theme_advanced_buttons2 : "bullist,numlist,|,outdent,indent,blockquote,|,undo,redo,|,link,unlink,|,image,|,cleanup,styleprops,code",
		theme_advanced_buttons3 : "tablecontrols,|,spellchecker",
		theme_advanced_buttons4 : "",
		theme_advanced_toolbar_location : "top",
		theme_advanced_toolbar_align : "left",
		theme_advanced_statusbar_location : false,
		theme_advanced_advanced_resizing : false,
		spellchecker_languages : "+English=en",
		spellchecker_rpc_url : "/common/tinymce/plugins/spellchecker/rpc.php",
		init_instance_callback : function(){
			var ed = tinyMCE.get('mceBox');
			ed.setContent(mychUtil.config.content);
		},
		convert_urls : false
	});

	chGreybox.create('',620,300);
	document.getElementById('greybox_content').appendChild(this.editor);

	document.getElementById('greybox_content').style.paddingLeft = '15px';
	tinyMCE.execCommand('mceAddControl', true, 'mceBox');

	chGreybox.preClose = function() {
		tinyMCE.execCommand('mceRemoveControl', false, 'mceBox');
		document.editingBox = false;
	};
}

Charts.insertFCKcontent = function() {

	var thexhtml = tinyMCE.activeEditor.getContent();
	this.mychUtil.contentElement.innerHTML = thexhtml;
	this.mychUtil.config.content_html = thexhtml;
	this.mychUtil.config.content = thexhtml;

	chUtil.ajax({id: this.mychUtil.id,
			   a: 'update',
			   content: {config: {content_html: thexhtml, content: thexhtml}}}
			 );
	
	this.mychUtil._onContentChange();
	this.mychUtil.reposition();
	this.mychUtil = null;
	tinyMCE.execCommand('mceRemoveControl', false, 'mceBox');
	document.editingBox = false;
	chGreybox.close();
	Charts.redraw();
}

Charts.waitingConnectionSource = null;

Connection.addMethods({
	remove: function() {
		if (!Charts.confirmDelete('connection')) {
			return;
		} 
		
		this.disconnect();
		
		Charts.redraw();
	},
	
	/** Disconnect this connection. */
	disconnect: function(){
	    //remove from database
	    chUtil.ajax({source_id: this.source.id,
	                 destination_id: this.destination.id,
	                 a: 'disconnect'});
      
	    //remove from source and destination widgets 
	    this.source.unregisterOutgoingConnection(this);
	    this.destination.unregisterIncomingConnection(this);
	    
	    Charts.unregisterComponent(this);
	},
	
	/** Sets the anchor point on the source widget. */
	anchorSource: function(anchorPoint) {
		this.sourceAnchorPoint = anchorPoint;
		this.onPropertyChange({'source_side': anchorPoint.side, 'source_position': anchorPoint.position});
	},
	
	/** Sets the anchor point on the destination widget. */
	anchorDestination: function(anchorPoint) {
		this.destinationAnchorPoint = anchorPoint;
		this.onPropertyChange({'destination_side': anchorPoint.side, 'destination_position': anchorPoint.position});
	},
	
	/** Sets the number of line segments used to draw the connection.
	 *  Only values 1-3 are supported.
	 */
	setNumSegments: function(numSegments) {
		this.numSegments = numSegments;
		this.onPropertyChange({'num_segments': numSegments});
	},
	
	/** Sets the axis of the line extending from the source anchor point. 
	 *  @param sourceAxis either 'x' or 'y'
	 */
	setSourceAxis: function(sourceAxis) {
		this.sourceAxis = sourceAxis;
		this.onPropertyChange({'source_axis': sourceAxis});
	},
	
	setColor: function(color) {
		this.color = color;
		this.shape.setStyle('color', '#' + color);
		this.onPropertyChange({'color': color});
	},
	
	autoposition: function() {
		var data = Connection.determineDefaultConnectionData(this.source, this.destination);
		this.sourceAnchorPoint = {side: data.source_side, position: data.source_position};
		this.destinationAnchorPoint = {side: data.destination_side, position: data.destination_position};
		this.sourceAxis = data.source_axis;
		this.numSegments = data.num_segments;
		this.onPropertyChange(data);
	},
	
	onPropertyChange: function(properties) {
		var data = {
            a: 'update',
			type: 'connection',
			id: this.id
		};
		for (key in properties) {
			data[key] = properties[key];
		}
		chUtil.ajax(data);
        
		this.reposition();
	},
	
	getControlPoints: function() {
		var startControlPoint = Object.clone(this.startPoint);
		startControlPoint.applyPosition = this.applyAnchorPointPosition.bind(this, this.source, this.sourceAnchorPoint, this.startPoint);
		startControlPoint.color = START_COLOR;
		
		if (this.numSegments != 1) {
			var endControlPoint = Object.clone(this.endPoint);
			endControlPoint.applyPosition = this.applyAnchorPointPosition.bind(this, this.destination, this.destinationAnchorPoint, this.endPoint);
			endControlPoint.color = END_COLOR;
			return [startControlPoint, endControlPoint];
		}
		else {
			return [startControlPoint];
		}
	},
	
	applyAnchorPointPosition: function(control, anchorPoint, point, position) {
		var translated = Geometry.translatedPoint(position, -control.getLeft(), -control.getTop());
		if (Side.isHorizontal(anchorPoint.side)) {
			anchorPoint.position = Math.max(0, Math.min(100, translated.x * 100 / control.getWidth()));
		}
		else {
			anchorPoint.position = Math.max(0, Math.min(100, translated.y * 100 / control.getHeight()));
		}
		this.reposition();
		position.x = point.x;
		position.y = point.y;
	},
	
	onReshape: function() {
		this.onPropertyChange({
			'source_side': this.sourceAnchorPoint.side,
			'source_position': this.sourceAnchorPoint.position,
			'destination_side': this.destinationAnchorPoint.side,
			'destination_position': this.destinationAnchorPoint.position
		});
	}
});

Side.isHorizontal = function(side) {
	return side == Side.TOP || side == Side.BOTTOM;
};

Connection.determineDefaultConnectionData = function(source, destination) {
	var up = source.getTop() - destination.getBottom();
	var down = destination.getTop() - source.getBottom();
	var left = source.getLeft() - destination.getRight();
	var right = destination.getLeft() - source.getRight();
	
	switch (Math.max(up, down, left, right)) {
		case up:
			data = {
				source_side: Side.TOP,
				destination_side: Side.BOTTOM,
				source_axis: 'y'
			};
			break;
		case down:
			data = {
				source_side: Side.BOTTOM,
				destination_side: Side.TOP,
				source_axis: 'y'
			};
			break;
		case right:
			data = {
				source_side: Side.RIGHT,
				destination_side: Side.LEFT,
				source_axis: 'x'
			};
			break;
		case left:
			data = {
				source_side: Side.LEFT,
				destination_side: Side.RIGHT,
				source_axis: 'x'
			};
			break;
	}
	
	data.num_segments = 1;
	data.source_position = 50;
	data.destination_position = 50;
	data.color = source.config.color;
	
	return data;
};

/* CONTEXT MENUS
******************************************************************************/

var LINK_TO_LABEL ='Start Connection Here';
var LINK_TO_HERE_LABEL = 'End Connection Here';

var selectMenuItemWithOnclickObj = function(menu, value) {
	menu.getItems().each(function(item) {
		item.cfg.setProperty('checked', item.cfg.getProperty('onclick').obj == value);
	});
};

/** Called when the Edit Title menu item is chosen
 *  from the box context menu.
 */
var onEditTitleSelect = function() {
	Charts.contextMenuTarget.changeTitle();
};

/** Called when the Edit Content menu item is chosen
 *  from the box context menu.
 */
var onEditContentSelect = function() {
	Charts.contextMenuTarget.changeContent();
};

/** Called when the Link To menu item is chosen
 *  from the box context menu.
 */
var onLinkToSelect = function() {
	Charts.contextMenuTarget.connect();
	Charts.redraw();
};

/** Called when a program menu item is chosen
 *  from the box context menu.
 *  @param value the id of the selected program
 */
var onProgramSelect = function(type, args, value) {
	Charts.contextMenuTarget.setProgram(value);
	Charts.redraw();
};

/** Called then the delete menu item is chosen
 *  from the component context menu.
 */
var onDeleteSelect = function() {
	Charts.contextMenuTarget.remove();
};

/** Called when an anchor point is chosen from
 *  the connection context menu.
 */
var onAnchorPointSelect = function(type, args, value) {
	if (this === anchorSourceMenu) {
		Charts.contextMenuTarget.anchorSource(value);
	}
	else {
		Charts.contextMenuTarget.anchorDestination(value);
	}
	Charts.redraw();
};

/** Called when a source axis is chosen from
 *  the connection context menu.
 */
var onSourceAxisSelect = function(type, args, value) {
	Charts.contextMenuTarget.setSourceAxis(value);
	Charts.redraw();
};

/** Called when a number of segments is chosen from
 *  the connection context menu.
 */
var onNumSegmentsSelect = function(type, args, value) {
	Charts.contextMenuTarget.setNumSegments(value);
	Charts.redraw();
};

var onAutopositionSelect = function() {
	Charts.contextMenuTarget.autoposition();
	Charts.redraw();
};

/*var onDashedSelect = function() {
	Charts.contextMenuTarget.dashed = Charts.contextMenuTarget.dashed ? false : true;
	Charts.redraw();
}*/

var onColorSelect = function(type, args, value) {
	Charts.contextMenuTarget.setColor(value);
	Charts.redraw();
};

var onDuplicateSelect = function() {
	Charts.contextMenuTarget.duplicate(Charts.redraw);
};

var onSnapToGridSelect = function() {
	Charts.snapToGrid = !Charts.snapToGrid;
};

var onDrawGridSelect = function() {
	Charts.drawGrid = !Charts.drawGrid;
	Charts.redraw();
};

var onNewLineSelect = function(type, args, value) {
	var options = value || {};
	var position = Charts.positionWithin(Charts.contextMenuPosition);
	
	Object.extend(options, {
		startPoint: {
			x: position.x,
			y: position.y
		}, 
		endPoint: {
			x: position.x + 100,
			y: position.y + 100
		}
	});
	 
	Charts.createComponent(ChartLine, options, Charts.redraw.bind(Charts));
};

var onNewBoxSelect = function() {
	var position = Charts.positionWithin(Charts.contextMenuPosition);
	Charts.createComponent(ChartBox, {x: position.x, y: position.y}, Charts.redraw.bind(Charts));
}

var onGridSizeSelect = function() {
	var gridSize = prompt('Edit grid size', Charts.gridSize);
	if (gridSize != null) {
		Charts.gridSize = Math.max(2, parseInt(gridSize));
		
		if (Charts.drawGrid) {
			Charts.redraw();
		}
	}
};

/* every menu item created should do nothing when clicked */
// TODO there should be a better way to do this.
YAHOO.widget.MenuItem.prototype.init_old = YAHOO.widget.MenuItem.prototype.init;
YAHOO.widget.MenuItem.prototype.init = function(p_oObject, p_oConfig) {
	p_oConfig.url = 'javascript:Prototype.emptyFunction()';
	YAHOO.widget.MenuItem.prototype.init_old.apply(this, arguments);
}

// create the edit box menu
var editBoxMenu = new YAHOO.widget.Menu('editBoxMenu');
editBoxMenu.addItems([
	{text: 'Title', onclick: {fn: onEditTitleSelect}},
	{text: 'Content', onclick: {fn: onEditContentSelect}}
]);

// create the box program menu
var typeMenu = new YAHOO.widget.Menu('typeMenu');
types.each(function(type) {
	typeMenu.addItem({text: type.description, onclick: {fn: onProgramSelect, obj: type.id}});
});
typeMenu.subscribe('show', function() {
	var box = Charts.contextMenuTarget;
	
	selectMenuItemWithOnclickObj(typeMenu, box.config.program);
});

var boxColorMenu = new YAHOO.widget.Menu('boxColorMenu');
chColor.each(function(color) {
	boxColorMenu.addItem({
		text: '<span style="background-color: #' + color + '">&nbsp;&nbsp;&nbsp;&nbsp;</span>',
		onclick: {fn: onColorSelect, obj: color, scope: boxColorMenu}
	});
});

// create the box connection menu item
var linkBoxesMenuItem = new YAHOO.widget.MenuItem(LINK_TO_LABEL, {onclick: {fn: onLinkToSelect}});

// create the box context menu
ChartBox.contextMenu = new YAHOO.widget.ContextMenu('ChartBox.contextMenu');
ChartBox.contextMenu.addItems([[
	// {text: 'Edit', submenu: editBoxMenu},
	{text: 'Edit Content', onclick: {fn: onEditContentSelect}},
	{text: 'Edit Title', onclick: {fn: onEditTitleSelect}},
	{text: 'Color', submenu: boxColorMenu},
	// {text: 'Box Type', submenu: typeMenu},
	linkBoxesMenuItem,
	{text: 'Duplicate', onclick: {fn: onDuplicateSelect}}
],
[
	{text: 'Delete', onclick: {fn: onDeleteSelect}}
]]);

/** Convenience function to add menu items for both ends of connection. */
var addAnchorPointMenuItems = function(menu) {
	menu.addItems([[
		{text: 'Top', onclick: {fn: onAnchorPointSelect, scope: menu, obj: AnchorPoint.TOP_CENTER}},
		{text: 'Bottom', onclick: {fn: onAnchorPointSelect, scope: menu, obj: AnchorPoint.BOTTOM_CENTER}}
		],[
		{text: 'Left', onclick: {fn: onAnchorPointSelect, scope: menu, obj: AnchorPoint.MIDDLE_LEFT}},
		{text: 'Right', onclick: {fn: onAnchorPointSelect, scope: menu, obj: AnchorPoint.MIDDLE_RIGHT}},
	]]);
	
	menu.subscribe('show', function() {
		var connection = Charts.contextMenuTarget;
		
		var anchorPoint = menu == anchorSourceMenu ? connection.sourceAnchorPoint : connection.destinationAnchorPoint;
		
		menu.getItems().each(function(item) {
			var obj = item.cfg.getProperty('onclick').obj;
			item.cfg.setProperty('checked', obj.side == anchorPoint.side && obj.position == anchorPoint.position);
		});
	});
};

// create the source and destination anchor point menus
var anchorSourceMenu = new YAHOO.widget.Menu('anchorSourceMenu');
addAnchorPointMenuItems(anchorSourceMenu);

var anchorDestinationMenu = new YAHOO.widget.Menu('anchorDestinationMenu');
addAnchorPointMenuItems(anchorDestinationMenu);

// create the connection source axis menu
var sourceAxisMenu = new YAHOO.widget.Menu('sourceAxisMenu');
sourceAxisMenu.addItems([
	{text: 'Horizontal', onclick: {fn: onSourceAxisSelect, obj: 'x'}},
	{text: 'Vertical', onclick: {fn: onSourceAxisSelect, obj: 'y'}}
]);

sourceAxisMenu.subscribe('show', function() {
	selectMenuItemWithOnclickObj(sourceAxisMenu, Charts.contextMenuTarget.sourceAxis);
});

// create number of connection segments menu
var numSegmentsMenu = new YAHOO.widget.Menu('numSegmentsMenu');
numSegmentsMenu.addItems([[
	{text: '1-Seg Line (Straight)', onclick: {fn: onNumSegmentsSelect, obj: 1}},
	{text: '1-Seg Line (Diagonal)', onclick: {fn: onNumSegmentsSelect, obj: 0}},
	{text: '2-Seg Line ("L")', onclick: {fn: onNumSegmentsSelect, obj: 2}},
	{text: '3-Seg Line', onclick: {fn: onNumSegmentsSelect, obj: 3}}
]]);

numSegmentsMenu.subscribe('show', function() {
	selectMenuItemWithOnclickObj(numSegmentsMenu, Charts.contextMenuTarget.numSegments);
});

var connectionColorMenu = new YAHOO.widget.Menu('connectionColorMenu');
chColor.each(function(color) {
	connectionColorMenu.addItem({
		text: '<span style="background-color: #' + color + '">&nbsp;&nbsp;&nbsp;&nbsp;</span>',
		onclick: {fn: onColorSelect, obj: color, scope: connectionColorMenu}
	});
});

/*var dashedMenuItem = new YAHOO.widget.MenuItem('Dashed', {onclick: {fn: onDashedSelect}});

var styleMenu = new YAHOO.widget.Menu('styleMenu');
styleMenu.addItems(
[
	dashedMenuItem
]);

styleMenu.subscribe('show', function() {
	dashedMenuItem.cfg.setProperty('checked', Charts.contextMenuTarget.dashed ? true : false);
});*/

var sourceAxisMenuItem = new YAHOO.widget.MenuItem('Orientation', {submenu: sourceAxisMenu});

// create the connection context menu
Connection.contextMenu = new YAHOO.widget.ContextMenu('connectionContextMenu');
Connection.contextMenu.addItems([[
	{text: 'Start Point', submenu: anchorSourceMenu},
	{text: 'End Point', submenu: anchorDestinationMenu},
	sourceAxisMenuItem,
	{text: 'Segments', submenu: numSegmentsMenu},
	{text: 'Color', submenu: connectionColorMenu},
	/*{text: 'Style', submenu: styleMenu},*/
	{text: 'Auto Position', onclick: {fn: onAutopositionSelect}}
],
[
	{text: 'Delete', onclick: {fn: onDeleteSelect}}
]]);

Connection.contextMenu.subscribe('show', function() {
	// disable the source axis menu if using direct line
	sourceAxisMenuItem.cfg.setProperty('disabled', Charts.contextMenuTarget.numSegments == 0);
});

var widgetColorMenu = new YAHOO.widget.Menu('widgetColorMenu');
chColor.each(function(color) {
	widgetColorMenu.addItem({
		text: '<span style="background-color: #' + color + '">&nbsp;&nbsp;&nbsp;&nbsp;</span>',
		onclick: {fn: onColorSelect, obj: color, scope: widgetColorMenu}
	});
});

var widgetContextMenu = new YAHOO.widget.ContextMenu('widgetContextMenu');
widgetContextMenu.addItems([[
	{text: 'Color', submenu: widgetColorMenu},
	{text: 'Duplicate', onclick: {fn: onDuplicateSelect}}
],
[
	{text: 'Delete', onclick: {fn: onDeleteSelect}}
]]);

var newComponentMenu = new YAHOO.widget.Menu('Charts.newComponentMenu');
newComponentMenu.addItems([
	{text: 'Box', onclick: {fn: onNewBoxSelect}},
	{text: 'Line', onclick: {fn: onNewLineSelect, obj: null}},
	{text: 'Arrow', onclick: {fn: onNewLineSelect, obj: {arrowheadAtEnd: true}}}
]);

var gridMenu = new YAHOO.widget.Menu('Charts.gridMenu');
var snapToGridMenuItem = new YAHOO.widget.MenuItem('Snap to Grid', {onclick: {fn: onSnapToGridSelect}});
var showGridMenuItem = new YAHOO.widget.MenuItem('Show Grid', {onclick: {fn: onDrawGridSelect}});
var gridSizeMenuItem = new YAHOO.widget.MenuItem('Edit Grid Size', {onclick: {fn: onGridSizeSelect}});

gridMenu.addItems([
	showGridMenuItem,
	snapToGridMenuItem,
	gridSizeMenuItem
]);

Charts.contextMenu = new YAHOO.widget.ContextMenu('Charts.contextMenu');
Charts.contextMenu.addItems([[
	//{text: 'Create Box', onclick: {fn: onNewBoxSelect}},
	{text: 'New', submenu: newComponentMenu},
],[
	{text: 'Grid', submenu: gridMenu}
]]);

Charts.contextMenu.subscribe('show', function() {
	showGridMenuItem.cfg.setProperty('checked', Charts.drawGrid);
	snapToGridMenuItem.cfg.setProperty('checked', Charts.snapToGrid);
});


document.observe('chart:drawn', function() {
	Charts.contextMenu.render(Charts.element);
	ChartBox.contextMenu.render(Charts.element);
	Connection.contextMenu.render(Charts.element);
	widgetContextMenu.render(Charts.element);
	onDrawGridSelect();
});
