<?php
header("Content-type: text/javascript");
?>

/****************************************************************
Charts -- the full canvas for a particular drawing, on which chart items can 
be placed
 ****************************************************************/
Charts.getID = function(config, object, oncomplete) {
  chUtil.ajax({id: this.id,
                   a: 'new',
                   content: config},
              function(ajax) {
                object.id = ajax.responseText;
                if (oncomplete) {
                	oncomplete();
                }
              });
}

document.observe('chart:drawn', function(e) {
	var toolbar = document.createElement('div');
	Charts.toolbar = toolbar;
	toolbar.className = 'toolbar';
  
	Charts.addToolbarButton('line', {
		type: 'line',
		x: 0,
		y: 100,
		h: 104,
		w: 0,
		localZindex: 10000
	 });

	Charts.addToolbarButton('arrow', {
    	type: 'arrow',
        x: 0,
        y: 100,
        h: 104,
        w: 0,
        config: {direction: 'n'}
	});

	Charts.addToolbarButton('box', {
		type: 'box',
		x: 0,
		y: 100,
		h: 100,
		w: 150,
		config: {
			title: 'title',
			content: 'content',
			content_html: 'content'
		}
	});
	
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
		if(evt.keyCode == Event.KEY_DELETE && document.chDragging && !document.editingBox && !document.editingTitle ) {
			document.chDragging.remove();
			document.chDragging = null;
		}
		//clipboard request (cut, copy, paste, etc.) 
		else if((evt.ctrlKey || evt.metaKey) && document.chDragging && !document.editingBox && !document.editingTitle ) {
			switch(evt.keyCode) {
			case 67: // ctrl+c
					document.chClipboard = document.chDragging;
					break;
				case 86: // ctrl+v
					if( document.chClipboard ) {
						document.chClipboard.duplicate();
					}
					break;
				case 88: // ctrl+x
					document.chClipboard = document.chDragging;
					document.chClipboard.remove();
					break;
				default:
					captured = false;										        
			}
		} else if( evt.keyCode == 27 && document.chDragging ) {
			document.chDragging.mUp();
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
    Event.observe(cButton, 'mousedown', Charts.setColor);
    toolbar.appendChild(cButton);
  });
  
  var clear = document.createElement('div');
  clear.style.clear = 'both';
  toolbar.appendChild(clear);

  var ddhelp = document.createElement('div');
  ddhelp.className = "tiny";
  ddhelp.innerHTML = "Drag and drop colors onto boxes, arrows or lines";
  toolbar.appendChild(ddhelp);
  
  Charts.toolbarContainer.appendChild(toolbar);

  // greybox overlay for fckeditor

  Charts.editor = document.createElement('div');
  Charts.editor.className = "editorWindow";

  Charts.editor.myfck = document.createElement('div');
  Charts.editor.myfck.id = "myFCKeditor";
  Charts.editor.appendChild(Charts.editor.myfck);

  var okbtn = document.createElement('div');
  okbtn.className = "fckOK";
  okbtn.innerHTML = "OK";
  Charts.editor.appendChild(okbtn);
  var self = this;
  Event.observe(okbtn, 'mousedown', function() {Charts.insertFCKcontent(Charts);});

  Charts.fck = new FCKeditor("PathwaysEditor");
  Charts.fck.BasePath = "/common/fckeditor/";
  Charts.fck.Height = 400;
  Charts.fck.Config["CustomConfigurationsPath"] = "/files/myfckconfig.js";
  Charts.fck.ToolbarSet = "PathwaysEditor";
});

/** Adds a button to the toolbar. The button will display the name,
 * and invoke the onclick when clicked.
 */
Charts.addToolbarButton = function(name, data) {
	var button = document.createElement('div');
  	button.className = 'button';
	button.innerHTML = name;
  	Event.observe(button, 'click', function() {
  		objectdata = Object.clone(data);
  		objectdata.config = Object.clone(objectdata.config);
  		Charts.createWidget(objectdata);
  	});
  	Charts.toolbar.appendChild(button);
};

Charts.setColor = function(evt) {
  document.observe('mouseup', Charts.unSetColor);
  if (!evt) evt = window.event;
  Charts.color = evt.target ? evt.target.color : evt.srcElement.color;
  
  // NOTE: internet explorer needs the onselectstart observer configured below
  if (evt.preventDefault) evt.preventDefault();
  
  return false;
}

// prevents internet explorer from selecting text
document.onselectstart = function() {
	return false;
};

