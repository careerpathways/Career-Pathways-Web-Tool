<?php
chdir("..");
require_once("inc.php");

	/**
	 * AJAX Handler for POST Drawings
	 */


	switch($_GET['mode'])
	{
		case "prompt":
?>
<div class="postGreyboxWrapper">
	<div class="postGreyboxContent">
<?php
			if($_GET['type'] == 'cell')
				printCellForm($_GET['id']);
			else
				printHeaderForm($_GET['id']);
?>
	</div>
</div>
<?php
		break;
		case "commit":
			if($_GET['type'] == 'cell')
				commitCell($_GET['id']);
			else
				commitHeader($_GET['id']);
		break;
		default:
			echo '<div style="greyboxError">This mode is not supported</div>';
		break;
	}//switch


	/***************************/
	/******* PROMPT MODE *******/
	/***************************/
	function printCellForm($id)
	{
		global $DB;

		$cell = $DB->SingleQuery("SELECT `post_drawing_main`.`type`, `content`, `href`, `course_subject`, `course_number`, `course_title`
			FROM `post_cell`
			LEFT JOIN `post_drawing_main` ON (`post_cell`.`drawing_id` = `post_drawing_main`.`id`)
			WHERE `post_cell`.`id` = '" . intval($id) . "'");

		// Draw the High School form
		if($cell['type'] == 'HS')
		{
?>
		<form action="javascript:void(0);">
			<div style="font-weight: bold;">Course Content:</div>
			<input type="text" id="postFormContent" style="width: 400px; border: 1px #AAA solid;" value="<?=$cell['content']?>" />
			<br /><br />
			<div style="font-weight: bold;">Link this Content:</div>
			URL: <input type="text" id="postFormURL" style="width: 300px; border: 1px #AAA solid;" value="<?=$cell['href']?>" />
			<br /><br />
			<div style="text-align: right;">
				<input type="button" id="postFormSave" value="Save" style="padding: 3px; background: #E0E0E0; border: 1px #AAA solid; font-weight: bold;" />
			</div>
		</form>
		<script language="JavaScript" type="text/javascript">
			$("#postFormSave").click(function(){
				$.ajax({
					type: "POST",
					url: "/a/postserv.php?mode=commit&type=cell&id=<?=$id?>",
					data: "content=" + $("#postFormContent").val() + "&href=" + $("#postFormURL").val(),
					success: function(data){
						$("#post_cell_<?=$id?>").html(data);
						chGreybox.close();
					}
				});
			});
		</script>
<?php
		}//if (high school)
		elseif($cell['type'] == 'CC')
		{
?>


<?php
		}//if (community college)
?>
		</form>
<?php
	}//end function printForm

	/**************************/
	/****** COMMIT CODE *******/
	/**************************/
	function commitCell($id)
	{
		global $DB;

		// Decide if we are drawing a link or not
		$href = $_POST['href'];
		$link = FALSE;
		if(isset($_POST['href']) && $_POST['href'] != '')
		{
			$link = TRUE;
			if(substr($href, 0, 7) != 'http://' && substr($href, 0, 8) != 'https://')
				$href = 'http://' . $_POST['href'];
		}

		// Update the database
		$DB->Update('post_cell', array('content' => $_POST['content'], 'href' => $href), intval($id));

		echo ($link?'<a href="javascript:void(0);">':'') . $_POST['content'] . ($link?'</a>':'');
	}//end function commitCell
	
	function commitHeader($id)
	{
		
	}//end function commitHeader
?>