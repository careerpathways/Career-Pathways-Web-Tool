<?php
header("Content-type: text/javascript");
?>

var clipboard = {
	clear: function(id){
		$.post("/a/postserv.php?mode=commit&type=cell&id="+id,
			{content: "", href:"", legend: ""},
			function(data){
				$("#post_cell_"+id).html(data);
				$("#post_cell_"+id).parent().css("background", "none");
				bindPostCells();
			}
		);
	},
	copy: function(id){
		$.get("/a/postserv.php?mode=fetch&type=cell&id="+id,
			  {},
			  function(cell){
				clipboard.data = cell;
			  },
			  "json");
	},
	paste: function(id){
		$.post("/a/postserv.php?mode=commit&type=cell&id="+id,
			{content: clipboard.data.content, href: clipboard.data.href, legend: clipboard.data.legend,
			 number: clipboard.data.course_number, title: clipboard.data.course_title,
			 subject: clipboard.data.course_subject, credits: clipboard.data.course_credits},
			function(data){
				$("#post_cell_"+id).html(data);
				var bgSwap = $("#post_cell_"+id).children().css("background");
				$("#post_cell_"+id).parent().css({"background" : bgSwap});
				$("#post_cell_"+id).children().css({"background" : "none"});
				bindPostCells();
			}
		);
	},
	data: {content: '', href: '', legend: '', course_number: '', course_title: '', course_subject:'', course_credits:''}
};

$(document).ready(function()
{
	// Bind all of our interactive elements
	bindEditableCells();

	$(".post_cell").contextMenu({
			menu: "contextMenu"
		},
		function(action, el, pos) {
			var cellID = $(el).find(".post_draggable").attr("id").split("_")[2];
			switch(action) {
				case "copy":
					clipboard.copy(cellID);
					break;
				case "cut":
					clipboard.copy(cellID);
					clipboard.clear(cellID);
					break;
				case "paste":
					clipboard.paste(cellID);
					break;
				case "clear":
					clipboard.clear(cellID);
					break;
			}
			bindEditableCells();
		}
	);

	// Add a cursor icon to all editable elements
	$(".post_head_main:not(.post_head_noClick), .post_cell, .post_footer, .post_headers .post_sidebar_right").css({cursor: "pointer"});

	// Suppress link following
	$(".post_cell a, .post_footer a, .post_headers a").attr("href", "javascript:void(0);").removeAttr('target');
});

/******************************/
/******* Editable Cells *******/
/******************************/

function bindEditableCells()
{
	// This is in its own function because the POST cells behave much differently than headers and footers
	bindPostCells();

	// Make the headers editable
	$(".post_head_main:not(.post_head_noClick)").unbind("click").click(function(){
		// Split apart the id into meaningful components
		var headID = $(this).attr("id").split("_")[2];

		$.get("/a/postserv.php",
			{mode: "prompt", type: "head", id: headID},
			function(data){
				chGreybox.create(data, 450, 300);
				chGreybox.onClose = function() {
				    bindPostCells();
				    tinyMCE.execCommand('mceRemoveControl', false, 'postFormContent');
				};
		}, "html");
	});

	// Make the row titles editable
	$(".post_row_editable").unbind("click").click(function(){
		// Split apart the id into meaningful components
		var rowID = $(this).attr("id").split("_")[2];

		$.get("/a/postserv.php",
			{mode: "prompt", type: "rowTitle", id: rowID},
			function(data){
				chGreybox.create(data, 450, 300);
				chGreybox.onClose = function() {
                    tinyMCE.execCommand('mceRemoveControl', false, 'postFormContent');
				    bindPostCells();
				};
		}, "html");
	});

	// Make the footer editable
	$(".post_footer").unbind("click").click(function(){
		// Split apart the id into meaningful components
		var footerID = $(this).attr("id").split("_")[2];

		$.get("/a/postserv.php",
			{mode: "prompt", type: "footer", id: footerID},
			function(data){
				chGreybox.create(data, 450, 300);
				chGreybox.onClose = function() {
				    tinyMCE.execCommand('mceRemoveControl', false, 'postFormContent');
				    bindPostCells();
				};
		}, "html");
	});

	// Make the header editable
	$(".post_headers").unbind("click").click(function(){
		// Split apart the id into meaningful components
		var headerID = $(this).attr("id").split("_")[2];

		$.get("/a/postserv.php",
			{mode: "prompt", type: "header", id: headerID},
			function(data){
				chGreybox.create(data, 450, 300);
				chGreybox.onClose = function() {
				    tinyMCE.execCommand('mceRemoveControl', false, 'postFormContent');
				    bindPostCells();
				};
		}, "html");
	});

	// Make the sidebar editable
	$(".post_sidebar_right").unbind("click").click(function(){
		// Split apart the id into meaningful components
		var versionID = $(this).attr("id").split("_")[1];

		$.get("/a/postserv.php",
			{mode: "prompt", type: "sidebar_right", id: versionID},
			function(data){
				chGreybox.create(data, 450, 300);
				chGreybox.onClose = function() {
				    tinyMCE.execCommand('mceRemoveControl', false, 'postFormContent');
				    bindPostCells();
				};
		}, "html");
	});

}//end function bindEditableCells