Charts.unSetColor = function() {
  Event.stopObserving(document.body, 'mousedown', Charts.setColor);
  Event.stopObserving(document.body, 'mouseup', Charts.unSetColor);
  Charts.color = null;
}

Charts.debug = function(txt) {
	document.getElementById('debugDiv').innerHTML = txt;
}

// TODO document this
Charts.whichi = function() {
	return 'edit';
}

Charts.confirmDelete = function(type) {
	return confirm('Are you sure you want to delete this ' + type + '?\n\nYou cannot undo this action.');
}



/**************************************************************
Chart Utilities/Items -- the actual widgets that live on the chart
***************************************************************/
chLine.prototype.moveCursor = ['c'];
chArrow.prototype.moveCursor = ['c'];
chBox.prototype.moveCursor = ['c', 's', 'ne', 'se', 'sw', 'nw', 'n'];

chLine.prototype.noCursor = ['ne', 'se', 'sw', 'nw'];
chArrow.prototype.noCursor = ['ne', 'se', 'sw', 'nw'];
chBox.prototype.noCursor = [];

var WidgetAdmin = {
    getElem: function() {
      this.createElement();
      this.elem.style.zIndex = this.getNewZindex();
      
      var self = this;
      this.setupHandles();
      Event.observe(this.elem, 'mouseup', function(evt) {self.localMouseUp(evt);});
      this.elem.widget = this;
      return this.setupElem(this.elem);
    },
    
    setupHandle: function(handle) {
    	if (this.entity) {
			if(!this.noCursor.member(handle.className)) {
				var cursor = handle.className + '-resize';
				if (this.moveCursor.member(handle.className)) {
					cursor = 'move';
				}
				handle.style.cursor = cursor;
			}
    	}
    	
    	Event.observe(handle, 'mousedown',
			function(evt) {
				if (evt.isLeftClick()) {
					 evt.stop();
					 this.mDown(evt, evt.findElement('td').className);
				}
		}.bindAsEventListener(this));
    },
    
    localMouseUp: function(evt) {
      if (Charts.color) this.setColor(Charts.color);
    },
    mDown: function(evt, handle) {
      document.chDragging = this;
      //this.elem.addClassName('selected');
      if (this.entity) {
	      var self = this;
	      this.mMoveHandler = function(evt) {
	                            evt.stop();
	                            this.mMove(evt);
	                          }.bindAsEventListener(this);
	      this.mUpHandler = function(evt) {
	                          evt.stop();
	                          this.mUp(evt);
	                        }.bindAsEventListener(this);
	      
	      Event.observe(document.documentElement, 'mousemove', this.mMoveHandler);
	      Event.observe(document.documentElement, 'mouseup', this.mUpHandler);
	      
	      Charts.canvasOffset = Charts.canvas.cumulativeOffset();
	      
	      var pointer = evt.pointer();
	
	      this.mStart = {x: Math.floor((pointer.x - Charts.canvasOffset.left) / 10) * 10,
	                     y: Math.floor((pointer.y - Charts.canvasOffset.top) / 10) * 10};
	
	      this.elemStart = {x: Math.floor(this.x / 10) * 10,
	                        y: Math.floor((this.y) / 10) * 10,
	                        h: Math.floor(this.h / 10) * 10,
	                        w: Math.floor(this.w / 10) * 10};
	
	      this.mHandle = handle;
	      if (this.onMouseDown) this.onMouseDown(evt);
      }
    },
    mUp: function(evt) {
      //this.elem.removeClassName('selected');
      Event.stopObserving(document.documentElement, 'mousemove', this.mMoveHandler);
      Event.stopObserving(document.documentElement, 'mouseup', this.mUpHandler);
      
      for (var m in this.mParams) 
          if (this.mParams[m] > 0) 
              this[m] = this.mParams[m];
      
      if(this.mMoveHandler && document.chDragging.id == this.id){
           //if we were in the process of moving something, update associated links
            this.redrawConnections();
      }
      this.mMoveHandler = null;
      this.mUpHandler = null;
      
      if (this.onMouseUp) 
          this.onMouseUp(evt);
                
    },
    remove: function() {
    	if (!Charts.confirmDelete(this.type)) {
    		return;
    	}
      if (this.mMoveHandler) document.stopObserving('mousemove', this.mMoveHandler);
      if (this.mUpHandler) document.stopObserving('mouseup', this.mUpHandler);
      Charts.canvas.removeChild(this.elem);
      chUtil.ajax({id: this.id,
                   a: 'remove'});
      //also remove any links associated with this widget
      this.getConnections().invoke('disconnect', true);
      
      if (this.onRemove) {
      	this.onRemove();
      }
	    
      Charts.canvas.fire('widget:removed', {widget: this});
    },
    setColor: function(color) {
      /*if(this.config.program > 0){
          color = chColor[this.config.program % chColor.length];
      }*/
      if(!color){
          color = '333333';
      }
      this.elem.className = this.elem.className.replace(this.config.color || '333333', color);
      this.config.color = color;
      chUtil.ajax({id: this.id,
                   a: 'update',
                   content: { config: {color: color}}});
                   
      //any connections should inherit the same color
      this.getOutgoingConnections().invoke('colorElements', color);
    }
}

