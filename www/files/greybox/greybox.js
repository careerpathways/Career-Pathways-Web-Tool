/* Greybox Redux Part Deux
 * Required: http://jquery.com/
 * Written by: Torgie
 * Based on code by: John Resig and 4mir Salihefendic (http://amix.dk)
 * License: LGPL (read more in LGPL.txt)
 */

var GB_DONE = false;
var GB_HEIGHT = 400;
var GB_WIDTH = 400;

function GB_show(caption, url, width, height) {
	// Default width and height
	GB_HEIGHT = height || 400;
	GB_WIDTH = width || 400;

	if(!GB_DONE) {
		// Write the Greybox into the body of the document
		$(document.body).append("<div id='GB_overlay'></div><div id='GB_window'><div id='GB_caption'></div><img id='GB_closeBox' src='/files/greybox/close.gif' alt='Close window'/></div>");

		// Hook the window-clock events
		$("#GB_window img").click(GB_hide);
		$("#GB_overlay").click(GB_hide);

		// Whenever the window is resized, update the box's position
		$(window).resize(GB_position);
		GB_DONE = true;
	}

	// Remove any existing frames and write in a new one
	$("#GB_frame").remove();
	$("#GB_window").append("<div id='GB_frame'><img src='/files/greybox/ajax.gif' alt='Loading...' style='position: absolute; left: " + ((GB_WIDTH/2) - 12) + "px; top: " + ((GB_HEIGHT/2) - 30) + "px' /></div>");

	// Load in the AJAX information
	$.ajax({
		dataType: "HTML",
		type: "POST",
		url: url,
		success: function(html) {
			if (GB_ANIMATION) {
				$("#GB_frame").css({opacity: 0});
				$("#GB_frame").html(html);
				$("#GB_frame").animate({
					opacity: 1
				}, 200);
			}
			else
				$("#GB_frame").html(html);
		}
	});

	// Add the header information
	$("#GB_caption").html(caption);
	$("#GB_overlay").show();
	GB_position();

	// Show the box, either with an animation or directly
	if (GB_ANIMATION) {
		$("#GB_window,#GB_overlay").css({opacity: 0, display: "block"});
		$("#GB_window,#GB_overlay").animate({
			opacity: 1
		}, 200);
	}
	else 
		$("#GB_window,#GB_overlay").css({display: "block"});
}

// Hides the box when a user click outside the content area
function GB_hide() {
	if (GB_ANIMATION) {
		$("#GB_window,#GB_overlay").animate({
			opacity: 0
		}, 200, function(){
			$("#GB_window,#GB_overlay").css({
				display: "none"
			})
		});
	}
	else
		$("#GB_window,#GB_overlay").css({display: "none"});
}

// Position the box in the correct place on the page
function GB_position() {
	var de = document.documentElement;
	var w = self.innerWidth || (de&&de.clientWidth) || document.body.clientWidth;
	var h = self.innerHeight || (de&&de.clientHeight) || document.body.clientHeight;
	$("#GB_window").css({
		width: GB_WIDTH + "px",
		height: GB_HEIGHT + "px",
		left: (((w - GB_WIDTH)/2) - 5) + "px",
		top: (((h - GB_HEIGHT) / 3) - 5) + "px"
	});
	$("#GB_frame").css({
		height: (GB_HEIGHT - 18) + "px"
	});
}