function bindPostCells()
{
	// The event is on 'mouseup', not 'click', so we can also overload each cell to be draggable
	$(".post_cell").unbind('mouseup').bind('mouseup', function(e) {

		if( e.button == 2 )
		{
			// prevent right-clicks from triggering this
			return false;
		}

		// Split apart the id into meaningful components
		var cellID = $(this).find(".post_draggable").attr("id").split("_")[2];

		// Make the call to Greybox in and prompt
		$.get("/a/postserv.php",
			{mode: "prompt", type: "cell", id: cellID},
			function(data){
				chGreybox.create(data, 450, 300);
				chGreybox.onClose = function() {
				    tinyMCE.execCommand('mceRemoveControl', false, 'postFormContent');
				    bindPostCells();
				};
				$(document).keydown( function(e) {
					if( e.which == 27 )
					{
						chGreybox.close();
						$(document).keypress( function(e) {} );
					}
				});
		}, "html");
		
		return true;
	});

	// Droppable cells can have any other cell dragged into them
	$(".post_cell").droppable({
		hoverClass : "post_cell_dragHover",
		activeClass : "post_cell_dragHover",
		drop : function(e, ui){
			// Grab a copy of the IDs for both the objects we're interacting with
			var ele = $(this).find(".post_draggable");
			var toID = $(ele).attr("id").split("_");
			toID = toID[2];
			var fromID = $(ui.draggable).attr("id").split("_");
			fromID = fromID[2];

			// Write the changes to the database
			$.post("/a/postserv.php?mode=commit&type=swap", {"fromID": fromID, "toID": toID}, function(){
				var fromHTML = $("#post_cell_" + fromID).html();
				var toHTML = $("#post_cell_" + toID).html();
				var fromBG = $("#post_cell_" + fromID).parent().css("background");
				var toBG = $("#post_cell_" + toID).parent().css("background");
				
				// IE didn't like fromBG being undefined, but as an empty string it will clear out the background
				if( !fromBG ) fromBG = '';
				if( !toBG ) toBG = '';

				// If I were to animate these cells swapping, it would totally be here.
				$("#post_cell_" + toID).parent().empty().html('<div id="post_cell_' + fromID + '" class="post_draggable">' + fromHTML + '</div>').css({"background" : fromBG });
				$(ui.draggable).parent().empty().html('<div id="post_cell_' + toID + '" class="post_draggable">' + toHTML + '</div>').css({"background" : toBG });

				bindPostCells();
			});
		}
	});

	// Draggable cells can be dropped into droppable cells
	$(".post_draggable").draggable({
		start : function() {
			$(".post_cell").unbind('mouseup');
			$(".post_head_main:not(.post_head_noClick), .post_cell, .post_footer, .post_headers").hover(function(){},function(){});
			$(this).css({ border: "1px #777777 solid" })
		},
		helper : 'original',
		opacity : 0.5,
		revert : "invalid",
		stop: function(){
			$(this).css({ border: "none" });
			bindPostCells();
		}
	});

	// Set up the class to update on hover
	$(".post_head_main:not(.post_head_noClick), .post_cell, .post_footer, .post_headers, .post_sidebar_right, .post_row_editable").hover(function(){
		$(this).addClass("post_cell_hover");
	},function(){
		$(this).removeClass("post_cell_hover");
	});

	// clear out any lingering hovers
	$(".post_head_main:not(.post_head_noClick), .post_cell, .post_footer, .post_headers, .post_sidebar_right").removeClass("post_cell_hover");

}//end function bindPostCells