chLine.addMethods(WidgetAdmin);
chArrow.addMethods(WidgetAdmin);
chBox.addMethods(WidgetAdmin);

chBox.addMethods({
    localZindex: 1,
    getNewZindex: function() {
       return this.localZindex++;
    },
    assignHeight: function() {
       // don't set the height of text boxes
    },
    setupElem: function(elem) {
    	elem.box = this;
      var self = this;
      /*if(this.config.program > 0){
          this.config.color = chColor[this.config.program % chColor.length];
      }*/
      if(!this.config.color){
         this.config.color = '333333';
      } 
      this.elem.className = 'box' + ' box_' + this.config.color;
      
      this.titleElem = document.createElement('span');
      this.titleElem.innerHTML = this.config.title;
      this.titleElem.className = 'title';
      this.handles['n'].innerHTML = '';
      this.handles['n'].appendChild(this.titleElem);
      
      this.contentElem = document.createElement('span');
      this.contentElem.innerHTML = this.config.content;
      this.contentElem.className = 'content';
      if( this.contentElem.innerHTML == "" ) 
          this.contentElem.innerHTML = '<div style="color:#999999">[[empty]]</div>';
      this.handles['c'].innerHTML = '';                  
      this.handles['c'].appendChild(this.contentElem);
           
      return elem;
    },    
    setProgram: function(program){
      this.handles['s'].innerHTML = 'test';
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
      this.titleElem.innerHTML = '';
      this.titleElem.appendChild(input);
      input.focus();
	  document.editingTitle = true;
    },
    saveTitle: function(input) {
      this.titleElem.innerHTML = input.value;
      this.config.title = input.value;
      chUtil.ajax({id: this.id,
                   a: 'update',
                   content: { config: {title: input.value}}});
      document.editingTitle = false;
    },
    changeContent: function() {
	  if( document.editingBox ) return;
	  Charts.showEditor(this);
	  document.editingBox = true;
    },
    saveContent: function(input) {
      var self = this;
      this.config.content = input.value;
      chUtil.ajax({id: this.id,
                   a: 'update',
                   content: { config: {content: input.value}}}, 
                  function(ajax) {
                    self.setContent(ajax);
                  });
      document.editingBox = false;
      this.contentElem.innerHTML = '<img src="' + base_url + '/images/spin-loader.gif" width="32" height="32" style="margin-left:30px;margin-top:20px;">';
    },
    setContent: function(ajax) {
      this.contentElem.innerHTML = ajax.responseText.replace(/[\r\n]+$/, '');
      this.config.content_html = this.contentElem.innerHTML;
      if( this.contentElem.innerHTML == "" ) this.contentElem.innerHTML = '<div style="color:#999999">[[empty]]</div>'; 
    },
    mMove: function(evt) {
      var params = {};

      m = {x: 5 + Math.floor((Event.pointerX(evt) - Charts.canvasOffset.left) / 10) * 10,
           y: 5 + Math.floor((Event.pointerY(evt) - Charts.canvasOffset.top) / 10) * 10};

      if (this.mHandle == 'e') {
        params.w = m.x - this.elemStart.x;
      }

      if (this.mHandle == 'w') {
        params.x = (this.elemStart.x - this.mStart.x) + m.x;
        params.w = (this.elemStart.x - m.x) + this.elemStart.w;
      }
      if (this.mHandle == 'c' || this.mHandle == 'n' || this.mHandle == 's' || this.mHandle == 'ne' || this.mHandle == 'se' || this.mHandle == 'se' || this.mHandle == 'sw') {
        params.y = m.y - (this.mStart.y - this.elemStart.y);
        params.x = m.x - (this.mStart.x - this.elemStart.x);
      }
      this.mParams = params;
      if (this.onMouseMove) this.onMouseMove(params);
      else {
        if (params.x > 0) this.elem.style.left = params.x + 'px';
        if (params.y > 0) this.elem.style.top = params.y + 'px';
        if (params.w > 0) this.elem.style.width = params.w + 'px';
      }
    },
    duplicate: function() {
		 Charts.createWidget({type: this.type,
                                                     x: parseInt(this.x)+10,
                                                     y: parseInt(this.y)+10,
                                                     h: this.h,
                                                     w: this.w,
                                                     config: {color: this.config.color,
                                                              title: this.config.title,
                                                              content: this.config.content,
                                                              content_html: this.config.content_html}});
    },
    onMouseUp: function() {
      chUtil.ajax({id: this.id,
                   a: 'update',
				   content: {x: this.x,
					   y: this.y,
					   h: this.h,
					   w: this.w}});
    },
    
    onRemove: function() {
		Charts.removeBox(this);
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
        this.connectFrom(Charts.waitingConnectionSource);
        
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
		chUtil.ajax(params, function(ajax) {
			data.id = ajax.responseText;
			var connection = new Connection(beginning, this, data);
			Charts.canvas.appendChild(connection.getElem());
		}.bind(this));
    }
});

