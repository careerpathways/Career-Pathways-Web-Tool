/**
 * Do not bind or act on any DOM elements until the page has sufficiently loaded
 */
$(document).ready(function()
{
	bindEditableCells();
	
	$(".post_head_main:not(.post_head_noClick), .post_cell, .post_footer").css({cursor: "pointer"});
	$(".post_cell a").attr("href", "javascript:void(0);");
});

/******************************/
/******* Editable Cells *******/
/******************************/

function bindEditableCells()
{
	// Set up the class to update on hover
	$(".post_head_main:not(.post_head_noClick), .post_cell, .post_footer").hover(function(){
		$(this).addClass("postCellHover");
	},function(){
		$(this).removeClass("postCellHover");
	});

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
	$(".post_cell").click(function(){
		// Split apart the id into meaningful components
		var cellID = $(this).attr("id").split("_");
		cellID = cellID[2];

		$.get("/a/postserv.php",
			{mode: "prompt", type: "cell", id: cellID},
			function(data){
				chGreybox.create(data, 450, 300);
		});
	});
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
}
