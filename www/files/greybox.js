
var chGreybox = {

	onClose: null,
	preClose: null,

	create: function(content, content_width, content_height, cbfunc, headerText) {

		myHeight = document.documentElement.scrollHeight;
		myWidth = document.documentElement.scrollWidth;

		myInnerHeight = document.body.clientHeight;
		myInnerWidth = document.body.clientWidth;

		var overlay = document.createElement('div');
		overlay.id = "greybox";
		overlay.style.width = myWidth + "px";
		overlay.style.height = myHeight + "px";

		var headerTag = (headerText ? '<div style="margin-left:15px;font-size:15pt;font-weight:bold;color:#003366">'+headerText+'</div>' : '');

		var contents = '' + 
	'<div id="greybox_container" class="resizable" style="top: ' + (document.documentElement.scrollTop+40) + 'px; left: ' + (document.documentElement.scrollLeft+40) + 'px;">' + 
	'    <div id="greybox_xbutton" title="Click and drag to move."><a href="javascript:chGreybox.close();" title="Click to close.">x</a></div>' +
	'    <div id="greybox_content">' + ((typeof jQuery != "function") ? headerTag + content : '') + '</div>' +
	'</div>';
        
		overlay.innerHTML = contents;
		if (typeof jQuery == "function") {
			jQuery("body").append(overlay);
			jQuery("#greybox_content").html(headerTag + content);
		}
		else {
            document.body.appendChild(overlay);
        }
        
        chGreybox.dragElement(document.getElementById('greybox_container'));
	},
    
	close: function() {
		if( this.preClose != null ) {
			this.preClose();
			this.preClose = null;
		}

		var gb = document.getElementById('greybox');
		if( gb ) document.body.removeChild(gb);

		if( this.onClose != null ) {
			this.onClose();
			this.onClose = null;
		}
	},
    
    // Copied from https://www.w3schools.com/howto/howto_js_draggable.asp
    dragElement: function (elmnt) {
        var pos1 = 0, pos2 = 0, pos3 = 0, pos4 = 0;
        if (document.getElementById("greybox_xbutton")) {
            // if present, the header is where you move the DIV from:
            document.getElementById("greybox_xbutton").onmousedown = dragMouseDown;
        } else {
            // otherwise, move the DIV from anywhere inside the DIV: 
            elmnt.onmousedown = dragMouseDown;
        }
        
        function dragMouseDown(e) {
            e = e || window.event;
            e.preventDefault();
            // get the mouse cursor position at startup:
            pos3 = e.clientX;
            pos4 = e.clientY;
            document.onmouseup = closeDragElement;
            // call a function whenever the cursor moves:
            document.onmousemove = elementDrag;
        }
        
        function elementDrag(e) {
            e = e || window.event;
            e.preventDefault();
            // calculate the new cursor position:
            pos1 = pos3 - e.clientX;
            pos2 = pos4 - e.clientY;
            pos3 = e.clientX;
            pos4 = e.clientY;
            // set the element's new position:
            elmnt.style.top = (elmnt.offsetTop - pos2) + "px";
            elmnt.style.left = (elmnt.offsetLeft - pos1) + "px";
        }
        
        function closeDragElement() {
            // stop moving when mouse button is released:
            document.onmouseup = null;
            document.onmousemove = null;
        }
    }
    
};