var simpleWidgetAdminMethods = {
    localZindex: 0,
    getNewZindex: function() {
       return this.localZindex;
    },
    mAnchor: null,
    mNudge: {n: {x: 2,
                 y: -2,
                 h: 4,
                 w: 0},
             e: {x: -2,
                 y: 2,
                 h: 0,
                 w: 4},
             s: {x: 2,
                 y: -2,
                 h: 3,
                 w: 0},
             w: {x: -2,
                 y: 2,
                 h: 0,
                 w: 4},
             v: {x: 2,
                 y: -2,
                 h: 4,
                 w: 0},
             h: {x: -2,
                 y: 2,
                 h: 0,
                 w: 4}},
    onMouseDown: function(evt) {
      this.elem.style.zIndex = 10000;
      if (this.direction == 'n' || this.direction == 's' || this.direction == 'v') {
        if (this.mHandle == 'ne' || this.mHandle == 'nw') {
          this.mAnchor = {x: this.elemStart.x + this.elemStart.w,
                          y: this.elemStart.y + this.elemStart.h};
        }
        else if (this.mHandle == 'se' || this.mHandle == 'sw') {
          this.mAnchor = {x: this.elemStart.x,
                          y: this.elemStart.y};
        }
        else this.mAnchor = null;
      }
      else {
        if (this.mHandle == 'ne' || this.mHandle == 'se') {
          this.mAnchor = {x: this.elemStart.x,
                          y: this.elemStart.y};
        }
        else if (this.mHandle == 'nw' || this.mHandle == 'sw') {
          this.mAnchor = {x: this.elemStart.x + this.elemStart.w,
                          y: this.elemStart.y};
        }
        else this.mAnchor = null;
      }
    },
    mMove: function(evt) {
      var params = {};
      m = {x: (Math.floor((Event.pointerX(evt) - Charts.canvasOffset.left) / 10) * 10) - 5,
           y: (Math.floor((Event.pointerY(evt) - Charts.canvasOffset.top) / 10) * 10) - 5 };
      
      if (this.mHandle == 'c') {
        params.y = m.y - (this.mStart.y - this.elemStart.y);
        params.x = m.x - (this.mStart.x - this.elemStart.x);
      }
      else if (this.mHandle == 'n') {
        this.setDirection('n');
        params.y = (this.elemStart.y - this.mStart.y) + m.y + 10;
        params.h = (this.elemStart.y - m.y) + this.elemStart.h;
        params.w = 15;
      }
      else if (this.mHandle == 'w') {
        this.setDirection('w');
        params.x = (this.elemStart.x - this.mStart.x) + m.x + 10;
        params.w = (this.elemStart.x - m.x) + this.elemStart.w;
        params.h = 15;
      }
      else if (this.mHandle == 's') {
        this.setDirection('s');
        params.h = m.y - this.elemStart.y;
        params.w = 15;
      }
      else if (this.mHandle == 'e') {
        this.setDirection('e');
        params.w = m.x - this.elemStart.x;
        params.h = 15;
      }
      else if (this.mAnchor) {
        var a = Math.atan((m.y - this.mAnchor.y) / (m.x - this.mAnchor.x));
        if (m.x < this.mAnchor.x) a += Math.PI;
        if (a < 0) a += Math.PI * 2;
        params.y = this.mAnchor.y;
        params.x = this.mAnchor.x;
        ////console.debug(a);
        if (a > Math.PI * 1.75 || a < Math.PI * .25) {
          this.setDirection('e');
          params.w = m.x - this.mAnchor.x;
          params.h = 15;
        }
        else if (a > Math.PI * 1.25) {
          this.setDirection('n');
          params.y = m.y;
          params.h = this.mAnchor.y - m.y;
          params.w = 15;
        }
        else if (a > Math.PI * .75) {
          this.setDirection('w');
          params.x = m.x;
          params.w = this.mAnchor.x - m.x;
          params.h = 15;
        }
        else if (a > Math.PI * .25) {
          this.setDirection('s');
          params.h = m.y - this.mAnchor.y;
          params.w = 15;
        }
      }
      //console.debug('nudge:'+params.x);
      if (params.x > 0) params.x += this.mNudge[this.direction].x;
	  if (params.y > 0) params.y += this.mNudge[this.direction].y;
	  if (params.h > 0) params.h += this.mNudge[this.direction].h;
      if (params.w > 0) params.w += this.mNudge[this.direction].w;
      
      this.mParams = params;
      if (this.onMouseMove) {
        this.onMouseMove(params);
      } else {
        //console.debug('drag:'+params.x);
        if (params.x > 0) this.elem.style.left = params.x + 'px';
		if (params.y > 0) this.elem.style.top = params.y + 'px';
		if (params.h > 0) this.elem.style.height = params.h + 'px';
        if (params.w > 0) this.elem.style.width = params.w + 'px';
      }
    },
    duplicate: function() {
		 Charts.createWidget({type: this.type,
                                                     x: parseInt(this.x)+10,
                                                     y: parseInt(this.y)+10,
                                                     h: this.h,
                                                     w: this.w,
                                                     config: {color: this.config.color,
                                                              direction: this.direction}});      
    },
    onMouseUp: function() {
      this.elem.style.zIndex = 0;
      chUtil.ajax({id: this.id,
                   a: 'update',
                   content: {x: this.x,
					   y: this.y,
					   h: this.h,
					   w: this.w,
					   config: {direction: this.direction}}});      
    },
    assignHeight: function() {
    	if (this.h > 0) this.elem.style.height = this.h + 'px';
    }
}

