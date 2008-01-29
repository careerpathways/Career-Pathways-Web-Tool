
var chGreybox = {

	onClose: null,

	create: function(content, content_width, content_height, cbfunc) {

		myHeight = document.documentElement.scrollHeight;
		myWidth = document.documentElement.scrollWidth;

		myInnerHeight = document.body.clientHeight;
		myInnerWidth = document.body.clientWidth;

		var overlay = document.createElement('div');
		overlay.id = "greybox";
		overlay.style.width = myWidth + "px";
		overlay.style.height = myHeight + "px";
	
		var contents = '' + 
	'<div id="greybox_center" style="width:' + myInnerWidth + 'px">' +
	'  <div id="greybox_inset" style="margin-top:' + (document.documentElement.scrollTop+40) + 'px; margin-left: ' + (document.documentElement.scrollLeft+40) + 'px; width:' + (content_width+30) + 'px">' +
	' 	<div id="greybox_container">' + 
	'		<div id="greybox_xbutton"><a href="javascript:chGreybox.close();">x</a></div>' +
	'		<div id="greybox_content">' +
			content + 
	'		</div>' +
	'	</div>' +
	'	<table id="greybox_bottom"><tr>' +
	'		<td id="greybox_bottomleft" width="' + (content_width) + '">&nbsp;</td>' +
	'		<td id="greybox_bottomright">&nbsp;</td>' +
	'	</tr></table>' +
	'  </div>' +
	'</div>';
	
		overlay.innerHTML = contents;
		document.body.appendChild(overlay);
	},

	close: function() {
		var gb = document.getElementById('greybox');
		document.body.removeChild(gb);

		if( this.onClose != null ) {
			this.onClose();
		}
	}

}