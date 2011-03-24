
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
	'<div id="greybox_center" style="width:' + myInnerWidth + 'px">' +
	'  <div id="greybox_inset" style="margin-top:' + (document.documentElement.scrollTop+40) + 'px; margin-left: ' + (document.documentElement.scrollLeft+40) + 'px; width:' + (content_width+30) + 'px">' +
	' 	<div id="greybox_container">' + 
	'		<div id="greybox_xbutton"><a href="javascript:chGreybox.close();">x</a></div>' +
	'		<div id="greybox_content">' + ((typeof jQuery != "function") ? headerTag + content : '') + '</div>' +
	'	</div>' +
	'	<table id="greybox_bottom"><tr>' +
	'		<td id="greybox_bottomleft" width="' + (content_width) + '">&nbsp;</td>' +
	'		<td id="greybox_bottomright">&nbsp;</td>' +
	'	</tr></table>' +
	'  </div>' +
	'</div>';

		overlay.innerHTML = contents;
		if (typeof jQuery == "function")
		{
			jQuery("body").append(overlay);
			jQuery("#greybox_content").html(headerTag + content);
		}
		else
			document.body.appendChild(overlay);
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
	}

}