chLine.addMethods(simpleWidgetAdminMethods);
chArrow.addMethods(simpleWidgetAdminMethods);

var chUtil = {};
chUtil.ajax = function(post, callback) {
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
	this.fck.Value = mychUtil.config.content;
	this.editor.myfck.innerHTML = this.fck.CreateHtml();

	chGreybox.create('',620,300);
	document.getElementById('greybox_content').appendChild(this.editor);

	chGreybox.onClose = function() { document.editingBox = false; };
}

Charts.insertFCKcontent = function() {

	var oe = FCKeditorAPI.GetInstance("PathwaysEditor");
	var thexhtml = oe.GetXHTML();
	this.mychUtil.contentElem.innerHTML = thexhtml;
	this.mychUtil.config.content_html = thexhtml;
	this.mychUtil.config.content = thexhtml;

	chUtil.ajax({id: this.mychUtil.id,
			   a: 'update',
			   content: {config: {content_html: thexhtml, content: thexhtml}}}
			 );

	this.mychUtil = null;
	chGreybox.close();
	document.editingBox = false;
}

Charts.waitingConnectionSource = null;

Connection.addMethods({
	/** permanently remove/disconnect this connection**/
	disconnect: function(confirmed){
		if (!(confirmed || Charts.confirmDelete('connection'))) {
			return;
		}     
	    //remove from database
	    chUtil.ajax({source_id: this.source.id,
	                 destination_id: this.destination.id,
	                 a: 'disconnect'});
	    //remove from view
	    this.removeElement();       
	    //remove from source and destination widgets 
	    this.source.unregisterOutgoingConnection(this);
	    this.destination.unregisterIncomingConnection(this);
	    
	    Charts.canvas.fire('connection:removed', {connection: this});
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
		this.redraw();
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
        this.redraw();
	}
});

Connection.determineDefaultConnectionData = function(source, destination) {
	var up = source.getTop() - destination.getBottom();
	var down = destination.getTop() - source.getBottom();
	var left = source.getLeft() - destination.getRight();
	var right = destination.getLeft() - source.getRight();
	
	switch (Math.max(up, down, left, right)) {
		case up:
			data = {
				source_side: Side.NORTH,
				destination_side: Side.SOUTH,
				source_axis: 'y'
			};
			break;
		case down:
			data = {
				source_side: Side.SOUTH,
				destination_side: Side.NORTH,
				source_axis: 'y'
			};
			break;
		case right:
			data = {
				source_side: Side.EAST,
				destination_side: Side.WEST,
				source_axis: 'x'
			};
			break;
		case left:
			data = {
				source_side: Side.WEST,
				destination_side: Side.EAST,
				source_axis: 'x'
			};
			break;
	}
	
	data.num_segments = 1;
	data.source_position = 50;
	data.destination_position = 50;
	data.color = source.config.color;
	
	return data;
}

