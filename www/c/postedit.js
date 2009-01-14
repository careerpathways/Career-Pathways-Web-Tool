/**
 * Do not bind or act on any DOM elements until the page has sufficiently loaded
 */
$(document).ready(function()
{
	// Set the greybox animation speed ( in millis )
	GB_ANIMATION = 300;

	bindEditableCells();

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
		GB_show("Edit Row/Column Header", "/a/postserv.php?mode=prompt&type=header&content=" + escape($(this).html), 400, 300);
	});
	$(".post_cell:not(.post_cell_noClick)").click(function(){
		GB_show("Edit Row/Column Header", "/a/postserv.php?mode=prompt&type=cell&content=" + escape($(this).html), 400, 300);
	});
}