/**
 * Do not bind or act on any DOM elements until the page has sufficiently loaded
 */
$(document).ready(function()
{
	bindEditableCells();
	
	$(".post_cell, .post_head_main").css({cursor: "pointer"});
	$(".post_cell_noClick").css({cursor: "auto"});
	
	$(".post_cell a").attr("href", "javascript:void(0);");
});

/******************************/
/******* Editable Cells *******/
/******************************/

function bindEditableCells()
{
	// Set up the class to update on hover
	$(".post_head_main, .post_cell:not(.post_cell_noClick)").hover(function(){
		$(this).addClass("postCellHover");
	},function(){
		$(this).removeClass("postCellHover");
	});

	$(".post_head_main").click(function(){
		// Split apart the id into meaningful components
		var id = $(this).attr("id").split("_");
		var category = id[2];
		id = id[3];

		$.get("/a/postserv.php",
			{mode: "prompt", type: "header", content: escape($(this).html())},
			function(data){
			chGreybox.create(data, 450, 300);
		});
	});
	$(".post_cell:not(.post_cell_noClick)").click(function(){
		// Split apart the id into meaningful components
		var cellID = $(this).attr("id").split("_");
		cellID = cellID[2];

		$.get("/a/postserv.php",
			{mode: "prompt", type: "cell", id: cellID},
			function(data){
			chGreybox.create(data, 450, 300);
		});
	});
}