// CONTEXT MENUS

var LINK_TO_LABEL ='Start Connection Here';
var LINK_TO_HERE_LABEL = 'End Connection Here';

/** Returns the box that is the target of the box context menu,
 *  or null if none.
 */
chBox.getContextMenuTarget = function() {
	var element = chBox.contextMenu.contextEventTarget;
	
	while (element && !element.box) {
		element = element.parentNode;
	}
	
	if (element.box) {
		return element.box;
	}
	
	return null;
};

/** Returns the connection that is the target of the contect menu,
 *  or null if none.
 */
Connection.getContextMenuTarget = function() {
	var element = Connection.contextMenu.contextEventTarget;
	
	while (element && !element.connection) {
		element = element.parentNode;
	}
	
	if (element.connection) {
		return element.connection;
	}
	
	return null;
};

getWidgetContextMenuTarget = function() {
	var element = widgetContextMenu.contextEventTarget;
	
	while (element && !element.widget) {
		element = element.parentNode;
	}
	
	if (element.widget) {
		return element.widget;
	}
	
	return null;
};

var selectMenuItemWithOnclickObj = function(menu, value) {
	menu.getItems().each(function(item) {
		item.cfg.setProperty('checked', item.cfg.getProperty('onclick').obj == value);
	});
};

/** Called when the Edit Title menu item is chosen
 *  from the box context menu.
 */
var onEditTitleSelect = function() {
	var box = chBox.getContextMenuTarget();
	box.changeTitle();
};

/** Called when the Edit Content menu item is chosen
 *  from the box context menu.
 */
var onEditContentSelect = function() {
	var box = chBox.getContextMenuTarget();
	box.changeContent();
};

/** Called when the Link To menu item is chosen
 *  from the box context menu.
 */
var onLinkToSelect = function() {
	var box = chBox.getContextMenuTarget();
	box.connect();
};

/** Called when a program menu item is chosen
 *  from the box context menu.
 *  @param value the id of the selected program
 */
var onProgramSelect = function(type, args, value) {
	var box = chBox.getContextMenuTarget();
	box.setProgram(value);
};

/** Called then the delete menu item is chosen
 *  from the box context menu.
 */
var onDeleteBoxSelect = function() {
	var box = chBox.getContextMenuTarget();
	box.remove();
};

/** Called when the duplicate menu item is chosen
 *  from the box context menu.
 */
var onDuplicateBoxSelect = function() {
	var box = chBox.getContextMenuTarget();
	box.duplicate();
};

/** Called when an anchor point is chosen from
 *  the connection context menu.
 */
var onAnchorPointSelect = function(type, args, value) {
	var connection = Connection.getContextMenuTarget();
	
	if (this === anchorSourceMenu) {
		connection.anchorSource(value);
	}
	else {
		connection.anchorDestination(value);
	}
};

/** Called when a source axis is chosen from
 *  the connection context menu.
 */
var onSourceAxisSelect = function(type, args, value) {
	var connection = Connection.getContextMenuTarget();
	connection.setSourceAxis(value);
};

/** Called when a number of segments is chosen from
 *  the connection context menu.
 */
var onNumSegmentsSelect = function(type, args, value) {
	var connection = Connection.getContextMenuTarget();
	
	if (value == 0) {
		connection.fancy = true;
	}
	connection.setNumSegments(value);
};

var onAutopositionSelect = function() {
	var connection = Connection.getContextMenuTarget();
	connection.autoposition();
};

var onFancySelect = function() {
	var connection = Connection.getContextMenuTarget();
	
	connection.fancy = connection.fancy ? false : true;
	
	connection.redraw();
}

var onGradientSelect = function() {
	var connection = Connection.getContextMenuTarget();
	
	connection.gradient = connection.gradient ? false : true;
	
	connection.redraw();
}

var onDashedSelect = function() {
	var connection = Connection.getContextMenuTarget();
	
	connection.dashed = connection.dashed ? false : true;
	
	connection.redraw();
}

/** Called when delete is chosen from the connection
 *  context menu.
 */
var onDisconnectSelect = function() {
	var connection = Connection.getContextMenuTarget();
	connection.disconnect();
};

