/**
 * Do not bind or act on any DOM elements until the page has sufficiently loaded
 */
$(document).ready(function()
{
	// Bind all of our interactive elements
	bindEditableCells();

	// Add a cursor icon to all editable elements
	$(".post_head_main:not(.post_head_noClick), .post_cell, .post_footer").css({cursor: "pointer"});

	// Suppress link following
	$(".post_cell a").attr("href", "javascript:void(0);");
});

/******************************/
/******* Editable Cells *******/
/******************************/

function bindEditableCells()
{
	// This is in its own function because the POST cells behave much differently than headers and footers
	bindPostCells();

	// Make the headers editable
	$(".post_head_main:not(.post_head_noClick)").click(function(){
		// Split apart the id into meaningful components
		var headID = $(this).attr("id").split("_");
		headID = headID[2];

		$.get("/a/postserv.php",
			{mode: "prompt", type: "head", id: headID},
			function(data){
			chGreybox.create(data, 450, 300);
		});
	});

	// Make the footer editable
	$(".post_footer").click(function(){
		// Split apart the id into meaningful components
		var footerID = $(this).attr("id").split("_");
		footerID = footerID[2];

		$.get("/a/postserv.php",
			{mode: "prompt", type: "footer", id: footerID},
			function(data){
				chGreybox.create(data, 450, 300);
		});
	});
	
}//end function bindEditableCells

function bindPostCells()
{
	// The event is on 'mouseup', not 'click', so we can also overload each cell to be draggable
	$(".post_cell").unbind('mouseup').bind('mouseup', function(e) {
	
		if( e.button != 0 )
		{
			// prevent right-clicks from triggering this
			return false;
		}
	
		// Split apart the id into meaningful components
		var cellID = $(this).children().attr("id").split("_");
		cellID = cellID[2];

		// Make the call to Greybox in and prompt
		$.get("/a/postserv.php",
			{mode: "prompt", type: "cell", id: cellID},
			function(data){
				chGreybox.create(data, 450, 300);
				$(document).keydown( function(e) {
					if( e.which == 27 )
					{
						chGreybox.close();
						$(document).keypress( function(e) {} );
					}
				});
		});
		
		return true;
	});

	// Droppable cells can have any other cell dragged into them
	$(".post_cell").droppable({
		hoverClass : "post_cell_dragHover",
		activeClass : "post_cell_dragHover",
		drop : function(e, ui){
			// Grab a copy of the IDs for both the objects we're interacting with
			var ele = $(this).children();
			var toID = $(ele).attr("id").split("_");
			toID = toID[2];
			var fromID = $(ui.draggable).attr("id").split("_");
			fromID = fromID[2];

			// Write the changes to the database
			$.post("/a/postserv.php?mode=commit&type=swap", {"fromID": fromID, "toID": toID}, function(){
				var fromHTML = $("#post_cell_" + fromID).html();
				var toHTML = $("#post_cell_" + toID).html();

				// If I were to animate these cells swapping, it would totally be here.
				$("#post_cell_" + toID).parent().empty().html('<div id="post_cell_' + fromID + '" class="post_draggable">' + fromHTML + '</div>');
				$(ui.draggable).parent().empty().html('<div id="post_cell_' + toID + '" class="post_draggable">' + toHTML + '</div>');
		
				bindPostCells();
			});
		}
	});

	// Draggable cells can be dropped into droppable cells
	$(".post_draggable").draggable({
		start : function() {
			$(".post_cell").unbind('mouseup');
			$(".post_head_main:not(.post_head_noClick), .post_cell, .post_footer").hover(function(){},function(){});
			$(this).css({ background: "#FFFFFF", border: "1px #777777 solid" })
		},
		helper : 'original',
		opacity : 0.5,
		revert : "invalid",
		stop: function(){
			$(this).css({ background: "none", border: "none" });
			bindPostCells();
		}
	});

	// Set up the class to update on hover
	$(".post_head_main:not(.post_head_noClick), .post_cell, .post_footer").hover(function(){
		$(this).addClass("post_cell_hover");
	},function(){
		$(this).removeClass("post_cell_hover");
	});

	// Reset the heights of all the draggable DIVs and fill the parent element
	$.each($(".post_draggable"), function()
	{
		// Reset each element's size so we can get an accurate read
		$(this).css({
			"padding-top" : 0,
			height : "auto"
		});

		// Scan the cell for its width and its parent's width
		var eleHeight = $(this).height();
		var parHeight = $(this).parent().height();

		// Calculate our top padding
		var padTop = Math.round((parHeight/2) - (eleHeight/2));

		$(this).css({
			height : ($(this).parent().height() - padTop),
			"padding-top" : padTop,
			"vertical-align" : "middle"
		});
	});
}//end function bindPostCells