var onColorSelect = function(type, args, value) {
	if (this == boxColorMenu) {
		var target = chBox.getContextMenuTarget();
	}
	if (this == connectionColorMenu) {
		var target = Connection.getContextMenuTarget();
	}
	else if (this == widgetColorMenu) {
		var target = getWidgetContextMenuTarget();
	}
	
	target.setColor(value);
}

var onDuplicateWidgetSelect = function() {
	var widget = getWidgetContextMenuTarget();
	
	widget.duplicate();
}

var onDeleteWidgetSelect = function() {
	var widget = getWidgetContextMenuTarget();
	
	widget.remove();
}

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
	var box = chBox.getContextMenuTarget();
	
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
chBox.contextMenu = new YAHOO.widget.ContextMenu('chBox.contextMenu', {zindex: 10});
chBox.contextMenu.addItems([[
	{text: 'Edit', submenu: editBoxMenu},
	{text: 'Color', submenu: boxColorMenu},
	/*{text: 'Box Type', submenu: typeMenu},*/
	linkBoxesMenuItem,
	{text: 'Duplicate', onclick: {fn: onDuplicateBoxSelect}}
],
[
	{text: 'Delete', onclick: {fn: onDeleteBoxSelect}}
]]);

/** Convenience function to add menu items for both ends of connection. */
var addAnchorPointMenuItems = function(menu) {
	menu.addItems([
		{text: 'Top left', onclick: {fn: onAnchorPointSelect, scope: menu, scope: menu, obj: AnchorPoint.TOP_LEFT}},
		{text: 'Top center', onclick: {fn: onAnchorPointSelect, scope: menu, obj: AnchorPoint.TOP_CENTER}},
		{text: 'Top right', onclick: {fn: onAnchorPointSelect, scope: menu, obj: AnchorPoint.TOP_RIGHT}},
		{text: 'Middle left', onclick: {fn: onAnchorPointSelect, scope: menu, obj: AnchorPoint.MIDDLE_LEFT}},
		{text: 'Middle right', onclick: {fn: onAnchorPointSelect, scope: menu, obj: AnchorPoint.MIDDLE_RIGHT}},
		{text: 'Bottom left', onclick: {fn: onAnchorPointSelect, scope: menu, obj: AnchorPoint.BOTTOM_LEFT}},
		{text: 'Bottom center', onclick: {fn: onAnchorPointSelect, scope: menu, obj: AnchorPoint.BOTTOM_CENTER}},
		{text: 'Bottom right', onclick: {fn: onAnchorPointSelect, scope: menu, obj: AnchorPoint.BOTTOM_RIGHT}}
	]);
	
	menu.subscribe('show', function() {
		var connection = Connection.getContextMenuTarget();
		
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
	var connection = Connection.getContextMenuTarget();
	
	selectMenuItemWithOnclickObj(sourceAxisMenu, connection.sourceAxis);
});

// create number of connection segments menu
var numSegmentsMenu = new YAHOO.widget.Menu('numSegmentsMenu');
numSegmentsMenu.addItems([/*[
	{text: '1 (Direct Line) (requires fancy lines)', onclick: {fn: onNumSegmentsSelect, obj: 0}}
],*/
[
	{text: '1 (Straight Line)', onclick: {fn: onNumSegmentsSelect, obj: 1}},
	{text: '2', onclick: {fn: onNumSegmentsSelect, obj: 2}},
	{text: '3', onclick: {fn: onNumSegmentsSelect, obj: 3}}
]]);

numSegmentsMenu.subscribe('show', function() {
	var connection = Connection.getContextMenuTarget();
	
	selectMenuItemWithOnclickObj(numSegmentsMenu, connection.numSegments);
});

var connectionColorMenu = new YAHOO.widget.Menu('connectionColorMenu');
chColor.each(function(color) {
	connectionColorMenu.addItem({
		text: '<span style="background-color: #' + color + '">&nbsp;&nbsp;&nbsp;&nbsp;</span>',
		onclick: {fn: onColorSelect, obj: color, scope: connectionColorMenu}
	});
});

var fancyMenuItem = new YAHOO.widget.MenuItem('Use Fancy Lines (experimental)', {onclick: {fn: onFancySelect}});
var gradientMenuItem = new YAHOO.widget.MenuItem('Gradient', {onclick: {fn: onGradientSelect}});
var dashedMenuItem = new YAHOO.widget.MenuItem('Dashed', {onclick: {fn: onDashedSelect}});

var styleMenu = new YAHOO.widget.Menu('styleMenu');
styleMenu.addItems([[
	fancyMenuItem,
],
[
	gradientMenuItem,
	dashedMenuItem
]]);

styleMenu.subscribe('show', function() {
	var connection = Connection.getContextMenuTarget();
	
	var fancy = connection.fancy ? true : false;
	fancyMenuItem.cfg.setProperty('checked', fancy);
	gradientMenuItem.cfg.setProperty('checked', connection.gradient ? true : false);
	dashedMenuItem.cfg.setProperty('checked', connection.dashed ? true : false);
	
	gradientMenuItem.cfg.setProperty('disabled', !fancy);
	dashedMenuItem.cfg.setProperty('disabled', !fancy);
});

var sourceAxisMenuItem = new YAHOO.widget.MenuItem('Orientation', {submenu: sourceAxisMenu});

// create the connection context menu
Connection.contextMenu = new YAHOO.widget.ContextMenu('connectionContextMenu', {zindex: 10});
Connection.contextMenu.addItems([[
	{text: 'Start Point', submenu: anchorSourceMenu},
	{text: 'End Point', submenu: anchorDestinationMenu},
	sourceAxisMenuItem,
	{text: 'Segments', submenu: numSegmentsMenu},
	{text: 'Color', submenu: connectionColorMenu}/*,
	{text: 'Style', submenu: styleMenu},
	{text: 'Auto Position', onclick: {fn: onAutopositionSelect}}*/
],
[
	{text: 'Delete', onclick: {fn: onDisconnectSelect}}
]]);

/*
Connection.contextMenu.subscribe('show', function() {
	var connection = Connection.getContextMenuTarget();
	
	// disable the source axis menu if using direct line
	sourceAxisMenuItem.cfg.setProperty('disabled', connection.fancy && connection.numSegments == 0);
});
*/

var widgetColorMenu = new YAHOO.widget.Menu('widgetColorMenu');
chColor.each(function(color) {
	widgetColorMenu.addItem({
		text: '<span style="background-color: #' + color + '">&nbsp;&nbsp;&nbsp;&nbsp;</span>',
		onclick: {fn: onColorSelect, obj: color, scope: widgetColorMenu}
	});
});

var widgetContextMenu = new YAHOO.widget.ContextMenu('widgetContextMenu', {zindex: 10});
widgetContextMenu.addItems([[
	{text: 'Color', submenu: widgetColorMenu},
	{text: 'Duplicate', onclick: {fn: onDuplicateWidgetSelect}}
],
[
	{text: 'Delete', onclick: {fn: onDeleteWidgetSelect}}
]]);

document.observe('chart:drawn', function() {
	chBox.contextMenu.render(Charts.canvas);
	Connection.contextMenu.render(Charts.canvas);
	widgetContextMenu.render(Charts.canvas);
});

// keep track of box elements to attach context menu triggers
var boxTriggers = new Hash();

var widgetTriggers = new Hash();

/** Updates the box context menu to use all of the
 *  current box elements as triggers.
 */
var updateBoxContextMenuTriggers = function() {
	chBox.contextMenu.cfg.setProperty('trigger', boxTriggers.values());
}

var updateWidgetContextMenuTriggers = function() {
	widgetContextMenu.cfg.setProperty('trigger', widgetTriggers.values());
};

document.observe('widget:created', function(event) {
	var widget = event.memo.widget;
	if (widget.type == 'box') {
		boxTriggers.set(widget.id, widget.elem);
		
		updateBoxContextMenuTriggers();
	}
	else if (widget.entity) {
		widgetTriggers.set(widget.id, widget.elem);
		
		updateWidgetContextMenuTriggers();
	}
});

document.observe("widget:removed", function(event) {
	var widget = event.memo.widget;
	if (widget.type == 'box') {
		boxTriggers.unset(widget.id);
		
		updateBoxContextMenuTriggers();
	}
});

// keep track of connection elements to attach context menu triggers
var connectionContextMenuTriggers = new Hash();

/** Updates the connection context menu to use all of the
 *  current connection elements as triggers.
 */
var updateConnectionContextMenuTriggers = function() {
	Connection.contextMenu.cfg.setProperty('trigger', connectionContextMenuTriggers.values().flatten());
};

document.observe('connection:created', function(event) {
	var connection = event.memo.connection;
	connectionContextMenuTriggers.set(connection.id, connection.getElements());
	
	updateConnectionContextMenuTriggers();
});

document.observe('connection:removed', function(event) {
	var connection = event.memo.connection;
	connectionContextMenuTriggers.unset(connection.id);
	
	updateConnectionContextMenuTriggers();
});
